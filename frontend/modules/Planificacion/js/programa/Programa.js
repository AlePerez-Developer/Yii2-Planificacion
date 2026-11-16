let programaFiltroTexto = ''
let programaFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaPrograma(etiqueta) {
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

function aplicarFiltrosTablaPrograma() {
    if (typeof dt_programa === 'undefined' || !dt_programa) {
        return
    }
    dt_programa.draw()
}

function sincronizarFiltroEstadoPrograma(codigoEstado) {
    programaFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoPrograma').val(programaFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (programaFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaPrograma(statEtiqueta) === programaFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaPrograma()
}

function initProgramaListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__programaListadoFiltro === true
    })) {
        aplicarFiltrosTablaPrograma()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaProgramas') {
            return true
        }

        const row = dt_programa.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (programaFiltroCodigoEstado && row.CodigoEstado !== programaFiltroCodigoEstado) {
            return false
        }

        if (!programaFiltroTexto) {
            return true
        }

        const termino = programaFiltroTexto.toLowerCase()
        const codigo = (row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()

        return codigo.indexOf(termino) !== -1 || descripcion.indexOf(termino) !== -1
    }
    filtroFn.__programaListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaPrograma(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoPrograma('')
            return
        }

        sincronizarFiltroEstadoPrograma(codigo)
    })

    $('#buscarPrograma').on('input', function () {
        programaFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaPrograma()
    })

    $('#filtroEstadoPrograma').on('change', function () {
        sincronizarFiltroEstadoPrograma($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idPrograma = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/programa/'
    const dtEvents = $('#tablaListaProgramas')
    const $modalPrograma = $('#modalPrograma')

    function reiniciarCampos() {
        const validator = $('#formPrograma').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formPrograma *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formPrograma').trigger('reset')
        idPrograma = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del programa se ${accion}ron correctamente.`
    }

    function tituloModalPrograma(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalPrograma(esEdicion) {
        $('#programaModalTitulo').text(tituloModalPrograma(esEdicion))
        $modalPrograma.modal('show')
    }

    function cerrarModalPrograma() {
        reiniciarCampos()
        $modalPrograma.modal('hide')
    }

    async function guardarFormularioPrograma(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formPrograma').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idPrograma', idPrograma)
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idPrograma !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_programa !== 'undefined' ? dt_programa : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresPrograma === 'function') {
                        actualizarContadoresPrograma()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#programaModalTitulo').text(tituloModalPrograma(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoPrograma').on('click', function () {
        reiniciarCampos()
        abrirModalPrograma(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalPrograma()
    })

    $modalPrograma.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioPrograma({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioPrograma({
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
        cerrarMenusEstadoPrograma()
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
        const rowApi = dt_programa.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idPrograma', dt_row.IdPrograma)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresPrograma()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_programa.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdPrograma

        const datos = new FormData()
        datos.append('idPrograma', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el programa seleccionado?',
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
                    dt_programa.ajax.reload(function () {
                        actualizarContadoresPrograma()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_programa.row(objectBtn.closest('tr')).data()
        idPrograma = dt_row.IdPrograma

        const datos = new FormData()
        datos.append('idPrograma', idPrograma)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalPrograma(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formPrograma').validate({
        rules: {
            codigo: {
                required: true,
                minlength: 3,
                maxlength: 3,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idPrograma: function () {
                            return idPrograma
                        }
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
        },
        messages: {
            codigo: {
                required: 'Debe ingresar un código de programa',
                minlength: 'El código debe ser de 3 dígitos',
                maxlength: 'El código debe ser de 3 dígitos',
                remote: 'El código ingresado ya se encuentra en uso'
            },
            descripcion: {
                required: 'Debe ingresar la descripción del programa',
                minlength: 'La descripción debe tener por lo menos 2 caracteres',
                maxlength: 'La descripción debe tener máximo 500 caracteres',
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
