$(document).ready(function () {
    let idUe = '00000000-0000-0000-0000-000000000000';
    let baseUrl = "index.php?r=Planificacion/ue/"
    let dtEvents = $('#tablaListaUes')

    function reiniciarCampos() {
        $('#formUe *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formUe').trigger("reset");
        idUe = '00000000-0000-0000-0000-000000000000';
    }

    function mensajeAccion(accion) {
        return `Los datos de la Unidad se ${accion}ron correctamente.`;
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

        if (!$("#formUe").valid()) return;

        const datos = new FormData();
        datos.append('idUe', idUe)
        datos.append("ue", $("#ue").val());
        datos.append("descripcion", $("#descripcion").val());

        const hasCode =  idUe !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_ue
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_ue.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_ue.row(objectBtn.closest('tr')).data()
        let idUe = dt_row["IdUe"];

        const datos = new FormData();
        datos.append("idUe", idUe);

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
        const dt_row = dt_ue.row(objectBtn.closest('tr')).data()
        let idUe = dt_row["IdUe"];

        const datos = new FormData();
        datos.append("idUe", idUe);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la Unidad seleccionado?",
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
                        reloadTable: dt_ue
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
        const dt_row = dt_ue.row(objectBtn.closest('tr')).data()
        idUe = dt_row["IdUe"];

        const datos = new FormData();
        datos.append("idUe", idUe);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let ue = data.data
                $("#ue").val(ue["Ue"]);
                $("#descripcion").val(ue["Descripcion"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
     * Validacion del form
     */

    $( "#formUe" ).validate( {
        rules: {
            ue: {
                required: true,
                minlength: 3,
                maxlength: 3,
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        ue: function() {
                            return $('#ue').val(); // valor actual del campo
                        },
                        idUe: function (){
                            return idUe
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
            ue: {
                required: "Debe ingresar un valor a la Unidad",
                minlength: "El codigo debe debe ser de 3 digitos",
                maxlength: "El codigo debe debe ser de 3 digitos",
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