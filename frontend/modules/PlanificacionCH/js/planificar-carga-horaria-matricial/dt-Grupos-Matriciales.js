let tableTeoriaMatricial
let tablePracticaMatricial
let tableLaboratorioMatricial

let dataGruposMatricial = {};
let layoutGruposMatricial
let ajaxGruposMatricial
let columnsGruposMatricial

let initCompleteMatricial
let createdRowsMatricial

var chDocente
$(document).ready(function () {

    layoutGruposMatricial = {
        topStart: null,
        topEnd: null ,
        bottomStart: null,
        bottomEnd: null
    }

    ajaxGruposMatricial = {
        method: "POST",
        data: function ( d ) {
            return  $.extend(d, dataGruposMatricial);
        },
        dataType: 'json',
        cache: false,
        url: 'index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/listar-grupos',
        dataSrc: 'grupos',
        error: function (xhr, ajaxOptions, thrownError) {
            MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
        }
    }

    /*initCompleteMatricial = function initComplete(settings, json){
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
    }*/

    /*createdRowsMatricial = function createdRow(row, data, rowIndex) {
        let chv = 0
        let che = 0
        let cha = 0

        let subtotalMateria = 0
        let group = '<ul class="list-group">'

        let grupocarreras = ''
        let grupomaterias = ''

        let carrera = ''
        let flag = false
        vigente.forEach(function (persona, index) {
            flag = true
            if (persona.IdPersona === $.trim(data.IdPersona))
            {
                if (carrera === '') {
                    carrera = persona.carrera
                    subtotalMateria = subtotalMateria + parseInt(persona.Ch)
                    grupomaterias = '<ul class="list-group">'
                    grupomaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona.materia + ' - ' + persona.nombremateria +
                        '        <span class="badge text-bg-primary rounded-pill">'+persona.Ch+'</span>' +
                        '        </li>'
                } else {
                    if (carrera !== persona.carrera ){
                        grupomaterias += '</ul>'
                        grupocarreras += '<li class="list-group-item   justify-content-between align-items-center">'+ carrera +
                            '<span class="badge badge-primary badge-pill">'+subtotalMateria+'</span>'+
                            grupomaterias +
                            '</li>'
                        grupomaterias = '<ul class="list-group">'
                        grupomaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona.materia + ' - ' + persona.nombremateria +
                            '        <span class="badge text-bg-primary rounded-pill">'+persona.Ch+'</span>' +
                            '        </li>'
                        subtotalMateria =  parseInt(persona.Ch)
                    } else {
                        subtotalMateria = subtotalMateria + parseInt(persona.Ch)
                        grupomaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona.materia + ' - ' + persona.nombremateria +
                            '        <span class="badge text-bg-primary rounded-pill">'+persona.Ch+'</span>' +
                            '        </li>'

                    }
                }

                chv = chv + parseInt(persona.Ch)
            }
        });
        if (flag){
            grupomaterias += '</ul>'
            grupocarreras += '<li class="list-group-item   justify-content-between align-items-center">'+ carrera +
                '<span class="badge badge-primary badge-pill">'+subtotalMateria+'</span>'+
                grupomaterias +
                '</li></ul>'
        }

        group += grupocarreras + '</ul>'

        agregada.forEach(function (persona, index) {
            if (persona.IdPersona === $.trim(data.IdPersona))
                cha = persona.Ch
        });
        eliminada.forEach(function (persona, index) {
            if (persona.IdPersona === $.trim(data.IdPersona))
                che = persona.Ch
        });
        $(row).find('td:eq(2)')
            .attr('data-bs-toggle', 'popover')
            .attr('data-bs-trigger', 'focus')
            .attr('data-bs-placement', 'top')
            .attr('data-bs-html', true)
            //.attr('data-bs-container', 'body')
            .attr('data-bs-title', data.NombreMateria + '<a  class="close" data-dismiss="alert">&times;</a>')
            .attr('data-html', true)
            .attr('data-bs-content', '' +
                '<div class="container mt-1 d-flex justify-content-center"> ' +
                '  <div class="card card2 p-3"> ' +
                '    <div class="d-flex align-items-center"> ' +
                '      <div class="image"> ' +
                '        <img src="http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_' + $.trim(data.IdPersona) + '.jpg" class="rounded" width="155"> ' +
                '      </div>' +
                '      <div class="ml-3 w-100"> ' +
                '        <h4 class="mb-0 mt-0">' + data.Nombre + '</h4> ' +
                '        <span>Docente</span> ' +
                '        <div class="p-2 mt-2 bg-primary d-flex justify-content-between rounded text-white stats"> ' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="rating">Ch. vigente</span> ' +
                '            <span class="number3">' + chv + '</span> ' +
                '          </div>' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="followers">Ch. Eliminada</span> ' +
                '            <span class="number2">' + che + '</span> ' +
                '          </div>' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="rating">Ch. Agregada</span> ' +
                '            <span class="number3">' + cha + '</span> ' +
                '          </div>' +
                '        </div>' +
                '        <div class="button mt-2 d-flex flex-row align-items-center"> ' +
                '          <button class="btn btn-sm btn-outline-primary w-100">Chat</button> ' +
                '          <button class="btn btn-sm btn-primary w-100 ml-2">Follow</button> ' +
                '        </div>' +
                '      </div>' +
                '    </div>' +

                group+


                '  </div>' +
                '</div>')
            .attr('tabindex', 0)

        switch (data.CodigoEstado) {
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
    }*/

    columnsGruposMatricial = [
        {
            className: 'dt-small dt-center',
            orderable: false,
            searchable: false,
            data: 'CGA'
        },
        {
            className: 'dt-small dt-center',
            data: 'IdPersona',
            render: function (data, type, row) {
                if (row.CodigoEstado == 'C') {
                    let texto = row.Observaciones.split(',')
                    return texto[1] + ' -> ' + data
                } else {
                    return data
                }

            }
        },
        {
            className: 'dt-small',
            data: 'Nombre',
            render: function (data, type, row) {
                return '<div>'+data+'</div><div style="color: salmon">'+row.NombreCortoCarrera+'</div>'
            }
        },
        {
            className: 'dt-small dt-center',
            data: 'Grupo',
            render: function (data, type, row) {
                if (row.CodigoEstado == 'C') {
                    let texto = row.Observaciones.split(',')
                    return texto[0] + ' -> ' + data
                } else {
                    return data
                }

            }
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
            data: 'Abandonos',
        },
        {
            className: 'dt-small dt-center',
            data: 'CantidadProyeccion',
            render: function (data, type, row) {
                let eliminados = 0
                let table
                let proyectado

                switch(row.TipoGrupo) {
                    case 'T':
                        eliminados = $('#tablaTeoriaMatricial tbody tr.eliminado').length
                        table = $('#tablaTeoriaMatricial').DataTable();
                        proyectado = proyT
                        break;
                    case 'P':
                        eliminados = $('#tablaPracticaMatricial tbody tr.eliminado').length
                        table = $('#tablaPracticaMatricial').DataTable();
                        proyectado = proyP
                        break;
                    case 'L':
                        eliminados = $('#tablaLaboratorioMatricial tbody tr.eliminado').length
                        table = $('#tablaLaboratorioMatricial').DataTable();
                        proyectado = proyL
                        break;
                    default:
                    // code block
                }
                var table_length = table.data().count();
                return (proyectado/(table_length-eliminados)).toFixed(2)
            }
        },
        {
            className: 'dt-small dt-acciones dt-center',
            orderable: false,
            searchable: false,
            //visible: ( ($('#nivel').val() === "1") && ($('#envio').val() === "0")) ? true : false,
            data: 'CodigoCarrera',
            render: function (data, type, row) {
                let button
                switch  (row.CodigoEstado){
                    case 'V':
                        button ='<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" carrera="' + row.CodigoCarrera + '" plan="' + row.NumeroPlanEstudios + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                            '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" carrera="' + row.CodigoCarrera + '" plan="' + row.NumeroPlanEstudios + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" estado="' + row.CodigoEstado + '" data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'E':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado" carrera="' + row.CodigoCarrera + '" plan="' + row.NumeroPlanEstudios + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" estado="' + row.CodigoEstado + '" data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
                        break
                    case 'A':
                        button ='<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" carrera="' + row.CodigoCarrera + '" plan="' + row.NumeroPlanEstudios + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" estado="' + row.CodigoEstado + '" data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'C':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado" carrera="' + row.CodigoCarrera + '" plan="' + row.NumeroPlanEstudios + '" grupo="' + row.Grupo + '" tipoGrupo="' + row.TipoGrupo + '" estado="' + row.CodigoEstado + '" data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
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


    dataGruposMatricial.flag = 0

    dataGruposMatricial.tipoGrupo = 'T'
    tableTeoriaMatricial = $('#tablaTeoriaMatricial').DataTable({
        layout: layoutGruposMatricial,
        fixedColumns: true,
        autoWidth: false,
        fixedHeader: true,
        paging: false,
        scrollCollapse: true,
        scrollY: '500px',
        ajax: ajaxGruposMatricial,
        columns: columnsGruposMatricial,
        createdRow: createdRowsMatricial,
        initComplete: initCompleteMatricial,
    })

    dataGruposMatricial.tipoGrupo = 'L'
    tableLaboratorioMatricial = $('#tablaLaboratorioMatricial').DataTable({
        layout: layoutGruposMatricial,
        fixedColumns: true,
        autoWidth: false,
        fixedHeader: true,
        paging: false,
        scrollCollapse: true,
        scrollY: '500px',
        ajax: ajaxGruposMatricial,
        columns: columnsGruposMatricial,
        createdRow: createdRowsMatricial,
        initComplete: initCompleteMatricial,
    })

    dataGruposMatricial.tipoGrupo = 'P'
    tablePracticaMatricial = $('#tablaPracticaMatricial').DataTable({
        layout: layoutGruposMatricial,
        fixedColumns: true,
        autoWidth: false,
        fixedHeader: true,
        paging: false,
        scrollCollapse: true,
        scrollY: '500px',
        ajax: ajaxGruposMatricial,
        columns: columnsGruposMatricial,
        createdRow: createdRowsMatricial,
        initComplete: initCompleteMatricial,
    })
})