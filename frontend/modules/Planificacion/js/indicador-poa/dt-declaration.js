let dt_indicadorPoa = null;

$(document).ready(function () {
    dt_indicadorPoa = $('#tablaListaIndicadoresPoa').DataTable({
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
            url: 'index.php?r=Planificacion/indicador-poa/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje(
                    'error',
                    GenerarMensajeError(data.message || 'No se pudieron cargar los indicadores POA.'),
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
            {data: 'Codigo', visible: false},
            {
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') return row.Descripcion;

                    const indicador = row.indicador || {};
                    const unidad = indicador.catUnidadesIndicadores || {};
                    const resultado = indicador.catTiposResultados || {};
                    const categoria = indicador.catCategoriasIndicadores || {};

                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Indicador POA N°</span>
                            <div class="dtic-code-badge">${row.Codigo}</div>
                        </div>
                        <div class="dtic-item-main">${row.Descripcion}</div>
                        <div class="dtic-item-sub">
                            <b>Línea base:</b> ${row.LineaBase}
                            &nbsp;&nbsp;
                            <b>Meta:</b> ${row.Meta}
                        </div>
                        <div class="acc-footer mt-2" style="display:flex; gap:10px; flex-wrap:wrap">
                            <span class="badge-result">${unidad.Descripcion || ''}</span>
                            <span class="badge-result">${resultado.Descripcion || ''}</span>
                            <span class="badge-result">${categoria.Descripcion || ''}</span>
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
                data: 'IdIndicador',
                className: 'text-center',
                width: '140px',
                orderable: false,
                searchable: false,
                render: () => `
                    <button class="btn-action btn-edit" title="Editar">
                        <i class="fa fa-pen"></i>
                    </button>
                    <button class="btn-action btn-delete" title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                `
            }
        ]
    });

    $(document).on('click', '#refreshTable', () => dt_indicadorPoa.ajax.reload());
});
