let dt_pei
$(document).ready(function () {
    dt_pei = $("#tablaListaPeis").DataTable({
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/peis/listar-peis',
            dataSrc: 'peis',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' + GenerarMensajeError(JSON.parse(xhr.responseText)["respuesta"])))
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
                        ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado"><span class="btn_text">Vigente</span></button>'
                        : '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado"><span class="btn_text">Caducado</span></button>' ;
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
                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
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
