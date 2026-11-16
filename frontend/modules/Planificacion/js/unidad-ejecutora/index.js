let ueFiltroTexto = ''
let ueFiltroCodigoEstado = ''
let ueFiltroIdDa = ''

function codigoEstadoDesdeEtiquetaUe(etiqueta) {
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

function aplicarFiltrosTablaUe() {
    if (typeof dt_ue === 'undefined' || !dt_ue) {
        return
    }
    dt_ue.draw()
}

function sincronizarFiltroEstadoUe(codigoEstado) {
    ueFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoUe').val(ueFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (ueFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaUe(statEtiqueta) === ueFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaUe()
}

function initUeListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__ueListadoFiltro === true
    })) {
        aplicarFiltrosTablaUe()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaUes') {
            return true
        }

        const row = dt_ue.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (ueFiltroIdDa && row.IdDa !== ueFiltroIdDa) {
            return false
        }

        if (ueFiltroCodigoEstado && row.CodigoEstado !== ueFiltroCodigoEstado) {
            return false
        }

        if (!ueFiltroTexto) {
            return true
        }

        const termino = ueFiltroTexto.toLowerCase()
        const ue = (row.Ue || '').toLowerCase()
        const compuesto = (row.Compuesto || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const da = (row.Da || '').toLowerCase()
        const daDesc = ((row.das && row.das.Descripcion) ? row.das.Descripcion : '').toLowerCase()

        return ue.indexOf(termino) !== -1
            || compuesto.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || da.indexOf(termino) !== -1
            || daDesc.indexOf(termino) !== -1
    }
    filtroFn.__ueListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaUe(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoUe('')
            return
        }

        sincronizarFiltroEstadoUe(codigo)
    })

    $('#buscarUe').on('input', function () {
        ueFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaUe()
    })

    $('#filtroEstadoUe').on('change', function () {
        sincronizarFiltroEstadoUe($(this).val())
    })

    $('#filtroDa').on('change', function () {
        ueFiltroIdDa = $(this).val() || ''
        aplicarFiltrosTablaUe()
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idUe = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/unidad-ejecutora/'
    const dtEvents = $('#tablaListaUes')
    const $modalUe = $('#modalUe')

    function reiniciarCampos() {
        const validator = $('#formUe').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formUe *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        if (typeof unidadEjecutora_s2Da !== 'undefined') {
            unidadEjecutora_s2Da.val(null).trigger('change')
        }
        $('#formUe').trigger('reset')
        idUe = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la Unidad se ${accion}ron correctamente.`
    }

    function tituloModalUe(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalUe(esEdicion) {
        $('#ueModalTitulo').text(tituloModalUe(esEdicion))
        $modalUe.modal('show')
    }

    function cerrarModalUe() {
        reiniciarCampos()
        $modalUe.modal('hide')
    }

    async function guardarFormularioUe(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formUe').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idUnidadEjecutora', idUe)
        datos.append('idDa', $('#idDa').val())
        datos.append('ue', $('#ue').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idUe !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_ue !== 'undefined' ? dt_ue : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresUe === 'function') {
                        actualizarContadoresUe()
                    }
                    if (typeof popularFiltroDaUe === 'function') {
                        popularFiltroDaUe()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#ueModalTitulo').text(tituloModalUe(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoUe').on('click', function () {
        reiniciarCampos()
        abrirModalUe(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalUe()
    })

    $modalUe.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioUe({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioUe({
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
        cerrarMenusEstadoUe()
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
        const rowApi = dt_ue.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idUnidadEjecutora', dt_row.IdUnidadEjecutora)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresUe()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_ue.row(objectBtn.closest('tr')).data()
        const idUnidad = dt_row.IdUnidadEjecutora

        const datos = new FormData()
        datos.append('idUnidadEjecutora', idUnidad)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la Unidad seleccionada?',
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
                    dt_ue.ajax.reload(function () {
                        actualizarContadoresUe()
                        popularFiltroDaUe()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_ue.row(objectBtn.closest('tr')).data()
        idUe = dt_row.IdUnidadEjecutora

        const datos = new FormData()
        datos.append('idUnidadEjecutora', idUe)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                if (typeof unidadEjecutora_s2Da !== 'undefined') {
                    unidadEjecutora_s2Da.val(registro.IdDa).trigger('change.select2')
                }
                $('#ue').val(registro.Ue)
                $('#descripcion').val(registro.Descripcion)
                abrirModalUe(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formUe').validate({
        ignore: [],
        rules: {
            idDa: {
                required: true
            },
            ue: {
                required: true,
                minlength: 3,
                maxlength: 3,
                require_from_group: [2, '.codigo_group'],
                pattern: /^\d{3}$/,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        idDa: function () {
                            return $('#idDa').val()
                        },
                        ue: function () {
                            return $('#ue').val()
                        },
                        idUnidadEjecutora: function () {
                            return idUe
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
            idDa: {
                required: 'Debe seleccionar una Dirección Administrativa'
            },
            ue: {
                required: 'Debe ingresar un valor a la Unidad',
                minlength: 'El código debe ser de 3 dígitos',
                maxlength: 'El código debe ser de 3 dígitos',
                remote: 'El código ingresado ya se encuentra en uso',
                require_from_group: 'Debe seleccionar una DA para validar el valor de UE',
                pattern: 'El valor debe tener 3 dígitos de UE',
            },
            descripcion: {
                required: 'Debe ingresar la descripción de la unidad',
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
