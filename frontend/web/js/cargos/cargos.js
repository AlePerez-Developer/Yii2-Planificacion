$(document).ready(function(){
    let table = $(".tablaListaCargos").DataTable({
        initComplete: function () {
            this.api()
                .columns([4])
                .every(function () {
                    var column = this;
                    var select = $('<select><option value=""></option></select>')
                        .appendTo($(column.header()))
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                });
        },
        columnDefs: [
            {
                targets: [0,5,6],
                className: 'dt-center'
            },
            {
                targets: [0,5,6],
                searchable: false,
                orderable: false
            },
            {
                targets: [4],
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
            { data: 'DescripcionCargo' },
            {
                data: 'ArchivoManualFunciones',
                render: function (data, type, row, meta) {
                    return (type === 'display')
                        ? '<a href="#" class="enlace" data="'+data+'" >Ver Manual</a>'
                        : data
                }
            },
            { data: 'CodigoSectorTrabajo' },
            {
                data: 'CodigoCargo',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-xs  btnEstado" codigo="' + data + '" estado = "V" >VIGENTE</button>'
                        : '<button type="button" class="btn btn-danger btn-xs  btnEstado" codigo="' + data + '" estado = "C" >CADUCO</button>' ;
                },
            },
            {
                data: 'CodigoCargo',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                          '<button type="button" class="btn btn-warning btn-xs  btnEditar" codigo="' + data + '" ><i class="fa fa-pen"></i> EDITAR </button>' +
                          '<button type="button" class="btn btn-danger btn-xs  btnEliminar" codigo="' + data + '" ><i class="fa fa-times"></i> ELIMINAR </button>' +
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
        table.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();


    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formcargo *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("select[name='sectorTrabajo']").removeAttr("disabled");
        $("#codigo").val('');
        $("form").trigger("reset");
    }

    $("#btnMostrarCrearCargo").click(function () {
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
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
    });

    $(".btnGuardar").click(function () {
        if ($("#formcargo").valid()){
            if ($("#codigo").val() === ''){
                GuardarCargo();
            } else {
                ActualizarCargo();
            }
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE CARGO
    ===============================================================*/
    function GuardarCargo(){
        let nombrecargo = $("#nombreCargo").val();
        let descripcioncargo = $("#descripcionCargo").val();
        let requisitosprincipales = $("#requisitosPrincipales").val();
        let requisitosopcionales = $("#requisitosOpcionales").val();
        let sectortrabajo = $("#sectorTrabajo").val();

        let datos = new FormData();
        datos.append("nombrecargo", nombrecargo);
        datos.append("descripcioncargo", descripcioncargo);
        datos.append("requisitosprincipales", requisitosprincipales);
        datos.append("requisitosopcionales", requisitosopcionales);
        datos.append("sectortrabajo", sectortrabajo);
        $.ajax({
            url: "index.php?r=Planificacion/cargos/guardar-cargo",
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
                        text: "Los datos del nuevo cargo se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaCargos").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "existe") {
                        mensaje = "Los datos ingresados ya corresponden a un cargo existente";
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
    $(".tablaListaCargos tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigocargo = objectBtn.attr("codigo");
        let estadocargo = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        $.ajax({
            url: "index.php?r=Planificacion/cargos/cambiar-estado-cargo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estadocargo === "V") {
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

    /*=========================================================
        ELIMINA DE LA BD UN REGISTRO DE CARGO
    ==========================================================*/
    $(".tablaListaCargos tbody").on("click", ".btnEliminar", function () {
        let codigocargo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el cargo seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/cargos/eliminar-cargo",
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
                                text: "El cargo ha sido borrada correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaCargos").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "enUso") {
                                mensaje = "Error: el cargo se encuentra en uso y no puede ser eliminado";
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
        BUSCA EL CARGO SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaCargos tbody").on("click", ".btnEditar", function () {
        let codigocargo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        $.ajax({
            url: "index.php?r=Planificacion/cargos/buscar-cargo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoCargo);
                $("#sectorTrabajo").val(data.CodigoSectorTrabajo);
                $("#nombreCargo").val(data.NombreCargo);
                $("#descripcionCargo").val(data.DescripcionCargo);
                $("#requisitosPrincipales").val(data.RequisitosPrincipales);
                $("#requisitosOpcionales").val(data.RequisitosOpcionales);
                $("select[name='sectorTrabajo']").attr('disabled', 'disabled');
                $("#btnMostrarCrearCargo").trigger('click');
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
        ACTUALIZA EL CARGO SELECCIONADO EN LA BD
    =============================================*/
    function ActualizarCargo () {
        let codigocargo = $("#codigo").val();
        let nombrecargo = $("#nombreCargo").val();
        let descripcioncargo = $("#descripcionCargo").val();
        let requisitosprincipales = $("#requisitosPrincipales").val();
        let requisitosopcionales = $("#requisitosOpcionales").val();
        let sectortrabajo = $("#sectorTrabajo").val();
        let datos = new FormData();
        datos.append("codigocargo", codigocargo);
        datos.append("nombrecargo", nombrecargo);
        datos.append("descripcioncargo", descripcioncargo);
        datos.append("requisitosprincipales", requisitosprincipales);
        datos.append("requisitosopcionales", requisitosopcionales);
        datos.append("sectortrabajo", sectortrabajo);
        console.log(datos)
        $.ajax({
            url: "index.php?r=Planificacion/cargos/actualizar-cargo",
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
                        text: "El cargo seleccionado se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaCargos").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "existe") {
                        mensaje = "Los datos ingresados ya corresponden a un cargo existente";
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

    $(".tablaListaCargos tbody").on("click", ".enlace", function () {
        let file = $(this).attr("data");
        $('#pdfFrame').attr('src','pdf/'+file);
        $('#pdfModal').modal('show');
    });

});