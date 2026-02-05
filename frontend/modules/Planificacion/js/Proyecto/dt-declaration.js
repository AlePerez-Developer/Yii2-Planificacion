let dt_proyecto;
$(document).ready(function () {
    dt_proyecto = $("#tablaListaProyectos").DataTable({
        initComplete: function () {
            $("div.dt-search").append('<button type="button" id="refresh" class="btn btn-outline-primary ml-2" data-toggle="tooltip" title="Click! recarga la tabla" ><i class="fa fa-recycle fa-spin"></i></button>');
        },
        ajax: {
            method: "POST",
            dataType: "json",
            cache: false,
            contentType: false,
            processData: false,
            url: "index.php?r=Planificacion/proyecto/listar-todo",
            dataSrc: "data",
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_proyecto.processing(false);
            }
        },
        columns: [
            {
                className: "dt-small dt-center",
                orderable: false,
                searchable: false,
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                },
                width: 30,
            },
            {
                className: "dt-small dt-center",
                data: "programa",
                width: 300,
                render: function (data, type) {
                    return (type === 'display')? "(" + data["Codigo"] + ") <br>" + data["Descripcion"] : data["Codigo"];
                },
            },
            {
                className: "dt-small dt-center",
                data: "Codigo",
                width: 60,
            },
            {
                className: "dt-small",
                data: "Descripcion",
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row["CodigoEstado"] === ESTADO_VIGENTE))
                        ? '<button id="btnEstado" type="button" class="btn btn-outline-success btn-sm btnEstado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_text">Vigente</span>' +
                        '  </button>'
                        : '<button id="btnEstado" type="button" class="btn btn-outline-danger btn-sm btnEstado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_text">Caducado</span>' +
                        '  </button>' ;
                },
                visible: true
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'IdPrograma',
                render: function (data, type) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button id="btnEditar" type="button" class="btn btn-outline-warning btn-sm btnEditar" data-toggle="tooltip" title="Click! para editar el registro">' +
                        '    <i class="fa fa-pen-fancy"></i>' +
                        '</button>' +
                        '<button id="btnEliminar" type="button" class="btn btn-outline-danger btn-sm btnEliminar" data-toggle="tooltip" title="Click! para eliminar el registro">' +
                        '    <i class="fa fa-trash-alt"></i>' +
                        '</button>' +
                        '</div>'
                        : data;
                },
            },
        ],
    });

    dt_proyecto
        .on("order.dt search.dt", function () {
            let i = 1;
            dt_proyecto
                .cells(null, 0, { search: "applied", order: "applied" })
                .every(function () {
                    this.data(i++);
                });
        })
        .draw();
})