let tableMateriasMatriciales
let dataMateriasMatriciales = {};
$(document).ready(function () {
    dataMateriasMatriciales.flag = 0
    dataMateriasMatriciales.gestion = ''
    dataMateriasMatriciales.carrera = ''
    dataMateriasMatriciales.sede = ''
    dataMateriasMatriciales.curso = ''
    dataMateriasMatriciales.plan = ''

    tableMateriasMatriciales = $("#tablaMateriasMatriciales").DataTable({
        layout: {
            topStart: null,
            topEnd: null ,
            bottomStart: null,
            bottomEnd: null
        },
        pageLength : 50,
        ajax: {
            method: "POST",
            data: function ( d ) {
                return  $.extend(d, dataMateriasMatriciales);
            },
            dataType: 'json',
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/listar-materias',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
        columns: [
            {
                className: 'dt-small details-control dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
                },
                width: 30,
            },
            {
                className: 'dt-small',
                data: 'SiglaMateria'
            },
            {
                className: 'dt-small',
                data: 'NombreMateria'
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasTeoria'
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasPractica'
            },
            {
                className: 'dt-small dt-center',
                data: 'HorasLaboratorio'
            },
            {
                className: 'dt-small dt-center',
                data: 'Programados'
            },
            {
                className: 'dt-small dt-center',
                data: 'Aprobados'
            },
            {
                className: 'dt-small dt-center',
                data: 'Reprobados'
            },
            {
                className: 'dt-small dt-center',
                data: 'Abandonos'
            },
            {
                className: 'dt-small dt-center',
                data: 'CantidadProyeccion'
            },
        ],
    });
})