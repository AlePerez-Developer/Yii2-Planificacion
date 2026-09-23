let dt_partida = null;

$(document).ready(function () {
    dt_partida = $('#tablaListaPartidas').DataTable({
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
            url: 'index.php?r=Planificacion/partida/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar las partidas.'), data.errors);
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
                        return `${row.FuenteDescripcion || ''} ${row.OrganismoDescripcion || ''}`;
                    }
                    return `
                        <div class="dtic-item-main">${row.FuenteDescripcion || ''}</div>
                        <div class="dtic-item-sub"><b>Organismo:</b> ${row.OrganismoDescripcion || ''}</div>
                    `;
                }
            },
            columnaEstadoCatalogo(),
            columnaAccionesCatalogo('IdPartida')
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_partida.ajax.reload());
});
