let dt_objEspecifico = null;

function inicializarTablaObjEspecificos() {
    dt_objEspecifico = PlanificacionDataTable.crear('#tablaListaObjEspecificos', {
        ajax: PlanificacionDataTable.ajax({
            url: 'index.php?r=Planificacion/obj-especifico/listar-todo'
        }),
        columns: [
            {title: 'Código', data: 'Compuesto', className: 'text-center'},
            {title: 'Objetivo institucional', data: 'ObjetivoInstitucional'},
            {title: 'Objetivo específico', data: 'Objetivo'},
            {title: 'Producto', data: 'Producto'},
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
