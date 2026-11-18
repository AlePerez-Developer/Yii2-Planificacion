let urlProgramarPoa
let oesFiltroTexto = ''
let oesFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaOes(etiqueta) {
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

function aplicarFiltrosTablaOes() {
    if (typeof dt_objEspecifico === 'undefined' || !dt_objEspecifico) {
        return
    }
    dt_objEspecifico.draw()
}

function sincronizarFiltroEstadoOes(codigoEstado) {
    oesFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoOes').val(oesFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (oesFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaOes(statEtiqueta) === oesFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaOes()
}

function textoBusquedaOes(row) {
    const oi = row.objetivosInstitucionales || {}
    return [
        row.Compuesto,
        row.Codigo,
        row.Objetivo,
        oi.Codigo,
        oi.Objetivo,
        oi.Producto
    ].join(' ').toLowerCase()
}

function initOesListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__oesListadoFiltro === true
    })) {
        aplicarFiltrosTablaOes()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaObjEspecificos') {
            return true
        }

        const row = dt_objEspecifico.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (oesFiltroCodigoEstado && row.CodigoEstado !== oesFiltroCodigoEstado) {
            return false
        }

        if (!oesFiltroTexto) {
            return true
        }

        return textoBusquedaOes(row).indexOf(oesFiltroTexto.toLowerCase()) !== -1
    }
    filtroFn.__oesListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaOes(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoOes('')
            return
        }

        sincronizarFiltroEstadoOes(codigo)
    })

    $('#buscarOes').on('input', function () {
        oesFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaOes()
    })

    $('#filtroEstadoOes').on('change', function () {
        sincronizarFiltroEstadoOes($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idObjEspecifico = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/obj-especifico/'
    const dtEvents = $('#tablaListaObjEspecificos')
    const $modalOes = $('#modalObjEspecifico')

    function reiniciarCampos() {
        const validator = $('#formObjEspecifico').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formObjEspecifico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formObjEspecifico .select2-selection').removeClass('is-invalid')
        $('#formObjEspecifico').trigger('reset')
        if (typeof objEspecifico_s2ObjInstitucional !== 'undefined') {
            objEspecifico_s2ObjInstitucional.val(null).trigger('change')
        }
        idObjEspecifico = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        const mensajes = {
            guardar: 'Los datos del objetivo específico se guardaron correctamente.',
            actualizar: 'Los datos del objetivo específico se actualizaron correctamente.',
            eliminar: 'El objetivo específico se eliminó correctamente.'
        }
        return mensajes[accion] || 'Proceso realizado correctamente.'
    }

    function tituloModalOes(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalOes(esEdicion) {
        $('#oesModalTitulo').text(tituloModalOes(esEdicion))
        $modalOes.modal('show')
    }

    function cerrarModalOes() {
        reiniciarCampos()
        $modalOes.modal('hide')
    }

    $('#codigo').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2)
    }).on('blur', function () {
        if (this.value !== '') {
            this.value = this.value.padStart(2, '0')
        }
    })

    async function guardarFormularioOes(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        $('#codigo').trigger('blur')

        if (!$('#formObjEspecifico').valid()) {
            return false
        }

        const oiData = objEspecifico_s2ObjInstitucional.select2('data')
        const datos = new FormData()
        datos.append('idObjEspecifico', idObjEspecifico)
        datos.append('idObjInstitucional', oiData[0] ? oiData[0].id : '')
        datos.append('codigo', $('#codigo').val())
        datos.append('objetivo', $('#objetivo').val())

        const esEdicion = idObjEspecifico !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_objEspecifico !== 'undefined' ? dt_objEspecifico : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresOes === 'function') {
                        actualizarContadoresOes()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#oesModalTitulo').text(tituloModalOes(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoObjEspecifico').on('click', function () {
        reiniciarCampos()
        abrirModalOes(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalOes()
    })

    $modalOes.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioOes({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioOes({
            spinnerBtn: $(this),
            cerrarAlExito: false,
            continuar: true
        })
    })

    if (typeof objEspecifico_s2ObjInstitucional !== 'undefined') {
        objEspecifico_s2ObjInstitucional.on('change', function () {
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
        cerrarMenusEstadoOes()
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
        const rowApi = dt_objEspecifico.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idObjEspecifico', dt_row.IdObjEspecifico)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresOes()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-programar', function () {
        const dt_row = dt_objEspecifico.row($(this).closest('tr')).data()
        if (!dt_row || !urlProgramarPoa) {
            return
        }
        window.location.href = urlProgramarPoa + '&id=' + dt_row.IdObjEspecifico
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_objEspecifico.row(objectBtn.closest('tr')).data()
        const datos = new FormData()
        datos.append('idObjEspecifico', dt_row.IdObjEspecifico)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación de eliminación',
            text: '¿Está seguro de eliminar el objetivo específico seleccionado?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value || resultado.isConfirmed) {
                try {
                    await ajaxPromise({
                        url: baseUrl + 'eliminar',
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                    })
                    dt_objEspecifico.ajax.reload(function () {
                        actualizarContadoresOes()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_objEspecifico.row(objectBtn.closest('tr')).data()
        idObjEspecifico = dt_row.IdObjEspecifico

        const datos = new FormData()
        datos.append('idObjEspecifico', idObjEspecifico)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            })
            const obj = data.data
            objEspecifico_s2ObjInstitucional.val(obj.IdObjInstitucional).trigger('change.select2')
            $('#codigo').val(obj.Codigo)
            $('#objetivo').val(obj.Objetivo)
            abrirModalOes(true)
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formObjEspecifico').validate({
        ignore: [],
        rules: {
            idObjInstitucional: {
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
                        idObjInstitucional: function () {
                            return objEspecifico_s2ObjInstitucional.val() || ''
                        },
                        idObjEspecifico: function () {
                            return idObjEspecifico
                        }
                    }
                }
            },
            objetivo: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            idObjInstitucional: {
                required: 'Debe seleccionar un objetivo institucional.'
            },
            codigo: {
                required: 'Debe ingresar el código del objetivo específico.',
                digits: 'El código solo puede contener números.',
                minlength: 'El código debe tener exactamente 2 dígitos.',
                maxlength: 'El código debe tener exactamente 2 dígitos.',
                require_from_group: 'Debe seleccionar un objetivo institucional antes de validar el código.',
                remote: 'El código ya está registrado para el objetivo institucional seleccionado.'
            },
            objetivo: {
                required: 'Debe ingresar el objetivo específico.',
                minlength: 'El objetivo debe tener al menos 2 caracteres.',
                maxlength: 'El objetivo permite como máximo 500 caracteres.'
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
