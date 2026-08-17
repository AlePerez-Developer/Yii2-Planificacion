$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/indicador-poa/';
    let idIndicadorPoa = EMPTY;

    $('#btnMostrarCrear').on('click', () => mostrarFormulario());
    $('#btnCancelar').on('click', ocultarFormulario);

    $('#btnGuardar').on('click', async function () {
        if (!$('#formIndicadorPoa').valid()) return;

        const datos = new FormData();
        datos.append('idIndicadorPoa', idIndicadorPoa);
        datos.append('codigo', $('#codigo').val());
        datos.append('meta', $('#meta').val());
        datos.append('lineaBase', $('#lineaBase').val());
        datos.append('descripcion', $('#descripcion').val());
        datos.append('idTipoResultado', indicadorPoa_s2TipoResultado.val());
        datos.append('idCategoriaIndicador', indicadorPoa_s2CategoriaIndicador.val());
        datos.append('idUnidadIndicador', indicadorPoa_s2UnidadIndicador.val());

        try {
            await ajaxPromise({
                url: baseUrl + (idIndicadorPoa === EMPTY ? 'guardar' : 'actualizar'),
                data: datos,
                spinnerBtn: $(this),
                cancelBtn: $('#btnCancelar'),
                successMsg: 'Indicador POA guardado correctamente.',
                reloadTable: dt_indicadorPoa
            });
        } catch (error) {
            console.error('No se pudo guardar el indicador POA.', error);
        }
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-edit', async function () {
        const row = dt_indicadorPoa.row($(this).closest('tr')).data();
        idIndicadorPoa = row.IdIndicador;

        try {
            const response = await ajaxPromise({
                url: baseUrl + 'buscar',
                data: crearFormData('idIndicadorPoa', idIndicadorPoa),
                spinnerBtn: $(this)
            });
            const data = response.data;

            $('#codigo').val(data.Codigo);
            $('#meta').val(data.Meta);
            $('#lineaBase').val(data.LineaBase);
            $('#descripcion').val(data.Descripcion);
            indicadorPoa_s2TipoResultado.val(data.IdTipoResultado).trigger('change');
            indicadorPoa_s2CategoriaIndicador.val(data.IdCategoriaIndicador).trigger('change');
            indicadorPoa_s2UnidadIndicador.val(data.IdUnidadIndicador).trigger('change');
            mostrarFormulario(false);
        } catch (error) {
            console.error('No se pudo obtener el indicador POA.', error);
        }
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_indicadorPoa.row(btn.closest('tr')).data();

        try {
            const response = await ajaxPromise({
                url: baseUrl + 'cambiar-estado',
                data: crearFormData('idIndicadorPoa', row.IdIndicador),
                spinnerBtn: btn,
                successMsg: 'Estado actualizado correctamente.'
            });
            cambiarEstadoBtnDtic(btn, response.data);
        } catch (error) {
            console.error('No se pudo cambiar el estado.', error);
        }
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-delete', function () {
        const row = dt_indicadorPoa.row($(this).closest('tr')).data();

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación',
            text: '¿Eliminar el indicador POA seleccionado?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;

            try {
                await ajaxPromise({
                    url: baseUrl + 'eliminar',
                    data: crearFormData('idIndicadorPoa', row.IdIndicador),
                    successMsg: 'Indicador POA eliminado correctamente.',
                    reloadTable: dt_indicadorPoa
                });
            } catch (error) {
                console.error('No se pudo eliminar el indicador POA.', error);
            }
        });
    });

    $('#formIndicadorPoa').validate({
        rules: {
            codigo: {
                required: true,
                digits: true,
                min: 1,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        codigo: () => $('#codigo').val(),
                        idIndicadorPoa: () => idIndicadorPoa
                    }
                }
            },
            meta: {required: true, digits: true, min: 0},
            lineaBase: {required: true, digits: true, min: 0},
            descripcion: {required: true, minlength: 2, maxlength: 500},
            idTipoResultado: {required: true},
            idCategoriaIndicador: {required: true},
            idUnidadIndicador: {required: true}
        },
        messages: {
            codigo: {
                required: 'Debe ingresar el código.',
                digits: 'Solo se permiten números enteros.',
                min: 'El código debe ser mayor que cero.',
                remote: 'El código ya está en uso en la gestión activa.'
            },
            meta: {
                required: 'Debe ingresar la meta.',
                digits: 'Solo se permiten números enteros.',
                min: 'La meta debe ser mayor o igual a cero.'
            },
            lineaBase: {
                required: 'Debe ingresar la línea base.',
                digits: 'Solo se permiten números enteros.',
                min: 'La línea base debe ser mayor o igual a cero.'
            },
            descripcion: {
                required: 'Debe ingresar la descripción.',
                minlength: 'La descripción debe tener al menos 2 caracteres.',
                maxlength: 'La descripción admite hasta 500 caracteres.'
            },
            idTipoResultado: {required: 'Seleccione el tipo de resultado.'},
            idCategoriaIndicador: {required: 'Seleccione la categoría.'},
            idUnidadIndicador: {required: 'Seleccione la unidad.'}
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            error.insertAfter(element.next('.select2').length ? element.next('.select2') : element);
        },
        highlight: element => $(element).addClass('is-invalid').removeClass('is-valid'),
        unhighlight: element => $(element).addClass('is-valid').removeClass('is-invalid')
    });

    function mostrarFormulario(limpiar = true) {
        if (limpiar) limpiarFormulario();
        $('#btnMostrarCrear').removeClass('closed').addClass('opened');
        $('#divTabla').hide(300);
        $('#divDatos').show(300);
    }

    function ocultarFormulario() {
        limpiarFormulario();
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        $('#divDatos').hide(300);
        $('#divTabla').show(300);
    }

    function limpiarFormulario() {
        idIndicadorPoa = EMPTY;
        $('#formIndicadorPoa').trigger('reset');
        $('#formIndicadorPoa :input').removeClass('is-invalid is-valid');
        indicadorPoa_s2TipoResultado.val(null).trigger('change');
        indicadorPoa_s2CategoriaIndicador.val(null).trigger('change');
        indicadorPoa_s2UnidadIndicador.val(null).trigger('change');
    }

    function crearFormData(nombre, valor) {
        const datos = new FormData();
        datos.append(nombre, valor);
        return datos;
    }
});
