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
            MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
        }
    }

    initComplete = function initComplete(){
        $(this).DataTable().on('order.dt search.dt', function () {
            let i = 1;
            $(this).DataTable()
                .cells(null, 0, { search: 'applied', order: 'applied' })
                .every(function () {
                    this.data(i++);
                });
        }).draw();

        document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
            .forEach(popover => {
                new bootstrap.Popover(popover)
            })
    }

    createdRows = function createdRow(row, data) {
        let edad = (new Date()).getFullYear() - (new Date(data['FechaNacimiento'])).getFullYear();

        $(row).find('td:eq(2)')
            .attr('data-bs-toggle', 'popover')
            .attr('data-bs-trigger', 'focus')
            .attr('data-bs-placement', 'top')
            .attr('data-bs-html', true)
            .attr('data-bs-title', data["NombreMateria"] + '<a  class="close btnClose" data-dismiss="alert">&times;</a>')
            .attr('data-html', true)
            .attr('data-bs-content', '' +
                '<div class="container mt-1 d-flex justify-content-center"> ' +
                '  <div class="card card2 p-3"> ' +
                '    <div class="d-flex align-items-center aver"> ' +
                '      <div class="image"> ' +
                '        <img src="http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_' + $.trim(data["IdPersona"]) + '.jpg" class="rounded" width="155" alt="Persona Image"> ' +
                '        <span>Edad: </span><span>'+edad+'</span></br>' +
                '        <span>Antiguedad: </span><span id="chAntiguedad"></span>' +
                '      </div>' +
                '      <div class="ml-3 w-100"> ' +
                '        <h5 class="mb-0 mt-0">' + data["Nombre"] + '</h5> ' +
                '        <span>Condicion Laboral: </span><span id="docCondicion" class="docCondicion"></span> </br> ' +
                '        <span>Carga Horario Real: </span><span id="chReal"></span> '  +
                '        <div class="p-2 mt-2 bg-primary d-flex justify-content-between rounded text-white stats"> ' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="chTitle">Ch. vigente</span> ' +
                '            <span class="chValue" id="chVigente">0</span> ' +
                '          </div>' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="chTitle">Ch. Eliminada</span> ' +
                '            <span class="chValue" id="chEliminada">0</span> ' +
                '          </div>' +
                '          <div class="d-flex flex-column"> ' +
                '            <span class="chTitle">Ch. Agregada</span> ' +
                '            <span class="chValue" id="chAgregada">0</span> ' +
                '          </div>' +
                '        </div>' +
                '      </div>' +
                '    </div>' +

                '    <div id="chDetallada"></div>'+

                '  </div>' +
                '</div>')
            .attr('tabindex', 0)

        switch (data["CodigoEstado"]) {
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
            data: 'IdPersona',
            render: function (data, type, row) {
                if (row["CodigoEstado"] === 'C') {
                    let texto = row["Observaciones"].split(',')
                    return texto[1] + ' -> ' + data
                } else {
                    return data
                }
            }
        },
        {
            className: 'dt-small',
            data: 'Nombre',
        },
        {
            className: 'dt-small dt-center',
            data: 'Grupo',
            render: function (data, type, row) {
                if (row["CodigoEstado"] === 'C') {
                    let texto = row["Observaciones"].split(',')
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
            data: 'Abandonos'
        },
        {
            className: 'dt-small dt-center',
            data: 'CantidadProyeccion',
            render: function (data, type, row) {
                let rta
                console.log('inicio------------------------------------------------------------')
                console.log(row['Grupo'] + ' ' + row['TipoGrupo'] + ' ' + type)
                if (type === 'display') {
                    console.log('display:_  ' + type)
                    /*let eliminados = 0
                    let table
                    switch(row["TipoGrupo"]) {
                        case 'T':
                            //eliminados = $('#tablaPractica tbody tr.eliminado').length
                            //table = $('#tablaPractica').DataTable();
                            break;
                        case 'P':
                            //eliminados = $('#tablaPractica tbody tr.eliminado').length
                            //table = $('#tablaPractica').DataTable();
                            break;
                        case 'L':
                            eliminados = $('#tablaLaboratorio tbody tr.eliminado').length
                            table = $('#tablaLaboratorio').DataTable();
                            break;
                        default:
                    }*/
                    //let table_length = table.data().count();
                   //return (data/(table_length-eliminados)).toFixed(2)
                }
                console.log('fin------------------------------------------------------------')
                return data
            }
        },
        {
            className: 'dt-small dt-acciones dt-center',
            orderable: false,
            searchable: false,
            visible: (($('#nivel').val() === "1") && ($('#envio').val() === "0")),
            data: 'CodigoCarrera',
            render: function (data, type, row) {
                let button
                switch  (row["CodigoEstado"]){
                    case 'V':
                        button ='<button type="button" class="btn btn-outline-warning btn-sm  btnEditar"  data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                            '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado"  data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'E':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado"  data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
                        break
                    case 'A':
                        button ='<button type="button" class="btn btn-outline-danger btn-sm  btnEstado"  data-toggle="tooltip" title="Click! para eliminar el grupo"><i class="fa fa-trash-alt"></i></button>'
                        break
                    case 'C':
                        button ='<button type="button" class="btn btn-outline-success btn-sm  btnEstado"  data-toggle="tooltip" title="Click! para habilitar el grupo"><i class="fa fa-history"></i></button>'
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