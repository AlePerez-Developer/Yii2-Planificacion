$(document).ready(function(){
    let table = $(".tablaListaUnidades").DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'Exportar PDF',
                exportOptions: {
                    columns: [ 0, 1, 2, 3,4 ]
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
                            text: 'Listado de Unidades',

                        }
                    );
                }

            }
        ],
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/unidades-soa/listar-unidades',
            data:{ }
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,5,6] },
            { orderable: false, targets: [0,5,6] },
            { searchable: false, targets: [0,5,6] },
            { className: "dt-acciones", targets: 6 },
            { className: "dt-estado", targets: 5 },
        ],
        columns: [
            { data: 'CodigoUsuario' },
            { data: 'CodigoUnidad'},
            { data: 'NombreUnidad' },
            { data: 'NombreCorto' },
            { data: 'CodigoUnidadPadre' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado = "V" >Vigente</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado = "C" >No vigente</button>' ;
                },
            },
            {
                data: 'null',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-warning btn-sm  btnEditar" codigo="' + row.CodigoUnidad + '" ><i class="fa fa-pen"></i> Editar </button>' +
                        '<button type="button" class="btn btn-danger btn-sm  btnEliminar" codigo="' + row.CodigoUnidad + '" ><i class="fa fa-times"></i> Eliminar </button>' +
                        '</div>'
                        : data;
                },
            },
        ],

        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
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
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    function LoadArbol(){
        $arbol.tree('destroy')
        $.ajax({
            url: "index.php?r=Planificacion/unidades-soa/listar-unidades-padre",
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

    function ReiniciarCampos(){
        $('#formunidad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigo").val('');
        $("#formunidad").trigger("reset");
    }

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
        ReiniciarCampos();
        $arbol.tree('selectNode', null);
        $('#search').click();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
    });

    $(".btnGuardar").click(function () {
        let node = $arbol.tree('getSelectedNode');
        if ($("#formunidad").valid()){
            if ($("#codigo").val() === ''){
                if (node ){
                    GuardarUnidad();
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia...",
                        text: 'Debe seleccionar una unidades-soa padre para la nueva unidades-soa',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    });
                }

            } else {
                ActualizarUnidad();
            }
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE UNIDAD
    ===============================================================*/
    function GuardarUnidad(){
        let node = $arbol.tree('getSelectedNode');
        let nombreunidad = $("#nombreUnidad").val();
        let nombrecorto = $("#nombreCorto").val();
        let unidadpadre = node.id
        let datos = new FormData();
        datos.append("nombreunidad", nombreunidad);
        datos.append("nombrecorto", nombrecorto);
        datos.append("unidadpadre", unidadpadre);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-soa/guardar-unidad",
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
                        text: "Los datos de la nueva unidades-soa se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaUnidades").DataTable().ajax.reload();
                        LoadArbol()
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "existe") {
                        mensaje = "Los datos ingresados ya corresponden a una unidades-soa existente";
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
    $(".tablaListaUnidades tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigounidad = objectBtn.attr("codigo");
        let estadounidad = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigounidad", codigounidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-soa/cambiar-estado-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estadounidad === "V") {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.html('NO VIGENTE');
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

    /*=========================================================
        ELIMINA DE LA BD UN REGISTRO DE UNIDAD
    ==========================================================*/
    $(".tablaListaUnidades tbody").on("click", ".btnEliminar", function () {
        let codigounidad = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigounidad", codigounidad);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar la unidades-soa seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/unidades-soa/eliminar-unidad",
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
                                text: "La unidades-soa ha sido borrada correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaUnidades").DataTable().ajax.reload();
                                LoadArbol()
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "enUso") {
                                mensaje = "Error: la unidades-soa se encuentra en uso y no puede ser eliminada";
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

    /*=======================================================
        BUSCA LA UNIDAD SELECCIONADA EN LA BD
    ========================================================*/
    $(".tablaListaUnidades tbody").on("click", ".btnEditar", function () {
        let codigounidad = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigounidad", codigounidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-soa/buscar-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoUnidad);
                $("#nombreUnidad").val(data.NombreUnidad);
                $("#nombreCorto").val(data.NombreCorto);
                var node = $arbol.tree('getNodeById', data.CodigoUnidadPadre);
                $arbol.tree('selectNode', node);
                $("#btnMostrarCrearUnidad").trigger('click');
            },
            error: function (respuesta) {
                let rta = respuesta['responseText'];
                let mensaje;
                if (rta === "errorval") {
                    mensaje = "Error: Ocurrio un error al validar los datos enviados";
                } else if (rta === "errorenvio") {
                    mensaje = "Error: No se enviaron los datos de forma correcta.";
                } else if (rta === "errorcabezera") {
                    mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                } else {
                    mensaje = rta;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Alerta...',
                    text: mensaje,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                })
            }
        });
    });

    /*=============================================
        ACTUALIZA LA UNIDAD SELECCIONADO EN LA BD
    =============================================*/
    function ActualizarUnidad () {
        let node = $arbol.tree('getSelectedNode');
        let codigounidad = $("#codigo").val();
        let nombreunidad = $("#nombreUnidad").val();
        let nombrecorto = $("#nombreCorto").val();
        let unidadpadre = node.id
        let datos = new FormData();
        datos.append("codigounidad", codigounidad);
        datos.append("unidadpadre", unidadpadre);
        datos.append("nombreunidad", nombreunidad);
        datos.append("nombrecorto", nombrecorto);
        $.ajax({
            url: "index.php?r=Planificacion/unidades-soa/actualizar-unidad",
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
                        text: "La unidades-soa seleccionada se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaUnidades").DataTable().ajax.reload(null, false);
                        LoadArbol()
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "existe") {
                        mensaje = "Los datos ingresados ya corresponden a una unidades-soa existente";
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
});