$(document).ready(function () {

    $('#facultades').change(function () {
        $('#divMaterias').attr('hidden',true)
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != '') {
            $('#materias').val(null).trigger('change')
            $('#divMaterias').attr('hidden',false)
        }
    })

    $('#materias').change(function () {
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)

            dataMateriasMatriciales.materia = $('#materias').val()
            dataMateriasMatriciales.flag = 1

            tableMateriasMatriciales.ajax.reload()
        }
    })

    function format(d) {
        return (
            '<div style="background-color: white">'+
            '<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">\n' +
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
            '<div class="tab-content" id="pills-tabContent" >\n' +
            '  <div class="tab-pane fade show active" id="pills-teoria" role="tabpanel" aria-labelledby="pills-home-tab">' +
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "T" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Teoria</button></div></div>' +
            '    <div class="divGrupos">'+
            '               <table id="tablaTeoriaMatricial" class="table table-bordered  dt-responsive tablaTeoria" style="width: 100%" >' +
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
            '     </div>' +
            '   </div>' +
            '  <div class="tab-pane fade" id="pills-laboratorio" role="tabpanel" aria-labelledby="pills-profile-tab">' +
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "L" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Laboratorio</button></div></div>' +
            '    <div class="divGrupos">'+
            '            <table id="tablaLaboratorioMatricial" class="table table-bordered  dt-responsive" style="width: 100%" >' +
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
            '     </div>' +
            '</div>' +
            '  <div class="tab-pane fade" id="pills-practica" role="tabpanel" aria-labelledby="pills-contact-tab">' +
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "P" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Practica</button></div></div>' +
            '    <div class="divGrupos">'+
            '           <table id="tablaPracticaMatricial" class="table table-bordered  dt-responsive" style="width: 100%" >' +
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
            '     </div>' +
            '</div>' +
            '</div>'+
            '</div>'
        );
    }

    $(document).on('click','#tablaMateriasMatriciales tbody td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = tableMateriasMatriciales.row(tr);

        if (row.child.isShown()) {
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
            row.child.hide();
        }
        else {
            $("#tablaMateriasMatriciales  tr.shown").each(function () {
                let rowOpen = tableMateriasMatriciales.row($(this));
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

        let datos = new FormData();
        datos.append("gestion", $('#gestion').val());
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/mostrar",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    vigente = data.vigente
                    agregada = data.agregada
                    eliminada = data.eliminada
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        }).done(function (){
            dataGruposMatricial.gestion = $("#gestion").val()
            dataGruposMatricial.carrera = $("#carreras").val()
            dataGruposMatricial.curso = $("#cursos").val()
            dataGruposMatricial.plan = $("#planes").val()
            dataGruposMatricial.sede = $("#sedes").val()
            dataGruposMatricial.sigla = sigla

            dataGruposMatricial.tipoGrupo = 'T'
            tableTeoriaMatricial = $('#tablaTeoriaMatricial').dataTable({
                layout: layoutGruposMatricial,
                pageLength : 50,
                ajax: ajaxGruposMatricial,
                columns: columnsGruposMatricial,
                createdRow: createdRowsMatricial,
                initComplete: initCompleteMatricial,
            })

            dataGruposMatricial.tipoGrupo = 'L'
            tableLaboratorioMatricial = $('#tablaLaboratorioMatricial').dataTable({
                layout: layoutGruposMatricial,
                pageLength : 50,
                ajax: ajaxGruposMatricial,
                columns: columnsGruposMatricial,
                createdRow: createdRowsMatricial,
                initComplete: initCompleteMatricial,
            })

            dataGruposMatricial.tipoGrupo = 'P'
            tablePracticaMatricial = $('#tablaPracticaMatricial').dataTable({
                layout: layoutGruposMatricial,
                pageLength : 50,
                ajax: ajaxGruposMatricial,
                columns: columnsGruposMatricial,
                createdRow: createdRowsMatricial,
                initComplete: initCompleteMatricial,
            })
        })

    }

    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this);
        let grupo = objectBtn.attr("grupo");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("gestion", dataGruposMatricial.gestion);
        datos.append("carrera", dataGruposMatricial.carrera);
        datos.append("sede", dataGruposMatricial.sede);
        datos.append("plan", dataGruposMatricial.plan);
        datos.append("sigla", dataGruposMatricial.sigla);
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);
        datos.append("estado", estado);
        dataGruposMatricial.tipoGrupo = tipoGrupo

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
                    url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/cambiar-estado-grupo",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','El estado del grupo se cambio de forma correcta.','toast')
                            switch (tipoGrupo){
                                case 'T': $("#tablaTeoria").DataTable().ajax.reload(function (){
                                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                        .forEach(popover => {
                                            new bootstrap.Popover(popover)
                                        })
                                });
                                    break
                                case 'L': $("#tablaLaboratorio").DataTable().ajax.reload(function (){
                                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                        .forEach(popover => {
                                            new bootstrap.Popover(popover)
                                        })
                                });
                                    break
                                case 'P': $("#tablaPractica").DataTable().ajax.reload(function (){
                                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                        .forEach(popover => {
                                            new bootstrap.Popover(popover)
                                        })
                                });
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

    $("#btnGuardar").click(function () {
        if ($("#formCargaHorariaPropuesta").valid()) {
            if ($("#codigoCrear").val() === '') {
                guardarGrupo();
            } else {
                actualizarGrupo();
            }
        }
    });

    function guardarGrupo() {
        let datos = new FormData();
        datos.append("gestion", dataGruposMatricial.gestion)
        datos.append("carrera", dataGruposMatricial.carrera)
        datos.append("plan", dataGruposMatricial.plan)
        datos.append("sigla", dataGruposMatricial.sigla)
        datos.append('sede',dataGruposMatricial.sede)
        datos.append("tipoGrupo", dataGruposMatricial.tipoGrupo)
        datos.append('docente',$('#docentes').val())
        datos.append("grupo", $("#grupo").val())
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/guardar-grupo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos del nuevo grupo se guardaron correctamente.','toast')
                    $('#modalPlanificar').modal('hide')
                    switch (dataGruposMatricial.tipoGrupo){
                        case 'T': $("#tablaTeoriaMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                        case 'L': $("#tablaLaboratorioMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                        case 'P': $("#tablaPracticaMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                    }
                    reiniciarCampos()
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
    }

    $(document).on('click', 'tbody .btnEditar', function() {
        let objectBtn = $(this);
        let grupo = objectBtn.attr("grupo");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("gestion", dataGruposMatricial.gestion);
        datos.append("carrera", dataGruposMatricial.carrera);
        datos.append("sede", dataGruposMatricial.sede);
        datos.append("plan", dataGruposMatricial.plan);
        datos.append("sigla", dataGruposMatricial.sigla);
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);
        dataGruposMatricial.grupo = grupo
        dataGruposMatricial.tipoGrupo = tipoGrupo
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/buscar-grupo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let grupo = JSON.parse(JSON.stringify(data.grupo));

                    $("#docentes").select2("trigger", "select", {
                        data: { id: $.trim(grupo.IdPersona), text: $.trim(grupo.Paterno).toUpperCase() + ' ' + $.trim(grupo.Materno).toUpperCase() + ' ' + $.trim(grupo.Nombres).toUpperCase(), condicion: 'Docente' }
                    });


                    $("#grupo").val(grupo.Grupo);
                    $('#grupo').attr('p',$.trim(grupo.IdPersona))
                    $("#codigoCrear").val('update');
                    DetenerSpiner(objectBtn)
                    $("#modalPlanificar").modal('show');
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    function actualizarGrupo() {
        let datos = new FormData();
        datos.append("gestion", dataGruposMatricial.gestion);
        datos.append("carrera", dataGruposMatricial.carrera);
        datos.append("sede", dataGruposMatricial.sede);
        datos.append("plan", dataGruposMatricial.plan);
        datos.append("sigla", dataGruposMatricial.sigla);
        datos.append("grupo", dataGruposMatricial.grupo);
        datos.append("tipoGrupo", dataGruposMatricial.tipoGrupo);
        datos.append("grupoN", $('#grupo').val());
        datos.append("idPersonaN", $('#docentes').val());
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/actualizar-grupo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','El grupo se actualizó correctamente.','toast')
                    switch (dataGrupos.tipoGrupo){
                        case 'T': $("#tablaTeoriaMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                        case 'L': $("#tablaLaboratorioMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                        case 'P': $("#tablaPracticaMatricial").DataTable().ajax.reload(function (){
                            document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                                .forEach(popover => {
                                    new bootstrap.Popover(popover)
                                })
                        });
                            break
                    }
                    reiniciarCampos()
                    $('#modalPlanificar').modal('hide')
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
    }


    $(document).on('click', '.btnCrear', function(){
        let objectBtn = $(this)
        dataGrupos.tipoGrupo = objectBtn.attr('grupo')
        $('#modalPlanificar').modal('show')
    })

    $(document).on('click', '#cerrarModal', function(){
        reiniciarCampos()
    })

    function reiniciarCampos() {
        $('#formCargaHorariaPropuesta *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#codigoCrear').val('');
        $('#formCargaHorariaPropuesta').trigger("reset");
        $('#docentes').val(null).trigger('change')
    }


})