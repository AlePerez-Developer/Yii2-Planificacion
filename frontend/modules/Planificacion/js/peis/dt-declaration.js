let dt_pei
$(document).ready(function () {
    dt_pei = $("#tablaListaPeis").DataTable({
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/peis/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_pei.processing(false);
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
                className: 'dt-small',
                data: 'DescripcionPei'
            },
            {
                className: 'dt-small dt-center',
                data: 'FechaAprobacion'
            },
            {
                className: 'dt-small dt-center',
                data: 'GestionInicio'
            },
            {
                className: 'dt-small dt-center',
                data: 'GestionFin'
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
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoPei',
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

    dt_pei.on('order.dt search.dt', function () {
        let i = 1;
        dt_pei.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
