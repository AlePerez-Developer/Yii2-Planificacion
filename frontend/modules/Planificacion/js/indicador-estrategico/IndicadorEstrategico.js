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
                .columns([6,7,8,9])
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
                        .each(function (d) {
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
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
        createdRow: function (row, data, dataIndex) {
            if (data.Diff == 0 ) {
                $(row).addClass('completo');
            }
        },
        fixedColumns: true,
        autoWidth: false,
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
                data: 'Codigo',
                width: 60
            },
            {
                className: 'dt-small dt-center',
                data: 'Meta',
                width: 60
            },
            {
                className: 'dt-small dt-center',
                data: 'Programado',
                width: 60
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
                    return ( (type === 'display') && (row.CodigoEstado === ESTADO_VIGENTE))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado =  "' + ESTADO_VIGENTE + '" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "' + ESTADO_CADUCO + '" ></button>' ;
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
                        '<button type="button" class="btn btn-info btn-sm  btnProgramar" codigo="' + row.CodigoIndicador + '" >' +
                            '<span class="spinner-grow spinner-grow-sm" style="display: none" aria-hidden="true"></span>' +
                            '<i class="fa fa-eye"></i>' +
                        '</button>' +
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
        $('#metaIndicador').prop('readonly',false);
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

    $('#tipoUnidad').change(function (){
        ($(this).val() === '2')? $('#metaIndicador').val('100').prop('readonly',true): $('#metaIndicador').prop('readonly',false)
    })

    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnProgramar", function () {
        $(this).find('span').removeAttr("style")
        $(this).find('i').css("display", "none")
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
                    MostrarMensaje('error',GenerarMensajeError(respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
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
                    if (estado === ESTADO_VIGENTE) {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.attr('estado', ESTADO_CADUCO);
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.attr('estado', ESTADO_VIGENTE);
                    }
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
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
                            MostrarMensaje('error',GenerarMensajeError(respuesta))
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
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
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
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
                    MostrarMensaje('error',GenerarMensajeError(respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
    }
})