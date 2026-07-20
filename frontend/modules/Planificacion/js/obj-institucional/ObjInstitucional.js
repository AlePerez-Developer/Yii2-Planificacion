$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/obj-institucional/';
    const dtEvents = $('#tablaListaObjInstitucionales');
    const btnToggleForm = $('#btnMostrarCrear');

    let idObjInstitucional = ID_EMPTY_GUID;

    $('#gestion').val(new Date().getFullYear());

    function normalizarCodigo() {
        const input = $('#codigo');
        const valor = input.val().replace(/\D/g, '').slice(0, 2);

        if (valor !== '') {
            input.val(valor.padStart(2, '0'));
        }
    }

    function reiniciarCampos() {
        $('#formObjInstitucional *:input').removeClass('is-invalid is-valid');
        $('#formObjInstitucional').trigger('reset');
        objInstitucional_s2ObjEstrategico.val(null).trigger('change');
        $('#gestion').val(new Date().getFullYear());
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

    $('#codigo').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
    }).on('blur', normalizarCodigo);

    $('#btnCancelar').on('click', function () {
        btnToggleForm.removeClass('opened').addClass('closed');
        reiniciarCampos();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        normalizarCodigo();

        if (!$('#formObjInstitucional').valid()) {
            return;
        }

        const btn = $(this);
        const btnCancel = $('#btnCancelar');
        const accion = idObjInstitucional === ID_EMPTY_GUID ? 'guardar' : 'actualizar';
        const datos = new FormData();

        datos.append('idObjInstitucional', idObjInstitucional);
        datos.append('idObjEstrategico', objInstitucional_s2ObjEstrategico.val());
        datos.append('codigo', $('#codigo').val());
        datos.append('objetivo', $('#objetivo').val());
        datos.append('producto', $('#producto').val());
        datos.append('gestion', $('#gestion').val());

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
        dt_objInstitucional.ajax.reload(null, false);
    });

    dtEvents.on('click', '.btn-toggle-estado', async function () {
        const objectBtn = $(this);
        const registro = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        const datos = new FormData();

        datos.append('idObjInstitucional', registro.IdObjInstitucional);

        try {
            const respuesta = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.'
            });

            cambiarEstadoBtn(objectBtn, respuesta.data);
        } catch (error) {
            console.error('Error al cambiar el estado:', error);
        }
    });

    dtEvents.on('click', '.btn-delete', function () {
        const objectBtn = $(this);
        const registro = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        const datos = new FormData();

        datos.append('idObjInstitucional', registro.IdObjInstitucional);

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
            if (!resultado.value && !resultado.isConfirmed) {
                return;
            }

            try {
                await ajaxPromise({
                    url: baseUrl + 'eliminar',
                    data: datos,
                    spinnerBtn: objectBtn,
                    successMsg: mensajeAccion('eliminar'),
                    reloadTable: dt_objInstitucional
                });
            } catch (error) {
                console.error('Error al eliminar:', error);
            }
        });
    });

    dtEvents.on('click', '.btn-edit', async function () {
        const objectBtn = $(this);
        const registro = dt_objInstitucional.row(objectBtn.closest('tr')).data();
        const datos = new FormData();

        idObjInstitucional = registro.IdObjInstitucional;
        datos.append('idObjInstitucional', idObjInstitucional);

        try {
            const respuesta = await ajaxPromise({
                url: baseUrl + 'buscar',
                data: datos,
                spinnerBtn: objectBtn
            });

            const obj = respuesta.data;

            objInstitucional_s2ObjEstrategico
                .val(obj.IdObjEstrategico)
                .trigger('change.select2');

            $('#codigo').val(obj.Codigo);
            $('#objetivo').val(obj.Objetivo);
            $('#producto').val(obj.Producto);
            $('#gestion').val(obj.Gestion);

            $('#btnMostrarCrear').trigger('click');
        } catch (error) {
            console.error('Error al buscar el registro:', error);
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
                maxlength: 200
            },
            producto: {
                required: true,
                minlength: 2,
                maxlength: 200
            },
            gestion: {
                required: true,
                digits: true,
                range: [2000, 2100]
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
            },
            gestion: {
                required: 'Debe ingresar la gestión.',
                digits: 'La gestión debe ser un número entero.',
                range: 'La gestión debe estar entre 2000 y 2100.'
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');

            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'));
                return;
            }

            error.insertAfter(element);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');

            if ($(element).hasClass('select2-hidden-accessible')) {
                $(element).next('.select2').find('.select2-selection')
                    .addClass('is-invalid')
                    .removeClass('is-valid');
            }
        },
        unhighlight: function (element) {
            $(element).addClass('is-valid').removeClass('is-invalid');

            if ($(element).hasClass('select2-hidden-accessible')) {
                $(element).next('.select2').find('.select2-selection')
                    .addClass('is-valid')
                    .removeClass('is-invalid');
            }
        }
    });
});
