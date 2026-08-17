$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/indicador-poa-programacion-anual/';

    programacionPoaAnual_s2ObjEspecifico.on('change', function () {
        const seleccionado = Boolean($(this).val());
        $('#btnAgregarRelacion').prop('disabled', !seleccionado);

        if (!seleccionado) {
            $('#mensajeInicial').show();
            $('#dticTableContainer, #dticTableLoading').hide();
            $('#resumenAnual').text('Total programado: 0');
            return;
        }

        cargarProgramacionPoaAnual();
    });

    $('#btnAgregarRelacion').on('click', function () {
        if (!programacionPoaAnual_s2ObjEspecifico.val()) return;
        limpiarModal();
        $('#modalRelacionPoa').modal('show');
    });

    $('#metaProgramada').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#btnGuardarRelacion').on('click', async function () {
        const idObjetivo = programacionPoaAnual_s2ObjEspecifico.val();
        const idLlave = programacionPoaAnual_s2Llave.val();
        const idIndicador = programacionPoaAnual_s2Indicador.val();
        const meta = Number($('#metaProgramada').val());

        if (!idObjetivo || !idLlave || !idIndicador) {
            MostrarMensaje('warning', 'Debe seleccionar objetivo, llave e indicador POA.');
            return;
        }
        if (!Number.isInteger(meta) || meta < 0) {
            MostrarMensaje('warning', 'La meta debe ser un entero mayor o igual a cero.');
            return;
        }

        const datos = new FormData();
        datos.append('idObjEspecifico', idObjetivo);
        datos.append('idLlavePresupuestaria', idLlave);
        datos.append('idIndicadorPoa', idIndicador);
        datos.append('metaProgramada', meta);

        try {
            await ajaxPromise({
                url: baseUrl + 'guardar',
                data: datos,
                spinnerBtn: $(this),
                successMsg: 'Relación agregada correctamente.',
                reloadTable: dt_programacionPoaAnual,
                onSuccess: () => $('#modalRelacionPoa').modal('hide')
            });
        } catch (error) {
            console.error('No se pudo guardar la relación.', error);
        }
    });

    $('#tablaProgramacionPoaAnual')
        .on('click', '.input-meta-poa-anual[readonly]', function () {
            $(this)
                .prop('readonly', false)
                .data('original', $(this).val())
                .focus()
                .select();
        })
        .on('input', '.input-meta-poa-anual:not([readonly])', function () {
            this.value = this.value.replace(/\D/g, '');
        })
        .on('keydown', '.input-meta-poa-anual:not([readonly])', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                guardarMetaProgramada($(this));
            }
            if (event.key === 'Escape') {
                $(this)
                    .val($(this).data('original'))
                    .prop('readonly', true);
            }
        })
        .on('blur', '.input-meta-poa-anual:not([readonly])', function () {
            guardarMetaProgramada($(this));
        });

    $('#tablaProgramacionPoaAnual').on('click', '.btn-delete-programacion', function () {
        const row = dt_programacionPoaAnual.row($(this).closest('tr')).data();

        Swal.fire({
            icon: 'warning',
            title: 'Eliminar programación',
            text: '¿Está seguro de eliminar esta relación anual?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const datos = new FormData();
            datos.append(
                'idProgramacion',
                row.IdProgramacionIndicadorPoaGestion
            );
            datos.append(
                'idObjEspecifico',
                programacionPoaAnual_s2ObjEspecifico.val()
            );

            try {
                await ajaxPromise({
                    url: baseUrl + 'eliminar',
                    data: datos,
                    successMsg: 'Programación eliminada correctamente.',
                    reloadTable: dt_programacionPoaAnual
                });
            } catch (error) {
                console.error('No se pudo eliminar la programación.', error);
            }
        });
    });

    function guardarMetaProgramada(input) {
        if (input.data('guardando')) return;

        const meta = Number(input.val());
        const original = Number(input.data('original'));
        const row = dt_programacionPoaAnual.row(input.closest('tr'));
        const rowData = row.data();

        if (!Number.isInteger(meta) || meta < 0) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('warning', 'La meta debe ser un entero mayor o igual a cero.');
            return;
        }
        if (meta === original) {
            input.prop('readonly', true);
            return;
        }
        if (!rowData) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('error', 'No se pudo identificar la programación.');
            return;
        }

        input.data('guardando', true).prop('disabled', true);

        $.ajax({
            url: baseUrl + 'actualizar-meta',
            method: 'POST',
            dataType: 'json',
            data: {
                idProgramacion: input.data('idprogramacion'),
                idObjEspecifico: programacionPoaAnual_s2ObjEspecifico.val(),
                metaProgramada: meta
            },
            success: function (response) {
                rowData.MetaProgramada = response.data.MetaProgramada;
                row.data(rowData).invalidate();
                dt_programacionPoaAnual.draw(false);
            },
            error: function (xhr) {
                input.val(original);
                mostrarErrorProgramacionPoa(xhr);
            },
            complete: function () {
                input
                    .data('guardando', false)
                    .prop('disabled', false)
                    .prop('readonly', true);
            }
        });
    }

    function limpiarModal() {
        $('#formRelacionPoa').trigger('reset');
        $('#metaProgramada').val(0);
        programacionPoaAnual_s2Llave.val(null).trigger('change');
        programacionPoaAnual_s2Indicador.val(null).trigger('change');
    }
});
