var materiasTable
var teoriaTable
var practicaTable
var laboratorioTable

var materiasData ={};

var dataGrupos = {}
var layoutGrupos
var ajaxGrupos
var columnsGrupos
$(document).ready(function () {
    materiasData.carrera = ''
    materiasData.curso = ''
    materiasData.plan = ''
    materiasData.gestion = '1/2021'

    materiasTable = $("#tablaMaterias").DataTable({
        layout: {
            topStart: null,
            topEnd: null ,
            bottomStart: null,
            bottomEnd: null
        },
        ajax: {
            method: "POST",
            data: function ( d ) {
                return  $.extend(d, materiasData);
            },
            dataType: 'json',
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-materias',
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
                select: {
                    selector:'td:not(:first-child)',
                    style:    'os'
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


    layoutGrupos = {
        topStart: null,
        topEnd: null ,
        bottomStart: null,
        bottomEnd: null
    }

    ajaxGrupos = {
        method: "POST",
            data: function ( d ) {
            return  $.extend(d, dataGrupos);
        },
        dataType: 'json',
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-grupos',
            dataSrc: 'grupos',
            error: function (xhr, ajaxOptions, thrownError) {
            MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
        }
    }

    columnsGrupos = [
        {
            className: 'dt-small dt-center',
            orderable: false,
            searchable: false,
            data: 'MGA',
            width: 30
        },
        {
            className: 'dt-small dt-center',
            data: 'IdPersona'
        },
        {
            className: 'dt-small',
            data: 'Nombre'
        },
        {
            className: 'dt-small dt-center',
            data: 'Grupo'
        },
        {
            className: 'dt-small dt-center',
            data: 'HorasSemana'
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
        {
            className: 'dt-small dt-acciones dt-center',
            orderable: false,
            searchable: false,
            data: 'CodigoCarrera',
            render: function (data, type, row) {
                let button
                console.log(row.CodigoEstado)
                switch  (row.CodigoEstado){
                    case 'V':
                        button ='<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                                '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'E':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
                        break
                    default: button = ''
                }
                console.log(button)
                return type === 'display'
                    ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        button +
                    '</div>'
                    : data;
            },
        },
    ]



    teoriaTable =  $('.tablaTeoria').dataTable({
        layout: {
            topStart: null,
            topEnd: null ,
            bottomStart: null,
            bottomEnd: null
        },
        pageLength : 20,
        ajax: {
            method: "POST",
            data: function ( d ) {
                return  $.extend(d, teoriaData);
            },
            dataType: 'json',
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-grupos',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
        createdRow: function (row, data) {
            if (data.CodigoEstado == 'E' ) {
                $(row).addClass('eliminado');
            }
        },
    })

})

