$(document).ready(function () {
    const idObjEstrategico = $('#idObjEstrategico').val()
    if (!idObjEstrategico) {
        return
    }

    const baseUrl = 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/'

    $('#dticTableLoading').show()
    $('#dticTableContainer').hide()
    inicializarTablaIndicadoresTrimestrales()

    if (dt_listaIndicadoresTrimestrales) {
        dt_listaIndicadoresTrimestrales.one('draw', function () {
            $('#dticTableLoading').hide()
            $('#dticTableContainer').fadeIn(180)
        })
    }

    $(document).on('click', '#refreshTable', function () {
        if (!dt_listaIndicadoresTrimestrales) {
            return
        }
        if (openedRowTrimestral) {
            cerrarFilaTrimestral(openedRowTrimestral)
            openedRowTrimestral = null
        }
        dt_listaIndicadoresTrimestrales.ajax.reload(null, false)
    })

    $(document).on('click', '.btnReiniciarTrimestre:not(:disabled)', function () {
        const objectBtn = $(this)
        const idProgramacion = objectBtn.data('idprogramacion')
        const tabla = objectBtn.closest('table')
        const dtTable = tabla.DataTable()
        const rowApi = dtTable.row(objectBtn.closest('tr'))
        const rowData = rowApi.data()

        if (!idProgramacion || !rowData) {
            MostrarMensaje('error', 'No se pudo identificar la programación trimestral.')
            return
        }

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación reinicio',
            text: '¿Está seguro de reiniciar la programación trimestral de esta llave?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Reiniciar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
        }).then(async function (resultado) {
            if (!resultado.value) {
                return
            }

            const datos = new FormData()
            datos.append('idProgramacionIndicadorGestio', idProgramacion)

            try {
                const response = await ajaxPromise({
                    url: baseUrl + 'eliminar',
                    data: datos,
                    spinnerBtn: objectBtn,
                    successMsg: 'Programación trimestral reiniciada correctamente.',
                })

                Object.assign(rowData, response.data || {})
                rowApi.data(rowData).invalidate().draw(false)
                aplicarEstadoFilaTrimestral(rowApi.node(), rowData)
            } catch (err) {
                console.error('Error al reiniciar programación trimestral:', err)
            }
        })
    })

    $(document).on('click', '.input-meta-trimestre[readonly]', function () {
        $(this).prop('readonly', false).data('original', $(this).val()).focus().select()
    })

    $(document).on('input', '.input-meta-trimestre:not([readonly])', function () {
        this.value = this.value.replace(/\D/g, '')
    })

    $(document).on('keydown', '.input-meta-trimestre:not([readonly])', function (event) {
        if (event.key === 'Enter' || event.which === 13) {
            event.preventDefault()
            guardarMetaTrimestral($(this))
        }
        if (event.key === 'Escape') {
            $(this).val($(this).data('original')).prop('readonly', true).removeClass('is-invalid meta-saved')
        }
    })

    $(document).on('blur', '.input-meta-trimestre:not([readonly])', function () {
        const input = $(this)
        if (!input.data('guardando')) {
            guardarMetaTrimestral(input)
        }
    })

    async function guardarMetaTrimestral(input) {
        if (input.data('guardando')) {
            return
        }

        const meta = Number(input.val())
        const original = Number(input.data('original'))
        const tabla = input.closest('table')
        const dtTable = tabla.DataTable()
        const rowApi = dtTable.row(input.closest('tr'))
        const rowData = rowApi.data()

        if (!Number.isInteger(meta) || meta < 0) {
            input.val(original).prop('readonly', true)
            MostrarMensaje('warning', 'La meta debe ser un número entero mayor o igual a cero.')
            return
        }

        if (meta === original) {
            input.prop('readonly', true)
            return
        }

        if (!rowData) {
            input.val(original).prop('readonly', true)
            MostrarMensaje('error', 'No se pudo identificar la programación anual.')
            return
        }

        input.data('guardando', true)
            .prop('disabled', true)
            .removeClass('is-valid is-invalid meta-saved')

        const datos = new FormData()
        datos.append('idProgramacionIndicadorGestio', input.data('idprogramacion'))
        datos.append('trimestre', input.data('trimestre'))
        datos.append('meta', meta)

        try {
            const response = await ajaxPromise({
                url: baseUrl + 'guardar-meta',
                data: datos,
                successMsg: 'Meta trimestral guardada correctamente.',
            })

            const trimestre = input.data('trimestre')
            Object.assign(rowData, response.data || {})
            rowApi.data(rowData).invalidate().draw(false)
            aplicarEstadoFilaTrimestral(rowApi.node(), rowData)

            const $nuevo = $(rowApi.node()).find(`.input-meta-trimestre[data-trimestre="${trimestre}"]`)
            $nuevo
                .prop('readonly', true)
                .prop('disabled', false)
                .addClass('is-valid meta-saved')
                .data('original', meta)
                .data('guardando', false)

            setTimeout(function () {
                $nuevo.removeClass('is-valid meta-saved')
            }, 1200)
        } catch (err) {
            input.val(original).addClass('is-invalid').prop('readonly', false).focus()
            console.error('Error al guardar meta trimestral:', err)
        } finally {
            input.data('guardando', false).prop('disabled', false)
        }
    }

    function aplicarEstadoFilaTrimestral(node, data) {
        if (!node) {
            return
        }
        $(node)
            .removeClass('programacion-completa programacion-pendiente')
            .addClass(Number(data.ProgramacionCompleta) === 1 ? 'programacion-completa' : 'programacion-pendiente')
    }
})
