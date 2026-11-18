let oiFiltroTexto = ''
let oiFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaOi(etiqueta) {
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

function aplicarFiltrosTablaOi() {
    if (typeof dt_objInstitucional === 'undefined' || !dt_objInstitucional) {
        return
    }
    dt_objInstitucional.draw()
}

function sincronizarFiltroEstadoOi(codigoEstado) {
    oiFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoOi').val(oiFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (oiFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaOi(statEtiqueta) === oiFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaOi()
}

function textoBusquedaOi(row) {
    const oe = row.objetivosEstrategicos || {}
    return [
        row.Compuesto,
        row.Codigo,
        row.Objetivo,
        row.Producto,
        oe.Codigo,
        oe.Objetivo,
        oe.Producto
    ].join(' ').toLowerCase()
}

function initOiListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__oiListadoFiltro === true
    })) {
        aplicarFiltrosTablaOi()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaObjInstitucionales') {
            return true
        }

        const row = dt_objInstitucional.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (oiFiltroCodigoEstado && row.CodigoEstado !== oiFiltroCodigoEstado) {
            return false
        }

        if (!oiFiltroTexto) {
            return true
        }

        return textoBusquedaOi(row).indexOf(oiFiltroTexto.toLowerCase()) !== -1
    }
    filtroFn.__oiListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaOi(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoOi('')
            return
        }

        sincronizarFiltroEstadoOi(codigo)
    })

    $('#buscarOi').on('input', function () {
        oiFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaOi()
    })

    $('#filtroEstadoOi').on('change', function () {
        sincronizarFiltroEstadoOi($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idObjInstitucional = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/obj-institucional/'
    const dtEvents = $('#tablaListaObjInstitucionales')
    const $modalOi = $('#modalObjInstitucional')

    function reiniciarCampos() {
        const validator = $('#formObjInstitucional').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formObjInstitucional *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formObjInstitucional .select2-selection').removeClass('is-invalid')
        $('#formObjInstitucional').trigger('reset')
        if (typeof objInstitucional_s2ObjEstrategico !== 'undefined') {
            objInstitucional_s2ObjEstrategico.val(null).trigger('change')
        }
        idObjInstitucional = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        const mensajes = {
            guardar: 'Los datos del objetivo institucional se guardaron correctamente.',
            actualizar: 'Los datos del objetivo institucional se actualizaron correctamente.',
            eliminar: 'El objetivo institucional se eliminó correctamente.'
        }
        return mensajes[accion] || 'Proceso realizado correctamente.'
    }

    function tituloModalOi(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalOi(esEdicion) {
        $('#oiModalTitulo').text(tituloModalOi(esEdicion))
        $modalOi.modal('show')
    }

    function cerrarModalOi() {
        reiniciarCampos()
        $modalOi.modal('hide')
    }

    async function guardarFormularioOi(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formObjInstitucional').valid()) {
            return false
        }

        const oeData = objInstitucional_s2ObjEstrategico.select2('data')
        const datos = new FormData()
        datos.append('idObjInstitucional', idObjInstitucional)
        datos.append('idObjEstrategico', oeData[0] ? oeData[0].id : '')
        datos.append('codigo', $('#codigo').val())
        datos.append('objetivo', $('#objetivo').val())
        datos.append('producto', $('#producto').val())

        const esEdicion = idObjInstitucional !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_objInstitucional !== 'undefined' ? dt_objInstitucional : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresOi === 'function') {
                        actualizarContadoresOi()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#oiModalTitulo').text(tituloModalOi(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoObjInstitucional').on('click', function () {
        reiniciarCampos()
        abrirModalOi(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalOi()
    })

    $modalOi.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioOi({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioOi({
            spinnerBtn: $(this),
            cerrarAlExito: false,
            continuar: true
        })
    })

    if (typeof objInstitucional_s2ObjEstrategico !== 'undefined') {
        objInstitucional_s2ObjEstrategico.on('change', function () {
            const codigo = $('#codigo')
            if (codigo.val()) {
                codigo.valid()
            }
        })
    }

    dtEvents.on('click', '.estado-trigger', function (e) {
        e.preventDefault()
        e.stopPropagation()
        const wrap = $(this).closest('.estado-wrap')
        const abierto = wrap.hasClass('open')
        cerrarMenusEstadoOi()
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
        const rowApi = dt_objInstitucional.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idObjInstitucional', dt_row.IdObjInstitucional)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresOi()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data()
        const datos = new FormData()
        datos.append('idObjInstitucional', dt_row.IdObjInstitucional)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación de eliminación',
            text: '¿Está seguro de eliminar el objetivo institucional seleccionado?',
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
                    dt_objInstitucional.ajax.reload(function () {
                        actualizarContadoresOi()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data()
        idObjInstitucional = dt_row.IdObjInstitucional

        const datos = new FormData()
        datos.append('idObjInstitucional', idObjInstitucional)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            })
            const obj = data.data
            objInstitucional_s2ObjEstrategico.val(obj.IdObjEstrategico).trigger('change.select2')
            $('#codigo').val(obj.Codigo)
            $('#objetivo').val(obj.Objetivo)
            $('#producto').val(obj.Producto)
            abrirModalOi(true)
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formObjInstitucional').validate({
        ignore: [],
        rules: {
            idObjEstrategico: {
                required: true
            },
            codigo: {
                required: true,
                digits: true,
                minlength: 2,
                maxlength: 2,
                require_from_group: [2, '.codigo_group'],
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idObjEstrategico: function () {
                            return objInstitucional_s2ObjEstrategico.val() || ''
                        },
                        idObjInstitucional: function () {
                            return idObjInstitucional
                        }
                    }
                }
            },
            objetivo: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
            producto: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            idObjEstrategico: {
                required: 'Debe seleccionar un objetivo estratégico.'
            },
            codigo: {
                required: 'Debe ingresar el código del objetivo institucional.',
                digits: 'El código solo puede contener números.',
                minlength: 'El código debe tener exactamente 2 dígitos.',
                maxlength: 'El código debe tener exactamente 2 dígitos.',
                require_from_group: 'Debe seleccionar un objetivo estratégico antes de validar el código.',
                remote: 'El código ya está registrado para el objetivo estratégico seleccionado.'
            },
            objetivo: {
                required: 'Debe ingresar el objetivo institucional.',
                minlength: 'El objetivo debe tener al menos 2 caracteres.',
                maxlength: 'El objetivo permite como máximo 500 caracteres.'
            },
            producto: {
                required: 'Debe ingresar el resultado o producto esperado.',
                minlength: 'El producto debe tener al menos 2 caracteres.',
                maxlength: 'El producto permite como máximo 500 caracteres.'
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
