$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/';

    programacionPoaTrimestral_s2ObjEspecifico.on('change', function () {
        if (!$(this).val()) {
            $('#mensajeInicial').show();
            $('#dticTableContainer, #dticTableLoading').hide();
            $('#resumenTrimestral [data-total]').text(0);
            return;
        }

        cargarProgramacionPoaTrimestral();
    });

    $('#tablaProgramacionPoaTrimestral')
        .on('click', '.input-meta-poa-trimestre[readonly]', function () {
            $(this)
                .prop('readonly', false)
                .data('original', $(this).val())
                .focus()
                .select();
        })
        .on('input', '.input-meta-poa-trimestre:not([readonly])', function () {
            this.value = this.value.replace(/\D/g, '');
        })
        .on('keydown', '.input-meta-poa-trimestre:not([readonly])', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                guardarMeta($(this));
            }
            if (event.key === 'Escape') {
                $(this)
                    .val($(this).data('original'))
                    .prop('readonly', true);
            }
        })
        .on('blur', '.input-meta-poa-trimestre:not([readonly])', function () {
            guardarMeta($(this));
        });

    function guardarMeta(input) {
        if (input.data('guardando')) return;

        const meta = Number(input.val());
        const original = Number(input.data('original'));
        const row = dt_programacionPoaTrimestral.row(input.closest('tr'));
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
            MostrarMensaje('error', 'No se pudo identificar la relación programada.');
            return;
        }

        input.data('guardando', true).prop('disabled', true);

        $.ajax({
            url: baseUrl + 'guardar-meta',
            method: 'POST',
            dataType: 'json',
            data: {
                idProgramacionIndicadorPoaGestion: input.data('idprogramacion'),
                idObjEspecifico: programacionPoaTrimestral_s2ObjEspecifico.val(),
                trimestre: input.data('trimestre'),
                meta
            },
            success: function (response) {
                Object.assign(rowData, response.data);
                row.data(rowData).invalidate();
                dt_programacionPoaTrimestral.draw(false);
            },
            error: function (xhr) {
                input.val(original);
                mostrarErrorTrimestral(xhr);
            },
            complete: function () {
                input
                    .data('guardando', false)
                    .prop('disabled', false)
                    .prop('readonly', true);
            }
        });
    }
});
