$(document).ready(function(){
    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="titulosmall">Plan estrategico institucional</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Aperturas Programadas</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Indicadores Programados</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Desc: </div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' + d.DescripcionPEI + '</div>' +
            '           </div>' +
            '       </div>' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Fechas</div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' +
            '                   Vigencia: ' + d.GestionInicio +  ' - ' + d.GestionFin + '<br>' +
            '                   Aprobacion: ' + d.FechaAprobacion +
            '               </div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="cell cell__big" style="vertical-align: middle; text-align: center">' +
            '        <button class="btn btn-info btn-sm">Ver Aperturas</button>       '+
            '       </div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="cell cell__big" style="vertical-align: middle; text-align: center">' +
            '        <button class="btn btn-info btn-sm">Ver Indicadores</button>       '+
            '       </div>' +
            '   </div>' +
            '</div>'
        );
    }
    let table = $(".tablaListaObjEstrategicos").DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        initComplete: function () {
            this.api()
                .columns([2])
                .every(function () {
                    var column = this;
                    var select = $('<select><option value="">Buscar pei...</option></select>')
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
            url: 'index.php?r=Planificacion/obj-estrategico/listar-objs',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,3,5,6] },
            { orderable: false, targets: [0,1,2,5,6] },
            { searchable: false, targets: [0,1,5,6] },
            { className: "dt-acciones", targets: 6 },
            { className: "dt-estado", targets: 5 },
            { width: 10, targets: 0 }
        ],
        fixedColumns: true,
        columns: [
            { data: 'CodigoUsuario' },
            {
                className: 'dt-control',
                data: null,
                defaultContent: '',
            },
            {
                data: 'DescripcionPEI',
                render: function (data, type, row, meta){
                    return (type === 'display')
                        ? data + '<br>' + ' (' + row.GestionInicio + ' - ' + row.GestionFin + ')'
                        :data;
                }
            },
            { data: 'CodigoCOGE'},
            { data: 'Objetivo' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoObjEstrategico + '" estado = "V" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoObjEstrategico + '" estado = "C" ></button>' ;
                },
            },
            {
                data: 'CodigoObjEstrategico',
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
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    $('.tablaListaObjEstrategicos tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    $("#divDatos").hide();

    function ReiniciarCampos(){
        $('#formObjEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#codigoObjEstrategico').val('');
        $('#formObjEstrategico').trigger("reset");
    }

    $("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")){
            $("#divDatos").show(500);
            $("#divTabla").hide(500);
        } else {
            $("#divDatos").hide(500);
            $("#divTabla").show(500);
        }
    });

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });


    $("#btnGuardar").click(function () {
        if ($("#formObjEstrategico").valid()){
            if ($("#codigoObjEstrategico").val() === ''){
                guardarObj();
            } else {
                actualizarObj();
            }
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE OBJETIVO ESTRATEGICO
    ===============================================================*/
    function guardarObj(){
        let codigoPei = $("#codigoPei").val();
        let codigoObj = $("#codigoObj").val();
        let objetivo = $("#objetivo").val();
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        datos.append("codigoObj", codigoObj);
        datos.append("objetivo", objetivo);
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/guardar-objs",
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
                        text: "Los datos del nuevo objetivo estreategico se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaObjEstrategicos").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un objetivo estrategico existente";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorCabecera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorSql") {
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
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoObjEstrategico = objectBtn.attr("codigo");
        let estadoObj = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/cambiar-estado-obj",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estadoObj === "V") {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.attr('estado', 'C');
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.attr('estado', 'V');
                    }
                }
                else {
                    let mensaje;
                    if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se pudo recuperar el Objetivo para su cambio de estado.";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorCabecera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorSql") {
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
        ELIMINA DE LA BD UN REGISTRO DE OBJETIVO ESTRATEGICO
    ==========================================================*/
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEliminar", function () {
        let codigoObjEstrategico = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el objetivo estrategico seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/obj-estrategico/eliminar-obj",
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
                                text: "El objetivo estrategico ha sido borrado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaObjEstrategicos").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "errorEnUso") {
                                mensaje = "Error: el objetivo estrategico se encuentra en uso y no puede ser eliminado";
                            } else if (respuesta === "errorNoEncontrado") {
                                mensaje = "Error: No se pudo recuperar el Objetivo para su eliminacion";
                            } else if (respuesta === "errorEnvio") {
                                mensaje = "Error: No se enviaron los datos de forma correcta.";
                            } else if (respuesta === "errorCabecera") {
                                mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                            } else if (respuesta === "errorSql") {
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
        BUSCA EL OBJETIVO ESTRATEGICO SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEditar", function () {
        let codigoObjEstrategico = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/buscar-obj",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigoObjEstrategico").val(data.CodigoObjEstrategico);
                $("#codigoPei").val(data.CodigoPei);
                $("#codigoObj").val(data.CodigoCOGE);
                $("#objetivo").val(data.Objetivo);
                $("#btnMostrarCrear").trigger('click');
            },
            error: function (respuesta) {
                let rta = respuesta['responseText'];
                let mensaje;
                if (rta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el Objetivo seleccionado.";
                } else if (rta === "errorEnvio") {
                    mensaje = "Error: No se enviaron los datos de forma correcta.";
                } else if (rta === "errorCabecera") {
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
        ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    function actualizarObj () {
        let codigoObjEstrategico = $("#codigoObjEstrategico").val();
        let codigoPei = $("#codigoPei").val();
        let codigoObj = $("#codigoObj").val();
        let objetivo = $("#objetivo").val();
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        datos.append("codigoPei", codigoPei);
        datos.append("codigoObj", codigoObj);
        datos.append("objetivo", objetivo);
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/actualizar-obj",
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
                        text: "El objetivo estrategico seleccionado se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaObjEstrategicos").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un objetivo estrategico existente";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorCabecera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro el Objetivo seleccionado.";
                    } else if (respuesta === "errorSql") {
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
})