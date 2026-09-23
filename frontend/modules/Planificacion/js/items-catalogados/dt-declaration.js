let dt_itemsCatalogados = null;
let dt_sigma = null;

$(document).ready(function () {
    const formulario = Number($('#itemsCatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/items-catalogados/';

    dt_itemsCatalogados = $('#tablaListaItemsCatalogados').DataTable({
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
                $('#totalFormulario').text(formatoMonto(total));
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
                        return `${row.Descripcion || ''} ${row.SigmaDescripcion || ''}`;
                    }
                    return `
                        <div class="dtic-item-main">${row.Descripcion || ''}</div>
                        <div class="dtic-item-sub"><b>SIGMA:</b> ${row.SigmaDescripcion || ''}</div>
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
                render: formatoMonto
            },
            {
                title: 'Precio',
                data: 'Precio',
                className: 'text-right',
                width: '110px',
                render: formatoMonto
            },
            {
                title: 'Total',
                data: 'TotalItem',
                className: 'text-right',
                width: '120px',
                render: formatoMonto
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

    dt_sigma = $('#tablaSigma').DataTable({
        orderCellsTop: true,
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: base + 'listar-sigma',
            data: {formulario},
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar el catálogo SIGMA.'), data.errors);
            }
        },
        columns: [
            {data: 'Clase', title: 'Clase'},
            {data: 'Descripcion', title: 'Descripción'},
            {data: 'RamaComercial', title: 'Rama comercial'},
            {data: 'Especificacion', title: 'Especificación'},
            {
                data: 'IdSigma',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '120px',
                render: () => '<button type="button" class="btn btn-sm btn-primary btn-select-sigma">Seleccionar</button>'
            }
        ]
    });

    $('#tablaSigma thead tr:eq(0)').clone().addClass('filters').appendTo('#tablaSigma thead');
    $('#tablaSigma thead tr.filters th').each(function (i) {
        if (i === 4) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Filtrar">');
        $('input', this).on('keyup change', function (e) {
            e.stopPropagation();
            if (dt_sigma.column(i).search() !== this.value) {
                dt_sigma.column(i).search(this.value).draw();
            }
        });
    });

    $(document).on('click', '#refreshTable', () => dt_itemsCatalogados.ajax.reload());
});

function formatoMonto(data) {
    const valor = Number(data || 0);
    return valor.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
