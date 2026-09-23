$(document).ready(function () {
    const idObjEspecifico = $('#idObjEspecifico').val();
    if (!idObjEspecifico) {
        return;
    }

    $('#dticTableLoading').show();
    inicializarTablaIndicadoresPoaTrimestrales();
    dt_listaIndicadoresPoaTrimestrales.one('draw', function () {
        $('#dticTableLoading').hide();
        $('#dticTableContainer').fadeIn(180);
    });

    $('#btnAgregarIndicador').on('click', function () {
        limpiarModal();
        $('#modalRelacionPoa').modal('show');
    });

    $('#metaProgramada').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#btnGuardarRelacion').on('click', async function () {
        const idLlave = programacionPoaTrimestral_s2Llave.val();
        const idIndicador = programacionPoaTrimestral_s2Indicador.val();
        const meta = Number($('#metaProgramada').val());

        if (!idLlave || !idIndicador) {
            MostrarMensaje('warning', 'Debe seleccionar llave e indicador POA.');
            return;
        }
        if (!Number.isInteger(meta) || meta < 0) {
            MostrarMensaje('warning', 'La meta debe ser un entero mayor o igual a cero.');
            return;
        }

        const datos = new FormData();
        datos.append('idObjEspecifico', idObjEspecifico);
        datos.append('idLlavePresupuestaria', idLlave);
        datos.append('idIndicadorPoa', idIndicador);
        datos.append('metaProgramada', meta);

        try {
            await ajaxPromise({
                url: 'index.php?r=Planificacion/indicador-poa-programacion-anual/guardar',
                data: datos,
                spinnerBtn: $(this),
                successMsg: 'Indicador agregado correctamente.',
                onSuccess: () => {
                    $('#modalRelacionPoa').modal('hide');
                    recargarIndicadoresPoaTrimestrales();
                }
            });
        } catch (error) {
            console.error('No se pudo guardar el indicador.', error);
        }
    });

    $(document).on('click', '.input-meta-poa-trimestre[readonly]', function () {
        if (window.poaPuedeEditar === false) return;
        $(this).prop('readonly', false).data('original', $(this).val()).focus().select();
    });

    $(document).on('input', '.input-meta-poa-trimestre:not([readonly])', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $(document).on('keydown', '.input-meta-poa-trimestre:not([readonly])', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            guardarMeta($(this));
        }
        if (event.key === 'Escape') {
            $(this).val($(this).data('original')).prop('readonly', true);
        }
    });

    $(document).on('blur', '.input-meta-poa-trimestre:not([readonly])', function () {
        guardarMeta($(this));
    });

    function guardarMeta(input) {
        if (input.data('guardando')) return;

        const meta = Number(input.val());
        const original = Number(input.data('original'));
        const tabla = input.closest('table');
        const dtTable = tabla.DataTable();
        const row = dtTable.row(input.closest('tr'));
        const rowData = row.data();

        if (!Number.isInteger(meta) || meta < 0) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('warning', 'La meta debe ser un número entero mayor o igual a cero.');
            return;
        }
        if (meta === original) {
            input.prop('readonly', true);
            return;
        }
        if (!rowData) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('error', 'No se pudo identificar la relación programada.');
            return;
        }

        input.data('guardando', true).prop('disabled', true);

        $.ajax({
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/guardar-meta',
            method: 'POST',
            dataType: 'json',
            data: {
                idProgramacionIndicadorPoaGestion: input.data('idprogramacion'),
                idObjEspecifico: idObjEspecifico,
                trimestre: input.data('trimestre'),
                meta
            },
            success: function (response) {
                Object.assign(rowData, response.data);
                row.data(rowData).invalidate().draw(false);
            },
            error: function (xhr) {
                input.val(original);
                mostrarErrorTrimestral(xhr);
            },
            complete: function () {
                input.data('guardando', false).prop('disabled', false).prop('readonly', true);
            }
        });
    }

    function limpiarModal() {
        $('#formRelacionPoa').trigger('reset');
        $('#metaProgramada').val(0);
        programacionPoaTrimestral_s2Llave.val(null).trigger('change');
        programacionPoaTrimestral_s2Indicador.val(null).trigger('change');
    }
});
