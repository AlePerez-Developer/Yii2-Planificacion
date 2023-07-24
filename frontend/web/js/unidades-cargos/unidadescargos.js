$(document).ready(function(){
    let tabledata = $(".tablaListaUnidadesCargos").DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'Exportar PDF',
                exportOptions: {
                    columns: [ 0, 1, 2, 3 ]
                },
                customize: function ( doc ) {
                    var cols = [];
                    cols[0] = {text: 'Pagina 1', alignment: 'left', margin:[20] };
                    cols[1] = {text:'pie de pagina', alignment: 'center' };
                    cols[2] = {text: 'Fecha/Hora', alignment: 'right', margin:[0,0,20] };

                    var objFooter = {};
                    objFooter['columns'] = cols;
                    doc['footer']=objFooter;

                    doc.content.splice(1, 0,
                        {
                            margin: [0, 0, 0, 12],
                            alignment: 'center',
                            text: 'Listado de Cargos Unidades',

                        }
                    );
                }

            }
        ],
        columnDefs: [
            {
                targets: [0,3,4,5],
                className: 'dt-center'
            },
            {
                targets: [0,4,5],
                searchable: false,
                orderable: false
            }
        ],
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/unidades-cargos/listar-unidades-cargos',
            dataSrc: '',
        },
        columns: [
            { data: 'UnidadSoa' },
            { data: 'NombreUnidad' },
            { data: 'NombreCargo' },
            { data: 'CodigoSectorTrabajo' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigoc="'+row.CodigoCargo+'" codigou="' + row.CodigoUnidad + '" estado = "V" >VIGENTE</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigoc="'+row.CodigoCargo+'" codigou="' + row.CodigoUnidad + '" estado = "C" >CADUCO</button>' ;
                },
            },
            {
                data: 'CodigoUsuario',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-danger btn-sm  btnEliminar" codigoc="'+row.CodigoCargo+'" codigou="' + row.CodigoUnidad + '" ><i class="fa fa-times"></i> ELIMINAR </button>' +
                        '</div>'
                        : data;
                },
            },
            //{ data: 'CodigoUsuario' }
        ],
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "<i class='fa fa-arrow-right'></i>",
                "sPrevious": "<i class='fa fa-arrow-left'></i>"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
    tabledata.on('order.dt search.dt', function () {
        let i = 1;
        tabledata.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();


    let table = $(".tablaListaCargos").DataTable({
        columnDefs: [
            {
                targets: [0,2],
                className: 'dt-center'
            },
            {
                targets: [0,1],
                className: 'dt-small'
            },
            {
                targets: [0,2],
                searchable: false,
                orderable: false
            }
        ],
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/cargos/listar-cargos',
            dataSrc: '',
        },
        columns: [
            { data: 'CodigoUsuario' },
            { data: 'NombreCargo' },
            {
                data: 'CodigoCargo',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<input class="form-check-input" type="radio" name="radiocargo" codigo="' + data + '">'
                        : data;
                },
            },
        ],
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "<i class='fa fa-arrow-right'></i>",
                "sPrevious": "<i class='fa fa-arrow-left'></i>"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();


    function LoadArbol(){
        $arbol.tree('destroy')
        $.ajax({
            url: "index.php?r=Planificacion/unidades/listar-unidades-padre",
            method: "POST",
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                $arbol.tree({
                    closedIcon: $('<i class="fas fa-arrow-circle-right"></i>'),
                    openedIcon: $('<i class="fas fa-arrow-circle-down"></i>'),
                    autoOpen: 1,
                    data: JSON.parse(respuesta),
                    onCreateLi: (node, $el) => {
                        if (node.matches) {
                            $el.addClass("highlight-node");
                        }
                    }
                });
            }
        });
    }

    var $arbol = $('#unidadPadre');
    LoadArbol()

    $("#search").on("click", () => {
        const searchTerm = $("#search-term").val().toLowerCase();
        const tree = $arbol.tree("getTree");
        if (!searchTerm) {
            foundMatch = false;
            tree.iterate((node) => {
                node['openForMatch'] = false;
                node["matches"] = false;
                return true;
            });
            $arbol.tree("refresh");
            return;
        }
        foundMatch = false;
        tree.iterate((node) => {
            const matches = node.name.toLowerCase().includes(searchTerm);
            node["openForMatch"] = matches;
            node["matches"] = matches;
            if (matches) {
                foundMatch = true;
                if (node.isFolder()) {
                    node.is_open = true;
                }
                let parent = node.parent;
                while (parent) {
                    parent["openForMatch"] = true;
                    parent.is_open = true;
                    parent = parent.parent;
                }
            }
            return true;
        });
        $arbol.tree("refresh");
    });

    $("#IngresoDatos").hide();


    $("#btnMostrarCrearUnidad").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")){
            $("#IngresoDatos").show(500);
            $("#Divtabla").hide(500);
        } else {
            $("#IngresoDatos").hide(500);
            $("#Divtabla").show(500);
        }
    });

    $(".btnCancel").click(function () {
        $('.icon').toggleClass('opened');
        $arbol.tree('selectNode', null);
        let radioValue = $("input[name='radiocargo']:checked");
        radioValue.prop('checked', false);
        LoadArbol();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
    });

    $(".btnGuardar").click(function () {
        let node = $arbol.tree('getSelectedNode');
        let radioValue = $("input[name='radiocargo']:checked").attr("codigo")
        if ( node && radioValue ){
            GuardarUnidadCargo();
        } else {
            Swal.fire({
                icon: "warning",
                title: "Advertencia...",
                text: 'Debe seleccionar una unidad y un cargo para crear la ralacion',
                showCancelButton: false,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Cerrar"
            });
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE UNIDAD
    ===============================================================*/
    function GuardarUnidadCargo(){
        let node = $arbol.tree('getSelectedNode');
        let codigocargo = $("input[name='radiocargo']:checked").attr("codigo")
        let unidadpadre = node.id
        let datos = new FormData();
        datos.append("unidad", unidadpadre);
        datos.append("cargo", codigocargo);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-cargos/guardar-unidad-cargo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    $(".btnCancel").click();
                    Swal.fire({
                        icon: "success",
                        title: "Exito...",
                        text: "Los datos se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaUnidadesCargos").DataTable().ajax.reload();
                        LoadArbol()
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "existe") {
                        mensaje = "Los datos ingresados ya corresponden a un registro existente";
                    } else if (respuesta === "errorval") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorenvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorcabezera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorsql") {
                        mensaje = "Error: Ocurrio un error en la sentencia SQL";
                    } else {
                        mensaje = respuesta;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Advertencia...",
                        text: mensaje,
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    }

    /*=============================================
        CAMBIA EL ESTADO DEL REGISTRO
    =============================================*/
    $(".tablaListaUnidadesCargos tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigocargo = objectBtn.attr("codigoc");
        let codigounidad = objectBtn.attr("codigou");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        datos.append("codigounidad", codigounidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-cargos/cambiar-estado-unidad-cargo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estado === "V") {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.html('CADUCO');
                        objectBtn.attr('estado', 'C');
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.html('VIGENTE');
                        objectBtn.attr('estado', 'V');
                    }
                }
                else {
                    let mensaje;
                    if (respuesta === "errorval") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorenvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorcabezera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorsql") {
                        mensaje = "Error: Ocurrio un error en la sentencia SQL";
                    } else {
                        mensaje = respuesta;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Alerta...',
                        text: mensaje,
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Cerrar'
                    });
                }
            }
        });
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    $(".tablaListaUnidadesCargos tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this);
        let codigocargo = objectBtn.attr("codigoc");
        let codigounidad = objectBtn.attr("codigou");
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        datos.append("codigounidad", codigounidad);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el registro elegido?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/unidades-cargos/eliminar-unidad-cargo",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (respuesta) {
                        if (respuesta === "ok") {
                            Swal.fire({
                                icon: "success",
                                title: "Exito...",
                                text: "La informacion ha sido borrado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaUnidadesCargos").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "errorCabecera") {
                                mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                            } else if (respuesta === "errorEnvio") {
                                mensaje = "Error: Ocurrio un error en el envio de los datos.";
                            } else if (respuesta === "errorValidacion") {
                                mensaje = "Error: No se llenaron correctamente los datos requeridos.";
                            } else if (respuesta === "errorExiste") {
                                mensaje = "Error: Los datos ingresados ya corresponden a un registro existente.";
                            } else if (respuesta === "errorSql") {
                                mensaje = "Error: Ocurrio un error en la base de datos al eliminar el registro.";
                            } else if (respuesta === "errorEnUso") {
                                mensaje = "Error: El registro se encuentra en uso y no puede ser eliminada.";
                            } else {
                                mensaje = respuesta;
                            }
                            Swal.fire({
                                icon: "error",
                                title: 'Alerta...',
                                text: mensaje,
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            })
                        }
                    }
                });
            }
        });
    });


});