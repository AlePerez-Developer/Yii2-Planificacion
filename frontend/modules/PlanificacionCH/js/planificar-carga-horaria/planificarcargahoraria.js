$(document).ready(function () {
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
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    carrera: $('#carreras').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-sedes',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.sedes
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
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

    $('#docentes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un docente",
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
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-docentes',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.docentes
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            },
        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    })

    function formatRepo (repo) {
        if (repo.loading) {
            return repo.text;
        }

        var $container = $(
            "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__avatar'><img src='http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_" + $.trim(repo.id) + ".jpg' /></div>" +
            "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title'></div>" +
            "<div class='select2-result-repository__description'></div>" +
            "</div>" +
            "</div>"
        );

        $container.find(".select2-result-repository__title").text(repo.text);
        $container.find(".select2-result-repository__description").text(repo.id);

        return $container;
    }

    function formatRepoSelection (repo) {
        return repo.full_name || repo.text;
    }



    $('#facultades').change(function () {
        $('#divCarreras').attr('hidden',true)
        $('#rowDos').attr('hidden',true)
        $('#divSedes').attr('hidden',true)
        $('#divPlanes').attr('hidden',true)
        $('#divCursos').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != '') {
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

        if ($(this).val() != ''){
            $('#sedes').val(null).trigger('change')
            $('#divSedes').attr('hidden',false)
            $('#rowDos').attr('hidden',false)
        }
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
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "T" class="form-control btn btn-info btnCrear">Crear Grupo de Teoria</button></div></div>' +
            '               <table id="tablaTeoria" class="table table-bordered  dt-responsive tablaTeoria" style="width: 100%" >' +
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
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "L" class="form-control btn btn-info btnCrear">Crear Grupo de Laboratorio</button></div></div>' +
            '            <table id="tablaLaboratorio" class="table table-bordered  dt-responsive" style="width: 100%" >' +
            '                    <thead>' +
            '                    <th>#</th>' +
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
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "P" class="form-control btn btn-info btnCrear">Crear Grupo de Practica</button></div></div>' +
            '           <table id="tablaPractica" class="table table-bordered  dt-responsive" style="width: 100%" >' +
            '                    <thead>' +
            '                    <th>#</th>' +
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
        $('#divTabla').attr('hidden',true)
        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)
            materiasData.carrera = $("#carreras").val()
            materiasData.curso = $("#cursos").val()
            materiasData.plan = $("#planes").val()
            materiasData.gestion = '1/2021'
            materiasTable.ajax.reload()
        }
    })

    $(document).on('click','#tablaMaterias tbody td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = materiasTable.row(tr);

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


        dataGrupos.carrera = $("#carreras").val()
        dataGrupos.curso = $("#cursos").val()
        dataGrupos.plan = $("#planes").val()
        dataGrupos.sigla = sigla
        dataGrupos.gestion = '1/2021'
        dataGrupos.tipoGrupo = 'T'

        teoriaTable = $('#tablaTeoria').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: function (row, data) {
                $( row ).find('td:eq(2)')
                    .attr('data-toggle', 'tooltip')
                    .attr('data-placement', 'top')
                    .attr('title', 'carga horaria docente')

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
            },
            initComplete: function (settings, json) {
                $('#tablaTeoria').DataTable().on('order.dt search.dt', function () {
                        var i = 1;
                        $('#tablaTeoria').DataTable()
                            .cells(null, 0, { search: 'applied', order: 'applied' })
                            .every(function (cell) {
                                this.data(i++);
                            });
                }).draw();
            }
        })

        dataGrupos.tipoGrupo = 'L'
        laboratorioTable = $('#tablaLaboratorio').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: function (row, data) {
                $( row ).find('td:eq(2)')
                    .attr('data-toggle', 'tooltip')
                    .attr('data-placement', 'top')
                    .attr('title', 'carga horaria docente')

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
            },
            initComplete: function (settings, json) {
                $('#tablaLaboratorio').DataTable().on('order.dt search.dt', function () {
                    var i = 1;
                    $('#tablaLaboratorio').DataTable()
                        .cells(null, 0, { search: 'applied', order: 'applied' })
                        .every(function (cell) {
                            this.data(i++);
                        });
                }).draw();
            }
        })

        dataGrupos.tipoGrupo = 'P'
        practicaTable = $('#tablaPractica').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: function (row, data) {
                $( row ).find('td:eq(2)')
                    .attr('data-toggle', 'tooltip')
                    .attr('data-placement', 'top')
                    .attr('title', 'carga horaria docente')

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
            },
            initComplete: function (settings, json) {
                $('#tablaPractica').DataTable().on('order.dt search.dt', function () {
                    var i = 1;
                    $('#tablaPractica').DataTable()
                        .cells(null, 0, { search: 'applied', order: 'applied' })
                        .every(function (cell) {
                            this.data(i++);
                        });
                }).draw();
            }
        })
    }


    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this);
        let carrera = objectBtn.attr("carrera");
        let plan = objectBtn.attr("plan");
        let sigla = objectBtn.attr("sigla");
        let grupo = objectBtn.attr("grupo");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let datos = new FormData();
        datos.append("carrera", carrera);
        datos.append("plan", plan);
        datos.append("sigla", sigla);
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);

        dataGrupos.tipoGrupo = tipoGrupo
        Swal.fire({
            icon: "warning",
            title: "Confirmación",
            text: "¿Está seguro de eliminar/habilitar el grupo seleccionado?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Continuar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=PlanificacionCH/planificar-carga-horaria/cambiar-estado-grupo",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','El estado del grupo se cambio de forma correcta.')
                            switch (tipoGrupo){
                                case 'T': $("#tablaTeoria").DataTable().ajax.reload();
                                    break
                                case 'L': $("#tablaLaboratorio").DataTable().ajax.reload();
                                    break
                                case 'P': $("#tablaPractica").DataTable().ajax.reload();
                                    break
                            }
                            DetenerSpiner(objectBtn)
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                            DetenerSpiner(objectBtn)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                        DetenerSpiner(objectBtn)
                    }
                });
            }
        });
    })

    $(document).on('click', '.btnCrear', function(){
        $('#modalPlanificar').modal('show')
    })
});


