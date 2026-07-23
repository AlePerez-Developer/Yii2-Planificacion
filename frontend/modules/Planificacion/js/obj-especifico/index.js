$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/obj-especifico/';
    let id = EMPTY;



    $('#codigo').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
    }).on('blur', function () {
        if (this.value !== '') this.value = this.value.padStart(2, '0');
    });

    $('#btnMostrarCrear').on('click', () => {
        limpiar();
        $('#divTabla').hide(300);
        $('#divDatos').show(300);
    });

    $('#btnCancelar').on('click', () => {
        limpiar();
        $('#divDatos').hide(300);
        $('#divTabla').show(300);
    });

    $('#btnGuardar').on('click', async function () {
        $('#codigo').trigger('blur');
        const datos = new FormData();
        datos.append('idObjEspecifico', id);
        datos.append('idObjInstitucional', objEspecifico_s2ObjInstitucional.val());
        datos.append('codigo', $('#codigo').val());
        datos.append('objetivo', $('#objetivo').val());
        datos.append('producto', $('#producto').val());
        datos.append('formula', $('#formula').val());
        datos.append('descripcion', $('#descripcion').val());

        await ajaxPromise({
            url: baseUrl + (id === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Objetivo específico guardado correctamente.',
            reloadTable: dt_objEspecifico
        });
    });

    $('#tablaListaObjEspecificos').on('click', '.btn-edit', async function () {
        const row = dt_objEspecifico.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idObjEspecifico', row.IdObjEspecifico)
        });
        const data = response.data;
        id = data.IdObjEspecifico;
        objEspecifico_s2ObjInstitucional.val(data.IdObjInstitucional).trigger('change');
        $('#codigo').val(data.Codigo);
        $('#objetivo').val(data.Objetivo);
        $('#producto').val(data.Producto);
        $('#formula').val(data.Indicador_Formula);
        $('#descripcion').val(data.Indicador_Descripcion);
        $('#divTabla').hide(300);
        $('#divDatos').show(300);
    });

    $('#tablaListaObjEspecificos').on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_objEspecifico.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idObjEspecifico', row.IdObjEspecifico),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtnDtic(btn, response.data);
    });

    $('#tablaListaObjEspecificos').on('click', '.btn-delete', function () {
        const row = dt_objEspecifico.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning', title: 'Confirmación', text: '¿Eliminar el objetivo específico?',
            showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idObjEspecifico', row.IdObjEspecifico),
                successMsg: 'Objetivo específico eliminado.',
                reloadTable: dt_objEspecifico
            });
        });
    });

    function crearDatos(nombre, valor) {
        const fd = new FormData();
        fd.append(nombre, valor);
        return fd;
    }

    function limpiar() {
        id = EMPTY;
        $('#formObjEspecifico').trigger('reset');
        objEspecifico_s2ObjInstitucional.val(null).trigger('change');
    }
});
