$(document).ready(function () {
    let idPei = 0
    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPei').trigger("reset");
        idPei = 0
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        IniciarSpiner(btn);
        btnCancel.prop('disabled', true);
        try {
            if ($("#formPei").valid()) {
                const hasCode =  idPei !== 0;
                hasCode ? actualizarRegistro() : guardarRegistro();
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


    $(document).on('click', '#refresh', function(){
        dt_pei.ajax.reload()
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
     function  guardarRegistro()   {
        let descripcion = $("#descripcion").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("descripcion", descripcion);
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
                // noinspection JSCheckFunctionSignatures
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
     function actualizarRegistro() {
        let descripcion = $("#descripcion").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("idPei", idPei.toString());
        datos.append("descripcion", descripcion);
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
                MostrarMensaje('success', 'Los datos del PEI se actualizaron correctamente.', null);
                // noinspection JSCheckFunctionSignatures
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
        let idPei = dt_row["IdPei"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/peis/cambiar-estado",
            method: "POST",
            data : {
                idPei: idPei,
            },
            dataType: "json",
            success: function (data) {
                cambiarEstadoBtn(objectBtn, data["data"]);
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
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    $(document).on('click', 'tbody #btnEliminar', function(){
        let objectBtn = $(this)
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let idPei = dt_row["IdPei"];

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el PEI seleccionado?",
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
                        idPei: idPei,
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
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    $(document).on('click', 'tbody #btnEditar', function(){
        let objectBtn = $(this)
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        idPei = dt_row["IdPei"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/peis/buscar",
            method: "POST",
            data : {
                idPei: idPei,
            },
            dataType: "json",
            success: function (data) {
                let pei = JSON.parse(JSON.stringify(data["data"]));
                $("#descripcion").val(pei["Descripcion"]);
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

    /**
     * Validacion del form
     */

    $( "#formPei" ).validate( {
        rules: {
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
            fechaAprobacion:{
                required: true,
                date: true
            },
            gestionInicio:{
                required: true,
                digits: true,
                min:2001,
                MenorQue: "#gestionFin"
            },
            gestionFin:{
                required: true,
                digits: true,
                min:2002,
                MayorQue: "#gestionInicio"
            }
        },
        messages: {
            descripcion: {
                required: "Debe ingresar una descripcion para el PEI",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 500 letras"
            },
            fechaAprobacion: {
                required: "Debe ingresar la fecha de aprobacion del PEI",
                date: "Debe ingresar una fecha valida"
            },
            gestionInicio: {
                required: "Debe ingresar la gestion de inicio del PEI",
                digits: "Solo debe ingresar el numero de año",
                min:"Debe ingresar un año valido mayor al 2000",
                MenorQue: "La gestion de incio debe ser menor que la gestion de fin"
            },
            gestionFin: {
                required: "Debe ingresar la gestion final del PEI",
                digits: "Solo debe ingresar el numero de año",
                min:"Debe ingresar un año valido mayor al 2000",
                MayorQue:"La gestion final debe ser mayor que la gestion de inicio"
            }
        },
        errorElement: "div",

        errorPlacement: function ( error, element ) {
            error.addClass( "invalid-feedback" );
            error.insertAfter(element);
        },
        highlight: function ( element  ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    });

    $.validator.addMethod("MayorQue",
        function (value, element, param) {
            let $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        });

    $.validator.addMethod("MenorQue",
        function (value, element, param) {
            let $otherElement = $(param);
            return parseInt(value, 10) < parseInt($otherElement.val(), 10);
        });

})