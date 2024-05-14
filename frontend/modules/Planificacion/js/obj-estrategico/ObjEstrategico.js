$(document).ready(function(){
    function format(d) {
        return (
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="titulosmall">Plan estrategico institucional</div>' +
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
            '</div>'
        );
    }
    let table = $(".tablaListaObjEstrategicos").DataTable({
        initComplete: function () {
            this.api()
                .columns([2])
                .every(function () {
                    let column = this;
                    let select = $('</br><select><option value="">Buscar pei...</option></select>')
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
            url: 'index.php?r=Planificacion/obj-estrategico/listar-objs',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
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
                className: 'dt-small',
                orderable: false,
                data: 'DescripcionPEI',
                render: function (data, type, row){
                    return (type === 'display')
                        ? data + '<br>' + ' (' + row.GestionInicio + ' - ' + row.GestionFin + ')'
                        :data;
                }
            },
            {
                className: 'dt-small dt-center',
                data: 'CodigoObjetivo'
            },
            {
                className: 'dt-small',
                data: 'Objetivo'
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row.CodigoEstado === ESTADO_VIGENTE))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoObjEstrategico + '" estado =  "' + ESTADO_VIGENTE + '" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoObjEstrategico + '" estado =  "' + ESTADO_CADUCO + '" ></button>' ;
                },
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoObjEstrategico',
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
        let codigoObjetivo = $("#codigoObjetivo").val();
        let objetivo = $("#objetivo").val();
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        datos.append("codigoObjetivo", codigoObjetivo);
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
                    MostrarMensaje('success','Los datos del nuevo objetivo estreategico se guardaron correctamente.')
                    $("#tablaListaObjEstrategicos").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoObjEstrategico = objectBtn.attr("codigo");
        let estadoObj = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/cambiar-estado-obj",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    if (estadoObj === ESTADO_VIGENTE) {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.attr('estado', ESTADO_CADUCO);
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.attr('estado', ESTADO_VIGENTE);
                    }
                    DetenerSpiner(objectBtn)
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(respuesta))
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
        ELIMINA DE LA BD UN REGISTRO DE OBJETIVO ESTRATEGICO
    ==========================================================*/
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this)
        let codigoObjEstrategico = objectBtn.attr("codigo");
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
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/obj-estrategico/eliminar-obj",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (respuesta) {
                        if (respuesta === "ok") {
                            MostrarMensaje('success','El objetivo estrategico ha sido borrado correctamente.')
                            $(".tablaListaObjEstrategicos").DataTable().ajax.reload();
                            DetenerSpiner(objectBtn)
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(respuesta))
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
        BUSCA EL OBJETIVO ESTRATEGICO SELECCIONADO EN LA BD
    ========================================================*/
    $(".tablaListaObjEstrategicos tbody").on("click", ".btnEditar", function () {
        let objectBtn = $(this)
        let codigoObjEstrategico = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        IniciarSpiner(objectBtn)
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
                $("#codigoObjetivo").val(data.CodigoObjetivo);
                $("#objetivo").val(data.Objetivo);
                DetenerSpiner(objectBtn)
                $("#btnMostrarCrear").trigger('click');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    /*=============================================
        ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    function actualizarObj () {
        let codigoObjEstrategico = $("#codigoObjEstrategico").val();
        let codigoPei = $("#codigoPei").val();
        let codigoObjetivo = $("#codigoObjetivo").val();
        let objetivo = $("#objetivo").val();
        let datos = new FormData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico);
        datos.append("codigoPei", codigoPei);
        datos.append("codigoObjetivo", codigoObjetivo);
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
                    MostrarMensaje('success','El objetivo estrategico seleccionado se actualizo correctamente.')
                    $(".tablaListaObjEstrategicos").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
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