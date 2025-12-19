$(document).ready(function () {
    let idPrograma = '00000000-0000-0000-0000-000000000000'
    let baseUrl = "index.php?r=Planificacion/programa/"

    function ReiniciarCampos(){
        $('#formPrograma *').filter(':input').each(function () {
          $(this).removeClass('is-invalid is-valid');
        });
        $('#formPrograma').trigger("reset");
        idPrograma = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos del programa se ${accion}ron correctamente.`;
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

        if (!$("#formPrograma").valid()) return;

        const hasCode =  idPrograma !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const codigo = $("#codigo").val();
        const descripcion = $("#descripcion").val();

        const datos = new FormData();
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
                reloadTable: dt_programa
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_programa.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_programa.row(objectBtn.closest('tr')).data()
        let idPrograma = dt_row["IdPrograma"];

        const datos = new FormData();
        datos.append("idPrograma", idPrograma);

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
        const dt_row = dt_programa.row(objectBtn.closest('tr')).data()
        let idPrograma = dt_row["IdPrograma"];

        const datos = new FormData();
        datos.append("idPrograma", idPrograma);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el programa seleccionado?",
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
                        reloadTable: dt_programa
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
        const dt_row = dt_programa.row(objectBtn.closest('tr')).data()
        idPrograma = dt_row["IdPrograma"];

        const datos = new FormData();
        datos.append("idPrograma", idPrograma);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
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
    $( "#formPrograma" ).validate( {
        rules: {
            codigo: {
                required: true,
                minlength: 3,
                maxlength: 3,
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        codigo: function() {
                            return $('#codigo').val(); // valor actual del campo
                        },
                        idPrograma: function (){
                            return idPrograma
                        }
                    }
                }
            },
            descripcion:{
                required: true,
                minlength: 2,
                maxlength: 500,
            },
        },
        messages: {
            codigo: {
                required: "Debe ingresar un codigo de programa",
                minlength: "El codigo debe debe ser de 3 digitos",
                maxlength: "El codigo debe debe ser de 3 digitos",
                remote: "El codigo ingresado ya se encuentra en uso"
            },
            descripcion: {
                required: "Debe ingresar la descripcion del programa",
                minlength: "La descripcion del programa debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion del programa  debe tener maximo 500 caracteres",
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

});