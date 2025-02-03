var vigente
var eliminada
var agregada

$(document).ready(function () {
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

        if ($(this).val() != ''){
            $('#planes').val(null).trigger('change')
            $('#divPlanes').attr('hidden',false)
        }
    });

    $("#planes").change(function () {
        $('#divCursos').attr('hidden',true)
        $('#divTabla').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)

        if ($(this).val() != ''){
            $('#cursos').val(null).trigger('change')
            $('#divCursos').attr('hidden',false)
        }
    });

    $('#cursos').change(function () {
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)


            dataMaterias.gestion = $('#gestion').val()
            dataMaterias.carrera = $("#carreras").val()
            dataMaterias.curso = $("#cursos").val()
            dataMaterias.plan = $("#planes").val()
            dataMaterias.sede = $("#sedes").val()
            dataMaterias.flag = 1

            let datos = new FormData();
            datos.append("gestion", $('#gestion').val());
            datos.append("carrera", $('#carreras').val());
            datos.append("sede", $('#sedes').val());
            datos.append("plan", $('#planes').val());



            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria/obtener-estado-envio",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (data) {
                    if (data.respuesta === RTA_CORRECTO) {
                        $('#envio').val(data.estado)
                        if (data.estado == '1'){
                            $('#enviarPlanificacion').hide()
                        }
                    }
                    else {
                        MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                }
            });

            tableMaterias.ajax.reload()
        }
    })

    $('#enviarPlanificacion').click(function (){
        let datos = new FormData();
        datos.append("gestion", $('#gestion').val());
        datos.append("carrera", $('#carreras').val());
        datos.append("sede", $('#sedes').val());
        datos.append("plan", $('#planes').val());

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/enviar-cargahoraria",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    $('#enviarPlanificacion').hide()
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
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
            '     </div>' +
            '   </div>' +
            '  <div class="tab-pane fade" id="pills-laboratorio" role="tabpanel" aria-labelledby="pills-profile-tab">' +
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "L" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Laboratorio</button></div></div>' +
            '    <div class="divGrupos">'+
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
            '     </div>' +
            '</div>' +
            '  <div class="tab-pane fade" id="pills-practica" role="tabpanel" aria-labelledby="pills-contact-tab">' +
            '  <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "P" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Practica</button></div></div>' +
            '    <div class="divGrupos">'+
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
            '     </div>' +
            '</div>' +
            '</div>'+
            '</div>'
        );
    }

    $(document).on('click','#tablaMaterias tbody td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = tableMaterias.row(tr);

        if (row.child.isShown()) {
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
            row.child.hide();
        }
        else {
            $("#tablaMaterias  tr.shown").each(function () {
                let rowOpen = tableMaterias.row($(this));
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
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/mostrar",
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
            dataGrupos.gestion = $("#gestion").val()
            dataGrupos.carrera = $("#carreras").val()
            dataGrupos.curso = $("#cursos").val()
            dataGrupos.plan = $("#planes").val()
            dataGrupos.sede = $("#sedes").val()
            dataGrupos.sigla = sigla

            dataGrupos.tipoGrupo = 'T'
            tableTeoria = $('#tablaTeoria').dataTable({
                layout: layoutGrupos,
                pageLength : 50,
                ajax: ajaxGrupos,
                columns: columnsGrupos,
                createdRow: createdRows,
                initComplete: initComplete,
            })

            dataGrupos.tipoGrupo = 'L'
            tableLaboratorio = $('#tablaLaboratorio').dataTable({
                layout: layoutGrupos,
                pageLength : 50,
                ajax: ajaxGrupos,
                columns: columnsGrupos,
                createdRow: createdRows,
                initComplete: initComplete,
            })

            dataGrupos.tipoGrupo = 'P'
            tablePractica = $('#tablaPractica').dataTable({
                layout: layoutGrupos,
                pageLength : 50,
                ajax: ajaxGrupos,
                columns: columnsGrupos,
                createdRow: createdRows,
                initComplete: initComplete,
            })
        })






    }

    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this);
        let grupo = objectBtn.attr("grupo");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("gestion", dataGrupos.gestion);
        datos.append("carrera", dataGrupos.carrera);
        datos.append("sede", dataGrupos.sede);
        datos.append("plan", dataGrupos.plan);
        datos.append("sigla", dataGrupos.sigla);
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);
        datos.append("estado", estado);
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
        datos.append("gestion", dataGrupos.gestion)
        datos.append("carrera", dataGrupos.carrera)
        datos.append("plan", dataGrupos.plan)
        datos.append("sigla", dataGrupos.sigla)
        datos.append('sede',dataGrupos.sede)
        datos.append("tipoGrupo", dataGrupos.tipoGrupo)
        datos.append('docente',$('#docentes').val())
        datos.append("grupo", $("#grupo").val())
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/guardar-grupo",
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
                    switch (dataGrupos.tipoGrupo){
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
        datos.append("gestion", dataGrupos.gestion);
        datos.append("carrera", dataGrupos.carrera);
        datos.append("sede", dataGrupos.sede);
        datos.append("plan", dataGrupos.plan);
        datos.append("sigla", dataGrupos.sigla);
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);
        dataGrupos.grupo = grupo
        dataGrupos.tipoGrupo = tipoGrupo
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/buscar-grupo",
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
        datos.append("gestion", dataGrupos.gestion);
        datos.append("carrera", dataGrupos.carrera);
        datos.append("sede", dataGrupos.sede);
        datos.append("plan", dataGrupos.plan);
        datos.append("sigla", dataGrupos.sigla);
        datos.append("grupo", dataGrupos.grupo);
        datos.append("tipoGrupo", dataGrupos.tipoGrupo);
        datos.append("grupoN", $('#grupo').val());
        datos.append("idPersonaN", $('#docentes').val());
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/actualizar-grupo",
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
});


