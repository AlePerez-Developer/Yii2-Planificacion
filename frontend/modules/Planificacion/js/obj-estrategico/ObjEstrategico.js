$(document).ready(function(){
    let idObjEstrategico = '00000000-0000-0000-0000-000000000000'
    let baseUrl = "index.php?r=Planificacion/obj-estrategico/"

    function ReiniciarCampos(){
        $('#formObjEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formObjEstrategico').trigger("reset");
        objEstrategico_s2PoliticaEstrategica.val(null).trigger('change')
        objEstrategico_s2AreaEstrategica.val(null).trigger('change')
        idObjEstrategico = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos del Objetivo Estratégico se ${accion}ron correctamente.`;
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    objEstrategico_s2AreaEstrategica.change(function () {
        objEstrategico_s2PoliticaEstrategica.val(null).trigger('change');
        if ($(this).val() !== null) {
            objEstrategico_s2PoliticaEstrategica.prop("disabled", false);
            populateS2Politicas($(this).val(),objEstrategico_s2PoliticaEstrategica,null)
        } else {
            objEstrategico_s2PoliticaEstrategica.prop("disabled", true);
        }
    })

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formObjEstrategico").valid()) return;

        const hasCode =  idObjEstrategico !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const idAreaEstrategica = objEstrategico_s2AreaEstrategica.select2('data')[0].id
        const idPoliticaEstrategica = objEstrategico_s2PoliticaEstrategica.select2('data')[0].id
        const codigo = $("#codigo").val();
        const objetivo = $("#objetivo").val();
        const producto = $("#producto").val();
        const descripcion = $("#descripcion").val();
        const formula = $("#formula").val();
        const datos = new FormData();
        datos.append("idObjEstrategico", idObjEstrategico);
        datos.append("idAreaEstrategica", idAreaEstrategica);
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);
        datos.append("codigo", codigo);
        datos.append("objetivo", objetivo);
        datos.append("producto", producto);
        datos.append("descripcion", descripcion);
        datos.append("formula", formula);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_objEstrategico
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_objEstrategico.ajax.reload();
    })


    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function(){

        let objectBtn = $(this);
        const dt_row = dt_objEstrategico.row(objectBtn.closest('tr')).data()
        let idObjEstrategico = dt_row["IdObjEstrategico"];

        const datos = new FormData();
        datos.append("idObjEstrategico", idObjEstrategico);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstadoBtn(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    $(document).on('click', 'tbody #btnEliminar', function(){
        let objectBtn = $(this)
        const dt_row = dt_objEstrategico.row(objectBtn.closest('tr')).data()
        let idObjEstrategico = dt_row["IdObjEstrategico"];

        const datos = new FormData();
        datos.append("idObjEstrategico", idObjEstrategico);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el Objetivo Estrategico seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + "eliminar",
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: dt_objEstrategico
                    });
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });
    });

    /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    $(document).on('click', 'tbody #btnEditar', async function(){
        let objectBtn = $(this);
        const dt_row = dt_objEstrategico.row(objectBtn.closest('tr')).data()
        idObjEstrategico = dt_row["IdObjEstrategico"];

        const datos = new FormData();
        datos.append("idObjEstrategico", idObjEstrategico);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
                objEstrategico_s2AreaEstrategica.val(obj["IdAreaEstrategica"]).trigger('change')
                console.log(obj["IdPoliticaEstrategica"])
                populateS2Politicas(obj["IdAreaEstrategica"],objEstrategico_s2PoliticaEstrategica,obj["IdPoliticaEstrategica"])
                $("#codigo").val(obj["Codigo"]);
                $("#objetivo").val(obj["Objetivo"]);
                $("#producto").val(obj["Producto"]);
                $("#descripcion").val(obj["Indicador_Descripcion"]);
                $("#formula").val(obj["Indicador_Formula"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });
})
