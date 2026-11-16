let politicaFiltroTexto = ''
let politicaFiltroCodigoEstado = ''
let politicaFiltroIdArea = ''

function codigoEstadoDesdeEtiquetaPolitica(etiqueta) {
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

function aplicarFiltrosTablaPolitica() {
    if (typeof dt_politica === 'undefined' || !dt_politica) {
        return
    }
    dt_politica.draw()
}

function sincronizarFiltroEstadoPolitica(codigoEstado) {
    politicaFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoPolitica').val(politicaFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (politicaFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaPolitica(statEtiqueta) === politicaFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaPolitica()
}

function initPoliticaListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__politicaListadoFiltro === true
    })) {
        aplicarFiltrosTablaPolitica()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaPoliticas') {
            return true
        }

        const row = dt_politica.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (politicaFiltroIdArea && row.IdAreaEstrategica !== politicaFiltroIdArea) {
            return false
        }

        if (politicaFiltroCodigoEstado && row.CodigoEstado !== politicaFiltroCodigoEstado) {
            return false
        }

        if (!politicaFiltroTexto) {
            return true
        }

        const termino = politicaFiltroTexto.toLowerCase()
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const areaCodigo = String((row.areaEstrategica && row.areaEstrategica.Codigo) ? row.areaEstrategica.Codigo : '').toLowerCase()
        const areaDesc = ((row.areaEstrategica && row.areaEstrategica.Descripcion) ? row.areaEstrategica.Descripcion : '').toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || areaCodigo.indexOf(termino) !== -1
            || areaDesc.indexOf(termino) !== -1
    }
    filtroFn.__politicaListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaPolitica(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoPolitica('')
            return
        }

        sincronizarFiltroEstadoPolitica(codigo)
    })

    $('#buscarPolitica').on('input', function () {
        politicaFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaPolitica()
    })

    $('#filtroEstadoPolitica').on('change', function () {
        sincronizarFiltroEstadoPolitica($(this).val())
    })

    $('#filtroArea').on('change', function () {
        politicaFiltroIdArea = $(this).val() || ''
        aplicarFiltrosTablaPolitica()
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idPoliticaEstrategica = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/politica-estrategica/'
    const dtEvents = $('#tablaListaPoliticas')
    const $modalPolitica = $('#modalPoliticaEstrategica')

    function reiniciarCampos() {
        const validator = $('#formPoliticaEstrategica').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formPoliticaEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        if (typeof politicas_s2AreasEstrategicas !== 'undefined') {
            politicas_s2AreasEstrategicas.val(null).trigger('change')
        }
        $('#formPoliticaEstrategica').trigger('reset')
        idPoliticaEstrategica = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la política estratégica se ${accion}ron correctamente.`
    }

    function tituloModalPolitica(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalPolitica(esEdicion) {
        $('#politicaModalTitulo').text(tituloModalPolitica(esEdicion))
        $modalPolitica.modal('show')
    }

    function cerrarModalPolitica() {
        reiniciarCampos()
        $modalPolitica.modal('hide')
    }

    async function guardarFormularioPolitica(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formPoliticaEstrategica').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idPoliticaEstrategica', idPoliticaEstrategica)
        datos.append('idAreaEstrategica', $('#idAreaEstrategica').val())
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idPoliticaEstrategica !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_politica !== 'undefined' ? dt_politica : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresPolitica === 'function') {
                        actualizarContadoresPolitica()
                    }
                    if (typeof popularFiltroAreaPolitica === 'function') {
                        popularFiltroAreaPolitica()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#politicaModalTitulo').text(tituloModalPolitica(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoPolitica').on('click', function () {
        reiniciarCampos()
        abrirModalPolitica(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalPolitica()
    })

    $modalPolitica.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioPolitica({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioPolitica({
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
        cerrarMenusEstadoPolitica()
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
        const rowApi = dt_politica.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idPoliticaEstrategica', dt_row.IdPoliticaEstrategica)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresPolitica()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdPoliticaEstrategica

        const datos = new FormData()
        datos.append('idPoliticaEstrategica', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la política estratégica seleccionada?',
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
                    dt_politica.ajax.reload(function () {
                        actualizarContadoresPolitica()
                        popularFiltroAreaPolitica()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        idPoliticaEstrategica = dt_row.IdPoliticaEstrategica

        const datos = new FormData()
        datos.append('idPoliticaEstrategica', idPoliticaEstrategica)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                if (typeof politicas_s2AreasEstrategicas !== 'undefined') {
                    politicas_s2AreasEstrategicas.val(registro.IdAreaEstrategica).trigger('change.select2')
                }
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalPolitica(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formPoliticaEstrategica').validate({
        ignore: [],
        rules: {
            idAreaEstrategica: {
                required: true
            },
            codigo: {
                required: true,
                digits: true,
                range: [1, 9],
                require_from_group: [2, '.codigo_group'],
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idAreaEstrategica: function () {
                            return $('#idAreaEstrategica').val()
                        },
                        idPoliticaEstrategica: function () {
                            return idPoliticaEstrategica
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
            idAreaEstrategica: {
                required: 'Debe seleccionar un área estratégica'
            },
            codigo: {
                required: 'Debe ingresar un código de política estratégica',
                digits: 'El código solo debe ser numérico',
                range: 'El código debe estar comprendido entre 1 y 9',
                require_from_group: 'Debe seleccionar un área estratégica para validar el código',
                remote: 'El código ingresado ya se encuentra en uso con el área seleccionada'
            },
            descripcion: {
                required: 'Debe ingresar una descripción de la política estratégica',
                minlength: 'La descripción debe tener al menos 2 caracteres',
                maxlength: 'La descripción debe tener máximo 500 caracteres'
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
