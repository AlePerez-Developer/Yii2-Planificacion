let dt_operacion = null;

$(document).ready(function () {
    dt_operacion = $('#tablaOperaciones').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/operacion/listar-todo',
            method: 'POST',
            dataType: 'json',
            dataSrc: 'data',
            error: function (xhr) {
                mostrarErrorOperacion(xhr);
                mostrarTablaOperacion();
            }
        },
        scrollX: true,
        responsive: false,
        autoWidth: false,
        order: [[0, 'asc']],
        columns: [
            {
                title: 'Código',
                data: 'Codigo',
                className: 'text-center',
                width: '70px',
                render: data => `<span class="operacion-code">${data || ''}</span>`
            },
            {
                title: 'Operación',
                data: null,
                width: '390px',
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return `${row.Descripcion || ''} ${row.ObjetivoDescripcion || ''} ${row.IndicadorDescripcion || ''}`;
                    }
                    return `
                        <div class="operacion-title">${row.Descripcion || 'Sin descripción'}</div>
                        <div class="operacion-detail">
                            <b>Objetivo ${row.ObjetivoCodigo || ''}:</b>
                            ${row.ObjetivoDescripcion || ''}
                        </div>
                        <div class="operacion-detail">
                            <span class="badge-indicador">${row.IndicadorTipo || ''}</span>
                            <b>Indicador ${row.IndicadorCodigo || ''}:</b>
                            ${row.IndicadorDescripcion || ''}
                        </div>
                    `;
                }
            },
            {
                title: 'Llave presupuestaria',
                data: null,
                width: '220px',
                render: function (data, type, row) {
                    const contenido = `${row.Llave || ''} ${row.LlaveDescripcion || ''}`;
                    return type === 'display'
                        ? `<div class="operacion-detail"><b>${row.Llave || ''}</b><br>${row.LlaveDescripcion || ''}</div>`
                        : contenido;
                }
            },
            {
                title: 'Tipo',
                data: 'TipoOperacion',
                className: 'text-center',
                width: '110px',
                render: data => `<span class="tipo-operacion">${data || ''}</span>`
            },
            trimestre(1, 'PrimerTrimestre', 'T1'),
            trimestre(2, 'SegundoTrimestre', 'T2'),
            trimestre(3, 'TercerTrimestre', 'T3'),
            trimestre(4, 'CuartoTrimestre', 'T4'),
            {
                title: 'Total',
                data: null,
                className: 'text-center total-operacion',
                width: '135px',
                render: function (data, type, row) {
                    const total = Number(row.PrimerTrimestre || 0)
                        + Number(row.SegundoTrimestre || 0)
                        + Number(row.TercerTrimestre || 0)
                        + Number(row.CuartoTrimestre || 0);
                    const estado = total === 100
                        ? 'completa'
                        : (total > 100 ? 'excedida' : 'pendiente');
                    return type === 'display'
                        ? `<div class="programacion-total">
                            <span class="total-badge ${estado}">${total} / 100</span>
                            <div class="programacion-progress">
                                <span class="${estado}" style="width:${Math.min(total, 100)}%"></span>
                            </div>
                            <small>${total === 100 ? 'Completa' : `Pendiente: ${Math.max(100 - total, 0)}`}</small>
                        </div>`
                        : total;
                }
            },
            {
                title: 'Estado',
                data: 'CodigoEstado',
                className: 'text-center',
                width: '85px',
                render: function (data) {
                    const activo = data === 'V';
                    return `<button class="btn-toggle-estado ${activo ? 'activo' : 'inactivo'}"
                                    title="Cambiar estado">
                        ${activo ? 'Vigente' : 'Caduco'}
                    </button>`;
                }
            },
            {
                title: 'Acciones',
                data: 'IdOperacion',
                className: 'text-center',
                width: '95px',
                orderable: false,
                searchable: false,
                render: () => `
                    <button class="btn-action btn-edit" title="Editar">
                        <i class="fa fa-pen"></i>
                    </button>
                    <button class="btn-action btn-delete" title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                `
            }
        ],
        rowCallback: function (row, data) {
            const total = Number(data.PrimerTrimestre || 0)
                + Number(data.SegundoTrimestre || 0)
                + Number(data.TercerTrimestre || 0)
                + Number(data.CuartoTrimestre || 0);
            $(row)
                .removeClass('programacion-completa programacion-pendiente')
                .addClass(total === 100 ? 'programacion-completa' : 'programacion-pendiente');
        },
        initComplete: mostrarTablaOperacion
    });
});

function trimestre(numero, atributo, titulo) {
    return {
        title: titulo,
        data: atributo,
        className: 'text-center trimestre-cell',
        width: '80px',
        render: function (data, type, row) {
            if (type !== 'display') return data;
            return `<input type="number" min="0" step="1" readonly
                class="form-control form-control-sm input-meta-operacion"
                value="${Number(data || 0)}"
                data-original="${Number(data || 0)}"
                data-trimestre="${numero}"
                data-idoperacion="${row.IdOperacion}">`;
        }
    };
}

function mostrarTablaOperacion() {
    $('#dticTableLoading').hide();
    $('#dticTableContainer').fadeIn(180);
}

function mostrarErrorOperacion(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje(
        'error',
        GenerarMensajeError(data.message || 'No se pudo procesar la operación.'),
        data.errors
    );
}
