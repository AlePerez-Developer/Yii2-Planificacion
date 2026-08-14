$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let idUe = ID_EMPTY_GUID
    let baseUrl = "index.php?r=Planificacion/unidad-ejecutora/"
    let dtEvents = $('#tablaListaUes')
    let btnToggleForm = $('#btnMostrarCrear');

    function reiniciarCampos() {
        $('#formUe *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        unidadEjecutora_s2Da.val(null).trigger('change')
        $('#formUe').trigger("reset");
        idUe = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos de la Unidad se ${accion}ron correctamente.`;
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

        if (!$("#formUe").valid()) return;

        const datos = new FormData();
        datos.append('idUnidadEjecutora', idUe)
        datos.append("ue", $("#ue").val());
        datos.append("descripcion", $("#descripcion").val());
        datos.append("idDa", $("#idDa").val());

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
        let idUe = dt_row["IdUnidadEjecutora"];

        const datos = new FormData();
        datos.append("idUnidadEjecutora", idUe);

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
        let idUe = dt_row["IdUnidadEjecutora"];

        const datos = new FormData();
        datos.append("idUnidadEjecutora", idUe);

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
        idUe = dt_row["IdUnidadEjecutora"];

        const datos = new FormData();
        datos.append("idUnidadEjecutora", idUe);

        IniciarSpiner(objectBtn);
        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos
            }).then((data) => {
                let ue = data.data
                unidadEjecutora_s2Da.val(ue["IdDa"]).trigger('change.select2');
                $("#ue").val(ue["Ue"]);
                $("#descripcion").val(ue["Descripcion"]);
                DetenerSpiner(objectBtn);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
            DetenerSpiner(objectBtn);
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
                require_from_group: [2, ".codigo_group"],
                pattern: /^\d{3}$/,
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        idDa: function () {
                            let idDa = $('#idDa').select2('data')
                            return idDa[0].id
                        },
                        ue: function() {
                            return $('#ue').val(); // valor actual del campo
                        },
                        idUnidadEjecutora: function (){
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
                remote: "El codigo ingresado ya se encuentra en uso",
                require_from_group: "Debe seleccionar una Da para validar el valor de UE",
                pattern:"el valor debe tener 3 digitos de UE",
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
