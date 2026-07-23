let dt_indicadorPoa = null;

$(document).ready(function () {

    dt_indicadorPoa = $("#tablaListaIndicadoresPoa").DataTable({
        initComplete: function () {
            $("div.dt-search").append(`
            <button id="refreshTable" class="btn-refresh">
                <i class="fas fa-sync-alt fa-spin"></i>
            </button>`
            );

            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/indicador-poa/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_indicadorPoa.processing(false);
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
                render: function (data, type, row) {
                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Indicador N°</span>
                            <div class="dtic-code-badge">
                                ${row["Codigo"]}
                            </div>                                  
                        </div>
                        
                        <div class="dtic-item-main">
                            ${row["Descripcion"]}
                        </div>
                        
                        <div class="dtic-item-sub">
                            ${row["LineaBase"]}  ${row["Meta"]}  
                        </div>
                        
                        <div class="dtic-item-sub2 group-container">
                            <div class="sub-group-container">
                                <div class="item-container"> 
                                    <div>Objetivo</div>
                                </div>
                                <div>${row["objetivosEspecificos"]['Objetivo']}</div>
                            </div>
                            <div class="sub-group-container">
                                <div class="item-container"> 
                                    <div>Producto</div>
                                </div>
                                <div>${row["objetivosEspecificos"]['Producto']}</div>
                            </div>
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
            },
            {
                data: "IdIndicadorPoa",
                className: "text-center",
                width: "140px",
                orderable: false,
                searchable: false,
                render: function () {

                    return `
                    <button class="btn-action btn-edit ">
                        <i class="fa fa-pen"></i>
                    </button>

                    <button class="btn-action btn-delete ">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
                }
            }
        ],
    });

    $(document).on("click", "#refreshTable", function () {
        dt_indicadorPoa.ajax.reload();
    });

    dt_indicadorPoa.on('order.dt search.dt', function () {
        let i = 1;
        dt_indicadorPoa.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})