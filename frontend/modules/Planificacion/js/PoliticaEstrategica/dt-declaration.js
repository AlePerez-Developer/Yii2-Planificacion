let dt_politica
$(document).ready(function () {
    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="titulosmall">Area estrategica institucional</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Codigo: </div>' +
            '           </div>' +
            '           <div class="col-8">' +
            '               <div class="little">' + d["areaEstrategica"]['Codigo'] + '</div>' +
            '           </div>' +
            '       </div>' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Descripcion</div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' +
            '                   ' + d["areaEstrategica"]['Descripcion'] +
            '               </div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        );
    }
    dt_politica = $("#tablaListaPoliticas").DataTable({
        initComplete: function () {
            $("div.dt-search").append('<button type="button" id="refresh" class="btn btn-outline-primary ml-2" data-toggle="tooltip" title="Click! recarga la tabla" ><i class="fa fa-recycle fa-spin"></i></button>');
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/politica-estrategica/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_politica.processing(false);
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
                className: 'dt-small dt-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
                width: 30
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'areaEstrategica.Codigo',
                width: 100
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'Codigo',
                width: 100
            },
            {
                className: 'dt-small dt-left',
                data: 'Descripcion'
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
                data: 'CodigoPoliticaEstrategica',
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

    dt_politica.on('order.dt search.dt', function () {
        let i = 1;
        dt_politica.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    $('#tablaListaPoliticas tbody').on('click', 'td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = dt_politica.row(tr);

        if (row["child"].isShown()) {
            row["child"].hide();
        }
        else {
            row["child"](format(row.data())).show();
        }
    });
})
