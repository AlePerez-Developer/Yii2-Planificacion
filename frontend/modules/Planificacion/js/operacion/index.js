$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/operacion/';
    let idOperacion = EMPTY;

    $('#codigo').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
    });

    $('#btnMostrarCrear').on('click', function () {
        limpiarFormulario();
        mostrarFormulario('Nueva operación POA');
    });

    $('#btnCancelar').on('click', function () {
        $('#divDatos').slideUp(180);
        $('#divTabla').slideDown(180);
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formOperacion').valid()) return;

        const datos = new FormData();
        datos.append('idOperacion', idOperacion);
        datos.append('codigo', $('#codigo').val());
        datos.append('idObjEspecifico', operacion_s2Objetivo.val() || '');
        datos.append('idIndicador', operacion_s2Indicador.val() || '');
        datos.append('idLlavePresupuestaria', operacion_s2Llave.val() || '');
        datos.append('descripcion', $('#descripcion').val());
        datos.append('tipoOperacion', $('#tipoOperacion').val());

        try {
            await ajaxPromise({
                url: baseUrl + (idOperacion === EMPTY ? 'guardar' : 'actualizar'),
                data: datos,
                spinnerBtn: $(this),
                cancelBtn: $('#btnCancelar'),
                successMsg: 'Operación guardada correctamente.',
                reloadTable: dt_operacion
            });
        } catch (error) {
            console.error('No se pudo guardar la operación.', error);
        }
    });

    $('#tablaOperaciones').on('click', '.btn-edit', function () {
        const row = dt_operacion.row($(this).closest('tr')).data();

        $.ajax({
            url: baseUrl + 'buscar',
            method: 'POST',
            dataType: 'json',
            data: {idOperacion: row.IdOperacion},
            success: function (response) {
                const data = response.data;
                idOperacion = data.IdOperacion;
                $('#codigo').val(data.Codigo);
                $('#descripcion').val(data.Descripcion || '');
                $('#tipoOperacion').val(data.TipoOperacion);
                operacion_s2Objetivo.val(data.IdObjEspecifico).trigger('change');
                operacion_s2Indicador.val(data.IdIndicador).trigger('change');
                operacion_s2Llave.val(data.IdLlavePresupuestaria).trigger('change');
                mostrarFormulario('Editar operación POA');
            },
            error: mostrarErrorOperacion
        });
    });

    $('#tablaOperaciones').on('click', '.btn-toggle-estado', function () {
        const row = dt_operacion.row($(this).closest('tr')).data();
        ejecutarAccion(
            'cambiar-estado',
            row.IdOperacion,
            'Estado actualizado correctamente.'
        );
    });

    $('#tablaOperaciones').on('click', '.btn-delete', function () {
        const row = dt_operacion.row($(this).closest('tr')).data();

        Swal.fire({
            icon: 'warning',
            title: 'Eliminar operación',
            text: '¿Está seguro de eliminar esta operación POA?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                ejecutarAccion(
                    'eliminar',
                    row.IdOperacion,
                    'Operación eliminada correctamente.'
                );
            }
        });
    });

    $('#tablaOperaciones')
        .on('click', '.input-meta-operacion[readonly]', function () {
            $(this)
                .prop('readonly', false)
                .data('original', $(this).val())
                .focus()
                .select();
        })
        .on('input', '.input-meta-operacion:not([readonly])', function () {
            this.value = this.value.replace(/\D/g, '');
        })
        .on('keydown', '.input-meta-operacion:not([readonly])', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                guardarMeta($(this));
            }
            if (event.key === 'Escape') {
                $(this)
                    .val($(this).data('original'))
                    .prop('readonly', true);
            }
        })
        .on('blur', '.input-meta-operacion:not([readonly])', function () {
            guardarMeta($(this));
        });

    $('#formOperacion').validate({
        ignore: [],
        rules: {
            codigo: {
                required: true,
                minlength: 2,
                maxlength: 2,
                digits: true,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'POST',
                    data: {
                        idOperacion: () => idOperacion,
                        idObjEspecifico: () => operacion_s2Objetivo.val() || '',
                        codigo: () => $('#codigo').val()
                    }
                }
            },
            idObjEspecifico: {required: true},
            idIndicador: {required: true},
            idLlavePresupuestaria: {required: true},
            tipoOperacion: {required: true},
            descripcion: {maxlength: 300}
        },
        messages: {
            codigo: {
                required: 'Ingrese el código.',
                minlength: 'El código debe tener dos dígitos.',
                maxlength: 'El código debe tener dos dígitos.',
                digits: 'El código solo admite números.',
                remote: 'El código ya existe para el objetivo seleccionado.'
            },
            idObjEspecifico: 'Seleccione un objetivo específico.',
            idIndicador: 'Seleccione un indicador programado.',
            idLlavePresupuestaria: 'Seleccione una llave presupuestaria.',
            tipoOperacion: 'Seleccione el tipo de operación.',
            descripcion: 'La descripción no puede superar 300 caracteres.'
        },
        errorPlacement: function (error, element) {
            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'));
                return;
            }
            error.insertAfter(element);
        }
    });

    function guardarMeta(input) {
        if (input.data('guardando')) return;

        const meta = Number(input.val());
        const original = Number(input.data('original'));
        const row = dt_operacion.row(input.closest('tr'));
        const rowData = row.data();

        if (!Number.isInteger(meta) || meta < 0) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('warning', 'La meta debe ser un entero mayor o igual a cero.');
            return;
        }
        if (meta === original) {
            input.prop('readonly', true);
            return;
        }
        if (!rowData) {
            input.val(original).prop('readonly', true);
            return;
        }

        const metas = [
            Number(rowData.PrimerTrimestre || 0),
            Number(rowData.SegundoTrimestre || 0),
            Number(rowData.TercerTrimestre || 0),
            Number(rowData.CuartoTrimestre || 0)
        ];
        metas[Number(input.data('trimestre')) - 1] = meta;

        if (metas.reduce((total, valor) => total + valor, 0) > 100) {
            input.val(original).prop('readonly', true);
            MostrarMensaje(
                'warning',
                'La programación trimestral acumulada no puede superar 100.'
            );
            return;
        }

        input.data('guardando', true).prop('disabled', true);
        $.ajax({
            url: baseUrl + 'guardar-meta-trimestral',
            method: 'POST',
            dataType: 'json',
            data: {
                idOperacion: input.data('idoperacion'),
                trimestre: input.data('trimestre'),
                meta
            },
            success: function (response) {
                Object.assign(rowData, response.data);
                row.data(rowData).invalidate();
                dt_operacion.draw(false);
            },
            error: function (xhr) {
                input.val(original);
                mostrarErrorOperacion(xhr);
            },
            complete: function () {
                input
                    .data('guardando', false)
                    .prop('disabled', false)
                    .prop('readonly', true);
            }
        });
    }

    async function ejecutarAccion(accion, id, mensaje) {
        const datos = new FormData();
        datos.append('idOperacion', id);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                successMsg: mensaje,
                reloadTable: dt_operacion
            });
        } catch (error) {
            console.error(`No se pudo ejecutar ${accion}.`, error);
        }
    }

    function mostrarFormulario(titulo) {
        $('#tituloFormulario').text(titulo);
        $('#divTabla').slideUp(180);
        $('#divDatos').slideDown(180);
        $('#btnMostrarCrear').removeClass('closed').addClass('opened');
    }

    function limpiarFormulario() {
        idOperacion = EMPTY;
        $('#formOperacion')[0].reset();
        $('#formOperacion').validate().resetForm();
        $('#tipoOperacion').val('Funcionamiento');
        operacion_s2Objetivo.val(null).trigger('change');
        operacion_s2Indicador.val(null).trigger('change');
        operacion_s2Llave.val(null).trigger('change');
    }
});
