$(document).ready(function () {
    let table
    let tabla
    let gestiones
    let vamosaver

    $('#facultades').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una facultad",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-facultades',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.facultades
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
    })

    $('#carreras').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una carrera",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    facultad: $('#facultades').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-carreras',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.carreras
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
    })

    $('#sedes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una sede",
        allowClear: true,
    })

    $('#planes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un plan",
        allowClear: true,
    })

    $('#cursos').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un curso",
        allowClear: true,
    })

    $('#facultades').change(function () {
        $('#divCarreras').attr('hidden',true)
        $('#rowDos').attr('hidden',true)
        $('#divSedes').attr('hidden',true)
        $('#divPlanes').attr('hidden',true)
        $('#divCursos').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)
        $('#divTabla').attr('hidden',true)

        facultad = $(this).val()

        if (facultad != '') {
            $('#carreras').val(null).trigger('change')
            $('#divCarreras').attr('hidden',false)
        }
    })

    $('#carreras').change(function () {
        $('#rowDos').attr('hidden',true)
        $('#divSedes').attr('hidden',true)
        $('#divPlanes').attr('hidden',true)
        $('#divCursos').attr('hidden',true)
        $('#divTabla').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)

        carrera = $(this).val()
        let datos = new FormData()
        datos.append('carrera',carrera)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-sedes",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (carrera != ''){
                    $("#sedes").empty().append(data);
                    $('#divSedes').attr('hidden',false)
                    $('#rowDos').attr('hidden',false)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "No se pudo listar las sedes de la carrera",
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                }).then(function() {
                    $('#rowDos').attr('hidden',true)
                    $('#divSedes').attr('hidden',true)
                    $('#divPlanes').attr('hidden',true)
                    $('#divCursos').attr('hidden',true)
                    $('#divTabla').attr('hidden',true)
                    $('#divConfiguracion').attr('hidden',true)
                })
            }
        });
    })

    $("#sedes").change(function () {
        $('#divPlanes').attr('hidden',true)
        $('#divCursos').attr('hidden',true)
        $('#divTabla').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)

        let codigoCarrera = $("#carreras").val();
        let sede = $(this).val();

        let datos = new FormData();
        datos.append("carrera", codigoCarrera);
        datos.append("sede", sede);

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-planes-estudios",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (sede != ''){
                    $("#planes").empty().append(data);
                    $('#divPlanes').attr('hidden',false)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "No se pudo listar las sedes de la carrera",
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                }).then(function() {
                    $('#divPlanes').attr('hidden',true)
                    $('#divCursos').attr('hidden',true)
                    $('#divTabla').attr('hidden',true)
                    $('#divConfiguracion').attr('hidden',true)
                })
            }
        });
    });

    $("#planes").change(function () {
        $('#divCursos').attr('hidden',true)
        $('#divTabla').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)

        let codigoCarrera = $("#carreras").val();
        let numeroPlanEstudios = $(this).val();
        let datos = new FormData();
        datos.append("codigocarrera", codigoCarrera);
        datos.append("numeroplanestudios", numeroPlanEstudios);
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-cursos",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (numeroPlanEstudios != ''){
                    $("#cursos").empty().append(data);
                    $('#divCursos').attr('hidden',false)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "No se pudo listar las sedes de la carrera",
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                }).then(function() {
                    $('#divCursos').attr('hidden',true)
                    $('#divTabla').attr('hidden',true)
                    $('#divConfiguracion').attr('hidden',true)
                })
            }
        });
    });


    function format(d) {
        return (
            '<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" >\n' +
            '  <li class="nav-item" role="presentation">\n' +
            '    <button class="nav-link active" id="pills-teoria-tab" data-bs-toggle="pill" data-bs-target="#pills-teoria" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Grupos Teoria</button>\n' +
            '  </li>\n' +
            '  <li class="nav-item" role="presentation">\n' +
            '    <button class="nav-link" id="pills-laboratorio-tab" data-bs-toggle="pill" data-bs-target="#pills-laboratorio" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Grupos Laboratorio</button>\n' +
            '  </li>\n' +
            '  <li class="nav-item" role="presentation">\n' +
            '    <button class="nav-link" id="pills-practica-tab" data-bs-toggle="pill" data-bs-target="#pills-practica" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Grupos Practica</button>\n' +
            '  </li>\n' +
            '</ul>\n' +
            '<div class="tab-content" id="pills-tabContent">\n' +
            '  <div class="tab-pane fade show active" id="pills-teoria" role="tabpanel" aria-labelledby="pills-home-tab">' +
            '               <table id="tablaTeoria" class="table table-bordered  dt-responsive" style="width: 100%" >' +
            '                    <thead>' +
            '                    <th>#</th>' +
            '                    <th>IdPersona</th>' +
            '                    <th>Nombre Docente</th>' +
            '                    <th>Grupo</th>' +
            '                    <th>Hrs.Teo</th>' +
            '                    <th>Prog.</th>' +
            '                    <th>Aprobados</th>' +
            '                    <th>Reprobados</th>' +
            '                    <th>Abandonos</th>' +
            '                    <th>Proy.</th>' +
            '                    <th>Accion</th>' +
            '                    </thead>\n' +
            '                </table>' +
            '   </div>' +
            '  <div class="tab-pane fade" id="pills-laboratorio" role="tabpanel" aria-labelledby="pills-profile-tab">' +
                '         <table id="tablaLaboratorio" class="table table-bordered  dt-responsive" style="width: 100%" >' +
            '                    <thead>' +
            '                    <th>IdPersona</th>' +
            '                    <th>Nombre Docente</th>' +
            '                    <th>Grupo</th>' +
            '                    <th>Hrs.Lab</th>' +
            '                    <th>Prog.</th>' +
            '                    <th>Aprobados</th>' +
            '                    <th>Reprobados</th>' +
            '                    <th>Abandonos</th>' +
            '                    <th>Proy.</th>' +
            '                    <th>Accion</th>' +
            '                    </thead>\n' +
            '            </table>' +
            '</div>' +
            '  <div class="tab-pane fade" id="pills-practica" role="tabpanel" aria-labelledby="pills-contact-tab">' +
            '<table id="tablaPractica" class="table table-bordered  dt-responsive" style="width: 100%" >' +
            '                    <thead>' +
            '                    <th>IdPersona</th>' +
            '                    <th>Nombre Docente</th>' +
            '                    <th>Grupo</th>' +
            '                    <th>Hrs.Prac</th>' +
            '                    <th>Prog.</th>' +
            '                    <th>Aprobados</th>' +
            '                    <th>Reprobados</th>' +
            '                    <th>Abandonos</th>' +
            '                    <th>Proy.</th>' +
            '                    <th>Accion</th>' +
            '                    </thead>\n' +
            '            </table>' +
            '</div>' +
            '</div>'
        );
    }


    $('#cursos').change(function () {
        $('#divConfiguracion').attr('hidden',false)
        $('#divTabla').attr('hidden',false)

        let carrera = $("#carreras").val()
        let curso = $("#cursos").val()
        let plan = $("#planes").val()
        let gestion = '1/2021'

        if (table){table.destroy();}

        table = $("#tablaMaterias").DataTable({
            layout: {
                topStart: null,
                topEnd: null ,
                bottomStart: null,
                bottomEnd: null
            },
            ajax: {
                method: "POST",
                data: {
                    carrera: carrera,
                    curso: curso,
                    plan: plan,
                    gestion: gestion
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

    })

    $(document).on('click','#tablaMaterias tbody td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = table.row(tr);

        if (row.child.isShown()) {
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
            row.child.hide();
        }
        else {
            $("#tablaMaterias  tr.shown").each(function () {
                let rowOpen = table.row($(this));
                let tdiOpen = $(this).find("i.fa");
                $(this).removeClass('shown');
                tdiOpen.first().removeClass('fa-minus-square');
                tdiOpen.first().addClass('fa-plus-square');
                rowOpen.child.hide();
            });

            tr.addClass('shown');
            tdi.first().removeClass('fa-plus-square');
            tdi.first().addClass('fa-minus-square');
            row.child(format(row.data())).show();
            llenarTablas(row.data().SiglaMateria)
        }
    })

    function llenarTablas(sigla){
        let carrera = $("#carreras").val()
        let curso = $("#cursos").val()
        let plan = $("#planes").val()
        let gestion = '1/2021'
        let datos = new FormData();
        datos.append("carrera", carrera);
        datos.append("curso", curso);
        datos.append("plan", plan);
        datos.append("gestion", gestion);
        datos.append("sigla", sigla);

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-grupos",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                   vamosaver =  $('#tablaTeoria').dataTable({
                        layout: {
                            topStart: null,
                            topEnd: null ,
                            bottomStart: null,
                            bottomEnd: null
                        },
                        "data": data.teoria,
                        columns: [
                            {
                                className: 'dt-small dt-center',
                                orderable: false,
                                searchable: false,
                                data: 'CodigoCarrera',
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
                                data: 'HorasTeoria'
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
                                render: function (data, type) {
                                    return type === 'display'
                                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                                        '</div>'
                                        : data;
                                },
                            },
                        ],
                    })

                    $('#tablaLaboratorio').dataTable({
                        layout: {
                            topStart: null,
                            topEnd: null ,
                            bottomStart: null,
                            bottomEnd: null
                        },
                        "data": data.laboratorio,
                        columns: [
                            {
                                className: 'dt-small',
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
                            {
                                className: 'dt-small dt-acciones dt-center',
                                orderable: false,
                                searchable: false,
                                data: 'CodigoCarrera',
                                render: function (data, type) {
                                    return type === 'display'
                                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                                        '</div>'
                                        : data;
                                },
                            },
                        ],
                    })

                    $('#tablaPractica').dataTable({
                        layout: {
                            topStart: null,
                            topEnd: null ,
                            bottomStart: null,
                            bottomEnd: null
                        },
                        "data": data.practica,
                        columns: [
                            {
                                className: 'dt-small',
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
                                data: 'HorasPractica'
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
                                render: function (data, type) {
                                    return type === 'display'
                                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                                        '</div>'
                                        : data;
                                },
                            },
                        ],
                    })
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        })
    }


    $(document).on('click', '#tablaMaterias tbody .btnProgramar', function(){
        let objectBtn = $(this)
        let facultad = objectBtn.attr("facultad");
        let carrera = objectBtn.attr("carrera");
        let sede = objectBtn.attr("sede");
        let sigla = objectBtn.attr("sigla");
        let curso = objectBtn.attr("curso");
        let plan = objectBtn.attr("plan");
        let datos = new FormData();
        datos.append('facultad',facultad)
        datos.append('carrera',carrera)
        datos.append('sede',sede)
        datos.append('sigla',sigla)
        datos.append('curso',curso)
        datos.append('plan',plan)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-encabezado-modal",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (rta) {
                if (rta.respuesta === RTA_CORRECTO) {
                    $('#inpFacultad').val(rta.data.NombreFacultad + ' - ' + rta.data.NombreCarrera)
                    $('#inpSede').val(rta.data.NombreSede)
                    $('#inpPlan').val(rta.data.NumeroPlanesEstudios)
                    $('#inpCurso').val(rta.data.curso)
                    $('#inpMateria').val(rta.data.SiglaMateria + ' - ' + rta.data.NombreMateria)
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        }).done(function (){
            if (tabla) {tabla.destroy()}
            tabla = $("#tablaGrpTeoria").DataTable({
                layout: {
                    topStart: null,
                    topEnd: null ,
                    bottomStart: null,
                    bottomEnd: null
                },
                ajax: {
                    method: "POST",
                    data: function ( d ) {
                        d.facultad = facultad
                        d.carrera =carrera
                        d.sede = sede
                        d.sigla = sigla
                        d.curso = curso
                        d.plan = plan
                    },
                    dataType: 'json',
                    cache: false,
                    url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-grupos-materias',
                    dataSrc: '',
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(GenerarMensajeError( thrownError + ' >' +xhr.responseText)))
                    }
                },
                columns: [
                    {
                        className: 'dt-small dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'CodigoCarrera',
                        width: 30
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'SiglaMateria'
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'NombreMateria',
                        width: 200
                    },
                    {
                        className: 'dt-small',
                        data: 'Grupo',
                    },
                    {
                        className: 'dt-small',
                        data: 'IdPersona',
                    },
                    {
                        className: 'dt-small dt-acciones dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'NumeroPlanEstudios',
                        render: function (data, type, row) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-warning btn-sm btnProgramarM data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                                '</div>'
                                : data;
                        },
                    },
                ],
            });

        })

        $('#modalPlanificar').modal('show');
    })

    $('#rrrr').click(function(){
        let a
        $("#oso").each(function () {
            a = $(this).val()
            console.log(a)
        });

    })





});

