/*global idObjEstrategico*/
let dt_indEstrategicoAccion;
function inicializarTablaIndAcciones()
{

}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    dt_indEstrategicoAccion = $("#tablaListaIndicadoresEstrategicosAccion").DataTable({
        initComplete: function () {
            $("div.dt-search").append(`
            <button id="refreshTable" class="btn-refresh">
                <i class="fas fa-sync-alt fa-spin"></i>
            </button>`
            );

            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },
        layout: {
            topEnd: null
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            data: function () {
                return {
                    idObjEstrategico: indicadorEstrategicoAccion_s2ObjEstrategico.val() || ID_EMPTY_GUID
                };
            },
            cache: false,


            url: 'index.php?r=Planificacion/indicador-estrategico-accion/listar-indicadores',
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
                            ${row["accionesEstrategicas"]["Descripcion"]} ${row["LineaBase"]} ${row["AccionDescripcion"]} ${row["Meta"]}  
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
                data: null,
                className: "text-center",
                width: "90px",
                orderable: false,
                searchable: false,
                render: function (data, type) {
                    return (type === 'display')?
                        '<button type="button" class="btn-programar" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-check-circle"></i></span>' +
                        '    <span class="btn_text">Asignar Accion</span>' +
                        '  </button>'
                        :data
                },
            },
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