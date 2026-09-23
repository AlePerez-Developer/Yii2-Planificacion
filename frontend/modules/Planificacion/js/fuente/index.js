$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/fuente/';
    const tabla = $('#tablaListaFuentes');
    let idFuente = EMPTY;

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formCatalogo').valid()) return;
        const datos = new FormData();
        datos.append('idFuente', idFuente);
        datos.append('descripcion', $('#descripcion').val());
        await ajaxPromise({
            url: baseUrl + (idFuente === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Fuente guardada correctamente.',
            reloadTable: dt_fuente
        });
    });

    tabla.on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_fuente.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idFuente', row.IdFuente),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtnDtic(btn, response.data);
    });

    tabla.on('click', '.btn-delete', function () {
        const row = dt_fuente.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar fuente',
            text: '¿Está seguro de eliminar la fuente seleccionada?',
            showCancelButton: true,
            confirmButtonText: 'Borrar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idFuente', row.IdFuente),
                spinnerBtn: $(this),
                successMsg: 'Fuente eliminada correctamente.',
                reloadTable: dt_fuente
            });
        });
    });

    tabla.on('click', '.btn-edit', async function () {
        const row = dt_fuente.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idFuente', row.IdFuente)
        });
        idFuente = response.data.IdFuente;
        $('#descripcion').val(response.data.Descripcion);
        $('#btnMostrarCrear').trigger('click');
    });

    $('#formCatalogo').validate({
        rules: {descripcion: {required: true, minlength: 2, maxlength: 500}},
        messages: {descripcion: 'Ingrese la descripción.'},
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            error.insertAfter(element);
        },
        highlight: element => $(element).addClass('is-invalid').removeClass('is-valid'),
        unhighlight: element => $(element).addClass('is-valid').removeClass('is-invalid')
    });

    function limpiar() {
        idFuente = EMPTY;
        $('#formCatalogo')[0].reset();
        $('#formCatalogo').validate().resetForm();
        $('#formCatalogo .is-invalid, #formCatalogo .is-valid').removeClass('is-invalid is-valid');
    }
});
