let dt_llavePresupuestaria;

$(document).ready(function () {

    dt_llavePresupuestaria = $("#tablaListaLlavesPresupuestarias").DataTable({
        initComplete: function () {
            $("div.dt-search").append('<button type="button" id="refresh" class="btn btn-outline-primary ml-2" data-toggle="tooltip" title="Click! recarga la tabla" ><i class="fa fa-recycle fa-spin"></i></button>');
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/llave-presupuestaria/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_llavePresupuestaria.processing(false);
            }
        },
        columns: [
            {
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoUsuario',
                width: 30
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'Llave',
                width: 20
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'Descripcion',
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'TechoPresupuestario',
                width: 30
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'FechaInicio',
                width: 20
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'FechaFin',
                width: 80
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
                data: 'IdLlavePresupuestaria',
                render: function (data, type) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button id="btnEditar" type="button" class="btn btn-outline-warning btn-sm btnEditar" data-toggle="tooltip" title="Click! para editar el registro">' +
                        '    <i class="fa fa-pen-fancy"></i>' +
                        '</button>' +
                        '<button id="btnEliminar" type="button" class="btn btn-outline-danger btn-sm btnEliminar" data-toggle="tooltip" title="Click! para eliminar el registro">' +
                        '    <i class="fa fa-trash-alt"></i>' +
                        '</button>' +
                        '<button id="btnFinalizar" type="button" class="btn btn-outline-info btn-sm btnFinalizar" data-toggle="tooltip" title="Click! para eliminar el registro">' +
                        '    <i class="fa fa-lock"></i>' +
                        '</button>' +
                        '</div>'
                        : data;
                },
            },
        ],
    });
    dt_llavePresupuestaria.on('order.dt search.dt', function () {
        let i = 1;
        dt_llavePresupuestaria.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();
})