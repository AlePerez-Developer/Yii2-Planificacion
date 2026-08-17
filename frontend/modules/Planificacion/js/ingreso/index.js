$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/ingreso/';
    let idIngreso = EMPTY;

    cargarResumen();

    $('#cantidad').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#precio').on('input', function () {
        this.value = normalizarDecimal(this.value);
    });

    $('#btnMostrarCrear').on('click', function () {
        limpiar();
        mostrarFormulario('Registrar ingreso');
    });

    $('#btnCancelar').on('click', cerrarFormulario);

    $('#btnGuardar').on('click', async function () {
        if (!$('#formIngreso').valid()) return;
        const datos = new FormData();
        datos.append('idIngreso', idIngreso);
        datos.append('cantidad', $('#cantidad').val());
        datos.append('precio', $('#precio').val());
        datos.append('descripcion', $('#descripcion').val());

        try {
            await ajaxPromise({
                url: baseUrl + (idIngreso === EMPTY ? 'guardar' : 'actualizar'),
                data: datos,
                spinnerBtn: $(this),
                cancelBtn: $('#btnCancelar'),
                successMsg: 'Ingreso guardado correctamente.',
                reloadTable: dt_ingreso,
                onSuccess: cargarResumen
            });
        } catch (error) {
            console.error(error);
        }
    });

    $('#tablaIngresos').on('click', '.btn-edit', function () {
        const row = dt_ingreso.row($(this).closest('tr')).data();
        $.ajax({
            url: baseUrl + 'buscar',
            method: 'POST',
            dataType: 'json',
            data: {idIngreso: row.IdIngreso},
            success: function (response) {
                idIngreso = response.data.IdIngreso;
                $('#cantidad').val(response.data.Cantidad);
                $('#precio').val(response.data.Precio);
                $('#descripcion').val(response.data.Descripcion || '');
                mostrarFormulario('Editar ingreso');
            },
            error: mostrarErrorIngreso
        });
    });

    $('#tablaIngresos').on('click', '.btn-toggle-estado', function () {
        const row = dt_ingreso.row($(this).closest('tr')).data();
        ejecutar('cambiar-estado', row.IdIngreso, 'Estado actualizado.');
    });

    $('#tablaIngresos').on('click', '.btn-delete', function () {
        const row = dt_ingreso.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar ingreso',
            text: '¿Está seguro de eliminar este ingreso?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) ejecutar('eliminar', row.IdIngreso, 'Ingreso eliminado.');
        });
    });

    $('#formIngreso').validate({
        rules: {
            cantidad: {required: true, digits: true, min: 1},
            precio: {required: true, number: true, min: 0.01},
            descripcion: {maxlength: 500}
        },
        messages: {
            cantidad: 'Ingrese una cantidad entera mayor que cero.',
            precio: 'Ingrese un precio válido con máximo dos decimales.',
            descripcion: 'La descripción no puede superar 500 caracteres.'
        }
    });

    async function ejecutar(accion, id, mensaje) {
        const datos = new FormData();
        datos.append('idIngreso', id);
        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                successMsg: mensaje,
                reloadTable: dt_ingreso,
                onSuccess: cargarResumen
            });
        } catch (error) {
            console.error(error);
        }
    }

    function cargarResumen() {
        $.post(baseUrl + 'resumen', response => {
            const ingresos = Number(response.data.TotalIngresos || 0);
            const techos = Number(response.data.TotalTechos || 0);
            const formato = {minimumFractionDigits: 2, maximumFractionDigits: 2};
            $('#totalIngresos').text(ingresos.toLocaleString('es-BO', formato));
            $('#totalTechos').text(techos.toLocaleString('es-BO', formato));
            $('#diferenciaIngresos').text((ingresos - techos).toLocaleString('es-BO', formato));
        }, 'json');
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
        idIngreso = EMPTY;
        $('#formIngreso')[0].reset();
        $('#formIngreso').validate().resetForm();
    }

    function normalizarDecimal(valor) {
        const limpio = String(valor).replace(',', '.').replace(/[^\d.]/g, '');
        const partes = limpio.split('.');
        return partes.length > 1
            ? `${partes.shift()}.${partes.join('').slice(0, 2)}`
            : partes[0];
    }
});
