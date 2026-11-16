let proyectoFiltroTexto = ''
let proyectoFiltroCodigoEstado = ''
let proyectoFiltroIdPrograma = ''

function codigoEstadoDesdeEtiquetaProyecto(etiqueta) {
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

function aplicarFiltrosTablaProyecto() {
    if (typeof dt_proyecto === 'undefined' || !dt_proyecto) {
        return
    }
    dt_proyecto.draw()
}

function sincronizarFiltroEstadoProyecto(codigoEstado) {
    proyectoFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoProyecto').val(proyectoFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (proyectoFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            const statEtiqueta = $(this).find('.stat-label').text().trim()
            if (codigoEstadoDesdeEtiquetaProyecto(statEtiqueta) === proyectoFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosTablaProyecto()
}

function initProyectoListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__proyectoListadoFiltro === true
    })) {
        aplicarFiltrosTablaProyecto()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaProyectos') {
            return true
        }

        const row = dt_proyecto.row(dataIndex).data()
        if (!row) {
            return true
        }

        if (proyectoFiltroIdPrograma && row.IdPrograma !== proyectoFiltroIdPrograma) {
            return false
        }

        if (proyectoFiltroCodigoEstado && row.CodigoEstado !== proyectoFiltroCodigoEstado) {
            return false
        }

        if (!proyectoFiltroTexto) {
            return true
        }

        const termino = proyectoFiltroTexto.toLowerCase()
        const codigo = (row.Codigo || '').toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const progCodigo = ((row.programa && row.programa.Codigo) ? row.programa.Codigo : (row.codex || '')).toLowerCase()
        const progDesc = ((row.programa && row.programa.Descripcion) ? row.programa.Descripcion : '').toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || progCodigo.indexOf(termino) !== -1
            || progDesc.indexOf(termino) !== -1
    }
    filtroFn.__proyectoListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('.stats').on('click', '.stat', function () {
        const etiqueta = $(this).find('.stat-label').text().trim()
        const codigo = codigoEstadoDesdeEtiquetaProyecto(etiqueta)

        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoProyecto('')
            return
        }

        sincronizarFiltroEstadoProyecto(codigo)
    })

    $('#buscarProyecto').on('input', function () {
        proyectoFiltroTexto = $(this).val().trim()
        aplicarFiltrosTablaProyecto()
    })

    $('#filtroEstadoProyecto').on('change', function () {
        sincronizarFiltroEstadoProyecto($(this).val())
    })

    $('#filtroPrograma').on('change', function () {
        proyectoFiltroIdPrograma = $(this).val() || ''
        aplicarFiltrosTablaProyecto()
    })
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'
    let idProyecto = ID_EMPTY_GUID
    const baseUrl = 'index.php?r=Planificacion/proyecto/'
    const dtEvents = $('#tablaListaProyectos')
    const $modalProyecto = $('#modalProyecto')

    function reiniciarCampos() {
        const validator = $('#formProyecto').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formProyecto *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        if (typeof proyecto_s2Programa !== 'undefined') {
            proyecto_s2Programa.val(null).trigger('change')
        }
        $('#formProyecto').trigger('reset')
        idProyecto = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del proyecto se ${accion}ron correctamente.`
    }

    function tituloModalProyecto(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModalProyecto(esEdicion) {
        $('#proyectoModalTitulo').text(tituloModalProyecto(esEdicion))
        $modalProyecto.modal('show')
    }

    function cerrarModalProyecto() {
        reiniciarCampos()
        $modalProyecto.modal('hide')
    }

    async function guardarFormularioProyecto(opciones) {
        const btn = opciones.spinnerBtn
        const cerrarAlExito = !!opciones.cerrarAlExito
        const continuar = !!opciones.continuar

        if (!$('#formProyecto').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idProyecto', idProyecto)
        datos.append('idPrograma', $('#idPrograma').val())
        datos.append('codigo', $('#codigo').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idProyecto !== ID_EMPTY_GUID
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: cerrarAlExito ? $('#btnCancelar') : null,
                successMsg: mensajeAccion(accion),
                reloadTable: typeof dt_proyecto !== 'undefined' ? dt_proyecto : null,
                onSuccess: function () {
                    if (typeof actualizarContadoresProyecto === 'function') {
                        actualizarContadoresProyecto()
                    }
                    if (typeof popularFiltroProgramaProyecto === 'function') {
                        popularFiltroProgramaProyecto()
                    }
                    if (continuar) {
                        reiniciarCampos()
                        $('#proyectoModalTitulo').text(tituloModalProyecto(false))
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#btnNuevoProyecto').on('click', function () {
        reiniciarCampos()
        abrirModalProyecto(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModalProyecto()
    })

    $modalProyecto.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormularioProyecto({
            spinnerBtn: $(this),
            cerrarAlExito: true,
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormularioProyecto({
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
        cerrarMenusEstadoProyecto()
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
        const rowApi = dt_proyecto.row(tr)
        const dt_row = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dt_row || codigoNuevo === dt_row.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idProyecto', dt_row.IdProyecto)
        datos.append('codigoEstado', codigoNuevo)

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.',
            })
            dt_row.CodigoEstado = data.data
            rowApi.data(dt_row).draw(false)
            actualizarContadoresProyecto()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    dtEvents.on('click', '.btn-action-new.delete', function () {
        const objectBtn = $(this)
        const dt_row = dt_proyecto.row(objectBtn.closest('tr')).data()
        const id = dt_row.IdProyecto

        const datos = new FormData()
        datos.append('idProyecto', id)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el proyecto seleccionado?',
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
                    dt_proyecto.ajax.reload(function () {
                        actualizarContadoresProyecto()
                        popularFiltroProgramaProyecto()
                    }, false)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    dtEvents.on('click', '.btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const dt_row = dt_proyecto.row(objectBtn.closest('tr')).data()
        idProyecto = dt_row.IdProyecto

        const datos = new FormData()
        datos.append('idProyecto', idProyecto)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn,
            }).then(function (data) {
                const registro = data.data
                if (typeof proyecto_s2Programa !== 'undefined') {
                    proyecto_s2Programa.val(registro.IdPrograma).trigger('change.select2')
                }
                $('#codigo').val(registro.Codigo)
                $('#descripcion').val(registro.Descripcion)
                abrirModalProyecto(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formProyecto').validate({
        ignore: [],
        rules: {
            idPrograma: {
                required: true
            },
            codigo: {
                required: true,
                minlength: 3,
                maxlength: 20,
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
                        idProyecto: function () {
                            return idProyecto
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
                required: 'Debe ingresar un código de proyecto',
                require_from_group: 'Debe seleccionar un programa para validar el código',
                minlength: 'El código debe tener al menos 3 dígitos',
                maxlength: 'El código debe tener máximo 20 dígitos',
                remote: 'El código ingresado ya se encuentra en uso'
            },
            descripcion: {
                required: 'Debe ingresar la descripción del proyecto',
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
