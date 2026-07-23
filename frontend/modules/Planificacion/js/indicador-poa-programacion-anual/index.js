$(document).ready(function () {
    let openedRow = null;
    inicializarTablaIndicadoresPoaProgramacion();

    programacionPoaAnual_s2ObjEspecifico.on('change', function () {
        if (openedRow && openedRow.child.isShown()) closeRow(openedRow);
        openedRow = null;
        PlanificacionDataTable.recargar(dt_programacionPoaAnual, false);
    });

    $('#tablaListaIndicadoresPoaProgramacion tbody').on('click', 'td.expandible', function () {
        const tr = $(this).closest('tr');
        const row = dt_programacionPoaAnual.row(tr);

        if (row.child.isShown()) {
            closeRow(row);
            openedRow = null;
            return;
        }

        if (openedRow && openedRow.child.isShown()) closeRow(openedRow);

        const data = row.data();
        const tableId = `tabla_programacion_poa_${data.IdIndicadorPoa}`;
        row.child(`
            <div class="slider" style="display:none">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item"><button class="nav-link active dtic-tab">Gestión activa</button></li>
                </ul>
                <div class="table-responsive mt-3">
                    <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100"></table>
                </div>
            </div>`, 'no-padding').show();

        tr.addClass('shown');
        $('div.slider', row.child()).hide().stop(true, true).slideDown(180);
        openedRow = row;
        crearTablaProgramacion(data.IdIndicadorPoa, tableId);
    });

    function crearTablaProgramacion(idIndicadorPoa, tableId) {
        PlanificacionDataTable.crear(`#${tableId}`, {
            planificacion: {refresh: false, loader: false},
            ajax: PlanificacionDataTable.ajax({
                url: 'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-programacion',
                data: {idIndicadorPoa}
            }),
            columns: [
                {title: 'Código compuesto', data: 'CodigoCompuesto'},
                {title: 'Descripción', data: 'Descripcion'},
                {
                    title: 'Meta programada', data: 'MetaProgramada', className: 'text-center',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        return `<input type="number" min="0" step="1" readonly
                            class="form-control form-control-sm input-meta-poa-anual"
                            value="${data}" data-original="${data}"
                            data-idindicador="${idIndicadorPoa}">`;
                    }
                }
            ],
            paging: false, searching: false, info: false, ordering: false, responsive: false
        });
    }

    $(document).on('click', '.input-meta-poa-anual[readonly]', function () {
        $(this).prop('readonly', false).data('original', $(this).val()).focus().select();
    });

    $(document).on('keydown', '.input-meta-poa-anual:not([readonly])', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); guardar($(this)); }
        if (e.key === 'Escape') $(this).val($(this).data('original')).prop('readonly', true);
    });

    $(document).on('blur', '.input-meta-poa-anual:not([readonly])', function () {
        guardar($(this));
    });

    function guardar(input) {
        if (input.data('guardando')) return;
        const meta = parseInt(input.val(), 10);
        const original = parseInt(input.data('original'), 10);

        if (!Number.isInteger(meta) || meta < 0) {
            input.val(original).prop('readonly', true);
            MostrarMensaje('warning', 'La meta debe ser un entero mayor o igual a cero.');
            return;
        }
        if (meta === original) {
            input.prop('readonly', true);
            return;
        }

        input.data('guardando', true).prop('disabled', true);

        $.ajax({
            url: 'index.php?r=Planificacion/indicador-poa-programacion-anual/guardar-meta',
            method: 'POST',
            dataType: 'json',
            data: {idIndicadorPoa: input.data('idindicador'), meta},
            success: response => input.val(response.data.MetaProgramada).data('original', response.data.MetaProgramada),
            error: manejarErrorDataTable,
            complete: () => input.data('guardando', false).prop('disabled', false).prop('readonly', true)
        });
    }

    function closeRow(row) {
        const slider = $('div.slider', row.child());
        slider.stop(true, true).slideUp(160, function () {
            row.child.hide();
            $(row.node()).removeClass('shown');
        });
    }
});
