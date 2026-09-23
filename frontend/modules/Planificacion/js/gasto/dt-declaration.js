let dt_gasto = null;

$(document).ready(function () {
    dt_gasto = $('#tablaListaGastos').DataTable({
        initComplete: function () {
            $('div.dt-search').append(`
                <button id="refreshTable" class="btn-refresh">
                    <i class="fas fa-sync-alt fa-spin"></i>
                </button>`);
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(250);
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/gasto/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar los gastos.'), data.errors);
            }
        },
        columns: [
            {
                data: 'CodigoUsuario',
                className: 'text-center',
                width: '60px',
                orderable: false,
                searchable: false,
                render: data => `<div class="badge-codigo">${data || ''}</div>`
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return `${row.CodigoGasto || ''} ${row.Descripcion || ''} ${row.EntidadTransferencia || ''}`;
                    }
                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Gasto</span>
                            <div class="dtic-code-badge">${row.CodigoGasto || ''}</div>
                        </div>
                        <div class="dtic-item-main">${row.Descripcion || ''}</div>
                        <div class="dtic-item-sub"><b>Entidad transferencia:</b> ${row.EntidadTransferencia || ''}</div>
                    `;
                }
            },
            columnaEstadoCatalogo(),
            columnaAccionesCatalogo('IdGasto')
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_gasto.ajax.reload());
});
