$(document).ready(function () {
    let codigoPei = 0
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

    $("#btnGuardar").click(async function () {
        const $btn = $(this);
        IniciarSpiner($btn);
        try {
            if ($("#formPei").valid()) {
                const hasCode =  codigoPei !== 0;
                const response = hasCode ? await actualizarPei() : await guardarPei();

                if (response["respuesta"] === RTA_CORRECTO) {
                    MostrarMensaje('success', 'Los datos del nuevo PEI se guardaron correctamente.');
                    $("#tablaListaPeis").DataTable().ajax.reload(() => {
                        $("#btnCancelar").click();
                    });
                } else {
                    MostrarMensaje('error', GenerarMensajeError(response["respuesta"]));
                }
            }
        } catch (err) {
            MostrarMensaje('error', GenerarMensajeError(err));
        } finally {
            DetenerSpiner($btn);
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
    async function  guardarPei()   {
        try {
            let descripcionPei = $("#descripcionPei").val();
            let fechaAprobacion = $("#fechaAprobacion").val();
            let gestionInicio = $("#gestionInicio").val();
            let gestionFin = $("#gestionFin").val();
            let datos = new FormData();
            datos.append("descripcionPei", descripcionPei);
            datos.append("fechaAprobacion", fechaAprobacion);
            datos.append("gestionInicio", gestionInicio);
            datos.append("gestionFin", gestionFin);
            return await $.ajax({
                url: "index.php?r=Planificacion/peis/guardar-pei",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
            });
        } catch (error) {
            throw error;
        }
    }

    /**
     * =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this);
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let codigoPei = dt_row["CodigoPei"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/peis/cambiar-estado-pei",
            method: "POST",
            data : {
                codigoPei: codigoPei,
            },
            dataType: "json",
            success: function (data) {
                if (data["respuesta"] === RTA_CORRECTO) {
                    console.log(data["estado"],ESTADO_VIGENTE)
                    if (data["estado"] === ESTADO_VIGENTE) {
                        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
                        objectBtn.find('.btn_text').html('Vigente')
                    } else {
                        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                        objectBtn.find('.btn_text').html('Caducado')
                    }
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                }
                DetenerSpiner(objectBtn)
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
    async function actualizarPei() {
        try{
            let descripcionPei = $("#descripcionPei").val();
            let fechaAprobacion = $("#fechaAprobacion").val();
            let gestionInicio = $("#gestionInicio").val();
            let gestionFin = $("#gestionFin").val();
            let datos = new FormData();
            datos.append("codigoPei", codigoPei.toString());
            datos.append("descripcionPei", descripcionPei);
            datos.append("gestionInicio", gestionInicio);
            datos.append("fechaAprobacion", fechaAprobacion);
            datos.append("gestionFin", gestionFin);

            return await $.ajax({
                url: "index.php?r=Planificacion/peis/actualizar-pei",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
            });
        } catch (error){
            throw error
        }

    }
})