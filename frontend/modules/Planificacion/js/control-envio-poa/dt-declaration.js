let dt_envio = null;

$(document).ready(function () {
    dt_envio = $('#tablaListaEnvios').DataTable({
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
            url: 'index.php?r=Planificacion/control-envio-poa/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar los envíos.'), data.errors);
            }
        },
        columns: [
            {
                data: 'UnidadCodigo',
                className: 'text-center',
                width: '80px',
                render: data => `<div class="badge-codigo">${data || ''}</div>`
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return `${row.UnidadDescripcion || ''} ${row.FechaInicio || ''}`;
                    }
                    return `
                        <div class="dtic-item-main">${row.UnidadDescripcion || ''}</div>
                        <div class="dtic-item-sub"><b>Cronograma:</b> ${fmtFecha(row.FechaInicio)} — ${fmtFecha(row.FechaFin)}</div>
                        <div class="dtic-item-sub"><b>Enviado:</b> ${fmtFecha(row.FechaHoraRegistro)}</div>
                    `;
                }
            },
            {
                data: 'Estado',
                className: 'text-center',
                width: '110px',
                render: function (data) {
                    return Number(data) === 1
                        ? '<span class="badge badge-success">Enviado</span>'
                        : `<span class="badge badge-secondary">${data ?? ''}</span>`;
                }
            },
            {
                data: 'IdControlEnvio',
                className: 'text-center',
                width: '90px',
                orderable: false,
                searchable: false,
                render: function () {
                    return `<button class="btn-action btn-delete" title="Eliminar envío"><i class="fa fa-trash"></i></button>`;
                }
            }
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_envio.ajax.reload());
});

function fmtFecha(valor) {
    if (!valor) return '';
    return String(valor).replace('T', ' ').substring(0, 16);
}
