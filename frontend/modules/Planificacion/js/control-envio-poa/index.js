$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/control-envio-poa/';
    const tabla = $('#tablaListaEnvios');

    tabla.on('click', '.btn-delete', function () {
        const row = dt_envio.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar envío',
            text: 'La unidad podrá volver a editar el POA. ¿Desea continuar?',
            showCancelButton: true,
            confirmButtonText: 'Eliminar envío',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idControlEnvio', row.IdControlEnvio),
                spinnerBtn: $(this),
                successMsg: 'Envío eliminado correctamente.',
                reloadTable: dt_envio
            });
        });
    });
});
