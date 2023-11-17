$(document).ready(function () {
    let table = $("#tablaListaPeis").DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/peis/listar-peis',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,2,3,4,5,6] },
            { orderable: false, targets: [0,5,6] },
            { searchable: false, targets: [0,5,6] },
            { className: "dt-acciones", targets: 6 },
            { className: "dt-estado", targets: 5 },
            { width: 10, targets: 0 }
        ],
        fixedColumns: true,
        columns: [
            { data: 'CodigoUsuario' },
            { data: 'DescripcionPei' },
            { data: 'FechaAprobacion' },
            { data: 'GestionInicio' },
            { data: 'GestionFin' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm btnEstado" codigo="' + row.CodigoPei + '" estado = "V" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoPei + '" estado = "C" ></button>' ;
                },
            },
            {
                data: 'CodigoPei',
                render: function (data, type) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><span class="fa fa-trash-alt"></span></button>' +
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
                "sNext": "<span class='fa fa-arrow-right'></span>",
                "sPrevious": "<span class='fa fa-arrow-left'></span>"
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

    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#codigoPei').val('');
        $('#formPei').trigger("reset");
    }

    $("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")) {
            $("#divDatos").show(500);
            $("#divTabla").hide(500);
        } else {
            $("#divDatos").hide(500);
            $("#divTabla").show(500);
        }
    });

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(function () {
        if ($("#formPei").valid()) {
            if ($("#codigoPei").val() === '') {
                guardarPei();
            } else {
                actualizarPei();
            }
        }
    });

    $("#gestionInicio").on( "keypress",function(){
        $("#formPei").validate().element('#gestionFin');
    })

    $("#gestionFin").on( "keypress",function(){
        $("#formPei").validate().element('#gestionInicio');
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO DE PEI
    =============================================*/
    function guardarPei() {
        let descripcionPei = $("#descripcionPei").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("descripcionPei", descripcionPei);
        datos.append("fechaAprobacion", fechaAprobacion);
        datos.append("gestionInicio", gestionInicio);
        datos.append("gestionFin", gestionFin);
        $.ajax({
            url: "index.php?r=Planificacion/peis/guardar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    $("#btnCancelar").click();
                    Swal.fire({
                        icon: "success",
                        title: "Exito...",
                        text: "Los datos del nuevo PEI se guardaron correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaPeis").DataTable().ajax.reload();
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
                        mensaje = "Error: Los datos ingresados ya corresponden a un PEI existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al guardar el PEI.";
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
    $("#tablaListaPeis tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoPei = objectBtn.attr("codigo");
        let estadoPei = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        $.ajax({
            url: "index.php?r=Planificacion/peis/cambiar-estado-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estadoPei === "V") {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.attr('estado', 'C');
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.attr('estado', 'V');
                    }
                }
                else {
                    let mensaje;
                    if (respuesta === "errorCabecera") {
                        mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: Ocurrio un error en el envio de los datos.";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se pudo recuperar el PEI para su cambio de estado.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al cambiar el estado del PEI.";
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
    ELIMINA DE LA BD UN REGISTRO DE PEI
    =============================================*/
    $("#tablaListaPeis tbody").on("click", ".btnEliminar", function () {
        let codigoPei = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el pei seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/peis/eliminar-pei",
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
                                text: "El PEI ha sido borrado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $("#tablaListaPeis").DataTable().ajax.reload();
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
                            } else if (respuesta === "errorNoEncontrado") {
                                mensaje = "Error: No se pudo recuperar el PEI para su eliminacion.";
                            } else if (respuesta === "errorSql") {
                                mensaje = "Error: Ocurrio un error en la base de datos al eliminar el PEI.";
                            } else if (respuesta === "errorEnUso") {
                                mensaje = "Error: El pei se encuentra en uso y no puede ser eliminado.";
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

    /*=============================================
    BUSCA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    $("#tablaListaPeis tbody").on("click", ".btnEditar", function () {
        let codigoPei = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        $.ajax({
            url: "index.php?r=Planificacion/peis/buscar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigoPei").val(data.CodigoPei);
                $("#descripcionPei").val(data.DescripcionPei);
                $("#fechaAprobacion").val(data.FechaAprobacion);
                $("#gestionInicio").val(data.GestionInicio);
                $("#gestionFin").val(data.GestionFin);
                $("#btnMostrarCrear").trigger('click');
            },
            error: function (respuesta) {
                let mensajeRespuesta = respuesta['responseText'];
                let mensaje;
                if (mensajeRespuesta === "errorCabecera") {
                    mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                } else if (mensajeRespuesta === "errorEnvio") {
                    mensaje = "Error: No se enviaron correctamente de los datos.";
                } else if (mensajeRespuesta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el PEI seleccionado.";
                } else {
                    mensaje = mensajeRespuesta;
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
    ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    function actualizarPei() {
        let codigoPei = $("#codigoPei").val();
        let descripcionPei = $("#descripcionPei").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        datos.append("descripcionPei", descripcionPei);
        datos.append("gestionInicio", gestionInicio);
        datos.append("fechaAprobacion", fechaAprobacion);
        datos.append("gestionFin", gestionFin);
        $.ajax({
            url: "index.php?r=Planificacion/peis/actualizar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    $("#btnCancelar").click();
                    Swal.fire({
                        icon: "success",
                        title: "Exito...",
                        text: "El PEI se actualizó correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaPeis").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorCabecera") {
                        mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron correctamente de los datos.";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro el PEI seleccionado.";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: No se llenaron correctamente los datos requeridos.";
                    } else if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un PEI existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al actualizar el PEI seleccionado.";
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
})