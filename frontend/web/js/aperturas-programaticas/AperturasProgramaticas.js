$(document).ready(function () {
    function format(d) {
        // `d` is the original data object for the row
        let org = (d.Organizacional==='1')?'Si':'No';
        let op = (d.Operacional==='1')?'Si':'No';
        return (
            '<dl>' +
                '<dt class="dt-small">Vigencia</dt>' +
                    '<dd class="dt-small"> De: ' + d.FechaInicio +  ' Hasta: ' + d.FechaFin + '</dd>' +
                '<dt class="dt-small">Organizacional: '+ org +'</dt>' +
                '<dt class="dt-small">Operacional: ' + op + '</dt>' +
            '</dl>'
        );
    }

    let table = $("#tablaListaAperturasProgramaticas").DataTable({
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/aperturas-programaticas/listar-aperturas',
            dataSrc: '',
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'Exportar PDF',
                exportOptions: {
                    columns: [ 0, 2, 3, 4, 5 ]
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
                            text: 'Listado de Aperturas Programaticas',

                        }
                    );
                }

            }
        ],
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,2,3,4,6,7] },
            { orderable: false, targets: [0,1,6,7] },
            { searchable: false, targets: [0,1,6,7] },
            { className: "dt-acciones", targets: 7 },
            { className: "dt-estado", targets: 6 },
        ],
        columns: [
            { data: 'CodigoUsuario' },
            {
                className: 'dt-control',
                data: null,
                defaultContent: '',
            },
            { data: 'Da' },
            { data: 'Ue' },
            { data: 'Prg' },
            { data: 'Descripcion' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoAperturaProgramatica + '" estado = "V" >Vigente</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoAperturaProgramatica + '" estado = "C" >No vigente</button>' ;
                },
            },
            {
                data: 'CodigoAperturaProgramatica',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-warning btn-sm  btnEditar" codigo="' + data + '" ><i class="fa fa-pen"></i> Editar </button>' +
                        '<button type="button" class="btn btn-danger btn-sm  btnEliminar" codigo="' + data + '" ><i class="fa fa-times"></i> Eliminar </button>' +
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

    $('#tablaListaAperturasProgramaticas tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            // Open this row
            row.child(format(row.data())).show();
        }
    });


    $("#ingresoDatos").hide();

    function reiniciarCampos() {
        $('#formAperturasProgramaticas *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigo").val('');
        $("form").trigger("reset");
    }

    $("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")) {
            $("#ingresoDatos").show(500);
            $("#divTabla").hide(500);
        } else {
            $("#ingresoDatos").hide(500);
            $("#divTabla").show(500);
        }
    });

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#ingresoDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(function () {
        if ($("#formAperturasProgramaticas").valid()) {
            if ($("#codigo").val() === '') {
                guardarApertura();
            } else {
                actualizarApertura()
            }
        }
    });


    $("#gg").click(function () {
        let organizacional = $("#organizacional").is(':checked')
        alert(organizacional)

    });
    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function guardarApertura() {
        let da = $("#da").val();
        let ue = $("#ue").val();
        let prg = $("#prg").val();
        let descripcion = $("#Descripcion").val();
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let operacional = $("#operacional").is(':checked')?1:0;
        let datos = new FormData();
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("prg", prg);
        datos.append("descripcion", descripcion);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        datos.append("organizacional", organizacional);
        datos.append("operacional", operacional);
        $.ajax({
            url: "index.php?r=Planificacion/aperturas-programaticas/guardar-apertura",
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
                        text: "Los datos de la nueva apertura programatica se guardaron correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaAperturasProgramaticas").DataTable().ajax.reload();
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
                        mensaje = "Error: Los datos ingresados ya corresponden a una apertura programatica existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al guardar la apertura programatica.";
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
    $("#tablaListaAperturasProgramaticas tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigo", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/aperturas-programaticas/cambiar-estado-apertura",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estado === "V") {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.html('No vigente');
                        objectBtn.attr('estado', 'C');
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.html('Vigente');
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
                        mensaje = "Error: No se pudo recuperar los adtos de la apertura programatica para su cambio de estado.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al cambiar el estado del la apertura programatica.";
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
    $("#tablaListaAperturasProgramaticas tbody").on("click", ".btnEliminar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigo", codigo);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la apertura programatica seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/aperturas-programaticas/eliminar-apertura",
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
                                text: "La apertura programatica ha sido borrada correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $("#tablaListaAperturasProgramaticas").DataTable().ajax.reload();
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
                                mensaje = "Error: Los datos ingresados ya corresponden a una apertura programatica existente.";
                            } else if (respuesta === "errorSql") {
                                mensaje = "Error: Ocurrio un error en la base de datos al eliminar la apertura programatica.";
                            } else if (respuesta === "errorEnUso") {
                                mensaje = "Error: La apertura programatica se encuentra en uso y no puede ser eliminada.";
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
    BUSCA LA APERTURA PROGRAMATICA SELECCIONADO EN LA BD
    =============================================*/
    $("#tablaListaAperturasProgramaticas tbody").on("click", ".btnEditar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigo", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/aperturas-programaticas/buscar-apertura",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoAperturaProgramatica);
                $("#da").val(data.Da);
                $("#ue").val(data.Ue);
                $("#prg").val(data.Prg);
                $("#Descripcion").val(data.Descripcion);
                $("#fechaInicio").val(data.FechaInicio);
                $("#fechaFin").val(data.FechaFin);
                $("#organizacional").prop( "checked", (data.Organizacional === 1))
                $("#operacional").prop( "checked", (data.Operacional === 1))
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
                    mensaje = "Error: No se encontro la informacion de la apertura programatica seleccionada.";
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
    function actualizarApertura() {
        let codigo = $("#codigo").val();
        let da = $("#da").val();
        let ue = $("#ue").val();
        let prg = $("#prg").val();
        let descripcion = $("#Descripcion").val();
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let operacional = $("#ejecutora").is(':checked')?1:0;
        let datos = new FormData();
        datos.append("codigo", codigo);
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("prg", prg);
        datos.append("descripcion", descripcion);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        datos.append("organizacional", organizacional);
        datos.append("operacional", operacional);
        $.ajax({
            url: "index.php?r=Planificacion/aperturas-programaticas/actualizar-apertura",
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
                        text: "La apertura programatica se actualizó correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaAperturasProgramaticas").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorCabecera") {
                        mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron correctamente de los datos.";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro la informacion de la apertura programatica seleccionada.";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: No se llenaron correctamente los datos requeridos.";
                    } else if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a una apertura programatica existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al actualizar los adtos de la apertura programatica seleccionada.";
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