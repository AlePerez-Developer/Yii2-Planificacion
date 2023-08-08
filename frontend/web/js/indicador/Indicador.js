$(document).ready(function(){
    let formReset = false

    function format(d) {
        return (
            '    <div class="container">' +
            '        <div class="row">' +
            '            <div class="col-12" style="text-align: center; font-weight: bold"> Datos Objetivos </div>' +
            '        </div>' +
            '        <div class="row justify-content-between">' +
            '            <div class="col-3 titulosmall"> Objetivo Institucional </div>' +
            '            <div class="col-9 titulosmall"> Objetivo Especifico </div>' +
            '        </div>' +
            '        <div class="row">' +
            '            <div class="col-2 subsmall"> Codigo: </div>' +
            '            <div class="col-4 little">' + d.CodigoInstitucional + '</div>' +
            '            <div class="col-2 subsmall"> Codigo: </div>' +
            '            <div class="col-4 little">' + d.CodigoEspecifico + '</div>' +
            '        </div>' +
            '        <div class="row">' +
            '            <div class="col-2 subsmall"> Descripcion: </div>' +
            '            <div class="col-4 little">' + d.ObjetivoInstitucional + '</div>' +
            '            <div class="col-2 subsmall"> Descripcion: </div>' +
            '            <div class="col-4 little">' + d.ObjetivoEspecifico + '</div>' +
            '        </div>' +
            '        <div class="row">' +
            '            <div class="col-12" style="text-align: center; font-weight: bold"> Programa / Actividad </div>' +
            '        </div>' +
            '        <div class="row justify-content-between"">' +
            '            <div class="col-3 titulosmall"> Programa </div>' +
            '            <div class="col-9 titulosmall"> Actividad </div>' +
            '        </div>' +
            '        <div class="row">' +
            '            <div class="col-2 subsmall"> Codigo: </div>' +
            '            <div class="col-4 little">' + d.CodigoPrograma + '</div>' +
            '            <div class="col-2 subsmall"> Codigo: </div>' +
            '            <div class="col-4 little">' + d.CodigoActividad + '</div>' +
            '        </div>' +
            '        <div class="row">' +
            '            <div class="col-2 subsmall"> Descripcion: </div>' +
            '            <div class="col-4 little">' + d.DescripcionPrograma + '</div>' +
            '            <div class="col-2 subsmall"> Descripcion: </div>' +
            '            <div class="col-4 little">' + d.DescripcionActividad + '</div>' +
            '        </div>' +
            '    </div>'
        )
    };

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
                .columns([5,6,7,8,9])
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
            { className: "dt-center", targets: [0,1,2,3,5,6,7,8,9,10,11] },
            { orderable: false, targets: [0,1,5,6,7,8,9,10,11] },
            { searchable: false, targets: [0,1,10,11] }
        ],
        columns: [
            { data: 'CodigoUsuario' },
            {
                className: 'dt-control',
                data: null,
                defaultContent: '',
            },
            { data: 'CodigoPei'},
            { data: 'CodigoPoa'},
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
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "V" >V</button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoIndicador + '" estado = "C" >C</button>' ;
                },
            },
            {
                data: 'CodigoIndicador',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-info btn-sm  btnUnidad" codigo="' + data + '" ><i class="fa fa-eye"></i></button>' +
                        '<button type="button" class="btn btn-warning btn-sm  btnEditar" codigo="' + data + '" ><i class="fa fa-pen"></i></button>' +
                        '<button type="button" class="btn btn-danger btn-sm  btnEliminar" codigo="' + data + '" ><i class="fa fa-times"></i></button>' +
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

    $('.tablaListaIndicadores tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    $('.objinstitucional').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo institucional",
        allowClear: true
    }).change(function(e) {
        if (!formReset){
            $("#formIndicadores").validate().element('#CodigoObjInstitucional');
        }
    })

    $('.objespecifico').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo especifico",
        allowClear: true
    }).change(function(e) {
        if (!formReset){
            $("#formIndicadores").validate().element('#CodigoObjEspecifico');
        }
    })

    $('.programa').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un programa",
        allowClear: true
    }).change(function(e) {
        if (!formReset){
            $("#formIndicadores").validate().element('#CodigoPrograma');
        }
    })

    $('.actividad').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una actividad",
        allowClear: true
    }).change(function(e) {
        if (!formReset){
            $("#formIndicadores").validate().element('#CodigoActividad');
        }
    })

    $("#CodigoObjInstitucional").change(function (val, obj){
        let codigo = $("#CodigoObjInstitucional").val();
        if (codigo !== ''){
            let datos = new FormData();
            datos.append("codigo", codigo);
            $.ajax({
                url: "index.php?r=Planificacion/indicador/listar-objsespecificos",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    var data = jQuery.parseJSON(respuesta);
                    var sel = $("#CodigoObjEspecifico");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoObjEspecifico'] + '">' + '(' + value['CodigoCOGE'] + ') - ' + value['Objetivo'] + '</option>');
                    });
                    $('#CodigoObjEspecifico').prop('disabled', false);
                },
            }).done(function (){
                if (obj !== undefined)
                    $("#CodigoObjEspecifico").val(obj).trigger('change');
            })
        } else {
            $("#CodigoObjEspecifico").val(null).trigger('change');
            $('#CodigoObjEspecifico').prop('disabled', true);
        }
    });

    $("#CodigoPrograma").change(function (val, act){
        let codigo = $("#CodigoPrograma").val();
        if (codigo !== ''){
            let datos = new FormData();
            datos.append("codigo", codigo);
            $.ajax({
                url: "index.php?r=Planificacion/indicador/listar-actividades",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    var data = jQuery.parseJSON(respuesta);
                    var sel = $("#CodigoActividad");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoActividad'] + '">' + '(' + value['Codigo'] + ') - ' + value['Descripcion'] + '</option>');
                    });
                    $('#CodigoActividad').prop('disabled', false);
                },
            }).done(function (){
                if (act !== undefined)
                    $("#CodigoActividad").val(act).trigger('change');
            })
        } else {
            $("#CodigoActividad").val(null).trigger('change');
            $('#CodigoActividad').prop('disabled', true);
        }
    });

    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formIndicadores *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('.invalid-feedback').each(function (){
            $(this).removeAttr('style')
        })
        $("#CodigoObjInstitucional").val(null).trigger('change');
        $("#CodigoPrograma").val(null).trigger('change');
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
        formReset = true;
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
        formReset = false;
    });


    $(".btnGuardar").click(function () {
        if ($("#formIndicadores").valid()){
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
        let objEspecifico = $("#CodigoObjEspecifico").val();
        let actividad = $("#CodigoActividad").val();
        let codigoPei = $("#CodigoPei").val();
        let codigoPoa = $("#CodigoPoa").val();
        let descripcion = $("#Descripcion").val();
        let articulacion = $("#Articulacion").val();
        let resultado = $("#Resultado").val();
        let tipoindicador = $("#Tipo").val();
        let categoria = $("#Categoria").val();
        let unidad = $("#Unidad").val();
        let datos = new FormData();
        datos.append("objEspecifico", objEspecifico);
        datos.append("actividad", actividad);
        datos.append("codigoPei", codigoPei);
        datos.append("codigoPoa", codigoPoa);
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
                        objectBtn.html('C');
                        objectBtn.attr('estado', 'C');
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.html('V');
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
        REGISTRO DE UNIDADES
    ========================================================*/
    $(".tablaListaIndicadores tbody").on("click", ".btnUnidad", function () {
        let codigo = $(this).attr("codigo");
        $('#indicadoresUnidades').modal('show')
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
                $("#CodigoPei").val(data.CodigoPei);
                $("#CodigoPoa").val(data.CodigoPoa);
                $(".objinstitucional").val(data.CodigoObjInstitucional).trigger('change',data.ObjetivoEspecifico)
                $(".programa").val(data.CodigoPrograma).trigger('change',data.Actividad)
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
        let objEspecifico = $("#CodigoObjEspecifico").val();
        let actividad = $("#CodigoActividad").val();
        let codigoPei = $("#CodigoPei").val();
        let codigoPoa = $("#CodigoPoa").val();
        let descripcion = $("#Descripcion").val();
        let articulacion = $("#Articulacion").val();
        let resultado = $("#Resultado").val();
        let tipoindicador = $("#Tipo").val();
        let categoria = $("#Categoria").val();
        let unidad = $("#Unidad").val();
        let datos = new FormData();
        datos.append("codigoindicador", codigoindicador);
        datos.append("objEspecifico", objEspecifico);
        datos.append("actividad", actividad);
        datos.append("codigoPei", codigoPei);
        datos.append("codigoPoa", codigoPoa);
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