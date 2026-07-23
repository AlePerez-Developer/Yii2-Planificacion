let dt_programacionPoaTrimestral = null;

function inicializarTablaIndicadoresPoaProgramacion() {
    dt_programacionPoaTrimestral = PlanificacionDataTable.crear('#tablaListaIndicadoresPoaProgramacion', {
        planificacion: {loader: true},
        ajax: PlanificacionDataTable.ajax({
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-indicadores',
            data: function (d) {
                d.idObjEspecifico = programacionPoaTrimestral_s2ObjEspecifico.val() || '00000000-0000-0000-0000-000000000000';
            }
        }),
        columns: [
            {
                title: '', data: null, orderable: false, className: 'expandible text-center',
                defaultContent: '<i class="fas fa-plus-circle"></i>'
            },
            {title: 'Código', data: 'Codigo', className: 'text-center'},
            {title: 'Descripción', data: 'Descripcion'},
            {title: 'Meta', data: 'Meta', className: 'text-center'},
            {title: 'Tipo', data: 'Tipo', className: 'text-center'},
            {title: 'Categoría', data: 'Categoria', className: 'text-center'},
            {title: 'Unidad', data: 'Unidad', className: 'text-center'}
        ]
    });
}
