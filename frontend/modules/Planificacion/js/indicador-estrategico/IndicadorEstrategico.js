let indEstrategicoFiltroTexto = ''
let indEstrategicoFiltroCodigoEstado = ''

function codigoEstadoDesdeEtiquetaInd(etiqueta) {
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

function aplicarFiltrosTablaIndEstrategico() {
    if (typeof dt_indEstrategico === 'undefined' || !dt_indEstrategico) {
        return
    }
    dt_indEstrategico.draw()
}

function sincronizarFiltroEstadoInd(codigoEstado) {
    indEstrategicoFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoIndicador').val(indEstrategicoFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (indEstrategicoFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaInd(statEtiqueta) === indEstrategicoFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaIndEstrategico()
}

function initIndEstrategicoListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__indEstrategicoListadoFiltro === true
    })) {
        aplicarFiltrosTablaIndEstrategico()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaIndicadoresEstrategicos') {
            return true
        }

        const row = dt_indEstrategico.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (indEstrategicoFiltroCodigoEstado && row.CodigoEstado !== indEstrategicoFiltroCodigoEstado) {
            return false
        }

        if (!indEstrategicoFiltroTexto) {
            return true
        }

        const termino = indEstrategicoFiltroTexto.toLowerCase()
        const indicador = row.indicador || {}
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = String(indicador.Descripcion || row.Descripcion || '').toLowerCase()
        const accionCodigo = String(
            (row.accionesEstrategicas && row.accionesEstrategicas.Codigo != null)
                ? row.accionesEstrategicas.Codigo
                : (row.AccionCodigo != null ? row.AccionCodigo : '')
        ).toLowerCase()
        const accionDesc = String(
            (row.accionesEstrategicas && row.accionesEstrategicas.Descripcion)
                ? row.accionesEstrategicas.Descripcion
                : (row.AccionDescripcion || '')
        ).toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || accionCodigo.indexOf(termino) !== -1
            || accionDesc.indexOf(termino) !== -1
    }
    filtroFn.__indEstrategicoListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaInd(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoInd('')
            return
        }

        sincronizarFiltroEstadoInd(codigo)
    })

    $('#buscarIndicador').on('input', function () {
        indEstrategicoFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaIndEstrategico()
    })

    $('#filtroEstadoIndicador').on('change', function () {
        sincronizarFiltroEstadoInd($(this).val())
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idIndicadorEstrategico = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/indicador-estrategico/'
    const dtEvents = $('#tablaListaIndicadoresEstrategicos')
    const $modalIndicador = $('#modalIndicadorEstrategico')

    function reiniciarCampos() {
        const validator = $('#formIndicadorEstrategico').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formIndicadorEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formIndicadorEstrategico').trigger('reset')
        if (typeof indicadorEstrategico_s2ObjEstrategico !== 'undefined') {
            indicadorEstrategico_s2ObjEstrategico.val(null).trigger('change')
        }
        if (typeof indicadorEstrategico_s2CategoriaIndicador !== 'undefined') {
            indicadorEstrategico_s2CategoriaIndicador.val(null).trigger('change')
        }
        if (typeof indicadorEstrategico_s2TipoResultado !== 'undefined') {
            indicadorEstrategico_s2TipoResultado.val(null).trigger('change')
        }
        if (typeof indicadorEstrategico_s2UnidadIndicador !== 'undefined') {
            indicadorEstrategico_s2UnidadIndicador.val(null).trigger('change')
        }
        if (typeof indicadorEstrategico_s2AccionEstrategica !== 'undefined') {
            indicadorEstrategico_s2AccionEstrategica.val(null).trigger('change')
        }
        idIndicadorEstrategico = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del Indicador Estratégico se ${accion}on correctamente.`
    }

    function tituloModalIndicador(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalIndicador(esEdicion) {
        $('#indEstrategicoModalTitulo').text(tituloModalIndicador(esEdicion))
        $modalIndicador.modal('show')
    }

    function cerrarModalIndicador() {
        reiniciarCampos()
        $modalIndicador.modal('hide')
    }

    function valorSelect2(select) {
        const data = select.select2('data')
        return (data && data[0] && data[0].id) ? data[0].id : ''
    }

    async function guardarFormularioIndicador(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formIndicadorEstrategico').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idIndicadorEstrategico', idIndicadorEstrategico)
        datos.append('idObjEstrategico', valorSelect2(indicadorEstrategico_s2ObjEstrategico))
        datos.append('idUnidadIndicador', valorSelect2(indicadorEstrategico_s2UnidadIndicador))
        datos.append('idCategoriaIndicador', valorSelect2(indicadorEstrategico_s2CategoriaIndicador))
        datos.append('idTipoResultado', valorSelect2(indicadorEstrategico_s2TipoResultado))
        datos.append('idAccionEstrategica', valorSelect2(indicadorEstrategico_s2AccionEstrategica))
        datos.append('codigo', $('#codigo').val())
        datos.append('meta', $('#meta').val())
        datos.append('lineaBase', $('#lineaBase').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idIndicadorEstrategico !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_indEstrategico !== 'undefined' ? dt_indEstrategico : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresInd === 'function') {
                        actualizarContadoresInd()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#indEstrategicoModalTitulo').text(tituloModalIndicador(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoIndicador').on('click', function () {
        reiniciarCampos()
        abrirModalIndicador(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalIndicador()
    })

    $modalIndicador.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioIndicador({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioIndicador({
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
        cerrarMenusEstadoInd()
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
        const rowApi = dt_indEstrategico.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idIndicadorEstrategico', dt_row.IdIndicador)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresInd()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_indEstrategico.row(objectBtn.closest('tr')).data()
        const rowId = dt_row.IdIndicador

        const datos = new FormData()
        datos.append('idIndicadorEstrategico', rowId)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el indicador estratégico seleccionado?',
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
                    dt_indEstrategico.ajax.reload(function () {
                        actualizarContadoresInd()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_indEstrategico.row(objectBtn.closest('tr')).data()
        idIndicadorEstrategico = dt_row.IdIndicador

        const datos = new FormData()
        datos.append('idIndicadorEstrategico', idIndicadorEstrategico)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const obj = data.data
                const indicador = obj.indicador || {}
                indicadorEstrategico_s2ObjEstrategico.val(obj.IdObjEstrategico).trigger('change')
                $('#codigo').val(obj.Codigo)
                $('#meta').val(indicador.Meta != null ? indicador.Meta : obj.Meta)
                $('#lineaBase').val(indicador.LineaBase != null ? indicador.LineaBase : obj.LineaBase)
                $('#descripcion').val(indicador.Descripcion || obj.Descripcion || '')
                indicadorEstrategico_s2TipoResultado.val(
                    indicador.IdTipoResultado || obj.IdTipoResultado
                ).trigger('change')
                indicadorEstrategico_s2CategoriaIndicador.val(
                    indicador.IdCategoriaIndicador || obj.IdCategoriaIndicador
                ).trigger('change')
                indicadorEstrategico_s2UnidadIndicador.val(
                    indicador.IdUnidadIndicador || obj.IdUnidadIndicador
                ).trigger('change')
                indicadorEstrategico_s2AccionEstrategica.val(obj.IdAccionEstrategica).trigger('change')
                abrirModalIndicador(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formIndicadorEstrategico').validate({
        rules: {
            idObjEstrategico: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                min: 1,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val()
                        },
                        idIndicadorEstrategico: function () {
                            return idIndicadorEstrategico
                        }
                    }
                }
            },
            meta: {
                required: true,
                digits: true,
                min: 0
            },
            lineaBase: {
                required: true,
                digits: true,
                min: 0
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            idTipoResultado: {
                required: true,
            },
            idCategoriaIndicador: {
                required: true,
            },
            idUnidadIndicador: {
                required: true,
            },
            idAccionEstrategica: {
                required: true
            }
        },
        messages: {
            idObjEstrategico: {
                required: 'Debe seleccionar un objetivo estratégico',
            },
            codigo: {
                required: 'Debe ingresar un código de indicador estratégico',
                digits: 'Solo se permiten números enteros',
                min: 'El código debe ser un número mayor que cero',
                remote: 'El código ingresado ya se encuentra en uso',
            },
            meta: {
                required: 'Debe ingresar un valor para la meta del indicador',
                digits: 'Solo se permiten números enteros',
                min: 'La meta del indicador debe ser un número mayor o igual a cero'
            },
            lineaBase: {
                required: 'Debe ingresar un valor para la línea base del indicador',
                digits: 'Solo se permiten números enteros',
                min: 'La línea base del indicador debe ser un número mayor o igual a cero'
            },
            descripcion: {
                required: 'Debe ingresar la descripción del indicador estratégico',
                minlength: 'La descripción del indicador debe tener por lo menos 2 caracteres',
                maxlength: 'La descripción del indicador debe tener máximo 500 caracteres',
            },
            idTipoResultado: {
                required: 'Debe seleccionar un tipo de resultado',
            },
            idCategoriaIndicador: {
                required: 'Debe seleccionar una categoría del indicador',
            },
            idUnidadIndicador: {
                required: 'Debe seleccionar una unidad del indicador',
            },
            idAccionEstrategica: {
                required: 'Debe seleccionar una acción estratégica',
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
