let dt_obj
$(document).ready(function () {
    function format(d) {
        return (
            '            <div class="row">' +
            '                <div class="col-4 titulosmall" style="padding-left: 50px">Plan Estrategico Institucional</div>' +
            '                <div class="col-4 titulosmall" style="padding-left: 50px">Area Estrategica</div>' +
            '                <div class="col-4 titulosmall" style="padding-left: 50px">Politica Estrategica</div>' +
            '            </div>' +
            '            <div class="row">' +
            '                <div class="col-1 subsmall">Desc:</div>' +
            '                <div class="col-3 little">' + d["pei"]["DescripcionPei"] + '</div>' +
            '                <div class="col-1 subsmall">Codigo</div>' +
            '                <div class="col-3 little"> - ' + d["areaEstrategica"]["Codigo"] + ' - </div>' +
            '                <div class="col-1 subsmall">Codigo</div>' +
            '                <div class="col-3 little"> - ' + d["politicaEstrategica"]["Codigo"] + ' - </div>' +
            '            </div>' +
            '            <div class="row">' +
            '                <div class="col-1 subsmall">Fechas</div>' +
            '                <div class="col-3 little">Vigencia: ' + d["pei"]["GestionInicio"] + ' - ' + d["pei"]["GestionFin"] + '</div>' +
            '                <div class="col-1 subsmall">Desc:</div>' +
            '                <div class="col-3 little">' + d["areaEstrategica"]["Descripcion"] + '</div>' +
            '                <div class="col-1 subsmall">Desc:</div>' +
            '                <div class="col-3 little">' + d["politicaEstrategica"]["Descripcion"] + '</div>' +
            '            </div>' +
            '            <div class="row">' +
            '                <div class="col-1"></div>' +
            '                <div class="col-3 little">Aprobacion: ' + d["pei"]["FechaAprobacion"] + '</div>' +
            '            </div>'
        );
    }
    dt_obj = $("#tablaListaObjEstrategicos").DataTable({
        initComplete: function () {
            $("div.dt-search").append('<button type="button" id="refresh" class="btn btn-outline-primary ml-2" data-toggle="tooltip" title="Click! recarga la tabla" ><i class="fa fa-recycle fa-spin"></i></button>');
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
                className: 'dt-small dt-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
                width: 30
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'CodigoObjetivo',
                render: function (data, type, row){
                    return (type === 'display')
                        ?  ' (' + row["pei"]["GestionInicio"] + ' - ' + row["pei"]["GestionFin"] + ')'
                        :data;
                },
                width: 100
            },
            {
                className: 'dt-small dt-center',
                data: 'Compuesto',
                width: 100
            },
            {
                className: 'dt-small',
                data: 'Objetivo'
            },
            {
                className: 'dt-small',
                data: 'Producto'
            },
            {
                className: 'dt-small',
                data: 'Indicador_Descripcion'
            },
            {
                className: 'dt-small',
                data: 'Indicador_Formula'
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
                visible: false
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoObjEstrategico',
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

    dt_obj.on('order.dt search.dt', function () {
        let i = 1;
        dt_obj.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    $('#tablaListaObjEstrategicos tbody').on('click', 'td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = dt_obj.row(tr);

        if (row["child"].isShown()) {
            row["child"].hide();
        }
        else {
            console.log(row.data()['pei']['DescripcionPei'])
            row["child"](format(row.data())).show();
        }
    });
})
