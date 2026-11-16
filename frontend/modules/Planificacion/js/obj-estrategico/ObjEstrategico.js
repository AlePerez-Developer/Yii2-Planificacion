let urlProgramar
let oeFiltroTexto = ''
let oeFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaOe(etiqueta) {
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

function aplicarFiltrosTablaOe() {
    if (typeof dt_objEstrategico === 'undefined' || !dt_objEstrategico) {
        return
    }
    dt_objEstrategico.draw()
}

function sincronizarFiltroEstadoOe(codigoEstado) {
    oeFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoOe').val(oeFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (oeFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaOe(statEtiqueta) === oeFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaOe()
}

function textoBusquedaOe(row) {
    const area = row.areaEstrategica || {}
    const politica = row.politicaEstrategica || {}
    return [
        row.Compuesto,
        row.Codigo,
        row.Objetivo,
        row.Producto,
        row.Indicador_Descripcion,
        row.Indicador_Formula,
        area.Codigo,
        area.Descripcion,
        politica.Codigo,
        politica.Descripcion
    ].join(' ').toLowerCase()
}

function initOeListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__oeListadoFiltro === true
    })) {
        aplicarFiltrosTablaOe()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaObjEstrategicos') {
            return true
        }

        const row = dt_objEstrategico.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (oeFiltroCodigoEstado && row.CodigoEstado !== oeFiltroCodigoEstado) {
            return false
        }

        if (!oeFiltroTexto) {
            return true
        }

        return textoBusquedaOe(row).indexOf(oeFiltroTexto.toLowerCase()) !== -1
    }
    filtroFn.__oeListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaOe(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoOe('')
            return
        }

        sincronizarFiltroEstadoOe(codigo)
    })

    $('#buscarOe').on('input', function () {
        oeFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaOe()
    })

    $('#filtroEstadoOe').on('change', function () {
        sincronizarFiltroEstadoOe($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idObjEstrategico = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/obj-estrategico/'
    const dtEvents = $('#tablaListaObjEstrategicos')
    const $modalOe = $('#modalObjEstrategico')

    function reiniciarCampos() {
        const validator = $('#formObjEstrategico').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formObjEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formObjEstrategico .select2-selection').removeClass('is-invalid')
        $('#formObjEstrategico').trigger('reset')
        if (typeof objEstrategico_s2PoliticaEstrategica !== 'undefined') {
            objEstrategico_s2PoliticaEstrategica.val(null).trigger('change')
            objEstrategico_s2PoliticaEstrategica.prop('disabled', true)
        }
        if (typeof objEstrategico_s2AreaEstrategica !== 'undefined') {
            objEstrategico_s2AreaEstrategica.val(null).trigger('change.select2')
        }
        idObjEstrategico = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del objetivo estratégico se ${accion}ron correctamente.`
    }

    function tituloModalOe(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalOe(esEdicion) {
        $('#oeModalTitulo').text(tituloModalOe(esEdicion))
        $modalOe.modal('show')
    }

    function cerrarModalOe() {
        reiniciarCampos()
        $modalOe.modal('hide')
    }

    objEstrategico_s2AreaEstrategica.on('change', async function () {
        const idAreaEstrategica = $(this).val()

        objEstrategico_s2PoliticaEstrategica.val(null).trigger('change')

        if (idAreaEstrategica) {
            objEstrategico_s2PoliticaEstrategica.prop('disabled', false)
            await populateS2Politicas(idAreaEstrategica, objEstrategico_s2PoliticaEstrategica)
        } else {
            objEstrategico_s2PoliticaEstrategica.prop('disabled', true)
        }
    })

    async function guardarFormularioOe(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formObjEstrategico').valid()) {
            return false
        }

        const area = objEstrategico_s2AreaEstrategica.select2('data')
        const politica = objEstrategico_s2PoliticaEstrategica.select2('data')
        const datos = new FormData()
        datos.append('idObjEstrategico', idObjEstrategico)
        datos.append('idAreaEstrategica', area[0] ? area[0].id : '')
        datos.append('idPoliticaEstrategica', politica[0] ? politica[0].id : '')
        datos.append('codigo', $('#codigo').val())
        datos.append('objetivo', $('#objetivo').val())
        datos.append('producto', $('#producto').val())
        datos.append('descripcion', $('#descripcion').val())
        datos.append('formula', $('#formula').val())

        const esEdicion = idObjEstrategico !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_objEstrategico !== 'undefined' ? dt_objEstrategico : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresOe === 'function') {
                        actualizarContadoresOe()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#oeModalTitulo').text(tituloModalOe(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoObjEstrategico').on('click', function () {
        reiniciarCampos()
        abrirModalOe(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalOe()
    })

    $modalOe.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioOe({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioOe({
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
        cerrarMenusEstadoOe()
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
        const rowApi = dt_objEstrategico.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idObjEstrategico', dt_row.IdObjEstrategico)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresOe()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_objEstrategico.row(objectBtn.closest('tr')).data()
        const datos = new FormData()
        datos.append('idObjEstrategico', dt_row.IdObjEstrategico)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el objetivo estratégico seleccionado?',
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
                    dt_objEstrategico.ajax.reload(function () {
                        actualizarContadoresOe()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_objEstrategico.row(objectBtn.closest('tr')).data()
        idObjEstrategico = dt_row.IdObjEstrategico

        const datos = new FormData()
        datos.append('idObjEstrategico', idObjEstrategico)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            })
            const obj = data.data
            objEstrategico_s2AreaEstrategica.val(obj.IdAreaEstrategica).trigger('change.select2')
            objEstrategico_s2PoliticaEstrategica.prop('disabled', false)
            await populateS2Politicas(obj.IdAreaEstrategica, objEstrategico_s2PoliticaEstrategica, obj.IdPoliticaEstrategica)
            $('#codigo').val(obj.Codigo)
            $('#objetivo').val(obj.Objetivo)
            $('#producto').val(obj.Producto)
            $('#descripcion').val(obj.Indicador_Descripcion)
            $('#formula').val(obj.Indicador_Formula)
            abrirModalOe(true)
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    dtEvents.on('click', '.btn-programar', function () {
        const dt_row = dt_objEstrategico.row($(this).closest('tr')).data()
        if (!dt_row || !urlProgramar) {
            return
        }
        window.location.href = urlProgramar + '&id=' + dt_row.IdObjEstrategico
    })

    $('#formObjEstrategico').validate({
        ignore: [],
        rules: {
            areasEstrategicas: {
                required: true,
            },
            politicasEstrategicas: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                range: [1, 9],
                require_from_group: [3, '.codigo_group'],
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idAreaEstrategica: function () {
                            const area = $('#areasEstrategicas').select2('data')
                            return area && area[0] ? area[0].id : ''
                        },
                        idPoliticaEstrategica: function () {
                            const politica = $('#politicasEstrategicas').select2('data')
                            return politica && politica[0] ? politica[0].id : ''
                        },
                        idObjEstrategico: function () {
                            return idObjEstrategico
                        }
                    }
                }
            },
            objetivo: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            producto: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            formula: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
        },
        messages: {
            areasEstrategicas: {
                required: 'Debe seleccionar un área estratégica',
            },
            politicasEstrategicas: {
                required: 'Debe seleccionar una política estratégica',
            },
            codigo: {
                required: 'Debe ingresar un código de objetivo estratégico (OE)',
                digits: 'Solo se permiten números enteros',
                range: 'Debe ingresar un número comprendido entre 1 y 9',
                require_from_group: 'Debe seleccionar un área y una política antes de validar el código',
                remote: 'El código ingresado ya se encuentra en uso con el área y la política seleccionadas'
            },
            objetivo: {
                required: 'Debe ingresar la descripción del objetivo estratégico',
                minlength: 'El objetivo debe tener por lo menos 2 caracteres',
                maxlength: 'El objetivo debe tener máximo 500 caracteres',
            },
            producto: {
                required: 'Debe ingresar el resultado esperado del objetivo estratégico',
                minlength: 'El resultado debe tener por lo menos 2 caracteres',
                maxlength: 'El resultado debe tener máximo 500 caracteres',
            },
            descripcion: {
                required: 'Debe ingresar la descripción del indicador del objetivo estratégico',
                minlength: 'La descripción del indicador debe tener por lo menos 2 caracteres',
                maxlength: 'La descripción del indicador debe tener máximo 500 caracteres',
            },
            formula: {
                required: 'Debe ingresar la fórmula del indicador del objetivo estratégico',
                minlength: 'La fórmula del indicador debe tener por lo menos 2 caracteres',
                maxlength: 'La fórmula del indicador debe tener máximo 500 caracteres',
            },
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
            const $el = $(element)
            $el.addClass('is-invalid').removeClass('is-valid')
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.next('.select2').find('.select2-selection').addClass('is-invalid')
            }
        },
        unhighlight: function (element) {
            const $el = $(element)
            $el.addClass('is-valid').removeClass('is-invalid')
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.next('.select2').find('.select2-selection').removeClass('is-invalid')
            }
        }
    })
})
