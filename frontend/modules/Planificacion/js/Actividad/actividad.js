let actividadFiltroTexto = ''
let actividadFiltroCodigoEstado = ''
let actividadFiltroIdPrograma = ''

function codigoEstadoDesdeEtiquetaActividad(etiqueta) {
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

function aplicarFiltrosTablaActividad() {
    if (typeof dt_actividad === 'undefined' || !dt_actividad) {
        return
    }
    dt_actividad.draw()
}

function sincronizarFiltroEstadoActividad(codigoEstado) {
    actividadFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoActividad').val(actividadFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (actividadFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaActividad(statEtiqueta) === actividadFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaActividad()
}

function initActividadListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__actividadListadoFiltro === true
    })) {
        aplicarFiltrosTablaActividad()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaActividades') {
            return true
        }

        const row = dt_actividad.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (actividadFiltroIdPrograma && row.IdPrograma !== actividadFiltroIdPrograma) {
            return false
        }

        if (actividadFiltroCodigoEstado && row.CodigoEstado !== actividadFiltroCodigoEstado) {
            return false
        }

        if (!actividadFiltroTexto) {
            return true
        }

        const termino = actividadFiltroTexto.toLowerCase()
        const codigo = (row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const progCodigo = ((row.programa && row.programa.Codigo) ? row.programa.Codigo : (row.codex || '')).toLowerCase()
        const progDesc = ((row.programa && row.programa.Descripcion) ? row.programa.Descripcion : '').toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || progCodigo.indexOf(termino) !== -1
            || progDesc.indexOf(termino) !== -1
    }
    filtroFn.__actividadListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaActividad(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoActividad('')
            return
        }

        sincronizarFiltroEstadoActividad(codigo)
    })

    $('#buscarActividad').on('input', function () {
        actividadFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaActividad()
    })

    $('#filtroEstadoActividad').on('change', function () {
        sincronizarFiltroEstadoActividad($(this).val())
    })

    $('#filtroPrograma').on('change', function () {
        actividadFiltroIdPrograma = $(this).val() || ''
        aplicarFiltrosTablaActividad()
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idActividad = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/actividad/'
    const dtEvents = $('#tablaListaActividades')
    const $modalActividad = $('#modalActividad')

    function reiniciarCampos() {
        const validator = $('#formActividad').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formActividad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        if (typeof actividad_s2Programa !== 'undefined') {
            actividad_s2Programa.val(null).trigger('change')
        }
        $('#formActividad').trigger('reset')
        idActividad = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la actividad se ${accion}ron correctamente.`
    }

    function tituloModalActividad(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalActividad(esEdicion) {
        $('#actividadModalTitulo').text(tituloModalActividad(esEdicion))
        $modalActividad.modal('show')
    }

    function cerrarModalActividad() {
        reiniciarCampos()
        $modalActividad.modal('hide')
    }

    async function guardarFormularioActividad(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formActividad').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idActividad', idActividad)
        datos.append('idPrograma', $('#idPrograma').val())
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idActividad !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_actividad !== 'undefined' ? dt_actividad : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresActividad === 'function') {
                        actualizarContadoresActividad()
                    }
                    if (typeof popularFiltroProgramaActividad === 'function') {
                        popularFiltroProgramaActividad()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#actividadModalTitulo').text(tituloModalActividad(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoActividad').on('click', function () {
        reiniciarCampos()
        abrirModalActividad(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalActividad()
    })

    $modalActividad.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioActividad({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioActividad({
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
        cerrarMenusEstadoActividad()
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
        const rowApi = dt_actividad.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idActividad', dt_row.IdActividad)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresActividad()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_actividad.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdActividad

        const datos = new FormData()
        datos.append('idActividad', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la actividad seleccionada?',
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
                    dt_actividad.ajax.reload(function () {
                        actualizarContadoresActividad()
                        popularFiltroProgramaActividad()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_actividad.row(objectBtn.closest('tr')).data()
        idActividad = dt_row.IdActividad

        const datos = new FormData()
        datos.append('idActividad', idActividad)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                if (typeof actividad_s2Programa !== 'undefined') {
                    actividad_s2Programa.val(registro.IdPrograma).trigger('change.select2')
                }
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalActividad(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formActividad').validate({
        ignore: [],
        rules: {
            idPrograma: {
                required: true
            },
            codigo: {
                required: true,
                minlength: 3,
                maxlength: 3,
                require_from_group: [2, '.codigo_group'],
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idPrograma: function () {
                            return $('#idPrograma').val()
                        },
                        idActividad: function () {
                            return idActividad
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
            idPrograma: {
                required: 'Debe seleccionar un programa'
            },
            codigo: {
                required: 'Debe ingresar un código de actividad',
                require_from_group: 'Debe seleccionar un programa para validar el código',
                minlength: 'El código debe tener exactamente 3 dígitos',
                maxlength: 'El código debe tener exactamente 3 dígitos',
                remote: 'El código ingresado ya se encuentra en uso'
            },
            descripcion: {
                required: 'Debe ingresar la descripción de la actividad',
                minlength: 'La descripción debe tener por lo menos 2 caracteres',
                maxlength: 'La descripción debe tener máximo 500 caracteres',
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback')
            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'))
            } else {
                error.insertAfter(element)
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid')
        },
        unhighlight: function (element) {
            $(element).addClass('is-valid').removeClass('is-invalid')
        }
    })
})
