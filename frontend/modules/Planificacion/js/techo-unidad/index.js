$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/techo-unidad/';
    let resumen = null;

    cargarResumen();

    $('#tablaTechos').on('input', '.input-techo', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#tablaTechos').on('click', '.btn-save-techo', function () {
        const row = dt_techoUnidad.row($(this).closest('tr'));
        const data = row.data();
        const input = $(this).closest('tr').find('.input-techo');
        const techo = Number(input.val());
        const original = Number(input.data('original'));
        const usado = Number(input.data('usado'));

        if (!Number.isInteger(techo) || techo <= 0) {
            MostrarMensaje('warning', 'El techo debe ser un entero positivo.');
            return;
        }

        if (techo < usado) {
            MostrarMensaje('warning', `No puede reducir el techo: la llave ya tiene ${usado.toLocaleString()} en uso.`);
            return;
        }

        if (resumen && resumen.ExistenIngresos) {
            const otros = Number(resumen.TotalTechos || 0) - original;
            const disponible = Number(resumen.TotalIngresos || 0) - otros;
            if (techo > disponible) {
                MostrarMensaje('warning', `El techo supera el monto disponible (${disponible}).`);
                return;
            }
        }

        const form = new FormData();
        form.append('idLlavePresupuestaria', data.IdLlavePresupuestaria);
        form.append('techo', techo);

        ajaxPromise({
            url: baseUrl + 'guardar',
            data: form,
            spinnerBtn: $(this),
            successMsg: 'Techo guardado correctamente.',
            reloadTable: dt_techoUnidad,
            onSuccess: cargarResumen
        }).catch(console.error);
    });

    $('#tablaTechos').on('click', '.btn-delete-techo', function () {
        const data = dt_techoUnidad.row($(this).closest('tr')).data();
        const form = new FormData();
        form.append('idAsignacion', data.IdAsignacion);

        ajaxPromise({
            url: baseUrl + 'eliminar',
            data: form,
            successMsg: 'Techo eliminado correctamente.',
            reloadTable: dt_techoUnidad,
            onSuccess: cargarResumen
        }).catch(console.error);
    });

    function cargarResumen() {
        $.post(baseUrl + 'resumen', response => {
            resumen = response.data;
            const limitado = Boolean(resumen.ExistenIngresos);
            $('[data-resumen="ingresos"]').text(Number(resumen.TotalIngresos || 0).toLocaleString());
            $('[data-resumen="techos"]').text(Number(resumen.TotalTechos || 0).toLocaleString());
            $('[data-resumen="disponible"]').text(
                limitado ? Number(resumen.Disponible || 0).toLocaleString() : 'Libre'
            );
            $('[data-resumen="regla"]')
                .text(limitado ? 'Limitado por ingresos' : 'Sin ingresos: monto libre')
                .toggleClass('libre', !limitado);
        }, 'json');
    }
});
