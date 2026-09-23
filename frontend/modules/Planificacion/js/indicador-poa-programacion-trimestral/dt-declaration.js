let dt_listaIndicadoresPoaTrimestrales = null;
let openedRowPoaTrimestral = null;

function inicializarTablaIndicadoresPoaTrimestrales() {
    if ($.fn.DataTable.isDataTable('#tablaListaIndicadoresPoaTrimestrales')) {
        dt_listaIndicadoresPoaTrimestrales.ajax.reload();
        return;
    }

    dt_listaIndicadoresPoaTrimestrales = $('#tablaListaIndicadoresPoaTrimestrales').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-indicadores',
            method: 'POST',
            dataType: 'json',
            data: function () {
                return {idObjEspecifico: $('#idObjEspecifico').val()};
            },
            dataSrc: 'data',
            error: function (xhr) {
                mostrarErrorTrimestral(xhr);
            }
        },
        columns: [
            {
                data: null,
                defaultContent: '',
                className: 'expandible dtic-control',
                orderable: false,
                width: 45
            },
            {
                data: null,
                className: 'expandible',
                render: function (data, type, row) {
                    const metaGlobal = parseFloat(row.Meta || 0);
                    const metaProgramada = parseFloat(row.MetaProgramada || 0);
                    let colorClass = 'bg-warning';
                    let texto = 'Excedente';
                    if (metaGlobal > metaProgramada) {
                        colorClass = 'bg-danger';
                        texto = 'Pendiente';
                    }
                    if (metaGlobal === metaProgramada) {
                        colorClass = 'bg-info';
                        texto = 'Completa';
                    }

                    if (type !== 'display') {
                        return row.Descripcion;
                    }

                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Indicador N°</span>
                            <div class="dtic-code-badge">${row.Codigo}</div>
                        </div>
                        <div class="dtic-item-main">${row.Descripcion}</div>
                        <div class="acc-footer">
                            <div class="meta-box-left dtic-item-sub">
                                <span class="meta-badge-text">Meta Global</span>
                                <span class="meta-badge">${row.Meta}</span>
                                <span class="meta-badge-text">Meta Programada</span>
                                <span class="meta-badge ${colorClass}">${row.MetaProgramada}</span>
                                <span class="meta-badge ${colorClass}">${texto}</span>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#tablaListaIndicadoresPoaTrimestrales tbody').on('click', 'td.expandible', function () {
        const tr = $(this).closest('tr');
        const currentRow = dt_listaIndicadoresPoaTrimestrales.row(tr);

        if (currentRow.child.isShown()) {
            cerrarFilaPoaTrimestral(currentRow);
            openedRowPoaTrimestral = null;
            return;
        }

        if (openedRowPoaTrimestral && openedRowPoaTrimestral.child.isShown()) {
            cerrarFilaPoaTrimestral(openedRowPoaTrimestral);
        }

        const rowData = currentRow.data();
        currentRow.child(formatoDetallePoaTrimestral(rowData), 'no-padding').show();
        tr.addClass('shown');
        $('div.slider', currentRow.child()).hide().stop(true, true).slideDown(180);
        openedRowPoaTrimestral = currentRow;
        inicializarTablaProgramacionPoaIndicador(rowData);
    });
}

function cerrarFilaPoaTrimestral(row) {
    const tr = $(row.node());
    const nested = $('table.dtic-gestion-table', row.child());
    if (nested.length && $.fn.DataTable.isDataTable(nested)) {
        nested.DataTable().destroy();
    }
    $('div.slider', row.child()).stop(true, true).slideUp(180, function () {
        row.child.hide();
        tr.removeClass('shown');
    });
}

function formatoDetallePoaTrimestral(row) {
    const tableId = `tbl_poa_trimestral_${row.IdIndicadorPoa}`;
    return `
        <div class="slider" style="display:none;">
            <div class="p-3">
                <div class="table-responsive table-container mt-3">
                    <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100">
                        <thead>
                            <tr>
                                <th>Código compuesto</th>
                                <th>Descripción</th>
                                <th>Meta Trim.</th>
                                <th>1er Trim.</th>
                                <th>2do Trim.</th>
                                <th>3er Trim.</th>
                                <th>4to Trim.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>`;
}

function inicializarTablaProgramacionPoaIndicador(indicador) {
    const tableId = `tbl_poa_trimestral_${indicador.IdIndicadorPoa}`;
    $(`#${tableId}`).DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-programacion',
            method: 'POST',
            dataType: 'json',
            data: {
                idObjEspecifico: $('#idObjEspecifico').val(),
                idIndicadorPoa: indicador.IdIndicadorPoa
            },
            dataSrc: 'data',
            error: function (xhr) {
                mostrarErrorTrimestral(xhr);
            }
        },
        columns: [
            {data: 'Llave', className: 'dt-small'},
            {data: 'LlaveDescripcion', className: 'dt-small'},
            {data: 'MetaProgramada', className: 'dt-center meta-programada', width: 110},
            trimestrePoa(1, 'MetaPrimerTrimestre'),
            trimestrePoa(2, 'MetaSegundoTrimestre'),
            trimestrePoa(3, 'MetaTercerTrimestre'),
            trimestrePoa(4, 'MetaCuartoTrimestre'),
            {
                data: 'TotalTrimestral',
                className: 'dt-center total-trimestral',
                width: 90,
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    return `<span class="total-badge ${row.ProgramacionCompleta == 1 ? 'completa' : 'pendiente'}">${data}</span>`;
                }
            }
        ],
        createdRow: function (row, data) {
            $(row)
                .removeClass('programacion-completa programacion-pendiente')
                .addClass(data.ProgramacionCompleta == 1 ? 'programacion-completa' : 'programacion-pendiente');
        },
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        autoWidth: false
    });
}

function trimestrePoa(numero, atributo) {
    return {
        data: atributo,
        className: 'dt-center',
        width: 110,
        render: function (data, type, row) {
            if (type !== 'display') return data;
            return `<input type="number" min="0" step="1" readonly
                class="form-control form-control-sm input-meta-poa-trimestre"
                value="${Number(data || 0)}"
                data-original="${Number(data || 0)}"
                data-trimestre="${numero}"
                data-idprogramacion="${row.IdProgramacionIndicadorPoaGestion}"
                ${window.poaPuedeEditar === false ? 'disabled' : ''}>`;
        }
    };
}

function mostrarErrorTrimestral(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje(
        'error',
        GenerarMensajeError(data.message || 'No se pudo cargar la programación trimestral.'),
        data.errors
    );
}

function recargarIndicadoresPoaTrimestrales() {
    if (dt_listaIndicadoresPoaTrimestrales) {
        dt_listaIndicadoresPoaTrimestrales.ajax.reload();
    }
}
