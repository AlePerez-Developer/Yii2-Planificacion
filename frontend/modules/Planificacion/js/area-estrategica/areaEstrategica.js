let areaFiltroTexto = ''
let areaFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaArea(etiqueta) {
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

function aplicarFiltrosTablaArea() {
    if (typeof dt_area === 'undefined' || !dt_area) {
        return
    }
    dt_area.draw()
}

function sincronizarFiltroEstadoArea(codigoEstado) {
    areaFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoArea').val(areaFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (areaFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaArea(statEtiqueta) === areaFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaArea()
}

function initAreaListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__areaListadoFiltro === true
    })) {
        aplicarFiltrosTablaArea()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaAreas') {
            return true
        }

        const row = dt_area.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (areaFiltroCodigoEstado && row.CodigoEstado !== areaFiltroCodigoEstado) {
            return false
        }

        if (!areaFiltroTexto) {
            return true
        }

        const termino = areaFiltroTexto.toLowerCase()
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()

        return codigo.indexOf(termino) !== -1 || descripcion.indexOf(termino) !== -1
    }
    filtroFn.__areaListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaArea(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoArea('')
            return
        }

        sincronizarFiltroEstadoArea(codigo)
    })

    $('#buscarArea').on('input', function () {
        areaFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaArea()
    })

    $('#filtroEstadoArea').on('change', function () {
        sincronizarFiltroEstadoArea($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idAreaEstrategica = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/area-estrategica/'
    const dtEvents = $('#tablaListaAreas')
    const $modalArea = $('#modalAreaEstrategica')

    function reiniciarCampos() {
        const validator = $('#formAreaEstrategica').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formAreaEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formAreaEstrategica').trigger('reset')
        idAreaEstrategica = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del área estratégica se ${accion}ron correctamente.`
    }

    function tituloModalArea(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalArea(esEdicion) {
        $('#areaModalTitulo').text(tituloModalArea(esEdicion))
        $modalArea.modal('show')
    }

    function cerrarModalArea() {
        reiniciarCampos()
        $modalArea.modal('hide')
    }

    async function guardarFormularioArea(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formAreaEstrategica').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idAreaEstrategica', idAreaEstrategica)
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idAreaEstrategica !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_area !== 'undefined' ? dt_area : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresArea === 'function') {
                        actualizarContadoresArea()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#areaModalTitulo').text(tituloModalArea(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoArea').on('click', function () {
        reiniciarCampos()
        abrirModalArea(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalArea()
    })

    $modalArea.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioArea({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioArea({
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
        cerrarMenusEstadoArea()
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
        const rowApi = dt_area.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idAreaEstrategica', dt_row.IdAreaEstrategica)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresArea()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_area.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdAreaEstrategica

        const datos = new FormData()
        datos.append('idAreaEstrategica', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el área estratégica seleccionada?',
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
                    dt_area.ajax.reload(function () {
                        actualizarContadoresArea()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_area.row(objectBtn.closest('tr')).data()
        idAreaEstrategica = dt_row.IdAreaEstrategica

        const datos = new FormData()
        datos.append('idAreaEstrategica', idAreaEstrategica)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalArea(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formAreaEstrategica').validate({
        rules: {
            codigo: {
                required: true,
                digits: true,
                range: [1, 9],
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idAreaEstrategica: function () {
                            return idAreaEstrategica
                        }
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            codigo: {
                required: 'Debe ingresar un código de área estratégica',
                digits: 'El código solo debe ser numérico',
                range: 'El código debe estar comprendido entre 1 y 9',
                remote: 'El código ingresado ya se encuentra en uso'
            },
            descripcion: {
                required: 'Debe ingresar una descripción del área estratégica',
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
