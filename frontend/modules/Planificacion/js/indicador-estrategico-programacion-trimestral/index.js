$(document).ready(function () {
    programacionTrimestral_s2ObjEstrategico.on('change', function () {
        const idObjEstrategico = $(this).val();

        openedRowTrimestral = null;
        $('#mensajeInicial').hide();

        if (!idObjEstrategico) {
            $('#dticTableContainer').hide();
            $('#dticTableLoading').hide();
            $('#mensajeInicial').show();
            return;
        }

        $('#dticTableLoading').show();
        $('#dticTableContainer').hide();

        if (dt_listaIndicadoresTrimestrales === null) {
            inicializarTablaIndicadoresTrimestrales(idObjEstrategico);
        } else {
            dt_listaIndicadoresTrimestrales.ajax.reload();
        }

        dt_listaIndicadoresTrimestrales.one('draw', function () {
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(180);
        });
    });
});

function cargarTabGestionActiva(indicador) {
    const contenedor = $(`#tabs_container_${indicador.IdIndicadorEstrategico}`);
    const loader = $(`#loader_${indicador.IdIndicadorEstrategico}`);

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/obtener-gestion-activa',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            const gestion = response.data;
            const tableId = `tbl_trimestral_${indicador.Codigo}_${gestion.Gestion}`;
            const paneId = `pane_trimestral_${indicador.Codigo}_${gestion.Gestion}`;

            const html = `
                <ul class="nav nav-pills nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active dtic-tab" type="button" role="tab">
                            Gestión ${gestion.Gestion}
                            <span id="estado_${tableId}" class="meta-badge bg-danger ms-2">Pendiente</span>
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="${paneId}" role="tabpanel">
                        <div class="table-responsive table-container mt-3">
                            <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100">
                                <thead>
                                    <tr>
                                        <th>Código compuesto</th>
                                        <th>Descripción</th>
                                        <th>Meta Trim.</th>
                                        <th>1er Trim.</th>
                                        <th>2do Trim.</th>
                                        <th>3er Trim.</th>
                                        <th>4to Trim.</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>`;

            loader.remove();
            contenedor.html(html).fadeIn(180);
            inicializarTablaProgramacionTrimestral(indicador.IdIndicadorEstrategico, tableId);
        },
        error: function (xhr) {
            loader.remove();
            const data = JSON.parse(xhr.responseText);
            contenedor.html('<div class="alert alert-danger">No fue posible obtener la gestión activa.</div>').show();
            MostrarMensaje('error', GenerarMensajeError(data.message), data.errors);
        }
    });
}

function inicializarTablaProgramacionTrimestral(idIndicadorEstrategico, tableId) {
    const selector = `#${tableId}`;

    $(selector).DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/listar-programacion',
            method: 'POST',
            dataType: 'json',
            data: {idIndicadorEstrategico: idIndicadorEstrategico},
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText);
                MostrarMensaje('error', GenerarMensajeError(data.message), data.errors);
            }
        },
        columns: [
            {data: 'CodigoCompuesto', className: 'dt-small'},
            {data: 'Descripcion', className: 'dt-small'},
            {data: 'MetaProgramada', className: 'dt-center meta-programada', width: 110},
            crearColumnaTrimestre(1, 'MetaPrimerTrimestre'),
            crearColumnaTrimestre(2, 'MetaSegundoTrimestre'),
            crearColumnaTrimestre(3, 'MetaTercerTrimestre'),
            crearColumnaTrimestre(4, 'MetaCuartoTrimestre'),
            {
                data: 'TotalTrimestral',
                className: 'dt-center total-trimestral',
                width: 90,
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    return `<span class="total-badge ${row.ProgramacionCompleta == 1 ? 'completa' : 'pendiente'}">${data}</span>`;
                }
            }
        ],
        createdRow: function (row, data) {
            actualizarEstadoFila($(row), data);
        },
        drawCallback: function () {
            actualizarEstadoTab(tableId, this.api());
        },
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        responsive: false,
        autoWidth: false
    });
}

function crearColumnaTrimestre(numero, atributo) {
    return {
        data: atributo,
        className: 'dt-center',
        width: 110,
        render: function (data, type, row) {
            if (type !== 'display') return data;

            return `
                <input type="number"
                       min="0"
                       step="1"
                       readonly
                       class="form-control form-control-sm input-meta-trimestre"
                       value="${data}"
                       data-original="${data}"
                       data-trimestre="${numero}"
                       data-idprogramacion="${row.IdProgramacionIndicadorGestio}">`;
        }
    };
}

$(document).on('click', '.input-meta-trimestre[readonly]', function () {
    $(this)
        .prop('readonly', false)
        .data('original', $(this).val())
        .focus()
        .select();
});

$(document).on('keydown', '.input-meta-trimestre:not([readonly])', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        guardarMetaTrimestral($(this));
    }

    if (event.key === 'Escape') {
        $(this).val($(this).data('original')).prop('readonly', true);
    }
});

$(document).on('blur', '.input-meta-trimestre:not([readonly])', function () {
    guardarMetaTrimestral($(this));
});

function guardarMetaTrimestral(input) {
    if (input.data('guardando')) return;

    const tabla = input.closest('table');

    const dt_table = tabla.DataTable();
    const tr = input.closest('tr')
    const dt_row = dt_table.row(tr).data();


    const meta = Number.parseInt(input.val(), 10);
    const original = Number.parseInt(input.data('original'), 10);

    if (!Number.isInteger(meta) || meta < 0) {
        input.val(original).prop('readonly', true);
        MostrarMensaje('warning', 'La meta debe ser un número entero mayor o igual a cero.');
        return;
    }

    if (meta === original) {
        input.prop('readonly', true);
        return;
    }

    input.data('guardando', true).prop('disabled', true);

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/guardar-meta',
        method: 'POST',
        dataType: 'json',
        data: {
            idProgramacionIndicadorGestio: input.data('idprogramacion'),
            trimestre: input.data('trimestre'),
            meta: meta
        },
        success: function (response) {
            const datos = response.data;
            const table = input.closest('table').DataTable();
            const row = table.row(input.closest('tr'));
            const rowData = row.data();

            rowData.MetaPrimerTrimestre = datos.MetaPrimerTrimestre;
            rowData.MetaSegundoTrimestre = datos.MetaSegundoTrimestre;
            rowData.MetaTercerTrimestre = datos.MetaTercerTrimestre;
            rowData.MetaCuartoTrimestre = datos.MetaCuartoTrimestre;
            rowData.TotalTrimestral = datos.TotalTrimestral;
            rowData.ProgramacionCompleta = datos.ProgramacionCompleta;

            row.data(rowData).invalidate().draw(false);
        },
        error: function (xhr) {
            input.val(original);
            const data = JSON.parse(xhr.responseText);
            MostrarMensaje('error', GenerarMensajeError(data.message), data.errors);
        },
        complete: function () {
            input.data('guardando', false).prop('disabled', false).prop('readonly', true);
            actualizarEstadoFila(tr,dt_row)
        }
    });
}

function actualizarEstadoFila(tr, data) {
    tr.removeClass('programacion-completa programacion-pendiente')
        .addClass(data.ProgramacionCompleta == 1 ? 'programacion-completa' : 'programacion-pendiente');
}

function actualizarEstadoTab(tableId, api) {
    let completa = true;
    let existenFilas = false;

    api.rows({page: 'current'}).every(function () {
        existenFilas = true;
        if (this.data().ProgramacionCompleta != 1) {
            completa = false;
        }
    });

    const badge = $(`#estado_${tableId}`);
    badge.removeClass('bg-success bg-danger');

    if (existenFilas && completa) {
        badge.addClass('bg-success').text('Completa');
    } else {
        badge.addClass('bg-danger').text('Pendiente');
    }
}
