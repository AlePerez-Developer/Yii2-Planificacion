$(document).ready(function(){
    let codigoObjEstrategico = 0
    let s2Areas = $('#areasEstrategicas')
    let s2Politicas = $('#politicasEstrategicas')

    function ReiniciarCampos(){
        $('#formObjEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#areasEstrategicas').val('').trigger('change');
        $('#formObjEstrategico').trigger("reset");
        codigoObjEstrategico = 0
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    s2Areas.change(function () {
        s2Politicas.val(null).trigger('change');
        if ($(this).val() !== null) {
            s2Politicas.prop("disabled", false);
            populateS2Politicas($(this).val())
        } else {
            s2Politicas.prop("disabled", true);
        }
    })

    $("#btnGuardar").click(function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        IniciarSpiner(btn);
        btnCancel.prop('disabled', true);
        try {
            if ($("#formObjEstrategico").valid()) {
                const hasCode =  codigoObjEstrategico !== 0;
                hasCode ? actualizarRegistro() : guardarRegistro();
            }
        } catch (err) {
            MostrarMensaje('error', GenerarMensajeError(err));
        } finally {
            DetenerSpiner(btn);
            btnCancel.prop('disabled', false);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_obj.ajax.reload();
    })

    function getDomData()
    {
        let areaEstrategica = s2Areas.select2('data')[0].id
        let politicaEstrategica = s2Politicas.select2('data')[0].id
        let codigoObjetivo = $("#codigoObjetivo").val();
        let objetivo = $("#objetivo").val();
        let producto = $("#producto").val();
        let indicadorDescripcion = $("#descripcion").val();
        let indicadorFormula = $("#formula").val();
        let datos = new FormData();
        datos.append("areaEstrategica", areaEstrategica);
        datos.append("politicaEstrategica", politicaEstrategica);
        datos.append("codigoObjetivo", codigoObjetivo);
        datos.append("objetivo", objetivo);
        datos.append("producto", producto);
        datos.append("indicadorDescripcion", indicadorDescripcion);
        datos.append("indicadorFormula", indicadorFormula);
        return datos
    }

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function  guardarRegistro()   {
        let datos = getDomData();
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/guardar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos del nuevo Objetivo Estrategico se guardaron correctamente.', null);
                dt_obj.ajax.reload(() => {
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
        let datos = getDomData();
        datos.append("codigoObjEstrategico", codigoObjEstrategico.toString());
        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/actualizar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function () {
                MostrarMensaje('success', 'Los datos del Obejtivo Estrategico se actualizaron correctamente.', null);
                dt_obj.ajax.reload(() => {
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
        const dt_row = dt_obj.row(objectBtn.closest('tr')).data()
        let codigoObjEstrategico = dt_row["CodigoObjEstrategico"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/cambiar-estado",
            method: "POST",
            data : {
                codigoObjEstrategico: codigoObjEstrategico,
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
        const dt_row = dt_obj.row(objectBtn.closest('tr')).data()
        let codigoObjEstrategico = dt_row["CodigoObjEstrategico"];

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el obejtivo estrategico seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/obj-estrategico/eliminar",
                    method: "POST",
                    data : {
                        codigoObjEstrategico: codigoObjEstrategico,
                    },
                    dataType: "json",
                    success: function () {
                        MostrarMensaje('success','El Objetivo Estrategico ha sido eliminado correctamente.','')
                        dt_obj.ajax.reload();
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
        let objectBtn = $(this);
        const dt_row = dt_obj.row(objectBtn.closest('tr')).data()
        codigoObjEstrategico = dt_row["CodigoObjEstrategico"];
        IniciarSpiner(objectBtn)

        $.ajax({
            url: "index.php?r=Planificacion/obj-estrategico/buscar",
            method: "POST",
            data : {
                codigoObjEstrategico: codigoObjEstrategico,
            },
            dataType: "json",
            success: function (data) {
                let obj = JSON.parse(JSON.stringify(data["data"]));
                $("#areasEstrategicas").val(obj["AreaEstrategica"]).trigger('change');
                $("#politicasEstrategicas").val(obj["PoliticaEstrategica"]).trigger('change');
                $("#codigoObjetivo").val(obj["CodigoObjetivo"]);
                $("#objetivo").val(obj["Objetivo"]);
                $("#producto").val(obj["Producto"]);
                $("#descripcion").val(obj["Indicador_Descripcion"]);
                $("#formula").val(obj["Indicador_Formula"]);
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