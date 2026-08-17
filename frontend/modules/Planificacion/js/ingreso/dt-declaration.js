let dt_ingreso = null;

$(document).ready(function () {
    dt_ingreso = $('#tablaIngresos').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/ingreso/listar-todo',
            method: 'POST',
            dataType: 'json',
            dataSrc: 'data',
            error: mostrarErrorIngreso
        },
        order: [[5, 'desc']],
        columns: [
            {
                title: 'Cantidad',
                data: 'Cantidad',
                className: 'text-right',
                render: data => `<span class="monto-ingreso">${Number(data || 0).toLocaleString()}</span>`
            },
            {
                title: 'Precio',
                data: 'Precio',
                className: 'text-right',
                render: data => `<span class="monto-ingreso">${formatoMontoIngreso(data)}</span>`
            },
            {
                title: 'Total',
                data: null,
                className: 'text-right',
                render: row => `<span class="monto-ingreso">${formatoMontoIngreso(
                    Number(row.Cantidad || 0) * Number(row.Precio || 0)
                )}</span>`
            },
            {title: 'Descripción', data: 'Descripcion', defaultContent: ''},
            {
                title: 'Estado',
                data: 'CodigoEstado',
                className: 'text-center',
                render: data => `<button class="btn-toggle-estado ${data === 'V' ? 'activo' : 'inactivo'}">
                    ${data === 'V' ? 'Vigente' : 'Caduco'}
                </button>`
            },
            {title: 'Fecha', data: 'FechaHoraRegistro', className: 'text-center'},
            {
                title: 'Acciones',
                data: 'IdIngreso',
                className: 'text-center',
                orderable: false,
                render: () => `
                    <button class="btn-action btn-edit"><i class="fa fa-pen"></i></button>
                    <button class="btn-action btn-delete"><i class="fa fa-trash"></i></button>`
            }
        ],
        initComplete: function () {
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(180);
        }
    });
});

function formatoMontoIngreso(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function mostrarErrorIngreso(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo procesar el ingreso.'), data.errors);
}
