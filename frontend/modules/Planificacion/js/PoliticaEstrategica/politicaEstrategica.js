$(document).ready(function () {
    let codigoPoliticaEstrategica = 0;

    function reiniciarCampos() {
        $('#formPoliticaEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPoliticaEstrategica').trigger("reset");
        s2AreasEstrategicas.val('').trigger('change')
        codigoPoliticaEstrategica = 0;
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
                const hasCode =  codigoPoliticaEstrategica !== 0;
                hasCode ? actualizarPolitica() : guardarPolitica();
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
    function  guardarPolitica()   {
        let codigoArea = $('#areasEstrategicas').select2('data')
        let codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("codigoAreaEstrategica", codigoArea[0].id);
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
    function actualizarPolitica() {
        let codigoArea = $('#areasEstrategicas').select2('data')
        let Codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("codigoPoliticaEstrategica", codigoPoliticaEstrategica);
        datos.append("codigoAreaEstrategica", codigoArea[0].id);
        datos.append("codigo", Codigo);
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
        let codigoPoliticaEstrategica = dt_row["CodigoPoliticaEstrategica"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/cambiar-estado",
            method: "POST",
            data : {
                codigoPoliticaEstrategica: codigoPoliticaEstrategica,
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
        let codigoPoliticaEstrategica = dt_row["CodigoPoliticaEstrategica"];

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
                        codigoPoliticaEstrategica: codigoPoliticaEstrategica,
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
        codigoPoliticaEstrategica = dt_row["CodigoPoliticaEstrategica"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/politica-estrategica/buscar",
            method: "POST",
            data : {
                codigoPoliticaEstrategica: codigoPoliticaEstrategica,
            },
            dataType: "json",
            success: function (data) {
                let politica = JSON.parse(JSON.stringify(data["data"]));
                s2AreasEstrategicas.val(politica['CodigoAreaEstrategica']).trigger('change');
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
});