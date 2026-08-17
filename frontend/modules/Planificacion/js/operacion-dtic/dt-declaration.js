let dt_operacionDtic = null;

$(document).ready(function () {
    dt_operacionDtic = $('#tablaOperacionesDtic').DataTable({
        initComplete: function () {
            $('div.dt-search').append(`
                <button id="refreshTable" class="btn-refresh">
                    <i class="fas fa-sync-alt fa-spin"></i>
                </button>
            `);
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(250);
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/operacion-dtic/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje(
                    'error',
                    GenerarMensajeError(data.message || 'No se pudieron cargar las operaciones.'),
                    data.errors
                );
            }
        },
        columns: [
            {
                data: 'CodigoUsuario',
                className: 'text-center',
                width: '60px',
                orderable: false,
                searchable: false,
                render: data => `<div class="badge-codigo">${data}</div>`
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return `${row.Codigo} ${row.Descripcion}`;
                    }

                    const indicadores = [
                        row.IndicadorEstrategicoDescripcion
                            ? `<span class="badge-result">Estratégico: ${row.IndicadorEstrategicoDescripcion}</span>`
                            : '',
                        row.IndicadorPoaDescripcion
                            ? `<span class="badge-result">POA: ${row.IndicadorPoaDescripcion}</span>`
                            : ''
                    ].join('');

                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Operación</span>
                            <div class="dtic-code-badge">${row.CodigoCompuesto || row.Codigo || ''}</div>
                        </div>
                        <div class="dtic-item-main">${row.Descripcion || ''}</div>
                        <div class="dtic-item-sub">
                            Objetivo: ${row.CompuestoObj || ''} - ${row.Objetivo || ''}
                        </div>
                        <div class="dtic-item-sub">${row.Producto || ''}</div>
                        <div class="acc-footer mt-2">${indicadores}</div>
                    `;
                }
            },
            {
                data: null,
                className: 'text-center',
                width: '260px',
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return row.PrimerTrimestre + row.SegundoTrimestre
                            + row.TercerTrimestre + row.CuartoTrimestre;
                    }

                    return `
                        <div class="trimestres-grid">
                            <span>T1: <b>${row.PrimerTrimestre}</b></span>
                            <span>T2: <b>${row.SegundoTrimestre}</b></span>
                            <span>T3: <b>${row.TercerTrimestre}</b></span>
                            <span>T4: <b>${row.CuartoTrimestre}</b></span>
                        </div>
                    `;
                }
            },
            {
                data: 'CodigoEstado',
                className: 'text-center',
                width: '90px',
                orderable: false,
                searchable: false,
                render: function (data, type) {
                    if (type !== 'display') return data;
                    return data === ESTADO_VIGENTE
                        ? `<button type="button" class="estado-on btn-toggle-estado">
                               <span class="btn_ico"><i class="fas fa-check-circle"></i></span>
                               <span class="btn_text">Vigente</span>
                           </button>`
                        : `<button type="button" class="estado-off btn-toggle-estado">
                               <span class="btn_ico"><i class="fas fa-times-circle"></i></span>
                               <span class="btn_text">Caducado</span>
                           </button>`;
                }
            },
            {
                data: 'IdOperacion',
                className: 'text-center',
                width: '160px',
                orderable: false,
                searchable: false,
                render: () => `
                    <button class="btn-action btn-items" title="Asignar ítems">
                        <i class="fa fa-boxes"></i>
                    </button>
                    <button class="btn-action btn-edit"><i class="fa fa-pen"></i></button>
                    <button class="btn-action btn-delete"><i class="fa fa-trash"></i></button>
                `
            }
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_operacionDtic.ajax.reload());
});
