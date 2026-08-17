$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/operacion-dtic/';
    let id = EMPTY;

    $('#codigo').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
    }).on('blur', function () {
        if (this.value !== '') this.value = this.value.padStart(2, '0');
    });

    $('#btnMostrarCrear').on('click', () => mostrarFormulario());
    $('#btnCancelar').on('click', ocultarFormulario);

    $('#btnGuardar').on('click', async function () {
        $('#codigo').trigger('blur');

        const datos = new FormData();
        datos.append('idOperacion', id);
        datos.append('idObjEspecifico', operacionDtic_s2Objetivo.val() || '');
        datos.append('idIndicadorEstrategico', operacionDtic_s2IndicadorEstrategico.val() || '');
        datos.append('idIndicadorPoa', operacionDtic_s2IndicadorPoa.val() || '');
        datos.append('codigo', $('#codigo').val());
        datos.append('descripcion', $('#descripcion').val());
        datos.append('primerTrimestre', $('#primerTrimestre').val());
        datos.append('segundoTrimestre', $('#segundoTrimestre').val());
        datos.append('tercerTrimestre', $('#tercerTrimestre').val());
        datos.append('cuartoTrimestre', $('#cuartoTrimestre').val());

        await ajaxPromise({
            url: baseUrl + (id === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Operación guardada correctamente.',
            reloadTable: dt_operacionDtic
        });
    });

    $('#tablaOperacionesDtic').on('click', '.btn-edit', async function () {
        const row = dt_operacionDtic.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: formData('idOperacion', row.IdOperacion)
        });
        const data = response.data;

        id = data.IdOperacion;
        await operacionDticS2Ready;
        operacionDtic_s2Objetivo.val(data.IdObjEspecifico).trigger('change.select2');
        operacionDtic_s2IndicadorEstrategico
            .val(data.IdIndicadorEstrategico || null)
            .trigger('change.select2');
        await operacionDticCargarIndicadoresPoa(data.IdIndicadorPoa || null);

        $('#codigo').val(data.Codigo);
        $('#descripcion').val(data.Descripcion);
        $('#primerTrimestre').val(data.PrimerTrimestre);
        $('#segundoTrimestre').val(data.SegundoTrimestre);
        $('#tercerTrimestre').val(data.TercerTrimestre);
        $('#cuartoTrimestre').val(data.CuartoTrimestre);
        mostrarFormulario(false);
    });

    $('#tablaOperacionesDtic').on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_operacionDtic.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: formData('idOperacion', row.IdOperacion),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });

        cambiarEstadoBtnDtic(btn, response.data);
    });

    $('#tablaOperacionesDtic').on('click', '.btn-delete', function () {
        const row = dt_operacionDtic.row($(this).closest('tr')).data();

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación',
            text: '¿Eliminar la operación?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;

            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: formData('idOperacion', row.IdOperacion),
                successMsg: 'Operación eliminada.',
                reloadTable: dt_operacionDtic
            });
        });
    });

    function mostrarFormulario(limpiar = true) {
        if (limpiar) limpiarFormulario();
        $('#divTabla').hide(300);
        $('#divDatos').show(300);
    }

    function ocultarFormulario() {
        limpiarFormulario();
        $('#divDatos').hide(300);
        $('#divTabla').show(300);
    }

    function limpiarFormulario() {
        id = EMPTY;
        $('#formOperacionDtic').trigger('reset');
        operacionDtic_s2Objetivo.val(null).trigger('change');
        operacionDtic_s2IndicadorEstrategico.val(null).trigger('change');
        operacionDtic_s2IndicadorPoa.val(null).trigger('change');
    }

    function formData(name, value) {
        const data = new FormData();
        data.append(name, value);
        return data;
    }
});
