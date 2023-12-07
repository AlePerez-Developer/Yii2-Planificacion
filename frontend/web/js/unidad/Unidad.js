$(document).ready(function () {
    function format(d) {
        return (
            '<dl>' +
            '<dt class="dt-small">Vigencia</dt>' +
            '<dd class="dt-small"> De: ' + d.FechaInicio +  ' Hasta: ' + d.FechaFin + '</dd>' +
            '</dl>'
        );
    }

    let table = $("#tablaListaUnidades").DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        initComplete: function () {
            this.api()
                .columns([2,3])
                .every(function () {
                    var column = this;
                    var select = $('<select><option value="">Buscar...</option></select>')
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
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/unidad/listar-unidades',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,2,3,5,6,7] },
            { orderable: false, targets: [0,1,2,3,6,7] },
            { searchable: false, targets: [0,1,5,6,7] },
            { className: "dt-acciones", targets: 7 },
            { className: "dt-estado", targets: 6 },
        ],
        fixedColumns: true,
        columns: [
            {
                className: 'dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoUsuario',
                width: 30
            },
            {
                className: 'dt-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
            },
            {
                className: 'dt-center',
                orderable: false,
                data: 'Da'
            },
            {
                className: 'dt-center',
                orderable: false,
                data: 'Ue'
            },
            { data: 'Descripcion' },
            {
                className: 'dt-center',
                orderable: false,
                searchable: false,
                data: 'Organizacional' ,
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.Organizacional === '1'))
                        ? 'SI'
                        : 'NO' ;
                },
            },
            {
                className: 'dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado = "V" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado = "C" ></button>' ;
                },
            },
            {
                data: 'CodigoUnidad',
                render: function (data, type, row, meta) {
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

    $('#tablaListaUnidades tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    function reiniciarCampos() {
        $('#formUnidad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigoUnidad").val('');
        $('#formUnidad').trigger("reset");
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
        if ($("#formUnidad").valid()) {
            if ($("#codigoUnidad").val() === '') {
                guardarUnidad();
            } else {
                actualizarUnidad()
            }
        }
    });

    $('#fechaInicio').change(function (){
        $("#formUnidad").validate().element('#fechaInicio');
        $("#formUnidad").validate().element('#fechaFin');
    })

    $('#fechaFin').change(function (){
        $("#formUnidad").validate().element('#fechaInicio');
        $("#formUnidad").validate().element('#fechaFin');
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function guardarUnidad() {
        let da = $("#da").val();
        let ue = $("#ue").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let datos = new FormData();
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/guardar-unidad",
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
                        text: "Los datos de la nueva unidad se guardaron correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaUnidades").DataTable().ajax.reload();
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
                        mensaje = "Error: Los datos ingresados ya corresponden a una unidad existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al guardar la unidad.";
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
    $("#tablaListaUnidades tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoUnidad = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/cambiar-estado-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estado === "V") {
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
                        mensaje = "Error: No se pudo recuperar los datos de la unidad para su cambio de estado.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al cambiar el estado del la unidad.";
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
    $("#tablaListaUnidades tbody").on("click", ".btnEliminar", function () {
        let codigoUnidad = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la unidad seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/unidad/eliminar-unidad",
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
                                text: "La unidad ha sido borrada correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $("#tablaListaUnidades").DataTable().ajax.reload();
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
                            } else if (respuesta === "errorSql") {
                                mensaje = "Error: Ocurrio un error en la base de datos al eliminar la unidad.";
                            } else if (respuesta === "errorEnUso") {
                                mensaje = "Error: La unidad se encuentra en uso y no puede ser eliminada.";
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
    BUSCA LA UNIDAD SELECCIONADA EN LA BD
    =============================================*/
    $("#tablaListaUnidades tbody").on("click", ".btnEditar", function () {
        let codigoUnidad = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/buscar-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigoUnidad").val(data.CodigoUnidad);
                $("#da").val(data.Da);
                $("#ue").val(data.Ue);
                $("#descripcion").val(data.Descripcion);
                $("#organizacional").prop( "checked", (data.Organizacional === 1))
                $("#fechaInicio").val(data.FechaInicio);
                $("#fechaFin").val(data.FechaFin);
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
                    mensaje = "Error: No se encontro la informacion de la unidad seleccionada.";
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
    ACTUALIZA LA UNIDAD SELECCIONADA EN LA BD
    =============================================*/
    function actualizarUnidad() {
        let codigoUnidad = $("#codigoUnidad").val();
        let da = $("#da").val();
        let ue = $("#ue").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/actualizar-unidad",
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
                        text: "La unidad se actualizó correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $("#tablaListaUnidades").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorCabecera") {
                        mensaje = "Error: Se esta intentando ingresar por un acceso no autorizado.";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron correctamente de los datos.";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro la informacion de la unidad seleccionada.";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: No se llenaron correctamente los datos requeridos.";
                    } else if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a una unidad existente.";
                    } else if (respuesta === "errorSql") {
                        mensaje = "Error: Ocurrio un error en la base de datos al actualizar los datos de la unidad seleccionada.";
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