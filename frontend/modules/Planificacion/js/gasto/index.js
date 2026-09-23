$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/gasto/';
    const tabla = $('#tablaListaGastos');
    let idGasto = EMPTY;

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formCatalogo').valid()) return;
        const datos = new FormData();
        datos.append('idGasto', idGasto);
        datos.append('codigoGasto', $('#codigoGasto').val());
        datos.append('descripcion', $('#descripcion').val());
        datos.append('entidadTransferencia', $('#entidadTransferencia').val());
        await ajaxPromise({
            url: baseUrl + (idGasto === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Gasto guardado correctamente.',
            reloadTable: dt_gasto
        });
    });

    tabla.on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_gasto.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idGasto', row.IdGasto),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtnDtic(btn, response.data);
    });

    tabla.on('click', '.btn-delete', function () {
        const row = dt_gasto.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar gasto',
            text: '¿Está seguro de eliminar el gasto seleccionado?',
            showCancelButton: true,
            confirmButtonText: 'Borrar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idGasto', row.IdGasto),
                spinnerBtn: $(this),
                successMsg: 'Gasto eliminado correctamente.',
                reloadTable: dt_gasto
            });
        });
    });

    tabla.on('click', '.btn-edit', async function () {
        const row = dt_gasto.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idGasto', row.IdGasto)
        });
        const data = response.data;
        idGasto = data.IdGasto;
        $('#codigoGasto').val(data.CodigoGasto);
        $('#descripcion').val(data.Descripcion);
        $('#entidadTransferencia').val(data.EntidadTransferencia);
        $('#btnMostrarCrear').trigger('click');
    });

    $('#formCatalogo').validate({
        rules: {
            codigoGasto: {
                required: true,
                maxlength: 10,
                remote: {
                    url: baseUrl + 'verificar-codigo',
                    type: 'POST',
                    data: {
                        codigoGasto: () => $('#codigoGasto').val(),
                        idGasto: () => idGasto
                    }
                }
            },
            descripcion: {required: true, minlength: 2, maxlength: 500},
            entidadTransferencia: {required: true, maxlength: 10}
        },
        messages: {
            codigoGasto: {
                required: 'Ingrese el código.',
                remote: 'El código de gasto ya está en uso.'
            },
            descripcion: 'Ingrese la descripción.',
            entidadTransferencia: 'Ingrese la entidad de transferencia.'
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            error.insertAfter(element);
        },
        highlight: element => $(element).addClass('is-invalid').removeClass('is-valid'),
        unhighlight: element => $(element).addClass('is-valid').removeClass('is-invalid')
    });

    function limpiar() {
        idGasto = EMPTY;
        $('#formCatalogo')[0].reset();
        $('#formCatalogo').validate().resetForm();
        $('#formCatalogo .is-invalid, #formCatalogo .is-valid').removeClass('is-invalid is-valid');
    }
});
