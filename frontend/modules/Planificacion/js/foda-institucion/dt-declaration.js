let dt_fodaInstitucion = null;
const tiposFoda = ['Fortaleza', 'Debilidad', 'Oportunidad', 'Amenaza'];
const incidenciasFoda = ['Alta', 'Media', 'Baja'];

$(document).ready(function () {
    dt_fodaInstitucion = $('#tablaFodaInstitucion').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/foda-institucion/listar-todo',
            method: 'POST',
            dataType: 'json',
            dataSrc: 'data',
            error: mostrarErrorFodaInstitucion
        },
        order: [[4, 'desc']],
        columns: [
            {
                title: 'Tipo',
                data: 'Tipo',
                className: 'text-center',
                render: function (data, type) {
                    if (type !== 'display') {
                        return data || '';
                    }
                    return `<span class="badge-tipo tipo-${String(data || '').toLowerCase()}">${data || ''}</span>`;
                }
            },
            {
                title: 'Incidencia',
                data: 'Incidencia',
                className: 'text-center',
                render: function (data, type) {
                    if (type !== 'display') {
                        return data || '';
                    }
                    return `<span class="badge-tipo incidencia-${String(data || '').toLowerCase()}">${data || ''}</span>`;
                }
            },
            {title: 'Descripción', data: 'Descripcion', defaultContent: ''},
            {
                title: 'Estado',
                data: 'CodigoEstado',
                className: 'text-center',
                render: data => window.poaPuedeEditar === false
                    ? (data === 'V' ? 'Vigente' : 'Caduco')
                    : `<button class="btn-toggle-estado ${data === 'V' ? 'activo' : 'inactivo'}">
                    ${data === 'V' ? 'Vigente' : 'Caduco'}
                </button>`
            },
            {title: 'Fecha', data: 'FechaHoraRegistro', className: 'text-center'},
            {
                title: 'Acciones',
                data: 'IdFoda',
                className: 'text-center',
                orderable: false,
                render: () => htmlAccionesPoa()
            }
        ],
        initComplete: function () {
            DataTable_filtroSelectHeader(dt_fodaInstitucion, 0, tiposFoda, 'Todos');
            DataTable_filtroSelectHeader(dt_fodaInstitucion, 1, incidenciasFoda, 'Todas');
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(180);
        }
    });
});

function mostrarErrorFodaInstitucion(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo procesar el FODA.'), data.errors);
}
