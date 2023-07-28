$(document).ready(function () {
    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Unidad</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Programa</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Proyecto</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Actividad</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-12">' +
            '       <div class="row">' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Da - Ue: </div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.Da + '-' + d.Ue + '</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Prg: </div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.CodigoPrograma + '</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Pry: </div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.CodigoProyecto + '</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Act: </div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.CodigoActividad + '</div>' +
            '           </div>' +
            '       </div>' +
            '       <div class="row">' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Desc:</div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">'+ d.DescripcionUnidad +'</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Desc:</div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.DescripcionPrograma + '</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Desc:</div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.DescripcionProyecto + '</div>' +
            '           </div>' +
            '           <div class="col-1">' +
            '               <div class="subsmall">Desc:</div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="little">' + d.DescripcionActividad + '</div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        )
    }

    let table = $("#tablaListaAperturasProgramaticas").DataTable({
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
        initComplete: function () {
            this.api()
                .columns([2,3,4,5,6])
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
            url: 'index.php?r=Planificacion/apertura-programatica/listar-aperturas-programaticas',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,2,3,4,5,6,7,9,10,11] },
            { orderable: false, targets: [0,1,2,3,4,5,6,9,10,11] },
            { searchable: false, targets: [0,1,9,10,11] },
            { className: "dt-acciones", targets: 11 },
            { className: "dt-estado", targets: 10 },
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
            { data: 'CodigoPrograma' },
            { data: 'CodigoProyecto' },
            { data: 'CodigoActividad' },
            { data: 'AperturaProgramatica' },
            { data: 'Descripcion' },
            {
                data: 'Organizacional',
                render: function (data, type, row, meta){
                    return ( (type === 'display') && (row.Organizacional === '1'))?'Si':'No'
                }
            },
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

    $('.unidad').select2({
        placeholder: "Elija una unidad",
        allowClear: true
    });

    $('.programa').select2({
        placeholder: "Elija un programa",
        allowClear: true
    });

    $('.proyecto').select2({
        placeholder: "Elija un proyecto",
        allowClear: true
    });

    $('.actividad').select2({
        placeholder: "Elija una actividad",
        allowClear: true
    });


    $("#ingresoDatos").hide();

    function reiniciarCampos() {
        $('#formAperturasProgramaticas *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigo").val('');
        $(".unidad").val(null).trigger('change');
        $(".programa").val(null).trigger('change');
        $(".proyecto").val(null).trigger('change');
        $(".actividad").val(null).trigger('change');
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
                guardarAperturaProgramatica();
            } else {
                actualizarAperturaProgramatica()
            }
        }
    });

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function guardarAperturaProgramatica() {
        let unidad = $("#unidad").val();
        let programa = $("#programa").val();
        let proyecto = $("#proyecto").val();
        let actividad = $("#actividad").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let datos = new FormData();
        datos.append("unidad", unidad);
        datos.append("programa", programa);
        datos.append("proyecto", proyecto);
        datos.append("actividad", actividad);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        $.ajax({
            url: "index.php?r=Planificacion/apertura-programatica/guardar-apertura-programatica",
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
        let codigoAperturaProgramatica = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoAperturaProgramatica", codigoAperturaProgramatica);
        $.ajax({
            url: "index.php?r=Planificacion/apertura-programatica/cambiar-estado-apertura-programatica",
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
                        mensaje = "Error: No se pudo recuperar los datos de la apertura programatica para su cambio de estado.";
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
        let codigoAperturaProgramatica = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoAperturaProgramatica", codigoAperturaProgramatica);
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
                    url: "index.php?r=Planificacion/apertura-programatica/eliminar-apertura-programatica",
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
                            } else if (respuesta === "errorNoEncontrado") {
                                mensaje = "Error: No se pudo recuperar la apertura programatica para su eliminacion";
                            } else if (respuesta === "errorEnUso") {
                                mensaje = "Error: La apertura programatica se encuentra en uso y no puede ser eliminada.";
                            } else if (respuesta === "errorSql") {
                                mensaje = "Error: Ocurrio un error en la base de datos al eliminar la apertura programatica.";
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
        let codigoAperturaProgramatica = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoAperturaProgramatica", codigoAperturaProgramatica);
        $.ajax({
            url: "index.php?r=Planificacion/apertura-programatica/buscar-apertura-programatica",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoAperturaProgramatica);
                $("#unidad").val(data.Unidad).trigger('change')
                $("#programa").val(data.Programa).trigger('change')
                $("#proyecto").val(data.Proyecto).trigger('change')
                $("#actividad").val(data.Actividad).trigger('change')
                $("#descripcion").val(data.Descripcion);
                $("#organizacional").prop( "checked", (data.Organizacional === 1))
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
    ACTUALIZA LA APERTURA PROGRAMATICA SELECCIONADA EN LA BD
    =============================================*/
    function actualizarAperturaProgramatica() {
        let codigoAperturaProgramatica = $("#codigo").val();
        let unidad = $("#unidad").val();
        let programa = $("#programa").val();
        let proyecto = $("#proyecto").val();
        let actividad = $("#actividad").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let datos = new FormData();
        datos.append("codigoAperturaProgramatica", codigoAperturaProgramatica);
        datos.append("unidad", unidad);
        datos.append("programa", programa);
        datos.append("proyecto", proyecto);
        datos.append("actividad", actividad);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        $.ajax({
            url: "index.php?r=Planificacion/apertura-programatica/actualizar-apertura-programatica",
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
                        mensaje = "Error: Ocurrio un error en la base de datos al actualizar los datos de la apertura programatica seleccionada.";
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