$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let idAccionEstrategica = ID_EMPTY_GUID;
    let baseUrl = "index.php?r=Planificacion/accion-estrategica/"
    let dtEvents = $('#tablaListaAccionesEstrategicas')
    let btnToggleForm = $('#btnMostrarCrear');

    function reiniciarCampos() {
        $('#formAccionEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formAccionEstrategica').trigger("reset");
        idAccionEstrategica = ID_EMPTY_GUID;
    }

    function mensajeAccion(accion) {
        return `Los datos de la accion estrategica se ${accion}on correctamente.`;
    }

    $("#btnCancelar").click(function () {
        btnToggleForm.removeClass('opened').addClass('closed')
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formAccionEstrategica").valid()) return;

        const datos = new FormData();
        datos.append('idAccionEstrategica', idAccionEstrategica)
        datos.append("descripcion", $("#descripcion").val());
        const hasCode =  idAccionEstrategica !== ID_EMPTY_GUID;
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_accion
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_accion.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_accion.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdAccionEstrategica"];

        const datos = new FormData();
        datos.append("idAccionEstrategica", rowId);

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
        const dt_row = dt_accion.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdAccionEstrategica"];

        const datos = new FormData();
        datos.append("idAccionEstrategica", rowId);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la accion estrategica seleccionada?",
            theme: 'bootstrap-5',
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + "eliminar",
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: dt_accion
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
        const dt_row = dt_accion.row(objectBtn.closest('tr')).data()
        idAccionEstrategica = dt_row["IdAccionEstrategica"];

        const datos = new FormData();
        datos.append("idAccionEstrategica", idAccionEstrategica);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let accion = data.data
                $("#descripcion").val(accion["Descripcion"]);

                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
     * Validacion del form
     */

    $( "#formAccionEstrategica" ).validate( {
        rules: {
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            descripcion: {
                required: "Debe ingresar una descripcion para el PEI",
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
})