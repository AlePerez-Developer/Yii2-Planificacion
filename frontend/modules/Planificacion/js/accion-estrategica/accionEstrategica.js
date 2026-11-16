let accionFiltroTexto = ''
let accionFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaAccion(etiqueta) {
    const texto = (etiqueta || '').trim().toLowerCase()
    if (!texto || texto.indexOf('todos') === 0) {
        return ''
    }
    if (texto.indexOf('vigent') !== -1) {
        return ESTADO_VIGENTE
    }
    if (texto.indexOf('cancel') !== -1 || texto.indexOf('caduc') !== -1) {
        return ESTADO_CADUCO
    }
    return ''
}

function aplicarFiltrosTablaAccion() {
    if (typeof dt_accion === 'undefined' || !dt_accion) {
        return
    }
    dt_accion.draw()
}

function sincronizarFiltroEstadoAccion(codigoEstado) {
    accionFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoAccion').val(accionFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (accionFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaAccion(statEtiqueta) === accionFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaAccion()
}

function initAccionListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__accionListadoFiltro === true
    })) {
        aplicarFiltrosTablaAccion()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaAccionesEstrategicas') {
            return true
        }

        const row = dt_accion.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (accionFiltroCodigoEstado && row.CodigoEstado !== accionFiltroCodigoEstado) {
            return false
        }

        if (!accionFiltroTexto) {
            return true
        }

        const termino = accionFiltroTexto.toLowerCase()
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()

        return codigo.indexOf(termino) !== -1 || descripcion.indexOf(termino) !== -1
    }
    filtroFn.__accionListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaAccion(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoAccion('')
            return
        }

        sincronizarFiltroEstadoAccion(codigo)
    })

    $('#buscarAccion').on('input', function () {
        accionFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaAccion()
    })

    $('#filtroEstadoAccion').on('change', function () {
        sincronizarFiltroEstadoAccion($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idAccionEstrategica = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/accion-estrategica/'
    const dtEvents = $('#tablaListaAccionesEstrategicas')
    const $modalAccion = $('#modalAccionEstrategica')

    function reiniciarCampos() {
        const validator = $('#formAccionEstrategica').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formAccionEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formAccionEstrategica').trigger('reset')
        idAccionEstrategica = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la acción estratégica se ${accion}ron correctamente.`
    }

    function tituloModalAccion(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalAccion(esEdicion) {
        $('#accionModalTitulo').text(tituloModalAccion(esEdicion))
        $modalAccion.modal('show')
    }

    function cerrarModalAccion() {
        reiniciarCampos()
        $modalAccion.modal('hide')
    }

    async function guardarFormularioAccion(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formAccionEstrategica').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idAccionEstrategica', idAccionEstrategica)
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idAccionEstrategica !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_accion !== 'undefined' ? dt_accion : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresAccion === 'function') {
                        actualizarContadoresAccion()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#accionModalTitulo').text(tituloModalAccion(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoAccion').on('click', function () {
        reiniciarCampos()
        abrirModalAccion(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalAccion()
    })

    $modalAccion.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioAccion({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioAccion({
            spinnerBtn: $(this),
            cerrarAlExito: false,
            continuar: true
        })
    })

    dtEvents.on('click', '.estado-trigger', function (e) {
        e.preventDefault()
        e.stopPropagation()
        const wrap = $(this).closest('.estado-wrap')
        const abierto = wrap.hasClass('open')
        cerrarMenusEstadoAccion()
        wrap.toggleClass('open', !abierto)
    })

    dtEvents.on('click', '.estado-menu', function (e) {
        e.stopPropagation()
    })

    dtEvents.on('click', '.estado-option', async function (e) {
        e.preventDefault()
        e.stopPropagation()

        const option = $(this)
        const wrap = option.closest('.estado-wrap')
        const tr = option.closest('tr')
        const rowApi = dt_accion.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idAccionEstrategica', dt_row.IdAccionEstrategica)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresAccion()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_accion.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdAccionEstrategica

        const datos = new FormData()
        datos.append('idAccionEstrategica', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la acción estratégica seleccionada?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + 'eliminar',
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                    })
                    dt_accion.ajax.reload(function () {
                        actualizarContadoresAccion()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_accion.row(objectBtn.closest('tr')).data()
        idAccionEstrategica = dt_row.IdAccionEstrategica

        const datos = new FormData()
        datos.append('idAccionEstrategica', idAccionEstrategica)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalAccion(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formAccionEstrategica').validate({
        rules: {
            codigo: {
                required: true,
                digits: true
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            codigo: {
                required: 'Debe ingresar un código de acción estratégica',
                digits: 'El código debe ser un valor numérico'
            },
            descripcion: {
                required: 'Debe ingresar una descripción de la acción estratégica',
                minlength: 'La descripción debe tener al menos 2 caracteres',
                maxlength: 'La descripción debe tener máximo 500 caracteres'
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback')
            error.insertAfter(element)
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid')
        },
        unhighlight: function (element) {
            $(element).addClass('is-valid').removeClass('is-invalid')
        }
    })
})
