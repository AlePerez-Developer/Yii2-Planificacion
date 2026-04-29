$(document).ready(function(){
    let idObjInstitucional = '00000000-0000-0000-0000-000000000000'

    let baseUrl = "index.php?r=Planificacion/obj-institucional/"

    function ReiniciarCampos(){
        $('#formObjInstitucional *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formObjInstitucional').trigger("reset");
        objInstitucional_s2ObjEstrategico.val(null).trigger('change')
        idObjInstitucional = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos del Objetivo Institucional se ${accion}ron correctamente.`;
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

        if (!$("#formObjInstitucional").valid()) return;

        const hasCode =  idObjInstitucional !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const idObjEstrategico = objInstitucional_s2ObjEstrategico.select2('data')[0].id
        const codigo = $("#codigo").val();
        const objetivo = $("#objetivo").val();
        const producto = $("#producto").val();
        const gestion = new Date().getFullYear().toString()
        const datos = new FormData();
        datos.append("idObjInstitucional", idObjInstitucional);
        datos.append("idObjEstrategico", idObjEstrategico);
        datos.append("codigo", codigo);
        datos.append("objetivo", objetivo);
        datos.append("producto", producto);
        datos.append("gestion", gestion);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_objInstitucional
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_objInstitucional.ajax.reload();
    })


    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function(){

        let objectBtn = $(this);
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data()
        let idObjInstitucional = dt_row["IdObjInstitucional"];

        const datos = new FormData();
        datos.append("idObjInstitucional", idObjInstitucional);

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
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data()
        let idObjInstitucional = dt_row["IdObjInstitucional"];

        const datos = new FormData();
        datos.append("idObjInstitucional", idObjInstitucional);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el Objetivo Institucional seleccionado?",
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
                        reloadTable: dt_objInstitucional
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
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data()
        idObjInstitucional = dt_row["IdObjInstitucional"];

        const datos = new FormData();
        datos.append("idObjInstitucional", idObjInstitucional);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
                objInstitucional_s2ObjEstrategico.val(obj["IdObjEstrategico"]).trigger('change')
                $("#codigo").val(obj["Codigo"]);
                $("#objetivo").val(obj["Objetivo"]);
                $("#producto").val(obj["Producto"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', 'tbody #btnProgramar', async function(){
        let objectBtn = $(this);
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        let idObjInstitucional = dt_row["IdObjInstitucional"];

        // redirección
        window.location.href = urlProgramar + '&id=' + idObjInstitucional;
    })


    /**
     * Validacion del form
     */
    $( "#formObjInstitucional" ).validate( {
        rules: {
            objsEstrategicos: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                minlength: 3,
                maxlength: 3,
                require_from_group: [2, ".codigo_group"],
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        codigo: function() {
                            return $('#codigo').val(); // valor actual del campo
                        },
                        idObjEstrategico: function (){
                            let objEstrategico = $('#objsEstrategicos').select2('data')
                            return objEstrategico[0].id
                        },
                        idObjInstitucional: function (){
                            return idObjInstitucional
                        }
                    }
                }
            },
            objetivo:{
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            producto:{
                required: true,
                minlength: 2,
                maxlength: 500,
            },
        },
        messages: {
            objsEstrategicos: {
                required: "Debe seleccionar una opcion de Objetivo Estrategico",
            },
            codigo: {
                required: "Debe ingresar un codigo de objetico institucional (OI)",
                digits: "Solo se permite numeros enteros",
                minlength: "El codigo debe ser de 3 caracteres",
                maxlength: "El codigo debe ser de 3 caracteres",
                require_from_group: "Debe seleccionar un area y una politica antes de validar el codigo de objetivo",
                remote: "El codigo ingresado ya se encuentra en uso con el area y politica seleccionadas"
            },
            objetivo: {
                required: "Debe ingresar la descripcion del objetivo institucional",
                minlength: "El objetivo debe tener por lo menos 2 caracteres",
                maxlength: "El objetivo debe tener maximo 500 caracteres",
            },
            producto: {
                required: "Debe ingresar el resultado esperado del objetivo institucional",
                minlength: "El resultado debe tener por lo menos 2 caracteres",
                maxlength: "El resultad debe tener maximo 500 caracteres",
            },
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
    } );
})
