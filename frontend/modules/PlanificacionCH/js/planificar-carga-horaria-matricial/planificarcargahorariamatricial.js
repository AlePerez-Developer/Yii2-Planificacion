var proyT = 0
var proyL = 0
var proyP = 0

$(document).ready(function () {
    $('#facultades').change(function () {
        $('#divMaterias').attr('hidden',true)
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != '') {
            $('#materias').val(null).trigger('change')




            $('#divMaterias').attr('hidden',false)
        }
    })


    $('#carreras').change(function () {
        $('#divPlanes').attr('hidden',true)

        if ($(this).val() != ''){
            $('#planes').val(null).trigger('change')
            $('#divPlanes').attr('hidden',false)
        }
    })


    $('#materias').change(function () {
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)


            llenarTablas($('#materias').val())
            /*dataMateriasMatriciales.materia = $('#materias').val()
            dataMateriasMatriciales.flag = 1

            tableMateriasMatriciales.ajax.reload()*/


        }
    })


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

        var otrosdatos = new FormData();
        otrosdatos.append('sigla',sigla)


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
            dataGruposMatricial.flag = 1
            dataGruposMatricial.sigla = sigla

            otrosdatos.append('tipogrupo','T')
            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/totales",
                method: "POST",
                data: otrosdatos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (data) {
                    if (!(data.respuesta === RTA_CORRECTO)) {
                        MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))

                }
            }).done(function (data){
                let totales = JSON.parse(JSON.stringify(data.totales));
                let programado = (totales.programado !== null)?totales.programado:0
                let reprobado = (totales.reprobado !== null)?totales.reprobado:0
                let aprobado = (totales.aprobado !== null)?totales.aprobado:0
                let abandono = (totales.abandono !== null)?totales.abandono:0
                let proyectado = (totales.proyectado !== null)?totales.proyectado:0

                proyT = proyectado

                dataGruposMatricial.tipoGrupo = 'T'
                $("#tablaTeoriaMatricial").DataTable().ajax.reload(function (){
                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                        .forEach(popover => {
                            new bootstrap.Popover(popover)
                        })
                    $(tableTeoriaMatricial.column(5).header()).text('Prog.(' + programado + ')');
                    $(tableTeoriaMatricial.column(6).header()).text('Aprob.(' + aprobado + ')');
                    $(tableTeoriaMatricial.column(7).header()).text('Reprob(' + reprobado + ')');
                    $(tableTeoriaMatricial.column(8).header()).text('Aband(' + abandono + ')');
                    $(tableTeoriaMatricial.column(9).header()).text('Proy.(' + proyectado + ')');
                })
            })

            otrosdatos.append('tipogrupo','L')
            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/totales",
                method: "POST",
                data: otrosdatos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (data) {
                    if (data.respuesta === RTA_CORRECTO) {


                    } else {
                        MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                }
            }).done(function (data){
                let totales = JSON.parse(JSON.stringify(data.totales));
                let programado = (totales.programado !== null)?totales.programado:0
                let reprobado = (totales.reprobado !== null)?totales.reprobado:0
                let aprobado = (totales.aprobado !== null)?totales.aprobado:0
                let abandono = (totales.abandono !== null)?totales.abandono:0
                let proyectado = (totales.proyectado !== null)?totales.proyectado:0

                proyL = proyectado
                dataGruposMatricial.tipoGrupo = 'L'
                $("#tablaLaboratorioMatricial").DataTable().ajax.reload(function (){
                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                        .forEach(popover => {
                            new bootstrap.Popover(popover)
                        })
                    $(tableLaboratorioMatricial.column(5).header()).text('Prog.(' + programado + ')');
                    $(tableLaboratorioMatricial.column(6).header()).text('Aprob.(' + aprobado + ')');
                    $(tableLaboratorioMatricial.column(7).header()).text('Reprob(' + reprobado + ')');
                    $(tableLaboratorioMatricial.column(8).header()).text('Aband(' + abandono + ')');
                    $(tableLaboratorioMatricial.column(9).header()).text('Proy.(' + proyectado + ')');
                });
            })
            otrosdatos.append('tipogrupo','P')
            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/totales",
                method: "POST",
                data: otrosdatos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (data) {
                    if (data.respuesta === RTA_CORRECTO) {


                    } else {
                        MostrarMensaje('error',GenerarMensajeError(data.respuesta))

                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))

                }
            }).done(function (data){
                let totales = JSON.parse(JSON.stringify(data.totales));
                let programado = (totales.programado !== null)?totales.programado:0
                let reprobado = (totales.reprobado !== null)?totales.reprobado:0
                let aprobado = (totales.aprobado !== null)?totales.aprobado:0
                let abandono = (totales.abandono !== null)?totales.abandono:0
                let proyectado = (totales.proyectado !== null)?totales.proyectado:0

                proyP = proyectado
                dataGruposMatricial.tipoGrupo = 'P'
                $("#tablaPracticaMatricial").DataTable().ajax.reload(function (){
                    document.querySelectorAll('table tbody [data-bs-toggle="popover"]')
                        .forEach(popover => {
                            new bootstrap.Popover(popover)
                        })
                    $(tablePracticaMatricial.column(5).header()).text('Prog.(' + programado + ')');
                    $(tablePracticaMatricial.column(6).header()).text('Aprob.(' + aprobado + ')');
                    $(tablePracticaMatricial.column(7).header()).text('Reprob(' + reprobado + ')');
                    $(tablePracticaMatricial.column(8).header()).text('Aband(' + abandono + ')');
                    $(tablePracticaMatricial.column(9).header()).text('Proy.(' + proyectado + ')');
                });
            })
        })
    }

    $(document).on('click', 'tbody .btnEstado', function(){
        let objectBtn = $(this);
        let carrera = objectBtn.attr("carrera");
        let plan = objectBtn.attr("plan");
        let grupo = objectBtn.attr("grupo");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("carrera", carrera);
        datos.append("sede", 'SU');
        datos.append("plan", plan);
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
        if ($("#formCargaHorariaPropuestaMatricial").valid()) {
            if ($("#codigoCrear").val() === '') {
                guardarGrupo();
            } else {
                actualizarGrupo();
            }
        }
    });

    function guardarGrupo() {
        let datos = new FormData();
        datos.append("carrera", $('#carreras').val())
        datos.append("plan", $('#planes').val())
        datos.append("sigla", $('#materias').val())
        datos.append('sede','SU')
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
        let carrera = objectBtn.attr("carrera");
        let plan = objectBtn.attr("plan");
        let tipoGrupo = objectBtn.attr("tipogrupo");
        let grupo = objectBtn.attr("grupo");
        let estado = objectBtn.attr("estado");

        dataGruposMatricial.carrera = carrera
        dataGruposMatricial.sede = 'SU'
        dataGruposMatricial.plan = plan
        dataGruposMatricial.sigla  = $('#materias').val()
        dataGruposMatricial.grupo = grupo
        dataGruposMatricial.tipoGrupo = tipoGrupo

        let datos = new FormData();
        datos.append("carrera", carrera);
        datos.append("sede", 'SU');
        datos.append("plan", plan);
        datos.append("sigla",  $('#materias').val());
        datos.append("grupo", grupo);
        datos.append("tipoGrupo", tipoGrupo);
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

                    $("#carreras").select2("trigger", "select", {
                        data: { id: grupo.CodigoCarrera, text: grupo.NombreCarrera }
                    });

                    $("#planes").select2("trigger", "select", {
                        data: { id: grupo.NumeroPlanEstudios, text: grupo.NumeroPlanEstudios }
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
        datos.append("carreraN", $('#carreras').val());
        datos.append("planN", $('#planes').val());
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
        dataGruposMatricial.tipoGrupo = objectBtn.attr('grupo')
        $('#modalPlanificar').modal('show')
    })

    $(document).on('click', '#cerrarModal', function(){
        reiniciarCampos()
    })

    function reiniciarCampos() {
        $('#formCargaHorariaPropuestaMatricial *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#codigoCrear').val('');
        $('#formCargaHorariaPropuesta').trigger("reset");
        $('#docentes').val(null).trigger('change')
        $('#carreras').val(null).trigger('change')
    }



    $('#ll').click(function (){

        $('#tprog').text('asdasdasdasdasdasd')

        /*
        $('#tablaTeoriaMatricial th').eq(5).text(programado);
        $('#tablaTeoriaMatricial th').eq(6).text(aprobado);
        $('#tablaTeoriaMatricial th').eq(7).text(reprobado);
        $('#tablaTeoriaMatricial th').eq(8).text(abandono);
        $('#tablaTeoriaMatricial th').eq(9).text(proyectado);*/
    })

})
