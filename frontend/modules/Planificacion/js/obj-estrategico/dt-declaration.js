let dt_objEstrategico;
$(document).ready(function () {

    dt_objEstrategico = $("#tablaListaObjEstrategicos").DataTable({
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
            url: 'index.php?r=Planificacion/obj-estrategico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_objEstrategico.processing(false);
            }
        },

        columns: [
            {
                data: "CodigoUsuario",
                className: "text-center",
                width: "60px",
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<div class="badge-codigo">${data}</div>`;
                }
            },
            {
                data: "Objetivo",
                render: function (data, type, row) {
                    if (type !== "display") {
                        return row["Objetivo"];
                    }

                    return `

                    <div class="dtic-code-container">
                        <span class="dtic-code-text">Objetivo:  </span>
                            <div class="dtic-code-badge">
                                ${row["Compuesto"]}
                            </div>                                  
                    </div>

                    
                    <div class="dtic-item-main">
                        ${row["Objetivo"]}
                    </div>
                    
                    <div class="dtic-item-sub">
                        <b>PRODUCTO:</b> ${row["Producto"]}
                    </div>
                  
                    <div class="dtic-item-sub2 group-container">
                        <div class="sub-group-container">
                            <div class="item-container"> 
                                <div>AREA</div>
                                <div>< ${row["areaEstrategica"]["Codigo"]} ></div>
                            </div>
                            <div>${row["areaEstrategica"]["Descripcion"]}</div>
                        </div>
                        <div class="sub-group-container">
                            <div class="item-container"> 
                                <div>POLITICA</div>
                                <div>< ${row["politicaEstrategica"]["Codigo"]} ></div>
                            </div>
                            <div>${row["politicaEstrategica"]["Descripcion"]}</div>
                        </div>
                    </div>
                    
                    <div class="dtic-item-sub">
                        <b>Gestión:</b> ${row["pei"]["GestionInicio"]} - ${row["pei"]["GestionFin"]}
                    </div>
                    <div class="dtic-item-sub">
                        <small>(${row["Indicador_Descripcion"]} - ${row["Indicador_Formula"]})</small>
                    </div>                    
                `;
                }
            },
            {data: "Compuesto",visible: false},
            {data: "Producto",visible: false},
            {
                data: "CodigoEstado",
                className: "text-center",
                width: "65px",
                orderable: false,
                searchable: false,
                render: function (data, type) {
                    return ((type === 'display'))
                        ? '<button type="button" class="btn-programar" data-toggle="tooltip" title="Click! para programar indicadores">' +
                        '    <span class="btn_ico"><i class="far fa-calendar-check"></i></span>' +
                        '    <span class="btn_text">Programar</span>' +
                        '  </button>'
                        : data
                },
            },
            {
                data: "CodigoEstado",
                className: "text-center",
                width: "65px",
                orderable: false,
                searchable: false,
                visible: false,
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
                data: "IdObjEstrategico",
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
            },
        ],
    });

    $(document).on("click", "#refreshTable", function () {
        dt_objEstrategico.ajax.reload();
    });

    dt_objEstrategico.on('order.dt search.dt', function () {
        let i = 1;
        dt_objEstrategico.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();
})
