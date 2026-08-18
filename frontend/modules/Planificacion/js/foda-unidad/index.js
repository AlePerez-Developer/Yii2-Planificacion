$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/foda-unidad/';
    let idFoda = EMPTY;

    $('#btnMostrarCrear').on('click', function () {
        limpiar();
        mostrarFormulario('Registrar FODA');
    });

    $('#btnReportePdf').on('click', function () {
        window.open(baseUrl + 'reporte', '_blank');
    });

    $('#btnCancelar').on('click', cerrarFormulario);

    $('#btnGuardar').on('click', async function () {
        if (!$('#formFodaUnidad').valid()) return;
        const datos = new FormData();
        datos.append('idFoda', idFoda);
        datos.append('tipo', $('#tipo').val());
        datos.append('descripcion', $('#descripcion').val());

        try {
            await ajaxPromise({
                url: baseUrl + (idFoda === EMPTY ? 'guardar' : 'actualizar'),
                data: datos,
                spinnerBtn: $(this),
                cancelBtn: $('#btnCancelar'),
                successMsg: 'Registro FODA guardado correctamente.',
                reloadTable: dt_fodaUnidad
            });
        } catch (error) {
            console.error(error);
        }
    });

    $('#tablaFodaUnidad').on('click', '.btn-edit', function () {
        const row = dt_fodaUnidad.row($(this).closest('tr')).data();
        $.ajax({
            url: baseUrl + 'buscar',
            method: 'POST',
            dataType: 'json',
            data: {idFoda: row.IdFoda},
            success: function (response) {
                idFoda = response.data.IdFoda;
                $('#tipo').val(response.data.Tipo);
                $('#descripcion').val(response.data.Descripcion || '');
                mostrarFormulario('Editar FODA');
            },
            error: mostrarErrorFodaUnidad
        });
    });

    $('#tablaFodaUnidad').on('click', '.btn-toggle-estado', function () {
        const row = dt_fodaUnidad.row($(this).closest('tr')).data();
        ejecutar('cambiar-estado', row.IdFoda, 'Estado actualizado.');
    });

    $('#tablaFodaUnidad').on('click', '.btn-delete', function () {
        const row = dt_fodaUnidad.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar registro',
            text: '¿Está seguro de eliminar este registro FODA?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) ejecutar('eliminar', row.IdFoda, 'Registro eliminado.');
        });
    });

    $('#formFodaUnidad').validate({
        rules: {
            tipo: {required: true},
            descripcion: {required: true, minlength: 2, maxlength: 500}
        },
        messages: {
            tipo: 'Seleccione un tipo FODA.',
            descripcion: 'Ingrese una descripción de 2 a 500 caracteres.'
        }
    });

    async function ejecutar(accion, id, mensaje) {
        const datos = new FormData();
        datos.append('idFoda', id);
        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                successMsg: mensaje,
                reloadTable: dt_fodaUnidad
            });
        } catch (error) {
            console.error(error);
        }
    }

    function mostrarFormulario(titulo) {
        $('#tituloFormulario').text(titulo);
        $('#divTabla').slideUp(180);
        $('#divDatos').slideDown(180);
    }

    function cerrarFormulario() {
        $('#divDatos').slideUp(180);
        $('#divTabla').slideDown(180);
    }

    function limpiar() {
        idFoda = EMPTY;
        $('#formFodaUnidad')[0].reset();
        $('#formFodaUnidad').validate().resetForm();
    }
});
