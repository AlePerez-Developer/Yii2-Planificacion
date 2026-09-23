let dt_organismo = null;

$(document).ready(function () {
    dt_organismo = $('#tablaListaOrganismos').DataTable({
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
            url: 'index.php?r=Planificacion/organismo/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar los organismos.'), data.errors);
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
                data: 'Descripcion',
                render: function (data, type) {
                    if (type !== 'display') return data;
                    return `<div class="dtic-item-main">${data || ''}</div>`;
                }
            },
            columnaEstadoCatalogo(),
            columnaAccionesCatalogo('IdOrganismo')
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_organismo.ajax.reload());
});
