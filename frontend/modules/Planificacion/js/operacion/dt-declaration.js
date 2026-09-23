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
        order: [[0, 'asc'], [1, 'asc']],
        columns: [
            {
                title: 'Llave',
                data: 'Llave',
                visible: false
            },
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
                width: '420px',
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
                    if (window.poaPuedeEditar === false) {
                        const activo = data === 'V';
                        return activo ? 'Vigente' : 'Caduco';
                    }
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
                render: () => htmlAccionesPoa()
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
        drawCallback: function () {
            agruparPorLlave(this.api());
        },
        initComplete: mostrarTablaOperacion
    });
});

function agruparPorLlave(api) {
    $(api.table().body()).find('tr.programacion-group-row').remove();

    const filas = api.rows({search: 'applied', order: 'applied'});
    const datos = filas.data().toArray();
    const nodos = filas.nodes().toArray();
    let ultimaLlave = null;
    const columnasVisibles = api.columns(':visible').count();

    datos.forEach((item, indice) => {
        if (item.IdLlavePresupuestaria === ultimaLlave) return;
        ultimaLlave = item.IdLlavePresupuestaria;
        $(nodos[indice]).before(`
            <tr class="programacion-group-row">
                <td colspan="${columnasVisibles}">
                    <strong>${item.Llave || ''}</strong>
                    <div class="operacion-group-detail">
                        Programa: ${item.ProgramaCodigo || ''} - ${item.ProgramaDescripcion || ''}
                        · Proyecto: ${item.ProyectoCodigo || ''} - ${item.ProyectoDescripcion || ''}
                        · Actividad: ${item.ActividadCodigo || ''} - ${item.ActividadDescripcion || ''}
                    </div>
                </td>
            </tr>
        `);
    });
}

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
                data-idoperacion="${row.IdOperacion}"
                ${window.poaPuedeEditar === false ? 'disabled' : ''}>`;
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
