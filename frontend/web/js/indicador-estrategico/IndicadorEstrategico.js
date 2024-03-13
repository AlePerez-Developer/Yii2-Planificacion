$(document).ready(function(){
    let formReset = false

    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="titulosmall">Objetivo Estrategico</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="row">' +
            '           <div class="col-4">' +
            '               <div class="subsmall" style="text-align: right">Codigo: </div>' +
            '           </div>' +
            '           <div class="col-8">' +
            '               <div class="little">' + d.CodigoObjetivo + '</div>' +
            '           </div>' +
            '       </div>' +
            '       <div class="row">' +
            '           <div class="col-4">' +
            '               <div class="subsmall" style="text-align: right">Descripcion: </div>' +
            '           </div>' +
            '           <div class="col-8">' +
            '               <div class="little">' + d.ObjetivoEstrategico + '</div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        );
    }

    let table = $(".tablaListaIndicadoresEstrategicos").DataTable({
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        initComplete: function () {
            this.api()
                .columns([5,6,7,8])
                .every(function () {
                    var column = this;
                    var select = $('</br><select><option value="">Buscar...</option></select>')
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
            url: 'index.php?r=Planificacion/indicador-estrategico/listar-indicadores-estrategicos',
            dataSrc: '',
        },
        fixedColumns: true,
        columns: [
            {
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoUsuario',
                width: 30
            },
            {
                className: 'dt-small dt-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
            },
            {
                className: 'dt-small dt-center',
                data: 'Codigo'
            },
            {
                className: 'dt-small dt-center',
                data: 'Meta'
            },
            {
                className: 'dt-small',
                data: 'Descripcion'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'ResultadoDescripcion'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'TipoDescripcion'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'CategoriaDescripcion'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'UnidadDescripcion'
            },
            {
                className: 'dt-small dt-center',
                data: 'CodigoObjetivo'
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row.CodigoEstado === 'V'))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "V" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "C" ></button>' ;
                },
            },
            {
                className: 'dt-small dt-center dt-small',
                orderable: false,
                searchable: false,
                data: null,
                render: function (data, type, row) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-info btn-sm  btnProgramar" codigo="' + row.CodigoIndicador + '" ><i class="fa fa-eye"></i></button>' +
                        '</div>'
                        : data;
                },
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoIndicador',
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
                "sFirst": "<span class='fas fa-angle-double-left'></span>",
                "sLast": "<span class='fas fa-angle-double-right'></span>",
                "sNext": "<span class='fas fa-angle-right'></span>",
                "sPrevious": "<span class='fas fa-angle-left'></span>"
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

    /*table.on('init',function (){
       for (let i = 0; i < table.rows().count(); i++){
           row = table.row(i);
           programado = row.data().Programado;
           if (programado == 0){
                $(row.nodes()).addClass('completo');
           } else {
               $(row.nodes()).addClass('incompleto');
           }
       }
    });*/

    $('.tablaListaIndicadoresEstrategicos tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    $('#codigoObjEstrategico').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo estrategico",
        allowClear: true
    }).change(function(e) {
        if (!formReset){
            $("#formIndicadorEstrategico").validate().element('#codigoObjEstrategico');
        }
    })

    function ReiniciarCampos(){
        $('#formIndicadorEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('.invalid-feedback').each(function (){
            $(this).removeAttr('style')
        })
        $("#codigoObjEstrategico").val(null).trigger('change');
        $("#codigoIndicadorEstrategico").val('');
        $('#formIndicadorEstrategico').trigger('reset');
    }

    $("#btnMostrarCrear").click(function () {
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

    $("#btnCancelar").click(function () {
        formReset = true;
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
        formReset = false;
    });

    $("#btnGuardar").click(function () {
        if ($("#formIndicadorEstrategico").valid()){
            if ($("#codigoIndicadorEstrategico").val() === ''){
                GuardarIndicador()
            } else {
                ActualizarIndicador()
            }
        }
    });

    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnProgramar", function () {
        $('#programarIndicadorEstrategico').modal('show')
    });

    /*=============================================================
       INSERTA EN LA BD UN NUEVO REGISTRO DE INDICADOR ESTRATEGICO
    ===============================================================*/
    function GuardarIndicador(){
        let codigoObjetivoEstrategico = $("#codigoObjEstrategico").val();
        let codigoIndicador = $("#codigoIndicador").val();
        let metaIndicador = $("#metaIndicador").val();
        let descripcion = $("#descripcion").val();
        let tipoResultado = $("#tipoResultado").val();
        let tipoIndicador = $("#tipoIndicador").val();
        let categoriaIndicador = $("#categoriaIndicador").val();
        let tipoUnidad = $("#tipoUnidad").val();
        let datos = new FormData();
        datos.append("codigoObjetivoEstrategico", codigoObjetivoEstrategico);
        datos.append("codigoIndicador", codigoIndicador);
        datos.append("metaIndicador", metaIndicador);
        datos.append("descripcion", descripcion);
        datos.append("tipoResultado", tipoResultado);
        datos.append("tipoIndicador", tipoIndicador);
        datos.append("categoriaIndicador", categoriaIndicador);
        datos.append("tipoUnidad", tipoUnidad);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/guardar-indicador-estrategico",
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
                        text: "Los datos del nuevo indicador estrategico se guardaron correctamente",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaIndicadoresEstrategicos").DataTable().ajax.reload();
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un indicador estrategico existente";
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
    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/cambiar-estado-indicador-estrategico",
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
                    if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se pudo recuperar el indicador estrategico para su cambio de estado.";
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
        ELIMINA DE LA BD UN REGISTRO DE INDICADOR ESTRATEGICO
    ==========================================================*/
    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnEliminar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigo);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el indicador estrategico seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                $.ajax({
                    url: "index.php?r=Planificacion/indicador-estrategico/eliminar-indicador-estrategico",
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
                                text: "El indicador estrategico ha sido eliminado correctamente.",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            }).then(function () {
                                $(".tablaListaIndicadoresEstrategicos").DataTable().ajax.reload();
                            });
                        }
                        else {
                            let mensaje;
                            if (respuesta === "errorEnUso") {
                                mensaje = "Error: el indicador estrategico se encuentra en uso y no puede ser eliminado";
                            } else if (respuesta === "errorNoEncontrado") {
                                mensaje = "Error: No se pudo recuperar el indicador estrategico para su eliminacion";
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
       BUSCA EL INDICADOR ESTRATEGICO SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnEditar", function () {
        let codigo = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/buscar-indicador-estrategico",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $("#codigoIndicadorEstrategico").val(data.CodigoIndicador);
                $("#codigoIndicador").val(data.Codigo);
                $("#metaIndicador").val(data.Meta);
                $("#descripcion").val(data.Descripcion);
                $('#codigoObjEstrategico').val(data.ObjetivoEstrategico).trigger('change');
                $("#tipoResultado").val(data.Resultado);
                $("#tipoIndicador").val(data.TipoIndicador);
                $("#categoriaIndicador").val(data.Categoria);
                $("#tipoUnidad").val(data.Unidad);
                $("#btnMostrarCrear").trigger('click');
            },
            error: function (respuesta) {
                let rta = respuesta['responseText'];
                let mensaje;
                if (rta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el indicador estrategico seleccionado.";
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
        let codigoIndicadorEstrategico = $("#codigoIndicadorEstrategico").val();
        let codigoObjetivoEstrategico = $("#codigoObjEstrategico").val();
        let codigoIndicador = $("#codigoIndicador").val();
        let metaIndicador = $("#metaIndicador").val();
        let descripcion = $("#descripcion").val();
        let tipoResultado = $("#tipoResultado").val();
        let tipoIndicador = $("#tipoIndicador").val();
        let categoriaIndicador = $("#categoriaIndicador").val();
        let tipoUnidad = $("#tipoUnidad").val();
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigoIndicadorEstrategico);
        datos.append("codigoObjetivoEstrategico", codigoObjetivoEstrategico);
        datos.append("codigoIndicador", codigoIndicador);
        datos.append("metaIndicador", metaIndicador);
        datos.append("descripcion", descripcion);
        datos.append("tipoResultado", tipoResultado);
        datos.append("tipoIndicador", tipoIndicador);
        datos.append("categoriaIndicador", categoriaIndicador);
        datos.append("tipoUnidad", tipoUnidad);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/actualizar-indicador-estrategico",
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
                        text: "El indicador estrategico seleccionado se actualizo correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        $(".tablaListaIndicadoresEstrategicos").DataTable().ajax.reload(null, false);
                    });
                }
                else {
                    let mensaje;
                    if (respuesta === "errorExiste") {
                        mensaje = "Los datos ingresados ya corresponden a un indicador estrategico existente";
                    } else if (respuesta === "errorValidacion") {
                        mensaje = "Error: Ocurrio un error al validar los datos enviados";
                    } else if (respuesta === "errorEnvio") {
                        mensaje = "Error: No se enviaron los datos de forma correcta.";
                    } else if (respuesta === "errorCabecera") {
                        mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                    } else if (respuesta === "errorNoEncontrado") {
                        mensaje = "Error: No se encontro el indicador estrategico seleccionado.";
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