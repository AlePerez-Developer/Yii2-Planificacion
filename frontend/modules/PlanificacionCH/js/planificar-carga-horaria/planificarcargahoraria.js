let CodigoGlobal = false
let estadoEnvioCarrera = false
$(document).ready(function () {
    let divCarreras = $('#divCarreras')
    let rowDos = $('#rowDos')
    let divSedes = $('#divSedes')
    let divPlanes = $('#divPlanes')
    let divCursos = $('#divCursos')
    let divTabla = $('#divTabla')

    function resetMaterias(){
        dataMaterias.flag = 0
        tableMaterias.ajax.reload()
    }

    $('#facultades').change(function () {
        divCarreras.attr('hidden',true)
        rowDos.attr('hidden',true)
        divSedes.attr('hidden',true)
        divPlanes.attr('hidden',true)
        divCursos.attr('hidden',true)
        divTabla.attr('hidden',true)

        if ($(this).val() !== '') {
            $('#carreras').val(null).trigger('change')
            divCarreras.attr('hidden',false)
        } else {
            resetMaterias()
        }
    })

    $('#carreras').change(function () {
        rowDos.attr('hidden',true)
        divSedes.attr('hidden',true)
        divPlanes.attr('hidden',true)
        divCursos.attr('hidden',true)
        divTabla.attr('hidden',true)

        if ($(this).val() !== ''){
            $('#sedes').val(null).trigger('change')
            divSedes.attr('hidden',false)
            rowDos.attr('hidden',false)
        } else {
            resetMaterias()
        }
    })

    $("#sedes").change(function () {
        divPlanes.attr('hidden',true)
        divCursos.attr('hidden',true)
        divTabla.attr('hidden',true)

        if ($(this).val() !== ''){
            $('#planes').val(null).trigger('change')
            divPlanes.attr('hidden',false)
        } else {
            resetMaterias()
        }
    });

    $("#planes").change(function () {
        divCursos.attr('hidden',true)
        divTabla.attr('hidden',true)

        if ($(this).val() !== ''){
            $('#cursos').val(null).trigger('change')
            divCursos.attr('hidden',false)
        } else {
            resetMaterias()
        }
    });

    $('#cursos').change(function () {
        divTabla.attr('hidden',true)

        if ($(this).val() !== ''){
            divTabla.attr('hidden',false)

            let carrera  = $('#carreras').select2('data')
            let curso  = $('#cursos').select2('data')
            let plan  = $('#planes').select2('data')
            let sede  = $('#sedes').select2('data')

            dataMaterias.carrera = carrera[0].id
            dataMaterias.curso = curso[0].id
            dataMaterias.plan = plan[0].id
            dataMaterias.sede = sede[0].id
            dataMaterias.flag = 1

            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria/obtener-estado-envio",
                method: "POST",
                data: dataMaterias,
                cache: false,
                dataType: "json",
                success: function (data) {
                    if (data['respuesta'] === RTA_CORRECTO) {
                        estadoEnvioCarrera = data['estado']
                        if (data['estado'] === ESTADO_ENVIADA){
                            $('#enviarPlanificacion').hide()
                        }
                    }
                    else {
                        MostrarMensaje('error',GenerarMensajeError(data['respuesta']))
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
                }
            });

            tableMaterias.ajax.reload()
        } else {
            resetMaterias()
        }
    })

    function format() {
        return (
            '<div style="background-color: white;">\n' +
    '            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">\n' +
    '              <li class="nav-item" role="presentation">\n' +
    '                <button class="nav-link active" id="pills-teoria-tab" data-tipo="T" data-bs-toggle="pill" data-bs-target="#pills-teoria" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Grupos Teoria</button>\n' +
    '              </li>\n' +
    '              <li class="nav-item" role="presentation">\n' +
    '                <button class="nav-link" id="pills-laboratorio-tab" data-tipo="L" data-bs-toggle="pill" data-bs-target="#pills-laboratorio" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Grupos Laboratorio</button>\n' +
    '              </li>\n' +
    '              <li class="nav-item" role="presentation">\n' +
    '                <button class="nav-link" id="pills-practica-tab" data-tipo="P" data-bs-toggle="pill" data-bs-target="#pills-practica" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Grupos Practica</button>\n' +
    '              </li>\n' +
    '            </ul>\n' +
    '            <div class="tab-content" id="pills-tabContent" >\n' +
    '                <div class="tab-pane fade show active" id="pills-teoria" role="tabpanel" aria-labelledby="pills-home-tab">\n' +
    '                    <div class="row">\n' +
    '                        <div class="col-10"></div>\n' +
    '                        <div class="col-2 btn-crear">\n' +
    '                            <button type="button" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Teoria</button>\n' +
    '                        </div>\n' +
    '                    </div>\n' +
    '                    <div class="divGrupos">\n' +
    '                        <table id="tablaTeoria" class="table table-bordered  dt-responsive" style="width: 100%" >\n' +
    '                            <thead>\n' +
    '                                <th>#</th>\n' +
    '                                <th>IdPersona</th>\n' +
    '                                <th>Nombre Docente</th>\n' +
    '                                <th>Grupo</th>\n' +
    '                                <th>Hrs.Teo</th>\n' +
    '                                <th>Prog.</th>\n' +
    '                                <th>Apro.</th>\n' +
    '                                <th>Repro.</th>\n' +
    '                                <th>Aband.</th>\n' +
    '                                <th>Proy.</th>\n' +
    '                                <th>Accion</th>\n' +
    '                            </thead>\n' +
    '                        </table>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '                <div class="tab-pane fade" id="pills-laboratorio" role="tabpanel" aria-labelledby="pills-profile-tab">\n' +
    '                    <div class="row">\n' +
    '                        <div class="col-10"></div>\n' +
    '                        <div class="col-2 btn-crear">\n' +
    '                            <button type="button" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Laboratorio</button>\n' +
    '                        </div>\n' +
    '                    </div>\n' +
    '                    <div class="divGrupos">\n' +
    '                        <table id="tablaLaboratorio" class="table table-bordered  dt-responsive" style="width: 100%" >\n' +
    '                            <thead>\n' +
    '                                <th>#</th>\n' +
    '                                <th>IdPersona</th>\n' +
    '                                <th>Nombre Docente</th>\n' +
    '                                <th>Grupo</th>\n' +
    '                                <th>Hrs.Lab</th>\n' +
    '                                <th>Prog.</th>\n' +
    '                                <th>Apro.</th>\n' +
    '                                <th>Repro.</th>\n' +
    '                                <th>Aband.</th>\n' +
    '                                <th>Proy.</th>\n' +
    '                                <th>Accion</th>\n' +
    '                            </thead>\n' +
    '                        </table>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '                <div class="tab-pane fade" id="pills-practica" role="tabpanel" aria-labelledby="pills-contact-tab">\n' +
    '                    <div class="row">\n' +
    '                        <div class="col-10"></div>\n' +
    '                        <div class="col-2 btn-crear">\n' +
    '                            <button type="button" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Practica</button>\n' +
    '                        </div>\n' +
    '                    </div>\n' +
    '                    <div class="divGrupos">\n' +
    '                        <table id="tablaPractica" class="table table-bordered  dt-responsive" style="width: 100%" >\n' +
    '                            <thead>\n' +
    '                                <th>#</th>\n' +
    '                                <th>IdPersona</th>\n' +
    '                                <th>Nombre Docente</th>\n' +
    '                                <th>Grupo</th>\n' +
    '                                <th>Hrs.Prac</th>\n' +
    '                                <th>Prog.</th>\n' +
    '                                <th>Apro.</th>\n' +
    '                                <th>Repro.</th>\n' +
    '                                <th>Aband.</th>\n' +
    '                                <th>Proy.</th>\n' +
    '                                <th>Accion</th>\n' +
    '                            </thead>\n' +
    '                        </table>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '            </div>\n' +
    '       </div>'
        );
    }

    $(document).on('click','#tablaMaterias tbody td.details-control', function () {
        let tr = $(this).closest('tr');
        let tdi = tr.find("i.fa");
        let row = tableMaterias.row(tr);

        if (row["child"].isShown()) {
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
            row["child"].hide();
        }
        else {
            $("#tablaMaterias  tr.shown").each(function () {
                let rowOpen = tableMaterias.row($(this));
                let tdiOpen = $(this).find("i.fa");
                $(this).removeClass('shown');
                tdiOpen.first().removeClass('fa-minus-square');
                tdiOpen.first().addClass('fa-plus-square');
                rowOpen["child"].hide();
            });

            tr.addClass('shown');
            tdi.first().removeClass('fa-plus-square');
            tdi.first().addClass('fa-minus-square');
            row.child(format(row.data())).show();

            llenarTablas(row.data()["Curso"],row.data()["SiglaMateria"])
        }
    })

    function llenarTablas(curso,sigla){
        dataGrupos.carrera =  dataMaterias.carrera
        dataGrupos.plan = dataMaterias.plan
        dataGrupos.sede = dataMaterias.sede
        dataGrupos.curso = curso
        dataGrupos.sigla = sigla

        dataGrupos.tipoGrupo = 'T'
        tableTeoria = $('#tablaTeoria').DataTable({
            layout: layoutGrupos,
            paging: 75,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
        })

        dataGrupos.tipoGrupo = 'L'
        tableLaboratorio = $('#tablaLaboratorio').DataTable({
            layout: layoutGrupos,
            paging: 75,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
        })

        dataGrupos.tipoGrupo = 'P'
        tablePractica = $('#tablaPractica').DataTable({
            layout: layoutGrupos,
            paging: 75,
            ajax: ajaxGrupos,
            columns: columnsGrupos,
            createdRow: createdRows,
            initComplete: initComplete,
        })

        dataGrupos.tipoGrupo = 'T'
    }

    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this)

        const tipoGrupo = document.querySelector("#tablaMaterias tbody  .active").dataset.tipo;
        const row = objectBtn.closest('tr');
        const dataRow = obtenerDataRow(tipoGrupo,row)

        dataGrupos.grupo = $.trim(dataRow['Grupo'])
        dataGrupos.tipoGrupo = tipoGrupo
        dataGrupos.estado = dataRow['CodigoEstado']

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
                    data: dataGrupos,
                    cache: false,
                    dataType: "json",
                    success: function (data) {
                        if (data["respuesta"] === RTA_CORRECTO) {
                            MostrarMensaje('success','El estado del grupo se cambio de forma correcta.','toast')
                            recargarTablas(dataGrupos.tipoGrupo)
                            DetenerSpiner(objectBtn)
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                            DetenerSpiner(objectBtn)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
                        DetenerSpiner(objectBtn)
                    }
                });
            }
        });
    })

    $("#btnGuardar").click(function () {
        if ($("#formCargaHorariaPropuesta").valid()) {
            if (!CodigoGlobal) {
                guardarGrupo();
            } else {
                actualizarGrupo();
            }
        }
    });

    function guardarGrupo() {
        let docente  = $('#docentes').select2('data')
        const tipoGrupo = document.querySelector("#tablaMaterias tbody  .active").dataset.tipo;

        let datos = new FormData();
        datos.append("carrera", dataGrupos.carrera)
        datos.append("plan", dataGrupos.plan)
        datos.append("sigla", dataGrupos.sigla)
        datos.append('sede',dataGrupos.sede)
        datos.append("tipoGrupo", tipoGrupo)
        datos.append('docente',docente[0].id)
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
                if (data["respuesta"] === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos del nuevo grupo se guardaron correctamente.','toast')
                    $('#modalPlanificar').modal('hide')
                    recargarTablas(dataGrupos.tipoGrupo)
                    reiniciarCampos()
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            }
        });
    }

    $(document).on('click', 'tbody .btnEditar', function() {
        let objectBtn = $(this)

        const tipoGrupo = document.querySelector("#tablaMaterias tbody  .active").dataset.tipo;
        const row = $(this).closest('tr');
        const dataRow = obtenerDataRow(tipoGrupo, row)

        dataGrupos.grupo = $.trim(dataRow['Grupo'])
        dataGrupos.tipoGrupo = tipoGrupo

        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/buscar-grupo",
            method: "POST",
            data: dataGrupos,
            cache: false,
            dataType: "json",
            success: function (data) {
                if (data["respuesta"] === RTA_CORRECTO) {
                    CodigoGlobal = true

                    let grupo = JSON.parse(JSON.stringify(data['grupo']));

                    $("#docentes").select2("trigger", "select", {
                        data: { id: $.trim(grupo["IdPersona"]), text: $.trim(grupo["Paterno"]).toUpperCase() + ' ' + $.trim(grupo["Materno"]).toUpperCase() + ' ' + $.trim(grupo["Nombres"]).toUpperCase(), condicion: 'Docente' }
                    });
                    $("#grupo").val(grupo["Grupo"]);
                    $('#grupo').attr('p',$.trim(grupo["IdPersona"]))

                    DetenerSpiner(objectBtn)
                    $("#modalPlanificar").modal('show');
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    function actualizarGrupo() {
        const tipoGrupo = document.querySelector("#tablaMaterias tbody  .active").dataset.tipo;
        let datos = new FormData();
        let docente  = $('#docentes').select2('data')

        datos.append("carrera", dataGrupos.carrera);
        datos.append("sede", dataGrupos.sede);
        datos.append("plan", dataGrupos.plan);
        datos.append("sigla", dataGrupos.sigla);
        datos.append("grupo", dataGrupos.grupo);
        datos.append("tipoGrupo", tipoGrupo);
        datos.append("grupoN", $('#grupo').val());
        datos.append("idPersonaN", docente[0].id);

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/actualizar-grupo",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data["respuesta"] === RTA_CORRECTO) {
                    MostrarMensaje('success','El grupo se actualizó correctamente.','toast')
                    recargarTablas(tipoGrupo)
                    reiniciarCampos()
                    $('#modalPlanificar').modal('hide')
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            }
        });
    }

    $(document).on('click', '.btnCrear', function(){
        dataGrupos.tipoGrupo = document.querySelector("#tablaMaterias tbody  .active").dataset.tipo
        $('#modalPlanificar').modal('show')
    })

    $(document).on('click', '#cerrarModal', function(){
        reiniciarCampos()
    })

    function reiniciarCampos() {
        CodigoGlobal=false
        $('#formCargaHorariaPropuesta *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formCargaHorariaPropuesta').trigger("reset");
        $('#docentes').val(null).trigger('change')
    }

    $('#enviarPlanificacion').click(function (){
        let datos = new FormData();
        datos.append("carrera", dataGrupos.carrera);
        datos.append("sede", dataGrupos.sede);
        datos.append("plan", dataGrupos.plan);

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/enviar-cargahoraria",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data["respuesta"] === RTA_CORRECTO) {
                    $('#enviarPlanificacion').hide()
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data["respuesta"]))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            }
        });
    })

    $(document).on('show.bs.popover', function (item) {
        let row = $(item.target).closest('tr');
        let idPersona = tableTeoria.row(row).data()["IdPersona"];
        let datos = new FormData();
        datos.append("persona", idPersona);

        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/obtener-ch-persona",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            complete: function (data){
                let rta = JSON.parse(data['responseText'])
                let chPersona = rta['ch']
                let chReal = (rta['chReal'])?rta['chReal']:null
                let chDetallada = rta['chDetallada']?rta['chDetallada']:null

                if (chDetallada !== null) {
                    $('#chDetallada').append(generarChDetallada(chDetallada))
                }

                if (chReal !== null){
                    $('#chReal').text(chReal['ch'])
                    $('#docCondicion').text(chReal['condicion'])
                    $('#chAntiguedad').text(chReal['antiguedad'])
                }

                if (chPersona !== null){
                    $('#chVigente').text(chPersona['vigente'])
                    $('#chEliminada').text(chPersona['eliminada'])
                    $('#chAgregada').text(chPersona['agregada'])
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            }
        });
    })

    function obtenerDataRow(tipoGrupo, row){
        switch (tipoGrupo) {
            case 'T': return tableTeoria.row(row).data()
            case 'P': return tablePractica.row(row).data()
            case 'L': return tableLaboratorio.row(row).data()
        }
    }

    function generarChDetallada(vigente) {
        let subtotalMateria = 0
        let group = '<ul class="list-group">'

        let grupoCarreras = ''
        let grupoMaterias = ''

        let carrera = ''
        let flag = false
        vigente.forEach(function (persona) {
            flag = true
            if (carrera === '') {
                carrera = persona['carrera']
                subtotalMateria = subtotalMateria + parseInt(persona["Ch"])
                grupoMaterias = '<ul class="list-group">'
                grupoMaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona['materia'] + ' - ' + persona["NombreMateria"] +
                    '        <span class="badge text-bg-primary rounded-pill">'+persona["Ch"]+'</span>' +
                    '        </li>'
            } else {
                if (carrera !== persona['carrera'] ){
                    grupoMaterias += '</ul>'
                    grupoCarreras += '<li class="list-group-item   justify-content-between align-items-center">'+ carrera +
                        '<span class="badge badge-primary badge-pill">'+subtotalMateria+'</span>'+
                        grupoMaterias +
                        '</li>'
                    grupoMaterias = '<ul class="list-group">'
                    grupoMaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona['materia'] + ' - ' + persona["NombreMateria"] +
                        '        <span class="badge text-bg-primary rounded-pill">'+persona["Ch"]+'</span>' +
                        '        </li>'
                    subtotalMateria =  parseInt(persona["Ch"])
                } else {
                    subtotalMateria = subtotalMateria + parseInt(persona["Ch"])
                    grupoMaterias += '<li class="list-group-item d-flex justify-content-between align-items-center c">'+ persona['materia'] + ' - ' + persona["NombreMateria"] +
                        '        <span class="badge text-bg-primary rounded-pill">'+persona["Ch"]+'</span>' +
                        '        </li>'
                }
            }
        });
        if (flag){
            grupoMaterias += '</ul>'
            grupoCarreras += '<li class="list-group-item   justify-content-between align-items-center">'+ carrera +
                '<span class="badge badge-primary badge-pill">'+subtotalMateria+'</span>'+
                grupoMaterias +
                '</li></ul>'
        }
        group += grupoCarreras + '</ul>'

        return group
    }

    function recargarTablas(tipoGrupo){
        switch (tipoGrupo){
            case 'T': tableTeoria.ajax.reload(function (){
                document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                    .forEach(popover => {
                        new bootstrap.Popover(popover)
                    })
            });
                break
            case 'L': tableLaboratorio.ajax.reload(function (){
                document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                    .forEach(popover => {
                        new bootstrap.Popover(popover)
                    })
            });
                break
            case 'P': tablePractica.ajax.reload(function (){
                document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                    .forEach(popover => {
                        new bootstrap.Popover(popover)
                    })
            });
                break
        }
    }
});