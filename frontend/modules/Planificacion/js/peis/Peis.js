$(document).ready(function () {
    let table = $("#tablaListaPeis").DataTable({
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/peis/listar-peis',
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
                className: 'dt-small',
                data: 'DescripcionPei'
            },
            {
                className: 'dt-small dt-center',
                data: 'FechaAprobacion'
            },
            {
                className: 'dt-small dt-center',
                data: 'GestionInicio'
            },
            {
                className: 'dt-small dt-center',
                data: 'GestionFin'
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row.CodigoEstado === ESTADO_VIGENTE))
                        ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado" codigo="' + row.CodigoPei + '" estado =  "' + ESTADO_VIGENTE + '" >Vigente</button>'
                        : '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" codigo="' + row.CodigoPei + '" estado =  "' + ESTADO_CADUCO + '" >Caducado</button>' ;
                },
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoPei',
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
        table.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();

    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#codigoPei').val('');
        $('#formPei').trigger("reset");
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
        if ($("#formPei").valid()) {
            if ($("#codigoPei").val() === '') {
                guardarPei();
            } else {
                actualizarPei();
            }
        }
    });

    $("#gestionInicio").on( "keypress",function(){
        $("#formPei").validate().element('#gestionFin');
    })

    $("#gestionFin").on( "keypress",function(){
        $("#formPei").validate().element('#gestionInicio');
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO DE PEI
    =============================================*/
    function guardarPei() {
        let descripcionPei = $("#descripcionPei").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("descripcionPei", descripcionPei);
        datos.append("fechaAprobacion", fechaAprobacion);
        datos.append("gestionInicio", gestionInicio);
        datos.append("gestionFin", gestionFin);
        $.ajax({
            url: "index.php?r=Planificacion/peis/guardar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos del nuevo PEI se guardaron correctamente.')
                    $("#tablaListaPeis").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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
    $("#tablaListaPeis tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoPei = objectBtn.attr("codigo");
        let estadoPei = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/peis/cambiar-estado-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    if (estadoPei === ESTADO_VIGENTE) {
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

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO DE PEI
    =============================================*/
    $("#tablaListaPeis tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this)
        let codigoPei = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de borrar el pei seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/peis/eliminar-pei",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','El PEI ha sido borrado correctamente.')
                            $("#tablaListaPeis").DataTable().ajax.reload();
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

    /*=============================================
    BUSCA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    $("#tablaListaPeis tbody").on("click", ".btnEditar", function () {
        let objectBtn = $(this)
        let codigoPei = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/peis/buscar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let pei = JSON.parse(JSON.stringify(data.pei));
                    $("#codigoPei").val(pei.CodigoPei);
                    $("#descripcionPei").val(pei.DescripcionPei);
                    $("#fechaAprobacion").val(pei.FechaAprobacion);
                    $("#gestionInicio").val(pei.GestionInicio);
                    $("#gestionFin").val(pei.GestionFin);
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
    ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    function actualizarPei() {
        let codigoPei = $("#codigoPei").val();
        let descripcionPei = $("#descripcionPei").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        datos.append("descripcionPei", descripcionPei);
        datos.append("gestionInicio", gestionInicio);
        datos.append("fechaAprobacion", fechaAprobacion);
        datos.append("gestionFin", gestionFin);
        $.ajax({
            url: "index.php?r=Planificacion/peis/actualizar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','El PEI se actualizó correctamente.')
                    $("#tablaListaPeis").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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