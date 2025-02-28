let dataMaterias = {};
let tableMaterias
$(document).ready(function () {
    dataMaterias.flag = 0
    dataMaterias.carrera = ''
    dataMaterias.sede = ''
    dataMaterias.curso = ''
    dataMaterias.plan = ''

    tableMaterias = $("#tablaMaterias").DataTable({
        layout: {
            topStart: {
                search: {
                    placeholder: 'Buscar registros..',
                }
            },
            topEnd:'pageLength',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        pageLength : 50,
        ajax: {
            method: "POST",
            data: function ( d ) {
                return  $.extend(d, dataMaterias);
            },
            dataType: 'json',
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-materias',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            }
        },
        columns: [
            {
                className: 'dt-small details-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
                render: function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
                },
                width: 50,
            },
            {
                className: 'dt-small',
                data: 'SiglaMateria',
                width: 120
            },
            {
                className: 'dt-small',
                data: 'NombreMateria'
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasTeoria',
                width: 80
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasPractica',
                width: 80
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasLaboratorio',
                width: 80
            },

            {
                className: 'dt-small dt-center',
                data: 'ProgT',
                width: 80
            },
            {
                className: 'dt-small dt-center',
                data: 'ProgL',
                width: 90,
                visible: false
            },
            {
                className: 'dt-small dt-center',
                data: 'ProgP',
                width: 90,
                visible: false
            },

            {
                className: 'dt-small dt-center',
                data: 'AproT',
                width: 90
            },
            {
                className: 'dt-small dt-center',
                data: 'AproL',
                width: 90,
                visible: false
            },
            {
                className: 'dt-small dt-center',
                data: 'AproP',
                width: 90,
                visible: false
            },

            {
                className: 'dt-small dt-center',
                data: 'ReproT',
                width: 90
            },
            {
                className: 'dt-small dt-center',
                data: 'ReproL',
                width: 90,
                visible: false
            },{
                className: 'dt-small dt-center',
                data: 'ReproP',
                width: 90,
                visible: false
            },

            {
                className: 'dt-small dt-center',
                data: 'AbanT',
                width: 90
            },
            {
                className: 'dt-small dt-center',
                data: 'AbanL',
                width: 90,
                visible: false
            },
            {
                className: 'dt-small dt-center',
                data: 'AbanP',
                width: 90,
                visible: false
            },

            {
                className: 'dt-small dt-center',
                data: 'CantidadProyeccion',
                width: 90
            },
        ],
    });
})