$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let idObjInstitucional = ID_EMPTY_GUID;
    const baseUrl = 'index.php?r=Planificacion/obj-institucional/';
    const dtEvents = $('#tablaListaObjInstitucionales');
    const btnToggleForm = $('#btnMostrarCrear');

    function reiniciarCampos() {
        $('#formObjInstitucional *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formObjInstitucional').trigger('reset');
        objInstitucional_s2ObjEstrategico.val(null).trigger('change');
        idObjInstitucional = ID_EMPTY_GUID;
    }

    function mensajeAccion(accion) {
        const mensajes = {
            guardar: 'Los datos del objetivo institucional se guardaron correctamente.',
            actualizar: 'Los datos del objetivo institucional se actualizaron correctamente.',
            eliminar: 'El objetivo institucional se eliminó correctamente.'
        };

        return mensajes[accion] || 'Proceso realizado correctamente.';
    }

    $('#btnCancelar').on('click', function () {
        btnToggleForm.removeClass('opened').addClass('closed');
        reiniciarCampos();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar');

        if (!$('#formObjInstitucional').valid()) {
            return;
        }


        const datos = new FormData();

        datos.append('idObjInstitucional', idObjInstitucional);
        datos.append('idObjEstrategico', objInstitucional_s2ObjEstrategico.select2('data')[0].id);
        datos.append('codigo', $('#codigo').val());
        datos.append('objetivo', $('#objetivo').val());
        datos.append('producto', $('#producto').val());

        const hasCode = idObjInstitucional !== ID_EMPTY_GUID;
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_objInstitucional
            });
        } catch (error) {
            console.error('Error al procesar el objetivo institucional:', error);
        }
    });

    $(document).on('click', '#refresh', function () {
        dt_objInstitucional.ajax.reload();
    });

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function () {
        const objectBtn = $(this);
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        let rowId = dt_row["IdObjInstitucional"];

        const datos = new FormData();
        datos.append('idObjInstitucional', rowId);

        try {
            await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.'
            }).then((data) => {
                cambiarEstadoBtn(objectBtn, data.data);
            })
        } catch (error) {
            console.error('Error al cambiar el estado:', error);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    dtEvents.on('click', '.btn-delete', function () {
        const objectBtn = $(this);
        const dt_row = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        let rowId = dt_row["IdObjInstitucional"];

        const datos = new FormData();
        datos.append('idObjInstitucional', rowId);

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación de eliminación',
            text: '¿Está seguro de eliminar el objetivo institucional seleccionado?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
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
    dtEvents.on('click', '.btn-edit', async function () {
        const objectBtn = $(this);
        const registro = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        const datos = new FormData();

        idObjInstitucional = registro.IdObjInstitucional;
        datos.append('idObjInstitucional', idObjInstitucional);

        IniciarSpiner(objectBtn);
        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
            }).then(async (data) => {
                let obj = data.data
                objInstitucional_s2ObjEstrategico
                    .val(obj.IdObjEstrategico)
                    .trigger('change.select2');

                $("#codigo").val(obj["Codigo"]);
                $("#objetivo").val(obj["Objetivo"]);
                $("#producto").val(obj["Producto"]);

                DetenerSpiner(objectBtn);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (error) {
            console.error("Error al procesar:", error);
            DetenerSpiner(objectBtn);
        }
    });

    $('#formObjInstitucional').validate({
        rules: {
            idObjEstrategico: {
                required: true
            },
            codigo: {
                required: true,
                digits: true,
                minlength: 2,
                maxlength: 2,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: function () {
                            return $('#codigo').val();
                        },
                        idObjEstrategico: function () {
                            return objInstitucional_s2ObjEstrategico.val() || '';
                        },
                        idObjInstitucional: function () {
                            return idObjInstitucional;
                        }
                    }
                }
            },
            objetivo: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
            producto: {
                required: true,
                minlength: 2,
                maxlength: 500
            }
        },
        messages: {
            idObjEstrategico: {
                required: 'Debe seleccionar un objetivo estratégico.'
            },
            codigo: {
                required: 'Debe ingresar el código del objetivo institucional.',
                digits: 'El código solo puede contener números.',
                minlength: 'El código debe tener exactamente 2 dígitos.',
                maxlength: 'El código debe tener exactamente 2 dígitos.',
                remote: 'El código ya está registrado para el objetivo estratégico seleccionado.'
            },
            objetivo: {
                required: 'Debe ingresar el objetivo institucional.',
                minlength: 'El objetivo debe tener al menos 2 caracteres.',
                maxlength: 'El objetivo permite como máximo 200 caracteres.'
            },
            producto: {
                required: 'Debe ingresar el resultado o producto esperado.',
                minlength: 'El producto debe tener al menos 2 caracteres.',
                maxlength: 'El producto permite como máximo 200 caracteres.'
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
});
