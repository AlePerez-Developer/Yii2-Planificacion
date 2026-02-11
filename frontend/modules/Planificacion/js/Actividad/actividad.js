$(document).ready(function () {
    let idActividad = '00000000-0000-0000-0000-000000000000'
    let baseUrl = "index.php?r=Planificacion/actividad/"

    function ReiniciarCampos() {
        $('#formActividad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formActividad').trigger("reset");
        actividad_s2Programa.val(null).trigger('change');
        idActividad = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos de la actividad se ${accion}ron correctamente.`;
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formActividad").valid()) return;

        const hasCode = idActividad !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const idPrograma = actividad_s2Programa.select2('data')[0].id
        const codigo = $("#codigo").val();
        const descripcion = $("#descripcion").val();

        const datos = new FormData();
        datos.append("idActividad", idActividad);
        datos.append("idPrograma", idPrograma);
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_actividad
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function () {
        dt_actividad.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function () {
        let objectBtn = $(this);
        const dt_row = dt_actividad.row(objectBtn.closest('tr')).data()
        let idActividad = dt_row["IdActividad"];

        const datos = new FormData();
        datos.append("idActividad", idActividad);

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
    $(document).on('click', 'tbody #btnEliminar', function () {
        let objectBtn = $(this)
        const dt_row = dt_actividad.row(objectBtn.closest('tr')).data()
        let idActividad = dt_row["IdActividad"];

        const datos = new FormData();
        datos.append("idActividad", idActividad);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la actividad seleccionado?",
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
                        reloadTable: dt_actividad
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
    $(document).on('click', 'tbody #btnEditar', async function () {
        let objectBtn = $(this);
        const dt_row = dt_actividad.row(objectBtn.closest('tr')).data()
        idActividad = dt_row["IdActividad"];

        const datos = new FormData();
        datos.append("idActividad", idActividad);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
                actividad_s2Programa.val(obj["IdPrograma"]).trigger('change')
                $("#codigo").val(obj["Codigo"]);
                $("#descripcion").val(obj["Descripcion"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
     * Validacion del form
     */
    $("#formActividad").validate({
        rules: {
            programas: {
                required: true,
            },
            codigo: {
                required: true,
                minlength: 3,
                maxlength: 3,
                require_from_group: [2, ".codigo_group"],
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        codigo: function () {
                            return $('#codigo').val(); // valor actual del campo
                        },
                        idPrograma: function () {
                            let programa = $('#programas').select2('data')
                            return programa[0].id
                        },
                        idActividad: function () {
                            return idActividad
                        }
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
        },
        messages: {
            programas: {
                required: "Debe escoger un programa",
            },
            codigo: {
                required: "Debe ingresar un codigo de programa",
                minlength: "El codigo debe debe ser de 3 digitos",
                maxlength: "El codigo debe debe ser de 3 digitos",
                require_from_group: "Debe seleccionar un programa",
                remote: "El codigo ingresado ya se encuentra en uso"
            },
            descripcion: {
                required: "Debe ingresar la descripcion del programa",
                minlength: "La descripcion del programa debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion del programa  debe tener maximo 500 caracteres",
            },
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
});
