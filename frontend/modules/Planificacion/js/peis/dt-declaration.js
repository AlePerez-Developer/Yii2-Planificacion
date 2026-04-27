let dt_pei
$(document).ready(function () {
    dt_pei = $("#tablaListaPeis").DataTable({
        initComplete: function () {
            $("div.dt-search").append(`
            <button id="refreshTable" class="btn-refresh">
                <i class="fas fa-sync-alt fa-spin"></i>
            </button>`
            );

            $("#peiLoading").hide();
            $("#peiTableContainer").fadeIn(400);
        },

        ajax: {
            method: "POST",
            dataType: "json",
            url: "index.php?r=Planificacion/peis/listar-todo",
            dataSrc: "data"
        },

        columns: [

            {
                title: "#",
                data: "CodigoUsuario",
                className: "text-center",
                width: "60px",
                render: function (data) {
                    return `<div class="badge-codigo">${data}</div>`;
                }
            },

            {
                title: 'Descripcion',
                data: null,
                render: function (data, type, row) {

                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                    <div class="pei-main">
                        ${row["Descripcion"]}
                    </div>

                    <div class="pei-sub">
                        Gestión ${row["GestionInicio"]} - ${row["GestionFin"]}
                    </div>

                    <div class="pei-sub">
                        Aprobación: ${row["FechaAprobacion"]}
                    </div>
                `;
                }
            },

            {
                title: 'Estado',
                data: "CodigoEstado",
                className: "text-center",
                width: "90px",

                render: function (data, type, row) {
                    return ( (type === 'display') && (row["CodigoEstado"] === ESTADO_VIGENTE))
                        ? '<button id="btnEstado" type="button" class="estado-on btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-check-circle"></i></span>'+
                        '    <span class="btn_text">Vigente</span>' +
                        '  </button>'
                        : '<button id="btnEstado" type="button" class="estado-off btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-times-circle"></i></span>'+
                        '    <span class="btn_text">Caducado</span>' +
                        '  </button>' ;
                },

                /*render: function (data, type, row) {

                    if (type !== "display") {
                        return data;
                    }

                    const vigente = row["CodigoEstado"] === ESTADO_VIGENTE;

                    return `
                        <button
                            type="button"
                            id="btnEstado"
                            class="btn-toggle-estado  ${vigente ? 'estado-on' : 'estado-off'}"
                            title="Click para cambiar estado">
            
                            <span class="estado-icon">
                                <i class="fas ${vigente ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                            </span>
            
                            <span class="estado-text">
                                ${vigente ? 'Vigente' : 'Caducado'}
                            </span>
            
                        </button>`;
                }*/
            },

            {
                title: 'Acciones',
                data: "IdPei",
                className: "text-center",
                width: "140px",
                orderable: false,
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
        dt_pei.ajax.reload();
    });

    dt_pei.on('order.dt search.dt', function () {
        let i = 1;
        dt_pei.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
