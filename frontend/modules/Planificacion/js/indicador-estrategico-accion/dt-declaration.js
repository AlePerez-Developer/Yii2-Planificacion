let dt_indEstrategicoAccion;

$(document).ready(function () {
    dt_indEstrategicoAccion = $("#tablaListaIndicadoresEstrategicosAccion").DataTable({
        initComplete: function () {
            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/indicador-estrategico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_indEstrategicoAccion.processing(false);
            }
        },
        columns: [
            {
                data: "CodigoUsuario",
                className: "dt-dtic-text-center",
                width: "60px",
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<div class="badge-codigo">${data}</div>`;
                }
            },
            {data: "Codigo", visible:false},
            {
                data: null,
                className: 'expandible',
                render: function (data, type, row) {
                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                        <div style="display: flex;align-items:center;">
                            <span class="dtic-item-main mr-2">Indicador N° </span>
                                <div class="kpi-circle">
                                    ${row["Codigo"]}
                                </div>                                
                        </div>
                        
                        <div class="dtic-item-main">
                            ${row["Descripcion"]}
                        </div>
                        
                        <div class="dtic-item-sub">
                            accion estrategica desde ${row["LineaBase"]} hasta ${row["Meta"]} 
                        </div>
                        
                        <div class="acc-footer mt-2" style="display: flex; gap: 10px">
                            <span class="badge-result">${row["catUnidadesIndicadores"]["Descripcion"]}</span>
                            <span class="badge-result">${row["catTiposResultados"]["Descripcion"]}</span>
                            <span class="badge-result">${row["catCategoriasIndicadores"]["Descripcion"]}</span>
                        </div>                        
                        `;
                }
            },
            {
                data: "CodigoEstado",
                className: "text-center",
                width: "90px",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return ((type === 'display') && (row["CodigoEstado"] === ESTADO_VIGENTE))
                        ? '<button type="button" class="estado-on btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-check-circle"></i></span>' +
                        '    <span class="btn_text">Vigente</span>' +
                        '  </button>'
                        : '<button type="button" class="estado-off btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-times-circle"></i></span>' +
                        '    <span class="btn_text">Caducado</span>' +
                        '  </button>';
                },
            }
        ],
    });

    $(document).on("click", "#refreshTable", function () {
        dt_indEstrategicoAccion.ajax.reload();
    });

    dt_indEstrategicoAccion.on('order.dt search.dt', function () {
        let i = 1;
        dt_indEstrategicoAccion.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})