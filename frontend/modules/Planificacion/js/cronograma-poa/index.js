$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/cronograma-poa/';
    const tabla = $('#tablaListaCronogramas');
    let idCronograma = EMPTY;

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formCatalogo').valid()) return;
        const datos = new FormData();
        datos.append('idCronograma', idCronograma);
        datos.append('fechaInicio', $('#fechaInicio').val());
        datos.append('fechaFin', $('#fechaFin').val());
        await ajaxPromise({
            url: baseUrl + (idCronograma === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Cronograma guardado correctamente.',
            reloadTable: dt_cronograma
        });
    });

    tabla.on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_cronograma.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idCronograma', row.IdCronograma),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtnDtic(btn, response.data);
    });

    tabla.on('click', '.btn-delete', function () {
        const row = dt_cronograma.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar cronograma',
            text: '¿Está seguro de eliminar el cronograma seleccionado?',
            showCancelButton: true,
            confirmButtonText: 'Borrar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idCronograma', row.IdCronograma),
                spinnerBtn: $(this),
                successMsg: 'Cronograma eliminado correctamente.',
                reloadTable: dt_cronograma
            });
        });
    });

    tabla.on('click', '.btn-edit', async function () {
        const row = dt_cronograma.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idCronograma', row.IdCronograma)
        });
        const data = response.data;
        idCronograma = data.IdCronograma;
        $('#fechaInicio').val(data.fechaInicio);
        $('#fechaFin').val(data.fechaFin);
        $('#btnMostrarCrear').trigger('click');
    });

    $('#formCatalogo').validate({
        rules: {
            fechaInicio: {required: true},
            fechaFin: {required: true}
        },
        messages: {
            fechaInicio: 'Ingrese la fecha de inicio.',
            fechaFin: 'Ingrese la fecha de fin.'
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
        idCronograma = EMPTY;
        $('#formCatalogo')[0].reset();
        $('#formCatalogo').validate().resetForm();
        $('#formCatalogo .is-invalid, #formCatalogo .is-valid').removeClass('is-invalid is-valid');
    }
});
