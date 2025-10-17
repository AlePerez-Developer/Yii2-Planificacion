$(document).ready(function () {
    let idPoliticaEstrategica = '00000000-0000-0000-0000-000000000000';

    function reiniciarCampos() {
        $('#formPoliticaEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPoliticaEstrategica').trigger("reset");
        politicas_s2AreasEstrategicas.val('').trigger('change')
        idPoliticaEstrategica = '00000000-0000-0000-0000-000000000000';
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
            if ($("#formPoliticaEstrategica").valid()) {
                const hasCode =  idPoliticaEstrategica !== '00000000-0000-0000-0000-000000000000';
                hasCode ? actualizar() : guardar();
            }
        } catch (err) {
            MostrarMensaje('error', GenerarMensajeError(err));
        } finally {
            DetenerSpiner(btn);
            btnCancel.prop('disabled', false);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_politica.ajax.reload();
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function  guardar()   {
        let idAreaEstrategica = $('#areasEstrategicas').select2('data')
        let codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("idAreaEstrategica", idAreaEstrategica[0].id);
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/guardar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos de la nueva Politica Estrategica se guardaron correctamente.', null);
                // noinspection JSCheckFunctionSignatures
                dt_politica.ajax.reload(() => {
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
    ACTUALIZA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    function actualizar() {
        let idAreaEstrategica = $('#areasEstrategicas').select2('data')
        let codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);
        datos.append("idAreaEstrategica", idAreaEstrategica[0].id);
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/actualizar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos de la Politica Estrategica se actualizaron correctamente.', null);
                // noinspection JSCheckFunctionSignatures
                dt_politica.ajax.reload(() => {
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
          CAMBIA EL ESTADO DEL REGISTRO
    ===============================================*/
    $(document).on('click', 'tbody #btnEstado', function(){
        let objectBtn = $(this);
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        let idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/cambiar-estado",
            method: "POST",
            data : {
                idPoliticaEstrategica: idPoliticaEstrategica,
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
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        let idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la politica estrategica seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/politica-estrategica/eliminar",
                    method: "POST",
                    data : {
                        idPoliticaEstrategica: idPoliticaEstrategica,
                    },
                    dataType: "json",
                    success: function () {
                        MostrarMensaje('success','La politic estrategica ha sido eliminado correctamente.','')
                        dt_politica.ajax.reload();
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
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/buscar",
            method: "POST",
            data : {
                idPoliticaEstrategica: idPoliticaEstrategica,
            },
            dataType: "json",
            success: function (data) {
                let politica = JSON.parse(JSON.stringify(data["data"]));
                politicas_s2AreasEstrategicas.val(politica['IdAreaEstrategica']).trigger('change');
                $("#codigo").val(politica["Codigo"]);
                $("#descripcion").val(politica["Descripcion"]);
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
    $( "#formPoliticaEstrategica" ).validate( {
        rules: {
            areasEstrategicas: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                range: [1, 9],
                verificarCodigoPolitica: {
                    depends: function() {
                        return $('#areasEstrategicas').valid();
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
        },
        messages: {
            areasEstrategicas: {
                required: "Debe seleccionar una area estrategica",
            },
            codigo: {
                required: "Debe ingresar un codigo de politica estrategica",
                digits: "El codigo solo debe ser numerico",
                range: "El codigo debe estar comprendido entre 1 y 9",
                verificarCodigoPolitica: "El codigo ingresado ya se encuentra en uso"
            },
            descripcion: {
                required: "Debe ingresar una descripcion del area estrategica",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 500 letras"
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

    $.validator.addMethod("verificarCodigoPolitica",
        function(value) {
            let result = false;
            let idAreaEstrategica = politicas_s2AreasEstrategicas.select2('data')
            let datos = new FormData();
            datos.append("codigo", value);
            datos.append("idPoliticaEstrategica", idPoliticaEstrategica);
            datos.append("idAreaEstrategica", idAreaEstrategica[0].id);
            $.ajax({
                url: "index.php?r=Planificacion/politica-estrategica/verificar-codigo",
                method: "POST",
                async: false,
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    result = !!(data);
                }
            });
            return result;
        }
    );

});