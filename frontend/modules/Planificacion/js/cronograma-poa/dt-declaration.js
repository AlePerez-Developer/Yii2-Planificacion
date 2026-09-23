let dt_cronograma = null;

$(document).ready(function () {
    dt_cronograma = $('#tablaListaCronogramas').DataTable({
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
            url: 'index.php?r=Planificacion/cronograma-poa/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar los cronogramas.'), data.errors);
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
                        return `${row.FechaInicio || ''} ${row.FechaFin || ''}`;
                    }
                    return `
                        <div class="dtic-item-main">Inicio: ${fmtFecha(row.FechaInicio)}</div>
                        <div class="dtic-item-sub"><b>Fin:</b> ${fmtFecha(row.FechaFin)}</div>
                    `;
                }
            },
            columnaEstadoCatalogo(),
            columnaAccionesCatalogo('IdCronograma')
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_cronograma.ajax.reload());
});

function fmtFecha(valor) {
    if (!valor) return '';
    return String(valor).replace('T', ' ').substring(0, 16);
}
