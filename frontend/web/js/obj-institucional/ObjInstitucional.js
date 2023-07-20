$(document).ready(function(){
    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Plan estrategico institucional</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Objetivo Estrategico</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Aperturas Programadas</div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="titulosmall">Indicadores Programados</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-6">' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Desc: </div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' + d.DescripcionPEI + '</div>' +
            '           </div>' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Codigo: </div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">'+ d.COGEEstrategico +'</div>' +
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
            '           <div class="col-2">' +
            '               <div class="subsmall">Desc:</div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">'+d.ObjEstrategico+'</div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="cell cell__big">' +
            '           <ol>'+
            '               <li class="little">27-258-256-0000-235 direccion superior de la usfx</li>'+
            '               <li class="little">27-258-256-0000-235 direccion superior de la usfx</li>'+
            '               <li class="little">27-258-256-0000-235 direccion superior de la usfx</li>'+
            '           </ol>'+
            '           <select class="oso" style="width: 100%; font-size: 13px"><option>27-258-256-0000-235 direccion superior de la usfx</option><option>27-258-256-0000-235 direccion superior de la usfx</option><option>27-258-256-0000-235 direccion superior de la usfx</option><option>27-258-256-0000-235 direccion superior de la usfx</option>  </select> '+
            '       </div>' +
            '   </div>' +
            '   <div class="col-3">' +
            '       <div class="cell cell__big">asdasdasd</div>' +
            '   </div>' +
            '</div>'
        );
    }
    let table = $(".tablaListaObjInstitucionales").DataTable({
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
                            text: 'Listado de Objetivos Institucionals',

                        }
                    );
                }

            }
        ],
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
            url: 'index.php?r=Planificacion/obj-institucional/listar-objs',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,3,5,6] },
            { orderable: false, targets: [0,1,2,5,6] },
            { searchable: false, targets: [0,1,5,6] },
            { className: "dt-acciones", targets: 6 },
            { className: "dt-estado", targets: 5 },
        ],
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
            { data: 'Codigo' },
            { data: 'Objetivo' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoObjInstitucional + '" estado = "V" >Vigente</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoObjInstitucional + '" estado = "C" >No vigente</button>' ;
                },
            },
            {
                data: 'CodigoObjInstitucional',
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
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    $('.tablaListaObjInstitucionales tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    $('.objestrategicos').select2({
        placeholder: "Elija un objetivo estrategico",
        allowClear: true
    });

    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formobjinstitucional *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $(".objestrategicos").val(null).trigger('change');
        $("#codigo").val('');
        $("form").trigger("reset");
    }

    $("#btnMostrarCrearObj").click(function () {
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
        if ($("#formobjinstitucional").valid()){
            if ($("#codigo").val() === ''){
                GuardarObj();
            } else {
                ActualizarObj();
            }
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE OBJETIVO ESTRATEGICO
    ===============================================================*/
    function GuardarObj(){
        let codigoobjestrategico = $("#CodigoObjEstrategico").val();
        let codigocoge = $("#CodigoCOGE").val();
        let objetivo = $("#Objetivo").val();
        let datos = new FormData();
        datos.append("codigoobjestrategico", codigoobjestrategico);
        datos.append("codigocoge", codigocoge);
        datos.append("objetivo", objetivo);
        $.ajax({
            url: "index.php?r=Planificacion/obj-institucional/guardar-objs",
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
                        text: "Los datos del nuevo objetivo institucional se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaObjInstitucionales").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un objetivo institucional existente";
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
    $(".tablaListaObjInstitucionales tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoobjinstitucional", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/obj-institucional/cambiar-estado-obj",
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
    $(".tablaListaObjInstitucionales tbody").on("click", ".btnEliminar", function () {
        let codigoobjinstitucional = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoobjinstitucional", codigoobjinstitucional);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el objetivo institucional seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/obj-institucional/eliminar-obj",
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
                                text: "El objetivo institucional ha sido borrado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaObjInstitucionales").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "errorEnUso") {
                                mensaje = "Error: el objetivo institucional se encuentra en uso y no puede ser eliminado";
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
    $(".tablaListaObjInstitucionales tbody").on("click", ".btnEditar", function () {
        let codigoobjinstitucional = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoobjinstitucional", codigoobjinstitucional);
        $.ajax({
            url: "index.php?r=Planificacion/obj-institucional/buscar-obj",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoObjInstitucional);
                $("#CodigoObjEstrategico").val(data.CodigoObjEstrategico);
                $(".objestrategicos").val(data.CodigoObjEstrategico).trigger('change')
                $("#CodigoCOGE").val(data.CodigoCOGE);
                $("#Objetivo").val(data.Objetivo);
                $("#btnMostrarCrearObj").trigger('click');
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
    function ActualizarObj () {
        let codigoobjinstitucional = $("#codigo").val();
        let codigoobjestrategico = $("#CodigoObjEstrategico").val();
        let codigocoge = $("#CodigoCOGE").val();
        let objetivo = $("#Objetivo").val();
        let producto = $("#Producto").val();
        let datos = new FormData();
        datos.append("codigoobjinstitucional", codigoobjinstitucional);
        datos.append("codigoobjestrategico", codigoobjestrategico);
        datos.append("codigocoge", codigocoge);
        datos.append("objetivo", objetivo);
        $.ajax({
            url: "index.php?r=Planificacion/obj-institucional/actualizar-obj",
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
                        text: "El objetivo institucional seleccionado se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaObjInstitucionales").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un objetivo institucional existente";
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