let dt_ue
$(document).ready(function () {
    dt_ue = $("#tablaListaUes").DataTable({
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
            dataType: "json",
            url: "index.php?r=Planificacion/ue/listar-todo",
            dataSrc: "data"
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
                data: null,
                render: function (data, type, row) {

                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                    <div class="dtic-item-main">
                        ${row["Ue"]}
                    </div>

                    <div class="dtic-item-sub">
                        ${row["Descripcion"]}
                    </div>
                `;
                }
            },
            {data: "Ue", visible: false },
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
                data: "IdUe",
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
        ]
    });

    $(document).on("click", "#refreshTable", function () {
        dt_ue.ajax.reload();
    });

    dt_ue.on('order.dt search.dt', function () {
        let i = 1;
        dt_ue.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
