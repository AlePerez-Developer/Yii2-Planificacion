$(document).ready(function () {
    let codigoPei = 0
    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPei').trigger("reset");
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        IniciarSpiner(btn);
        btnCancel.prop('disabled', true);
        try {
            if ($("#formPei").valid()) {
                const hasCode =  codigoPei !== 0;
                hasCode ? await actualizarPei() : await guardarPei();
            }
        } catch (err) {
            MostrarMensaje('error', GenerarMensajeError(err));
        } finally {
            DetenerSpiner(btn);
            btnCancel.prop('disabled', false);
        }
    });

    $('#gestionInicio').on('change keyup', function () {
        $('#gestionFin').valid();
    });

    $('#gestionFin').on('change keyup', function () {
        $('#gestionInicio').valid();
    });

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO DE PEI
    =============================================*/
    async function  guardarPei()   {
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
            url: "index.php?r=Planificacion/peis/guardar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos del nuevo PEI se guardaron correctamente.', null);
                dt_pei.ajax.reload(() => {
                    $("#btnCancelar").click();
                });
            },
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
            }
        });
    }

    /*=============================================
    ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    async function actualizarPei() {
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
        $.ajax({
            url: "index.php?r=Planificacion/peis/actualizar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos del nuevo PEI se actualizaron correctamente.', null);
                dt_pei.ajax.reload(() => {
                    $("#btnCancelar").click();
                });
            },
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
            }
        });
    }

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', function(){
        let objectBtn = $(this);
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let codigoPei = dt_row["CodigoPei"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/peis/cambiar-estado",
            method: "POST",
            data : {
                codigoPei: codigoPei,
            },
            dataType: "json",
            success: function (data) {
                if (data["data"] === ESTADO_VIGENTE) {
                    objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
                    objectBtn.find('.btn_text').html('Vigente')
                } else {
                    objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                    objectBtn.find('.btn_text').html('Caducado')
                }
                DetenerSpiner(objectBtn)
            },
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
                DetenerSpiner(objectBtn)
            }
        });
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO DE PEI
    =============================================*/
    $(document).on('click', 'tbody #btnEliminar', function(){
        let objectBtn = $(this)
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let codigoPei = dt_row["CodigoPei"];

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el pei seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/peis/eliminar",
                    method: "POST",
                    data : {
                        codigoPei: codigoPei,
                    },
                    dataType: "json",
                    success: function () {
                        MostrarMensaje('success','El PEI ha sido eliminado correctamente.','')
                        dt_pei.ajax.reload();
                        DetenerSpiner(objectBtn)
                    },
                    error: function (xhr) {
                        const data = JSON.parse(xhr.responseText)
                        MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
                        DetenerSpiner(objectBtn)
                    }
                });
            }
        });
    });

    /*=============================================
    BUSCA EL PEI SELECCIONADO EN LA BD
    =============================================*/
    $(document).on('click', 'tbody #btnEditar', function(){
        let objectBtn = $(this)
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        codigoPei = dt_row["CodigoPei"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/peis/buscar",
            method: "POST",
            data : {
                codigoPei: codigoPei,
            },
            dataType: "json",
            success: function (data) {
                let pei = JSON.parse(JSON.stringify(data["data"]));
                $("#codigoPei").val(pei["CodigoPei"]);
                $("#descripcionPei").val(pei["DescripcionPei"]);
                $("#fechaAprobacion").val(pei["FechaAprobacion"]);
                $("#gestionInicio").val(pei["GestionInicio"]);
                $("#gestionFin").val(pei["GestionFin"]);
                DetenerSpiner(objectBtn)
                $("#btnMostrarCrear").trigger('click');
            },
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
                DetenerSpiner(objectBtn)
            }
        });
    });
})