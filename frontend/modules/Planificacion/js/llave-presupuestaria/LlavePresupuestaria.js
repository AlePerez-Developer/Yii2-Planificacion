let llaveFiltroTexto = ''
let llaveFiltroCodigoEstado = ''
let llaveFiltroIdUnidadEjecutora = ''

function codigoEstadoDesdeEtiquetaLlave(etiqueta) {
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

function aplicarFiltrosTablaLlave() {
    if (typeof dt_llavePresupuestaria === 'undefined' || !dt_llavePresupuestaria) {
        return
    }
    dt_llavePresupuestaria.draw()
}

function sincronizarFiltroEstadoLlave(codigoEstado) {
    llaveFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoLlave').val(llaveFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (llaveFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaLlave(statEtiqueta) === llaveFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaLlave()
}

function coincideBusquedaUnidadLlave(ue, termino) {
    if (!termino) {
        return true
    }
    const t = termino.toLowerCase()
    const da = (ue.da || '').toLowerCase()
    const codigoUe = (ue.ue || '').toLowerCase()
    const compuesto = (ue.compuesto || '').toLowerCase()
    const descripcion = (ue.descripcion || '').toLowerCase()

    return da.indexOf(t) !== -1
        || codigoUe.indexOf(t) !== -1
        || compuesto.indexOf(t) !== -1
        || descripcion.indexOf(t) !== -1
}

function initLlaveListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__llaveListadoFiltro === true
    })) {
        aplicarFiltrosTablaLlave()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaLlavesPresupuestarias') {
            return true
        }

        const row = dt_llavePresupuestaria.row(dataIndex).data()
        if (!row) {
            return true
        }

        const ue = typeof llaveDatosUnidad === 'function' ? llaveDatosUnidad(row) : {}

        if (llaveFiltroIdUnidadEjecutora && (ue.id || row.IdUnidadEjecutora) !== llaveFiltroIdUnidadEjecutora) {
            return false
        }

        if (llaveFiltroCodigoEstado && row.CodigoEstado !== llaveFiltroCodigoEstado) {
            return false
        }

        if (!llaveFiltroTexto) {
            return true
        }

        const termino = llaveFiltroTexto.toLowerCase()
        const llave = (row.Llave || '').toLowerCase()
        const programa = (row.proyecto && row.proyecto.programa) || {}
        const proyecto = row.proyecto || {}
        const actividad = row.actividad || {}

        return coincideBusquedaUnidadLlave(ue, termino)
            || llave.indexOf(termino) !== -1
            || ((programa.Codigo || '').toLowerCase().indexOf(termino) !== -1)
            || ((programa.Descripcion || '').toLowerCase().indexOf(termino) !== -1)
            || ((proyecto.Codigo || '').toLowerCase().indexOf(termino) !== -1)
            || ((proyecto.Descripcion || '').toLowerCase().indexOf(termino) !== -1)
            || ((actividad.Codigo || '').toLowerCase().indexOf(termino) !== -1)
            || ((actividad.Descripcion || '').toLowerCase().indexOf(termino) !== -1)
    }
    filtroFn.__llaveListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaLlave(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoLlave('')
            return
        }

        sincronizarFiltroEstadoLlave(codigo)
    })

    $('#buscarLlave').on('input', function () {
        llaveFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaLlave()
    })

    $('#filtroEstadoLlave').on('change', function () {
        sincronizarFiltroEstadoLlave($(this).val())
    })

    $('#filtroUnidadEjecutora').on('change', function () {
        llaveFiltroIdUnidadEjecutora = $(this).val() || ''
        aplicarFiltrosTablaLlave()
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idLlavePresupuestaria = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/llave-presupuestaria/'
    const dtEvents = $('#tablaListaLlavesPresupuestarias')
    const $modalLlave = $('#modalLlavePresupuestaria')

    function reiniciarCampos() {
        const validator = $('#formLlavePresupuestaria').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formLlavePresupuestaria *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        if (typeof llavePresupuestaria_s2UnidadEjecutora !== 'undefined') {
            llavePresupuestaria_s2UnidadEjecutora.val(null).trigger('change')
        }
        if (typeof llavePresupuestaria_s2Programa !== 'undefined') {
            llavePresupuestaria_s2Programa.val(null).trigger('change')
        }
        if (typeof llavePresupuestaria_s2Proyecto !== 'undefined') {
            llavePresupuestaria_s2Proyecto.val(null).trigger('change').prop('disabled', true)
        }
        if (typeof llavePresupuestaria_s2Actividad !== 'undefined') {
            llavePresupuestaria_s2Actividad.val(null).trigger('change').prop('disabled', true)
        }
        $('#organizacional').prop('checked', false)
        $('#llave').val('00-000-000-0000-000').removeClass('is-invalid is-valid')
        $('#formLlavePresupuestaria').trigger('reset')
        idLlavePresupuestaria = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la llave presupuestaria se ${accion}ron correctamente.`
    }

    function tituloModalLlave(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalLlave(esEdicion) {
        $('#llaveModalTitulo').text(tituloModalLlave(esEdicion))
        $modalLlave.modal('show')
    }

    function cerrarModalLlave() {
        reiniciarCampos()
        $modalLlave.modal('hide')
    }

    function obtenerValorLlave(selector) {
        const select = $(selector)
        return select.find('option:selected').data('key')
            ?? select.attr('data-default')
            ?? ''
    }

    function actualizarLlave() {
        const partes = [
            obtenerValorLlave('#idUnidadEjecutora'),
            obtenerValorLlave('#idPrograma'),
            obtenerValorLlave('#idProyecto'),
            obtenerValorLlave('#idActividad')
        ]
        $('#llave').val(partes.join('-'))
    }

    function todosLosSelectsTienenValor() {
        let completos = true
        $('.codigo_group').each(function () {
            if (!$(this).val()) {
                completos = false
                return false
            }
        })
        return completos
    }

    async function guardarFormularioLlave(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formLlavePresupuestaria').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idLlavePresupuestaria', idLlavePresupuestaria)
        datos.append('idUnidadEjecutora', $('#idUnidadEjecutora').val())
        datos.append('idPrograma', $('#idPrograma').val())
        datos.append('idProyecto', $('#idProyecto').val())
        datos.append('idActividad', $('#idActividad').val())
        datos.append('llave', $('#llave').val())
        datos.append('esOrganizacional', $('#organizacional').is(':checked') ? '1' : '0')
        datos.append('fechaInicio', $('#fechaInicio').val())

        const esEdicion = idLlavePresupuestaria !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_llavePresupuestaria !== 'undefined' ? dt_llavePresupuestaria : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresLlave === 'function') {
                        actualizarContadoresLlave()
                    }
                    if (typeof popularFiltroUnidadEjecutoraLlave === 'function') {
                        popularFiltroUnidadEjecutoraLlave()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#llaveModalTitulo').text(tituloModalLlave(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoLlave').on('click', function () {
        reiniciarCampos()
        abrirModalLlave(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalLlave()
    })

    $modalLlave.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioLlave({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioLlave({
            spinnerBtn: $(this),
            cerrarAlExito: false,
            continuar: true
        })
    })

    llavePresupuestaria_s2Programa.on('change', async function () {
        const idPrograma = $(this).val()

        llavePresupuestaria_s2Proyecto.val(null).trigger('change')
        llavePresupuestaria_s2Actividad.val(null).trigger('change')

        if (idPrograma) {
            llavePresupuestaria_s2Proyecto.prop('disabled', false)
            llavePresupuestaria_s2Actividad.prop('disabled', false)
            await populateS2Proyectos(idPrograma, llavePresupuestaria_s2Proyecto)
            await populateS2Actividades(idPrograma, llavePresupuestaria_s2Actividad)
        } else {
            llavePresupuestaria_s2Proyecto.prop('disabled', true)
            llavePresupuestaria_s2Actividad.prop('disabled', true)
        }
        actualizarLlave()
    })

    $('#idUnidadEjecutora, #idPrograma, #idProyecto, #idActividad').on('change', function () {
        actualizarLlave()
        if (todosLosSelectsTienenValor()) {
            $('#formLlavePresupuestaria').validate().element('#idActividad')
        }
    })

    dtEvents.on('click', '.estado-trigger', function (e) {
        e.preventDefault()
        e.stopPropagation()
        const wrap = $(this).closest('.estado-wrap')
        const abierto = wrap.hasClass('open')
        cerrarMenusEstadoLlave()
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
        const rowApi = dt_llavePresupuestaria.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idLlavePresupuestaria', dt_row.IdLlavePresupuestaria)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresLlave()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdLlavePresupuestaria

        const datos = new FormData()
        datos.append('idLlavePresupuestaria', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la llave presupuestaria seleccionada?',
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
                    dt_llavePresupuestaria.ajax.reload(function () {
                        actualizarContadoresLlave()
                        popularFiltroUnidadEjecutoraLlave()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        idLlavePresupuestaria = dt_row.IdLlavePresupuestaria

        const datos = new FormData()
        datos.append('idLlavePresupuestaria', idLlavePresupuestaria)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(async function (data) {
                const obj = data.data
                llavePresupuestaria_s2UnidadEjecutora.val(obj.IdUnidadEjecutora).trigger('change.select2')

                llavePresupuestaria_s2Programa.val(obj.IdPrograma).trigger('change.select2')

                llavePresupuestaria_s2Proyecto.prop('disabled', false)
                llavePresupuestaria_s2Actividad.prop('disabled', false)

                await populateS2Proyectos(obj.IdPrograma, llavePresupuestaria_s2Proyecto, obj.IdProyecto)
                await populateS2Actividades(obj.IdPrograma, llavePresupuestaria_s2Actividad, obj.IdActividad)

                $('#organizacional').prop('checked', obj.esOrganizacional === 1 || obj.esOrganizacional === '1')
                const fechaInicio = obj.FechaInicio || ''
                $('#fechaInicio').val(fechaInicio.split(' ')[0])
                actualizarLlave()
                abrirModalLlave(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formLlavePresupuestaria').validate({
        ignore: [],
        rules: {
            idUnidadEjecutora: {
                required: true
            },
            idPrograma: {
                required: true
            },
            idProyecto: {
                required: true
            },
            idActividad: {
                required: true,
                require_from_group: [4, '.codigo_group'],
                remote: {
                    url: baseUrl + 'verificar-llave',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        idUnidadEjecutora: function () {
                            return $('#idUnidadEjecutora').val()
                        },
                        idPrograma: function () {
                            return $('#idPrograma').val()
                        },
                        idProyecto: function () {
                            return $('#idProyecto').val()
                        },
                        idActividad: function () {
                            return $('#idActividad').val()
                        },
                        idLlavePresupuestaria: function () {
                            return idLlavePresupuestaria
                        }
                    }
                }
            },
            fechaInicio: {
                required: true
            }
        },
        messages: {
            idUnidadEjecutora: {
                required: 'Debe seleccionar una unidad ejecutora'
            },
            idPrograma: {
                required: 'Debe seleccionar un programa'
            },
            idProyecto: {
                required: 'Debe seleccionar un proyecto'
            },
            idActividad: {
                required: 'Debe seleccionar una actividad',
                require_from_group: 'Debe completar todos los campos que intervienen en la llave presupuestaria',
                remote: 'errorLlave'
            },
            fechaInicio: {
                required: 'Debe seleccionar una fecha'
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback')
            const errorElement = $('#llave')
            errorElement.html('')

            if (element.attr('name') === 'idActividad' && error.text() === 'errorLlave') {
                $('.codigo_group').each(function () {
                    $(this).removeClass('is-valid').addClass('is-invalid')
                })
                errorElement.removeClass('is-valid').addClass('is-invalid')
                error.text('La llave presupuestaria ya se encuentra registrada')
                error.insertAfter(errorElement)
                return
            }

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
