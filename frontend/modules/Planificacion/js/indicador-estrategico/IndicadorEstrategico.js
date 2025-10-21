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
        initComplete: function () {
            /*this.api()
                .columns([6,7,8,9])
                .every(function () {
                    let column = this;
                    let select = $('</br><select><option value="">Buscar...</option></select>')
                        .appendTo($(column.header()))
                        .on('change', function () {
                            let val = $.fn.dataTable.util.escapeRegex($(this).val());

                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                });*/
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/indicador-estrategico/listar-indicadores-estrategicos',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
        createdRow: function (row, data) {
            if (data.Diff == 0 ) {
                $(row).addClass('completo');
            }
        },
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
                        ? '<button type="button" class="btn btn-outline-success btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado =  "' + ESTADO_VIGENTE + '" >Vigente</button>'
                        : '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "' + ESTADO_CADUCO + '" >Caducado</button>' ;
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
                            '<button type="button" class="btn btn-outline-info btn-sm  btnProgramarG" codigo="' + row.CodigoIndicador + '" ><i class="fa fa-eye"></i></button>' +
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
                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                        '</div>'
                        : data;
                },
            },
        ],
    });

    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    $('.tablaListaIndicadoresEstrategicos tbody').on('click', 'td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);

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
    }).change(function() {
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

    /*$("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        alert(icono.hasClass("closed"))
        icono.toggleClass('opened');
        alert(icono.hasClass("closed"))
        if (icono.hasClass("opened")){
            $("#IngresoDatos").show(500);
            $("#Divtabla").hide(500);
        } else {
            $("#IngresoDatos").hide(500);
            $("#Divtabla").show(500);
        }
    });*/

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
        let metaIndicador = $('#metaIndicador');
        ($(this).val() === '2')? metaIndicador.val('100').prop('readonly',true): metaIndicador.prop('readonly',false)
    })

    $("#tablaListaIndicadoresEstrategicos tbody").on("click", ".btnProgramarG", function () {
        let objectBtn = $(this)
        IniciarSpiner(objectBtn)
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
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos del nuevo indicador estrategico se guardaron correctamente')
                    $("#tablaListaIndicadoresEstrategicos").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    });
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
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
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/cambiar-estado-indicador-estrategico",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    if (estado === ESTADO_VIGENTE) {
                        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                        objectBtn.html('Caducado')
                        objectBtn.attr('estado', ESTADO_CADUCO);
                    } else {
                        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
                        objectBtn.html('Vigente')
                        objectBtn.attr('estado', ESTADO_VIGENTE);
                    }
                    DetenerSpiner(objectBtn)
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    /*=========================================================
        ELIMINA DE LA BD UN REGISTRO DE INDICADOR ESTRATEGICO
    ==========================================================*/
    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
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
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/indicador-estrategico/eliminar-indicador-estrategico",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','El indicador estrategico ha sido eliminado correctamente.')
                            $("#tablaListaIndicadoresEstrategicos").DataTable().ajax.reload();
                            DetenerSpiner(objectBtn)
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                            DetenerSpiner(objectBtn)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                        DetenerSpiner(objectBtn)
                    }
                });
            }
        });
    });

    /*=======================================================
       BUSCA EL INDICADOR ESTRATEGICO SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaIndicadoresEstrategicos tbody").on("click", ".btnEditar", function () {
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigo);
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/buscar-indicador-estrategico",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let ind = JSON.parse(JSON.stringify(data.ind));
                    $("#codigoIndicadorEstrategico").val(ind.CodigoIndicador);
                    $("#codigoIndicador").val(ind.Codigo);
                    $("#metaIndicador").val(ind.Meta);
                    $("#descripcion").val(ind.Descripcion);
                    $('#codigoObjEstrategico').val(ind.ObjetivoEstrategico).trigger('change');
                    $("#tipoResultado").val(ind.Resultado);
                    $("#tipoIndicador").val(ind.TipoIndicador);
                    $("#categoriaIndicador").val(ind.Categoria);
                    $("#tipoUnidad").val(ind.Unidad);
                    DetenerSpiner(objectBtn)
                    $("#btnMostrarCrear").trigger('click');
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
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
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','El indicador estrategico seleccionado se actualizo correctamente.')
                    $("#tablaListaIndicadoresEstrategicos").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    });
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
    }
})