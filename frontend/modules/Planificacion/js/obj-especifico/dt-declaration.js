let dt_objEspecifico;
$(document).ready(function () {
    dt_objEspecifico = $('#tablaListaObjEspecificos').DataTable({
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
            url: 'index.php?r=Planificacion/obj-especifico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_objEspecifico.processing(false);
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
                    <div class="dtic-item-sub2 group-container"> 
                            <div class="sub-group-container">
                                <div class="item-container"> 
                                    <div>Objetivo</div>
                                </div>
                                <div>${row["objetivosInstitucionales"]["Objetivo"]}</div>
                            </div>
                            <div class="sub-group-container">
                                <div class="item-container"> 
                                    <div>Producto</div>
                                </div>
                                <div>${row["objetivosInstitucionales"]["Producto"]}</div>
                            </div>
                        </div>
                `;
                }
            },
            {data: 'Compuesto', visible: false},
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
                visible: true,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    if (window.poaPuedeEditar === false) {
                        return row["CodigoEstado"] === ESTADO_VIGENTE ? 'Vigente' : 'Caducado';
                    }
                    return (row["CodigoEstado"] === ESTADO_VIGENTE)
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
                data: "IdObjEspecifico",
                className: "text-center",
                width: "140px",
                orderable: false,
                searchable: false,
                render: function () {
                    return htmlAccionesPoa();
                }
            },
        ]
    });

    $(document).on("click", "#refreshTable", function () {
        dt_objEspecifico.ajax.reload();
    });

    dt_objEspecifico.on('order.dt search.dt', function () {
        let i = 1;
        dt_objEspecifico.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
