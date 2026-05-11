$(document).ready(function () {
    let idDa = '00000000-0000-0000-0000-000000000000';
    let baseUrl = "index.php?r=Planificacion/da/"
    let dtEvents = $('#tablaListaDas')

    function reiniciarCampos() {
        $('#formDa *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formDa').trigger("reset");
        idDa = '00000000-0000-0000-0000-000000000000';
    }

    function mensajeAccion(accion) {
        return `Los datos de la Da Estratégica se ${accion}ron correctamente.`;
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

        if (!$("#formDa").valid()) return;

        const datos = new FormData();
        datos.append('idDa', idDa)
        datos.append("da", $("#da").val());
        datos.append("descripcion", $("#descripcion").val());

        const hasCode =  idDa !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_da
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_da.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_da.row(objectBtn.closest('tr')).data()
        let idDa = dt_row["IdDa"];

        const datos = new FormData();
        datos.append("idDa", idDa);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstadoBtnDtic(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    dtEvents.on('click', '.btn-delete', function(){
        let objectBtn = $(this)
        const dt_row = dt_da.row(objectBtn.closest('tr')).data()
        let idDa = dt_row["IdDa"];

        const datos = new FormData();
        datos.append("idDa", idDa);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la Da seleccionado?",
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
                        reloadTable: dt_da
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
    dtEvents.on('click', '.btn-edit', async function(){
        let objectBtn = $(this)
        const dt_row = dt_da.row(objectBtn.closest('tr')).data()
        idDa = dt_row["IdDa"];

        const datos = new FormData();
        datos.append("idDa", idDa);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let pei = data.data
                $("#da").val(pei["Da"]);
                $("#descripcion").val(pei["Descripcion"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
     * Validacion del form
     */

    $( "#formDa" ).validate( {
        rules: {
            da: {
                required: true,
                minlength: 2,
                maxlength: 2,
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        da: function() {
                            return $('#da').val(); // valor actual del campo
                        },
                        idDa: function (){
                            return idDa
                        }
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
            da: {
                required: "Debe ingresar un valor a la Da",
                minlength: "El codigo debe debe ser de 2 digitos",
                maxlength: "El codigo debe debe ser de 2 digitos",
                remote: "El codigo ingresado ya se encuentra en uso"
            },
            descripcion: {
                required: "Debe ingresar la descripcion del programa",
                minlength: "La descripcion del programa debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion del programa  debe tener maximo 500 caracteres",
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
})