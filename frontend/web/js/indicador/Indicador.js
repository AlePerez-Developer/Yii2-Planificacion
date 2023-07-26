$(document).ready(function(){
    let table = $(".tablaListaIndicadores").DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'Exportar PDF',
                exportOptions: {
                    columns: [ 0, 2, 3, 4 ]
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
                            text: 'Listado de Objetivos Estrategicos',

                        }
                    );
                }

            }
        ],
        initComplete: function () {
            this.api()
                .columns([3,4,5,6,7])
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
            url: 'index.php?r=Planificacion/indicador/listar-indicadores',
            dataSrc: '',
        },
        columnDefs: [
            { className: "dt-small", targets: "_all" },
            { className: "dt-center", targets: [0,1,3,8,9] },
            { orderable: false, targets: [0,3,4,5,6,7,8,9] },
            { searchable: false, targets: [0,8,9] },
            { className: "dt-acciones", targets: 9 },
            { className: "dt-estado", targets: 8 },
        ],
        columns: [
            { data: 'CodigoUsuario' },
            { data: 'Codigo'},
            { data: 'Descripcion' },
            { data: 'ArticulacionDescripcion' },
            { data: 'ResultadoDescripcion' },
            { data: 'TipoDescripcion' },
            { data: 'CategoriaDescripcion' },
            { data: 'UnidadDescripcion' },
            {
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "V" >Vigente</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "C" >No vigente</button>' ;
                },
            },
            {
                data: 'CodigoIndicador',
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

    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formindicador *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigo").val('');
        $("form").trigger("reset");
    }

    $("#btnMostrarCrearIndicador").click(function () {
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
        if ($("#formindicador").valid()){
            if ($("#codigo").val() === ''){
                GuardarIndicador();
            } else {
                ActualizarIndicador();
            }
        }
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE OBJETIVO ESTRATEGICO
    ===============================================================*/
    function GuardarIndicador(){
        let codigo = $("#Codigo").val();
        let descripcion = $("#Descripcion").val();
        let articulacion = $("#Articulacion").val();
        let resultado = $("#Resultado").val();
        let tipoindicador = $("#Tipo").val();
        let categoria = $("#Categoria").val();
        let unidad = $("#Unidad").val();
        let datos = new FormData();
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        datos.append("articulacion", articulacion);
        datos.append("resultado", resultado);
        datos.append("tipoindicador", tipoindicador);
        datos.append("categoria", categoria);
        datos.append("unidad", unidad);
        $.ajax({
            url: "index.php?r=Planificacion/indicador/guardar-indicador",
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
                        text: "Los datos del nuevo indicador se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaIndicadores").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un indicador existente";
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
    $(".tablaListaIndicadores tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoindicador", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/indicador/cambiar-estado-indicador",
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
                        mensaje = "Error: No se pudo recuperar el indicador para su cambio de estado.";
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
        ELIMINA DE LA BD UN REGISTRO DE INDICADOR
    ==========================================================*/
    $(".tablaListaIndicadores tbody").on("click", ".btnEliminar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoindicador", codigo);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el indicador seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/indicador/eliminar-indicador",
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
                                text: "El indicador ha sido borrado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaIndicadores").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "errorEnUso") {
                                mensaje = "Error: el indicador se encuentra en uso y no puede ser eliminado";
                            } else if (respuesta === "errorNoEncontrado") {
                                mensaje = "Error: No se pudo recuperar el indicador para su eliminacion";
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
        BUSCA EL INDICADOR SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaIndicadores tbody").on("click", ".btnEditar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoindicador", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/indicador/buscar-indicador",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigo").val(data.CodigoIndicador);
                $("#Codigo").val(data.Codigo);
                $("#Descripcion").val(data.Descripcion);
                $("#Articulacion").val(data.Articulacion);
                $("#Resultado").val(data.Resultado);
                $("#Tipo").val(data.TipoIndicador);
                $("#Categoria").val(data.Categoria);
                $("#Unidad").val(data.Unidad);
                $("#btnMostrarCrearIndicador").trigger('click');
            },
            error: function (respuesta) {
                let rta = respuesta['responseText'];
                let mensaje;
                if (rta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el indicador seleccionado.";
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
        ACTUALIZA EL INDICADOR SELECCIONADO EN LA BD
    =============================================*/
    function ActualizarIndicador () {
        let codigoindicador = $("#codigo").val();
        let codigo = $("#Codigo").val();
        let descripcion = $("#Descripcion").val();
        let articulacion = $("#Articulacion").val();
        let resultado = $("#Resultado").val();
        let tipoindicador = $("#Tipo").val();
        let categoria = $("#Categoria").val();
        let unidad = $("#Unidad").val();
        let datos = new FormData();
        datos.append("codigoindicador", codigoindicador);
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        datos.append("articulacion", articulacion);
        datos.append("resultado", resultado);
        datos.append("tipoindicador", tipoindicador);
        datos.append("categoria", categoria);
        datos.append("unidad", unidad);
        $.ajax({
            url: "index.php?r=Planificacion/indicador/actualizar-indicador",
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
                        text: "El indicador seleccionado se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaIndicadores").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un indicador existente";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorCabecera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro el indicador seleccionado.";
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