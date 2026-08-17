let dt_techoUnidad = null;

$(document).ready(function () {
    dt_techoUnidad = $('#tablaTechos').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/techo-unidad/listar-llaves',
            method: 'POST',
            dataType: 'json',
            dataSrc: 'data',
            error: mostrarErrorTecho
        },
        order: [[0, 'asc']],
        columns: [
            {title: 'Llave', data: 'Llave', width: '190px'},
            {title: 'Descripción', data: 'Descripcion'},
            {
                title: 'Monto usado',
                data: 'MontoUsado',
                className: 'text-right',
                render: data => Number(data || 0).toLocaleString()
            },
            {
                title: 'Disponible real',
                data: 'DisponibleReal',
                className: 'text-right',
                render: data => Number(data || 0).toLocaleString()
            },
            {
                title: 'Techo asignado',
                data: 'Techo',
                className: 'text-center',
                width: '180px',
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    return `<input type="number" min="1" step="1"
                        class="form-control input-techo"
                        value="${Number(data || 0)}"
                        data-original="${Number(data || 0)}"
                        data-usado="${Number(row.MontoUsado || 0)}"
                        data-idllave="${row.IdLlavePresupuestaria}">`;
                }
            },
            {
                title: 'Acciones',
                data: null,
                className: 'text-center',
                width: '120px',
                orderable: false,
                render: function (data, type, row) {
                    return `<button class="btn-action btn-save-techo" title="Guardar techo">
                                <i class="fa fa-check"></i>
                            </button>
                            ${row.IdAsignacion ? `
                            <button class="btn-action btn-delete-techo" title="Quitar techo">
                                <i class="fa fa-trash"></i>
                            </button>` : ''}`;
                }
            }
        ],
        rowCallback: function (row, data) {
            $(row).toggleClass('techo-asignado', Number(data.Techo || 0) > 0);
        },
        initComplete: function () {
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(180);
        }
    });
});

function mostrarErrorTecho(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo procesar el techo.'), data.errors);
}
