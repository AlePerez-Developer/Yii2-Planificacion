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

            tableMaterias.ajax.reload()
        }
    })

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

        dataGrupos.gestion = $("#gestion").val()
        dataGrupos.carrera = $("#carreras").val()
        dataGrupos.curso = $("#cursos").val()
        dataGrupos.plan = $("#planes").val()
        dataGrupos.sede = $("#sedes").val()
        dataGrupos.sigla = sigla
        dataGrupos.tipoGrupo = 'T'

        tableTeoria = $('#tablaTeoria').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
        })

        dataGrupos.tipoGrupo = 'L'
        tableLaboratorio = $('#tablaLaboratorio').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
        })

        dataGrupos.tipoGrupo = 'P'
        tablePractica = $('#tablaPractica').dataTable({
            layout: layoutGrupos,
            pageLength : 20,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
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
        datos.append("gestion", '1/2022')
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
                    MostrarMensaje('success','Los datos del nuevo grupo se guardaron correctamente.')
                    $('#modalPlanificar').modal('hide')
                    switch (dataGrupos.tipoGrupo){
                        case 'T': $("#tablaTeoria").DataTable().ajax.reload();
                            break
                        case 'L': $("#tablaLaboratorio").DataTable().ajax.reload();
                            break
                        case 'P': $("#tablaPractica").DataTable().ajax.reload();
                            break
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
    }

    function actualizarPei() {
        let codigoPei = $("#codigoPei").val();
        let descripcionPei = $("#descripcionPei").val();
        let fechaAprobacion = $("#fechaAprobacion").val();
        let gestionInicio = $("#gestionInicio").val();
        let gestionFin = $("#gestionFin").val();
        let datos = new FormData();
        datos.append("codigoPei", codigoPei);
        datos.append("descripcionPei", descripcionPei);
        datos.append("gestionInicio", gestionInicio);
        datos.append("fechaAprobacion", fechaAprobacion);
        datos.append("gestionFin", gestionFin);
        $.ajax({
            url: "index.php?r=Planificacion/peis/actualizar-pei",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','El PEI se actualizó correctamente.')
                    $("#tablaListaPeis").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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

});


