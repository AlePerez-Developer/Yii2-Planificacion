let dt_listaIndicadoresTrimestrales = null;
let openedRowTrimestral = null;

function inicializarTablaIndicadoresTrimestrales(idObjEstrategico) {
    if ($.fn.DataTable.isDataTable('#tablaListaIndicadoresTrimestrales')) {
        dt_listaIndicadoresTrimestrales.ajax.reload();
        return;
    }

    dt_listaIndicadoresTrimestrales = $('#tablaListaIndicadoresTrimestrales').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/listar-indicadores',
            method: 'POST',
            dataType: 'json',
            data: function () {
                return {idObjEstrategico: programacionTrimestral_s2ObjEstrategico.val()};
            },
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText);
                MostrarMensaje('error', GenerarMensajeError(data.message), data.errors);
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
                    if (type !== 'display') return row.Codigo;

                    return `
                        <div class="dtic-item-main">
                            <div class="dtic-item-title">${row.Codigo} - ${row.Descripcion}</div>
                            <div class="dtic-item-sub2">
                                <span><strong>Meta:</strong> ${row.Meta}</span>
                                <span><strong>Programada:</strong> ${row.MetaProgramada}</span>
                                <span><strong>Unidad:</strong> ${row.Unidad || '-'}</span>
                            </div>
                        </div>`;
                }
            },
            {
                data: null,
                className: 'dt-center',
                width: 170,
                render: function (data, type, row) {
                    if (type !== 'display') return row.MetaProgramada;
                    return `<span class="badge-programacion-anual completa">
                                <i class="fas fa-check-circle"></i> Anual completa
                            </span>`;
                }
            }
        ],
        order: [[1, 'asc']],
        responsive: false,
        autoWidth: false,
        paging: false,
        searching: false,
        info: false
    });

    $('#tablaListaIndicadoresTrimestrales tbody').on('click', 'td.expandible', function () {
        const tr = $(this).closest('tr');
        const currentRow = dt_listaIndicadoresTrimestrales.row(tr);

        if (currentRow.child.isShown()) {
            cerrarFilaTrimestral(currentRow);
            openedRowTrimestral = null;
            return;
        }

        if (openedRowTrimestral && openedRowTrimestral.child.isShown()) {
            cerrarFilaTrimestral(openedRowTrimestral);
        }

        const rowData = currentRow.data();
        currentRow.child(formatoDetalleTrimestral(rowData), 'no-padding').show();
        tr.addClass('shown');

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180);

        openedRowTrimestral = currentRow;
        cargarTabGestionActiva(rowData);
    });
}

function cerrarFilaTrimestral(row) {
    const tr = $(row.node());
    $('div.slider', row.child())
        .stop(true, true)
        .slideUp(180, function () {
            row.child.hide();
            tr.removeClass('shown');
        });
}

function formatoDetalleTrimestral(row) {
    return `
        <div class="slider" style="display:none;">
            <div class="p-3">
                <div id="loader_${row.IdIndicadorEstrategico}" class="p-4">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="tabs_container_${row.IdIndicadorEstrategico}" class="tab-container"></div>
            </div>
        </div>`;
}
