$(document).ready(function () {
    let openedRow = null;
    inicializarTablaIndicadoresPoaProgramacion();

    programacionPoaTrimestral_s2ObjEspecifico.on('change', function () {
        if (openedRow && openedRow.child.isShown()) closeRow(openedRow);
        openedRow = null;
        PlanificacionDataTable.recargar(dt_programacionPoaTrimestral, false);
    });

    $('#tablaListaIndicadoresPoaProgramacion tbody').on('click', 'td.expandible', function () {
        const tr = $(this).closest('tr');
        const row = dt_programacionPoaTrimestral.row(tr);

        if (row.child.isShown()) {
            closeRow(row);
            openedRow = null;
            return;
        }

        if (openedRow && openedRow.child.isShown()) closeRow(openedRow);

        const data = row.data();
        const tableId = `tabla_trimestres_poa_${data.IdIndicadorPoa}`;
        row.child(`
            <div class="slider" style="display:none">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item">
                        <button class="nav-link active dtic-tab">
                            Gestión activa <span id="estado_${tableId}" class="meta-badge bg-danger ml-2">Pendiente</span>
                        </button>
                    </li>
                </ul>
                <div class="table-responsive mt-3">
                    <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100"></table>
                </div>
            </div>`, 'no-padding').show();

        tr.addClass('shown');
        $('div.slider', row.child()).hide().stop(true, true).slideDown(180);
        openedRow = row;
        crearTabla(data.IdIndicadorPoa, tableId);
    });

    function crearTabla(idIndicadorPoa, tableId) {
        PlanificacionDataTable.crear(`#${tableId}`, {
            planificacion: {refresh: false, loader: false},
            ajax: PlanificacionDataTable.ajax({
                url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-programacion',
                data: {idIndicadorPoa}
            }),
            columns: [
                {title: 'Código compuesto', data: 'CodigoCompuesto'},
                {title: 'Descripción', data: 'Descripcion'},
                {title: 'Meta programada', data: 'MetaProgramada', className: 'text-center'},
                trimestre(1, 'MetaPrimerTrimestre', '1er trimestre'),
                trimestre(2, 'MetaSegundoTrimestre', '2do trimestre'),
                trimestre(3, 'MetaTercerTrimestre', '3er trimestre'),
                trimestre(4, 'MetaCuartoTrimestre', '4to trimestre'),
                {
                    title: 'Total', data: 'TotalTrimestral', className: 'text-center',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        return `<span class="total-badge ${row.ProgramacionCompleta == 1 ? 'completa' : 'pendiente'}">${data}</span>`;
                    }
                }
            ],
            createdRow: (row, data) => pintarFila($(row), data),
            drawCallback: function () { actualizarTab(tableId, this.api()); },
            paging: false, searching: false, info: false, ordering: false, responsive: false
        });
    }

    function trimestre(numero, atributo, titulo) {
        return {
            title: titulo, data: atributo, className: 'text-center',
            render: function (data, type, row) {
                if (type !== 'display') return data;
                return `<input type="number" min="0" step="1" readonly
                    class="form-control form-control-sm input-meta-poa-trimestre"
                    value="${data}" data-original="${data}"
                    data-trimestre="${numero}"
                    data-idprogramacion="${row.IdProgramacionIndicadorPoaGestion}">`;
            }
        };
    }

    $(document).on('click', '.input-meta-poa-trimestre[readonly]', function () {
        $(this).prop('readonly', false).data('original', $(this).val()).focus().select();
    });

    $(document).on('keydown', '.input-meta-poa-trimestre:not([readonly])', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); guardar($(this)); }
        if (e.key === 'Escape') $(this).val($(this).data('original')).prop('readonly', true);
    });

    $(document).on('blur', '.input-meta-poa-trimestre:not([readonly])', function () {
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
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/guardar-meta',
            method: 'POST',
            dataType: 'json',
            data: {
                idProgramacionIndicadorPoaGestion: input.data('idprogramacion'),
                trimestre: input.data('trimestre'),
                meta
            },
            success: function (response) {
                const table = input.closest('table').DataTable();
                const row = table.row(input.closest('tr'));
                const data = row.data();
                Object.assign(data, response.data);
                row.data(data).invalidate().draw(false);
            },
            error: manejarErrorDataTable,
            complete: () => input.data('guardando', false).prop('disabled', false).prop('readonly', true)
        });
    }

    function pintarFila(tr, data) {
        tr.removeClass('programacion-completa programacion-pendiente')
            .addClass(data.ProgramacionCompleta == 1 ? 'programacion-completa' : 'programacion-pendiente');
    }

    function actualizarTab(tableId, api) {
        let completa = true;
        let existen = false;
        api.rows().every(function () {
            existen = true;
            if (this.data().ProgramacionCompleta != 1) completa = false;
        });

        const badge = $(`#estado_${tableId}`).removeClass('bg-success bg-danger');
        badge.addClass(existen && completa ? 'bg-success' : 'bg-danger')
            .text(existen && completa ? 'Completa' : 'Pendiente');
    }

    function closeRow(row) {
        const slider = $('div.slider', row.child());
        slider.stop(true, true).slideUp(160, function () {
            row.child.hide();
            $(row.node()).removeClass('shown');
        });
    }
});
