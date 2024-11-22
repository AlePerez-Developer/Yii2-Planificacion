$(document).ready(function () {
    let table
    let tabla
    let gestiones

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
                    q: params.term, // search term
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
        let datos = new FormData()
        datos.append('facultad',facultad)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-carreras",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (facultad != '') {
                    $("#carreras").empty().append(data);
                    $('#divCarreras').attr('hidden',false)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "No se pudo listar las carreras de la facultad",
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                }).then(function() {
                    $('#divCarreras').attr('hidden',true)
                    $('#rowDos').attr('hidden',true)
                    $('#divSedes').attr('hidden',true)
                    $('#divPlanes').attr('hidden',true)
                    $('#divCursos').attr('hidden',true)
                    $('#divConfiguracion').attr('hidden',true)
                    $('#divTabla').attr('hidden',true)
                })
            }
        });
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
            '<ul class="nav nav-tabs" id="myTab" role="tablist">\n' +
            '                                <li class="nav-item" role="presentation">\n' +
            '                                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#teoria" type="button" role="tab" aria-controls="home" aria-selected="true">Grupos de Teoria</button>\n' +
            '                                </li>\n' +
            '                                <li class="nav-item" role="presentation">\n' +
            '                                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#practica" type="button" role="tab" aria-controls="profile" aria-selected="false">Grupos de Practica</button>\n' +
            '                                </li>\n' +
            '                                <li class="nav-item" role="presentation">\n' +
            '                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#laboratorio" type="button" role="tab" aria-controls="contact" aria-selected="false">Grupos de Laboratorio</button>\n' +
            '                                </li>\n' +
            '                            </ul>\n' +
            '                            <div class="tab-content" id="myTabContent">\n' +
            '                                <div class="tab-pane fade show active" id="teoria" role="tabpanel" aria-labelledby="home-tab">\n' +
            '                                    <div class="card">\n' +
            '                                        <div class="card-header">\n' +
            '                                            <button id="btnTeoria" type="button" class="btn btn-info form-control">Agregar Grupo Nuevo</button>\n' +
            '                                        </div>\n' +
            '                                        <div class="card-body">\n' +
            '                                            <table id="tablaGrpTeoria" class="table table-bordered table-striped dt-responsive " style="width: 100%" >\n' +
            '                                                <thead>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">#</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Sigla</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Grupo</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Ci</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Docente</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Obs</th>\n' +
            '                                                    <th style="text-align: center; vertical-align: middle;">Editar</th>\n' +
            '                                                </thead>\n' +
            '                                            </table>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '\n' +
            '                                </div>\n' +
            '                                <div class="tab-pane fade" id="practica" role="tabpanel" aria-labelledby="profile-tab">\n' +
            '\n' +
            '                                </div>\n' +
            '                                <div class="tab-pane fade" id="laboratorio" role="tabpanel" aria-labelledby="contact-tab">\n' +
            '\n' +
            '                                </div>\n' +
            '                            </div>'
        );
    }


    $('#cursos').change(function () {
        $('#divConfiguracion').attr('hidden',false)
        $('#divTabla').attr('hidden',false)

        let carrera = $("#carreras").val()
        let curso = $("#cursos").val()
        let plan = $("#planes").val()
        let gestion = '1/2022'

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
                    className: 'dt-small dt-control dt-center',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '',
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
                    data: 'Prog'
                },
                {
                    className: 'dt-small dt-center',
                    data: 'Proy'
                },
                {
                    className: 'dt-small dt-acciones dt-center',
                    orderable: false,
                    searchable: false,
                    data: 'CodigoCarrera',
                    render: function (data, type, row) {
                        return type === 'display'
                            ? '<div class="btn-group" role="group" aria-label="Acciones">' +
                            '<button type="button" class="btn btn-outline-primary btn-sm btnProgramar" facultad =  "' + $('#facultades').val() + '" carrera="' + data + '" sede = "' + row.CodigoSede + '"  plan="' + row.NumeroPlanEstudios + '" curso="' + row.Curso + '" sigla =  "' + row.SiglaMateria + '"  data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                            '</div>'
                            : data;
                    },
                },
            ],
        });

    })

    $(document).on('click','#tablaMaterias tbody td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    })

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





});

