$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/indicador-poa/';
    let id = EMPTY;

    inicializarTablaIndicadoresPoa();

    // Reutiliza aquí exactamente las funciones que llenan Tipo, Categoría y Unidad
    // del CRUD IndicadorEstrategico.
    if (typeof populateS2TiposIndicador === 'function') populateS2TiposIndicador($('#tipo'));
    if (typeof populateS2CategoriasIndicador === 'function') populateS2CategoriasIndicador($('#categoria'));
    if (typeof populateS2UnidadesIndicador === 'function') populateS2UnidadesIndicador($('#unidad'));

    $('#btnMostrarCrear').on('click', () => mostrarFormulario());
    $('#btnCancelar').on('click', () => ocultarFormulario());

    $('#btnGuardar').on('click', async function () {
        const datos = new FormData();
        datos.append('idIndicadorPoa', id);
        datos.append('idObjEspecifico', indicadorPoa_s2ObjEspecifico.val());
        datos.append('codigo', $('#codigo').val());
        datos.append('descripcion', $('#descripcion').val());
        datos.append('meta', $('#meta').val());
        datos.append('tipo', $('#tipo').val());
        datos.append('categoria', $('#categoria').val());
        datos.append('unidad', $('#unidad').val());

        await ajaxPromise({
            url: baseUrl + (id === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Indicador POA guardado correctamente.',
            reloadTable: dt_indicadorPoa
        });
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-edit', async function () {
        const row = dt_indicadorPoa.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: fd('idIndicadorPoa', row.IdIndicadorPoa)
        });
        const data = response.data;
        id = data.IdIndicadorPoa;
        indicadorPoa_s2ObjEspecifico.val(data.IdObjEspecifico).trigger('change');
        $('#codigo').val(data.Codigo);
        $('#descripcion').val(data.Descripcion);
        $('#meta').val(data.Meta);
        $('#tipo').val(data.Tipo).trigger('change');
        $('#categoria').val(data.Categoria).trigger('change');
        $('#unidad').val(data.Unidad).trigger('change');
        mostrarFormulario(false);
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_indicadorPoa.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: fd('idIndicadorPoa', row.IdIndicadorPoa),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtn(btn, response.data);
    });

    $('#tablaListaIndicadoresPoa').on('click', '.btn-delete', function () {
        const row = dt_indicadorPoa.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning', title: 'Confirmación', text: '¿Eliminar el indicador POA?',
            showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: fd('idIndicadorPoa', row.IdIndicadorPoa),
                successMsg: 'Indicador POA eliminado.',
                reloadTable: dt_indicadorPoa
            });
        });
    });

    function fd(name, value) {
        const data = new FormData();
        data.append(name, value);
        return data;
    }

    function mostrarFormulario(limpiar = true) {
        if (limpiar) {
            id = EMPTY;
            $('#formIndicadorPoa').trigger('reset');
            indicadorPoa_s2ObjEspecifico.val(null).trigger('change');
        }
        $('#divTabla').hide(300);
        $('#divDatos').show(300);
    }

    function ocultarFormulario() {
        id = EMPTY;
        $('#divDatos').hide(300);
        $('#divTabla').show(300);
    }
});
