function codigoEstadoDesdeEtiquetaFodaInst(etiqueta) {
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

function aplicarFiltrosFodaInst() {
    if (typeof refrescarTablasFodaInst === 'function') {
        refrescarTablasFodaInst()
    }
}

function sincronizarFiltroEstadoFodaInst(codigoEstado) {
    fodaInstFiltroCodigoEstado = codigoEstado || ''
    $('#filtroEstadoFoda').val(fodaInstFiltroCodigoEstado)
    $('.stats .stat').removeClass('active')
    if (fodaInstFiltroCodigoEstado) {
        $('.stats .stat').each(function () {
            if ($(this).data('estado') === fodaInstFiltroCodigoEstado) {
                $(this).addClass('active')
            }
        })
    }
    aplicarFiltrosFodaInst()
}

function initFodaInstitucionFiltros() {
    $('.stats').off('click.fodaInst').on('click.fodaInst', '.stat', function () {
        const codigo = $(this).data('estado') || codigoEstadoDesdeEtiquetaFodaInst($(this).find('.stat-label').text())
        if ($(this).hasClass('active')) {
            sincronizarFiltroEstadoFodaInst('')
            return
        }
        sincronizarFiltroEstadoFodaInst(codigo)
    })

    $('#buscarFoda').off('input.fodaInst').on('input.fodaInst', function () {
        fodaInstFiltroTexto = $(this).val().trim()
        aplicarFiltrosFodaInst()
    })

    $('#filtroEstadoFoda').off('change.fodaInst').on('change.fodaInst', function () {
        sincronizarFiltroEstadoFodaInst($(this).val())
    })

    $('#filtroIncidenciaFoda').off('change.fodaInst').on('change.fodaInst', function () {
        fodaInstFiltroIncidencia = $(this).val() || ''
        aplicarFiltrosFodaInst()
    })

    $('.foda-tab-btn').off('click.fodaInst').on('click.fodaInst', function () {
        activarTabFodaInst($(this).data('tipo'))
    })
}

$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000'
    const baseUrl = 'index.php?r=Planificacion/foda-institucion/'
    let idFoda = EMPTY
    const $modal = $('#modalFodaInstitucion')

    function tipoActivo() {
        return typeof fodaInstTipoActivo !== 'undefined' ? fodaInstTipoActivo : 'Fortaleza'
    }

    function pintarSelectIncidencia() {
        const $select = $('#incidencia')
        $select.removeClass('incidencia-alta incidencia-media incidencia-baja')
        const valor = ($select.val() || '').toLowerCase()
        if (valor) {
            $select.addClass('incidencia-' + valor)
        }
    }

    function reiniciarCampos() {
        const validator = $('#formFodaInstitucion').data('validator')
        if (validator) {
            validator.resetForm()
        }
        $('#formFodaInstitucion *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid')
        })
        $('#formFodaInstitucion').trigger('reset')
        idFoda = EMPTY
        $('#tipo').val(tipoActivo())
        $('#tipoActivoLabel').text(tipoActivo())
        pintarSelectIncidencia()
    }

    function mensajeAccion(accion) {
        return 'Los datos del FODA se ' + accion + 'ron correctamente.'
    }

    function tituloModal(esEdicion) {
        return esEdicion ? 'Edición de datos' : 'Ingreso de datos'
    }

    function abrirModal(esEdicion) {
        $('#fodaModalTitulo').text(tituloModal(esEdicion))
        $modal.modal('show')
    }

    function cerrarModal() {
        reiniciarCampos()
        $modal.modal('hide')
    }

    async function guardarFormulario(opciones) {
        const btn = opciones.spinnerBtn
        const continuar = !!opciones.continuar

        if (!$('#formFodaInstitucion').valid()) {
            return false
        }

        const datos = new FormData()
        datos.append('idFoda', idFoda)
        datos.append('tipo', $('#tipo').val())
        datos.append('incidencia', $('#incidencia').val())
        datos.append('descripcion', $('#descripcion').val())

        const esEdicion = idFoda !== EMPTY
        const accion = esEdicion ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: continuar ? null : $('#btnCancelar'),
                successMsg: mensajeAccion(accion),
                onSuccess: function () {
                    cargarListadoFodaInst()
                    if (continuar) {
                        reiniciarCampos()
                        $('#fodaModalTitulo').text(tituloModal(false))
                    } else {
                        cerrarModal()
                    }
                }
            })
            return true
        } catch (err) {
            console.error('Error al procesar:', err)
            return false
        }
    }

    $('#incidencia').on('change', pintarSelectIncidencia)

    $('#btnNuevoFoda').on('click', function () {
        reiniciarCampos()
        abrirModal(false)
    })

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank')
    })

    $('#btnCancelar').on('click', function () {
        cerrarModal()
    })

    $modal.on('hidden.bs.modal', function () {
        reiniciarCampos()
    })

    $('#btnGuardar').on('click', function () {
        guardarFormulario({
            spinnerBtn: $(this),
            continuar: false
        })
    })

    $('#btnGuardarContinuar').on('click', function () {
        guardarFormulario({
            spinnerBtn: $(this),
            continuar: true
        })
    })

    $(document).on('click', '.tabla-foda .estado-trigger', function (e) {
        e.preventDefault()
        e.stopPropagation()
        const wrap = $(this).closest('.estado-wrap')
        const abierto = wrap.hasClass('open')
        cerrarMenusEstadoFodaInst()
        wrap.toggleClass('open', !abierto)
    })

    $(document).on('click', '.tabla-foda .estado-menu', function (e) {
        e.stopPropagation()
    })

    $(document).on('click', '.tabla-foda .estado-option', async function (e) {
        e.preventDefault()
        e.stopPropagation()

        const option = $(this)
        const wrap = option.closest('.estado-wrap')
        const table = option.closest('table')
        const tableId = table.attr('id')
        const tab = FODA_INST_TABS.find(function (t) {
            return t.tableId === tableId
        })
        const dt = tab ? dtFodaInstitucionByTipo[tab.tipo] : null
        if (!dt) {
            wrap.removeClass('open')
            return
        }

        const rowApi = dt.row(option.closest('tr'))
        const dtRow = rowApi.data()
        const codigoNuevo = option.data('estado')

        if (!dtRow || codigoNuevo === dtRow.CodigoEstado) {
            wrap.removeClass('open')
            return
        }

        const datos = new FormData()
        datos.append('idFoda', dtRow.IdFoda)

        try {
            await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                successMsg: 'Estado actualizado correctamente.'
            })
            dtRow.CodigoEstado = codigoNuevo
            const cached = fodaInstCache.find(function (r) {
                return r.IdFoda === dtRow.IdFoda
            })
            if (cached) {
                cached.CodigoEstado = codigoNuevo
            }
            rowApi.data(dtRow).draw(false)
            actualizarContadoresFodaInst()
        } catch (err) {
            console.error('Error al procesar:', err)
        } finally {
            wrap.removeClass('open')
        }
    })

    $(document).on('click', '.tabla-foda .btn-action-new.delete', function () {
        const objectBtn = $(this)
        const table = objectBtn.closest('table')
        const tableId = table.attr('id')
        const tab = FODA_INST_TABS.find(function (t) {
            return t.tableId === tableId
        })
        const dt = tab ? dtFodaInstitucionByTipo[tab.tipo] : null
        if (!dt) {
            return
        }

        const dtRow = dt.row(objectBtn.closest('tr')).data()
        const datos = new FormData()
        datos.append('idFoda', dtRow.IdFoda)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar el registro FODA seleccionado?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (!resultado.value) {
                return
            }
            try {
                await ajaxPromise({
                    url: baseUrl + 'eliminar',
                    data: datos,
                    spinnerBtn: objectBtn,
                    successMsg: mensajeAccion('eliminar')
                })
                cargarListadoFodaInst()
            } catch (err) {
                console.error('Error al procesar:', err)
            }
        })
    })

    $(document).on('click', '.tabla-foda .btn-action-new.edit', async function () {
        const objectBtn = $(this)
        const table = objectBtn.closest('table')
        const tableId = table.attr('id')
        const tab = FODA_INST_TABS.find(function (t) {
            return t.tableId === tableId
        })
        const dt = tab ? dtFodaInstitucionByTipo[tab.tipo] : null
        if (!dt) {
            return
        }

        const dtRow = dt.row(objectBtn.closest('tr')).data()
        idFoda = dtRow.IdFoda

        const datos = new FormData()
        datos.append('idFoda', idFoda)

        try {
            await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn
            }).then(function (data) {
                const registro = data.data
                $('#tipo').val(registro.Tipo)
                $('#tipoActivoLabel').text(registro.Tipo)
                $('#incidencia').val(registro.Incidencia)
                $('#descripcion').val(registro.Descripcion || '')
                pintarSelectIncidencia()
                abrirModal(true)
            })
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $('#formFodaInstitucion').validate({
        rules: {
            incidencia: {required: true},
            descripcion: {required: true, minlength: 2, maxlength: 500}
        },
        messages: {
            incidencia: {
                required: 'Debe seleccionar una incidencia'
            },
            descripcion: {
                required: 'Debe ingresar la descripción',
                minlength: 'La descripción debe tener por lo menos 2 caracteres',
                maxlength: 'La descripción debe tener máximo 500 caracteres'
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
