let tableTeoria
let tablePractica
let tableLaboratorio

let dataGrupos = {};

let layoutGrupos
let ajaxGrupos
let columnsGrupos

let createdRows
let initComplete
$(document).ready(function () {
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

    initComplete = function initComplete(settings, json){
        $(this).DataTable().on('order.dt search.dt', function () {
            var i = 1;
            $(this).DataTable()
                .cells(null, 0, { search: 'applied', order: 'applied' })
                .every(function (cell) {
                    this.data(i++);
                });
        }).draw();

        document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
            .forEach(popover => {
                new bootstrap.Popover(popover)
            })
    }

    createdRows = function createdRow(row, data, rowIndex){
        $( row ).find('td:eq(2)')
            .attr('data-bs-toggle', 'popover')
            .attr('data-bs-trigger', 'focus')
            .attr('data-bs-placement', 'top')
            .attr('data-bs-html', true)
            .attr('data-bs-title', data.NombreMateria)
            .attr('data-html',true)
            .attr('data-bs-content','<div class="container">' +
                '<div class="row" style="display:inline;">' +
                '<div class="col-sm-2 bg-info" style="width:20px; display:inline;">x</div>' +
                '<div class="col-sm-2" style="display:inline;">1 a 5</div>'+
                '</div><br>'+
                '<div class="row" style="display:inline;">'+
                '<div class="col-sm-2 bg-warning" style="width:20px; display:inline;">x</div>'+
                '<div class="col-sm-2" style="display:inline;">6 a 8</div>'+
                '</div><br>'+
                '<div class="row" style="display:inline;">'+
                '<div class="col-sm-2 bg-danger" style="width:20px; display:inline;">x</div>'+
                '<div class="col-sm-2" style="display:inline;">9 a 10</div>'+
                '</div></div>')
            .attr('tabindex', 0)

        switch (data.CodigoEstado){
            case 'E':
                $(row).addClass('eliminado');
                $(row).removeClass('agregado');
                $(row).removeClass('vigente');
                $(row).removeClass('editado');
                break
            case 'V':
                $(row).addClass('vigente');
                $(row).removeClass('agregado');
                $(row).removeClass('eliminado');
                $(row).removeClass('editado');
                break
            case 'A':
                $(row).addClass('agregado');
                $(row).removeClass('vigente');
                $(row).removeClass('eliminado');
                $(row).removeClass('editado');
                break
            case 'C':
                $(row).addClass('editado');
                $(row).removeClass('vigente');
                $(row).removeClass('agregado');
                $(row).removeClass('eliminado');
                break
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
                switch  (row.CodigoEstado){
                    case 'V':
                        button ='<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                            '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'E':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
                        break
                    case 'A':
                        button ='<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" carrera="' + data + '" plan="' + row.NumeroPlanEstudios + '" sigla="' + row.SiglaMateria + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    default: button = ''
                }
                return type === 'display'
                    ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                    button +
                    '</div>'
                    : data;
            },
        },
    ]

})