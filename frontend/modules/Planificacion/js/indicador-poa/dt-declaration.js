let dt_indicadorPoa = null;

function inicializarTablaIndicadoresPoa() {
    dt_indicadorPoa = PlanificacionDataTable.crear('#tablaListaIndicadoresPoa', {
        ajax: PlanificacionDataTable.ajax({
            url: 'index.php?r=Planificacion/indicador-poa/listar-todo'
        }),
        columns: [
            {title: 'Objetivo específico', data: 'ObjEspecificoCompuesto', className: 'text-center'},
            {title: 'Código', data: 'Codigo', className: 'text-center'},
            {title: 'Descripción', data: 'Descripcion'},
            {title: 'Meta', data: 'Meta', className: 'text-center'},
            {title: 'Tipo', data: 'Tipo', className: 'text-center'},
            {title: 'Categoría', data: 'Categoria', className: 'text-center'},
            {title: 'Unidad', data: 'Unidad', className: 'text-center'},
            {
                title: 'Estado', data: 'CodigoEstado', className: 'text-center',
                render: function (data, type) {
                    if (type !== 'display') return data;
                    return data === ESTADO_VIGENTE
                        ? '<button class="btn btn-sm btn-success btn-toggle-estado">Vigente</button>'
                        : '<button class="btn btn-sm btn-secondary btn-toggle-estado">Caducado</button>';
                }
            },
            {
                title: 'Acciones', data: null, orderable: false, className: 'text-center',
                render: () => `
                    <button class="btn btn-sm btn-primary btn-edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>`
            }
        ]
    });
}
