$(document).ready(function () {
    let idLlavePresupuestaria = '00000000-0000-0000-0000-000000000000'
    let baseUrl = "index.php?r=Planificacion/llave-presupuestaria/"

    function ReiniciarCampos() {
        $('#formLlavePresupuestaria *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formLlavePresupuestaria').trigger("reset");
        llavePresupuestaria_s2Unidad.val(null).trigger('change')
        llavePresupuestaria_s2Programa.val(null).trigger('change')
        llavePresupuestaria_s2Proyecto.val(null).trigger('change')
        llavePresupuestaria_s2Actividad.val(null).trigger('change')

        idLlavePresupuestaria = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos de la llave presupuestaria se ${accion}ron correctamente.`;
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500)
    });

    llavePresupuestaria_s2Programa.change(function () {
        llavePresupuestaria_s2Proyecto.val(null).trigger('change');
        llavePresupuestaria_s2Actividad.val(null).trigger('change');
        if ($(this).val() !== null) {
            llavePresupuestaria_s2Proyecto.prop("disabled", false);
            llavePresupuestaria_s2Actividad.prop("disabled", false);
            populateS2Proyectos($(this).val(), llavePresupuestaria_s2Proyecto, null)
            populateS2Actividades($(this).val(), llavePresupuestaria_s2Actividad, null)
        } else {
            llavePresupuestaria_s2Proyecto.prop("disabled", true);
            llavePresupuestaria_s2Actividad.prop("disabled", true);
        }
    })


    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formLlavePresupuestaria").valid()) return;

        const hasCode = idLlavePresupuestaria !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const idUnidad = llavePresupuestaria_s2Unidad.select2('data')[0].id
        const idPrograma = llavePresupuestaria_s2Programa.select2('data')[0].id
        const idProyecto = llavePresupuestaria_s2Proyecto.select2('data')[0].id
        const idActividad = llavePresupuestaria_s2Actividad.select2('data')[0].id

        const descripcion = $("#descripcion").val();
        const techoPresupuestario = $("#techoPresupuestario").val();
        const fechaInicio = $("#fechaInicio").val();

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);
        datos.append('idUnidad', idUnidad)
        datos.append("idPrograma", idPrograma);
        datos.append("idProyecto", idProyecto);
        datos.append("idActividad", idActividad);
        datos.append("descripcion", descripcion);
        datos.append("techoPresupuestario", techoPresupuestario);
        datos.append("fechaInicio", fechaInicio);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_llavePresupuestaria
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function () {
        dt_llavePresupuestaria.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function () {

        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

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
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la llave presupuestaria seleccionada?",
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
                        reloadTable: dt_llavePresupuestaria
                    });
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });
    });

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnFinalizar', async function () {

        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        try {
            await ajaxPromise({
                url: baseUrl + "finalizar",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
                reloadTable: dt_llavePresupuestaria
            }).then((data) => {
                cambiarEstadoBtn(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    $(document).on('click', 'tbody #btnEditar', async function(){
        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
                llavePresupuestaria_s2Unidad.val(obj["IdUnidad"]).trigger('change')
                llavePresupuestaria_s2Programa.val(obj["IdPrograma"]).trigger('change')
                populateS2Proyectos(obj["IdPrograma"],llavePresupuestaria_s2Proyecto,obj["IdProyecto"])
                populateS2Actividades(obj["IdPrograma"],llavePresupuestaria_s2Actividad,obj["IdActividad"])
                $("#descripcion").val(obj["Descripcion"]);
                $("#techoPresupuestario").val(obj["TechoPresupuestario"]);
                let FechaInicio = obj["FechaInicio"]
                $("#fechaInicio").val(FechaInicio.split(' ')[0]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });


    /**
     * Validacion del form
     */
    $("#formLlavePresupuestaria").validate({
        rules: {
            unidad: {
                required: true,
            },
            programa: {
                required: true,
            },
            proyecto: {
                required: true,
            },
            actividad: {
                required: true,
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            techoPresupuestario: {
                required: true,
            },
            fechaInicio: {
                required: true,
            }
        },
        messages: {
            unidad: {
                required: "Debe seleccionar una unidad",
            },
            programa: {
                required: "Debe seleccionar un programa",
            },
            proyecto: {
                required: "Debe seleccionar un proyecto",
            },
            actividad: {
                required: "Debe seleccionar una actividad",
            },
            descripcion: {
                required: "Debe ingresar la descripcion del indicador estrategico",
                minlength: "La descripcion del indicador debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion del indicador debe tener maximo 500 caracteres",
            },
            techoPresupuestario: {
                required: "Debe ingresar un techo presupuestario",
            },
            fechaInicio: {
                required: "Debe seleccionar una fecha",
            }
        },
        errorElement: "div",

        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            error.insertAfter(element);
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }
    });


})