let dt_indEstrategico;
$(document).ready(function () {
    function format(d) {
        return (
            '            <div class="row">' +
            '                <div class="col-2 childtitulosmall" style="padding-left: 50px">Objetivo Estrategico: </div>' +
            '                <div class="col-10 childtitulosmall" style="padding-left: 50px">123</div>' +
            '            </div>' +
            '            <div class="row">' +
            '                <div class="col-3 little"> ' + d["objetivosEstrategicos"]["Objetivo"] + '</div>' +
            '                <div class="col-3 little"> ' + d["objetivosEstrategicos"]["Producto"] + '</div>' +
            '                <div class="col-3 little"> ' + d["objetivosEstrategicos"]["Indicador_Descripcion"] + '</div>' +
            '                <div class="col-3 little"> ' + d["objetivosEstrategicos"]["Indicador_Formula"] + '</div>' +
            '            </div>'
        );
    }
    dt_indEstrategico = $("#tablaListaIndicadoresEstrategicos").DataTable({
        initComplete: function () {
            $("div.dt-search").append('<button type="button" id="refresh" class="btn btn-outline-primary ml-2" data-toggle="tooltip" title="Click! recarga la tabla" ><i class="fa fa-recycle fa-spin"></i></button>');

            dt_indEstrategico.rows().every( function () {
                this.child( format(this.data()) ).show();
            } );
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/indicador-estrategico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"])
                dt_indEstrategico.processing(false);
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
                data: 'Codigo',
                width: 30
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'Meta',
                width: 100
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'Descripcion',
                width: 100
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'LineaBase',
                width: 100
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'catTiposResultados.Descripcion',
                width: 100
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'catCategoriasIndicadores.Descripcion',
                width: 100
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'catUnidadesIndicadores.Descripcion',
                width: 100
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
                data: 'IdIndicadorEstrategico',
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

    dt_indEstrategico.on('order.dt search.dt', function () {
        let i = 1;
        dt_indEstrategico.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();
})