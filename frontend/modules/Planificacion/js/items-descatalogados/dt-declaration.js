let dt_itemsDescatalogados = null;

$(document).ready(function () {
    const formulario = Number($('#itemsDescatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/items-descatalogados/';

    dt_itemsDescatalogados = $('#tablaListaItemsDescatalogados').DataTable({
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
            url: base + 'listar-todo',
            data: {formulario},
            dataSrc: function (json) {
                const payload = json.data || {};
                const total = payload.totalFormulario ?? 0;
                $('#totalFormulario').text(formatoMontoDesc(total));
                return payload.items || [];
            },
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar los ítems.'), data.errors);
            }
        },
        columns: [
            {
                title: '',
                data: null,
                className: 'text-center details-control',
                orderable: false,
                searchable: false,
                width: '40px',
                defaultContent: '<i class="fas fa-chevron-right"></i>'
            },
            {
                title: 'Ítem',
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return `${row.Descripcion || ''} ${row.GastoCodigo || ''} ${row.GastoDescripcion || ''}`;
                    }
                    return `
                        <div class="dtic-item-main">${row.Descripcion || ''}</div>
                        <div class="dtic-item-sub"><b>Gasto:</b> ${row.GastoCodigo || ''} - ${row.GastoDescripcion || ''}</div>
                        <div class="dtic-item-sub">${row.FuenteDescripcion || ''} / ${row.OrganismoDescripcion || ''}</div>
                    `;
                }
            },
            {
                title: 'U.M.',
                data: 'UnidadMedidaSimbolo',
                className: 'text-center',
                width: '80px',
                render: (data, type, row) => row.UnidadMedidaSimbolo || row.UnidadMedidaDescripcion || ''
            },
            {
                title: 'Cantidad',
                data: 'CantidadTotal',
                className: 'text-right',
                width: '110px',
                render: formatoMontoDesc
            },
            {
                title: 'Precio',
                data: 'Precio',
                className: 'text-right',
                width: '110px',
                render: formatoMontoDesc
            },
            {
                title: 'Total',
                data: 'TotalItem',
                className: 'text-right',
                width: '120px',
                render: formatoMontoDesc
            },
            {
                title: '',
                data: 'IdItem',
                className: 'text-center',
                width: '120px',
                orderable: false,
                searchable: false,
                render: function () {
                    return htmlAccionesPoa();
                }
            }
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_itemsDescatalogados.ajax.reload());
});

function formatoMontoDesc(data) {
    const valor = Number(data || 0);
    return valor.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
