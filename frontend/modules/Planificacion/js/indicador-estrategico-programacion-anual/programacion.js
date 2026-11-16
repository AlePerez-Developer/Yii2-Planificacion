$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/'

    programacionAnual_s2ObjEstrategico.on('change', function () {
        const idObjEstrategico = programacionAnual_s2ObjEstrategico.select2('data')[0]?.id
        const placeHolder = $('#mensajeInicial')
        const loader = $('#dticTableLoading')
        const container = $('#dticTableContainer')

        if (openedRow) {
            closeChildRow(openedRow)
            openedRow = null
        }

        if (!idObjEstrategico) {
            container.hide()
            loader.hide()
            placeHolder.show()
            $('#progAnualSubtitle').text('Seleccione un objetivo estratégico')
            return
        }

        placeHolder.hide()
        loader.show()
        container.hide()
        $('#progAnualSubtitle').text('Indicadores del objetivo seleccionado')

        if ($.fn.DataTable.isDataTable('#tablaListaIndicadores')) {
            dt_listaIndicadores.ajax.reload(function () {
                loader.hide()
                container.fadeIn(180)
            }, false)
        } else {
            inicializarTablaIndicadores()
            dt_listaIndicadores.one('draw', function () {
                loader.hide()
                container.fadeIn(180)
            })
        }
    })

    $(document).on('click', '#refreshTable', function () {
        if (!$.fn.DataTable.isDataTable('#tablaListaIndicadores')) {
            return
        }
        if (openedRow) {
            closeChildRow(openedRow)
            openedRow = null
        }
        dt_listaIndicadores.ajax.reload(null, false)
    })

    function mensajeAccion(accion) {
        return `Los datos de la programación se ${accion}ron correctamente.`
    }

    $(document).on('click', 'tbody .btnEstado', async function () {
        const btn = $(this)
        const datos = new FormData()
        datos.append('idLlavePresupuestaria', btn.data('idllave'))
        datos.append('codigoIndicador', btn.data('indicador'))
        datos.append('gestion', btn.data('gestion'))

        try {
            const data = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                spinnerBtn: btn,
                successMsg: 'Asignación de llave actualizada.',
            })
            cambiarEstado(btn, data.data)
        } catch (err) {
            console.error('Error al procesar:', err)
        }
    })

    $(document).on('click', 'tbody .btnQuitar', function () {
        const objectBtn = $(this)
        const tabla = objectBtn.closest('table')
        const codigo = objectBtn.data('codigoindicador')
        const gestion = objectBtn.data('gestion')
        const dt_table = tabla.DataTable()
        const dt_row = dt_table.row(objectBtn.closest('tr')).data()
        const rowId = dt_row.IdProgramacionIndicadorGestion
        const idIndicadorEstrategico = dt_row.IdIndicador

        const datos = new FormData()
        datos.append('idProgramacion', rowId)

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación eliminación',
            text: '¿Está seguro de eliminar la programación?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + 'eliminar',
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: dt_table,
                    })
                    actualizarSumaGlobal(codigo)
                    actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion)
                } catch (err) {
                    console.error('Error al procesar:', err)
                }
            }
        })
    })

    $(document).on('click', '.input-meta', function () {
        const input = $(this)
        if (input.prop('readonly')) {
            input.prop('readonly', false).select()
        }
    })

    $(document).on('keydown', '.input-meta', async function (e) {
        if (e.key !== 'Enter' && e.which !== 13) {
            return
        }

        e.preventDefault()
        const objectInput = $(this)
        if (objectInput.data('saving')) {
            return
        }

        const meta = objectInput.val()
        const tabla = objectInput.closest('table')
        const dt_table = tabla.DataTable()
        const dt_row = dt_table.row(objectInput.closest('tr')).data()
        if (!dt_row) {
            return
        }

        const rowId = dt_row.IdProgramacionIndicadorGestion
        const idIndicadorEstrategico = dt_row.IdIndicador
        const codigo = objectInput.data('codigoindicador')
        const gestion = objectInput.data('gestion')

        objectInput.data('saving', true)
        objectInput.prop('disabled', true)
            .removeClass('is-valid is-invalid meta-saved')

        const datos = new FormData()
        datos.append('idProgramacion', rowId)
        datos.append('meta', meta)

        try {
            await ajaxPromise({
                url: baseUrl + 'guardar-meta',
                data: datos,
                successMsg: 'Meta guardada correctamente.',
            })

            objectInput
                .prop('readonly', true)
                .prop('disabled', false)
                .addClass('is-valid meta-saved')

            dt_row.Meta = meta
            actualizarSumaGlobal(codigo)
            actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion)

            setTimeout(function () {
                objectInput.removeClass('is-valid meta-saved')
            }, 1400)
        } catch (err) {
            objectInput
                .prop('disabled', false)
                .prop('readonly', false)
                .addClass('is-invalid')
                .focus()
            console.error('Error al guardar meta:', err)
        } finally {
            objectInput.data('saving', false)
        }
    })

    function actualizarSumaGlobal(codigo) {
        const metaGlobal = paNumero($('#paMetaTot_' + codigo).text())
        $.ajax({
            url: baseUrl + 'calcular-meta',
            method: 'POST',
            dataType: 'json',
            data: {codigoIndicador: codigo},
            success: function (resp) {
                const nuevoTotal = paNumero(resp.data)
                actualizarMetaFracDom(codigo, nuevoTotal, metaGlobal)

                if (typeof dt_listaIndicadores !== 'undefined' && dt_listaIndicadores) {
                    dt_listaIndicadores.rows().every(function () {
                        const row = this.data()
                        if (row && String(row.Codigo) === String(codigo)) {
                            row.MetaProgramada = nuevoTotal
                        }
                    })
                }
            },
        })
    }

    function actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion) {
        const tableId = 'tbl_' + codigo + '_' + gestion
        const metaGlobal = paNumero($('#sum_' + tableId).attr('data-meta-global'))
        $.ajax({
            url: baseUrl + 'calcular-meta-gestion',
            method: 'POST',
            dataType: 'json',
            data: {
                idIndicadorEstrategico: idIndicadorEstrategico,
                gestion: gestion,
            },
            success: function (resp) {
                const nuevoTotal = paNumero(resp.data)
                actualizarTabMetaDom(tableId, nuevoTotal, metaGlobal)
                $('#nav-pane_' + codigo + '_' + gestion + ' .iepa-tab-meta strong')
                    .text(paFormatoMeta(nuevoTotal) + '/' + paFormatoMeta(metaGlobal))
            },
        })
    }

    $(document).on('blur', '.input-meta', function () {
        const input = $(this)
        if (!input.data('saving')) {
            input.prop('readonly', true).prop('disabled', false)
        }
    })

    function cambiarEstado(objectBtn, data) {
        if (data === 0) {
            objectBtn.addClass('estado-on').removeClass('estado-off')
            objectBtn.find('.btn_text').html('Agregar')
        } else {
            objectBtn.removeClass('estado-on').addClass('estado-off')
            objectBtn.find('.btn_text').html('Quitar')
        }
    }
})
