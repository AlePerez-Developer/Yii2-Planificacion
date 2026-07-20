let dt_objInstitucional;

$(document).ready(function () {
    dt_objInstitucional = $('#tablaListaObjInstitucionales').DataTable({
        initComplete: function () {
            $('div.dt-search').append(
                '<button type="button" id="refresh" class="btn btn-outline-primary ml-2" ' +
                'data-toggle="tooltip" title="Recargar tabla">' +
                '<i class="fa fa-recycle"></i></button>'
            );

            $('#dticTableLoading').hide();
            $('#dticTableContainer').show();
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/obj-institucional/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje(
                    'error',
                    GenerarMensajeError(data.message || data.mensaje || 'No se pudo cargar la información.'),
                    data.errors || null
                );
                dt_objInstitucional.processing(false);
            }
        },
        columns: [
            {
                title: '#',
                data: null,
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                width: 35
            },
            {
                title: 'Código',
                data: 'Compuesto',
                className: 'dt-small dt-center',
                width: 120
            },
            {
                title: 'Objetivo estratégico',
                data: 'ObjetivoEstrategico',
                className: 'dt-small'
            },
            {
                title: 'Objetivo institucional',
                data: 'Objetivo',
                className: 'dt-small'
            },
            {
                title: 'Producto esperado',
                data: 'Producto',
                className: 'dt-small'
            },
            {
                title: 'Gestión',
                data: 'Gestion',
                className: 'dt-small dt-center',
                width: 75
            },
            {
                data: "CodigoEstado",
                className: "text-center",
                width: "65px",
                orderable: false,
                searchable: false,
                visible: true,
                render: function (data, type, row) {
                    return ((type === 'display') && (row["CodigoEstado"] === ESTADO_VIGENTE))
                        ? '<button type="button" class="estado-on btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-check-circle"></i></span>' +
                        '    <span class="btn_text">Vigente</span>' +
                        '  </button>'
                        : '<button type="button" class="estado-off btn-toggle-estado" data-toggle="tooltip" title="Click! para cambiar el estado del registro">' +
                        '    <span class="btn_ico"><i class="fas fa-times-circle"></i></span>' +
                        '    <span class="btn_text">Caducado</span>' +
                        '  </button>';
                },
            },
            {
                title: 'Acciones',
                data: 'IdObjInstitucional',
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                width: 100,
                render: function (data, type) {
                    if (type !== 'display') {
                        return data;
                    }

                    return '<div class="btn-group" role="group">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm btn-edit" title="Editar">' +
                        '<i class="fa fa-pen-fancy"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm btn-delete" title="Eliminar">' +
                        '<i class="fa fa-trash-alt"></i></button>' +
                        '</div>';
                }
            }
        ],
        order: [[1, 'asc']]
    });

    dt_objInstitucional.on('order.dt search.dt draw.dt', function () {
        let numero = 1;
        dt_objInstitucional
            .cells(null, 0, {search: 'applied', order: 'applied'})
            .every(function () {
                this.data(numero++);
            });
    });
});
