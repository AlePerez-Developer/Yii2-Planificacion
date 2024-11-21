$(document).ready(function () {
    let table
    let gestiones

    $('#facultades').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una facultad",
        allowClear: true,
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
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="titulosmall">Plan estrategico institucional</div>' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-5">' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Desc: </div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' + 'd.DescripcionPEI' + '</div>' +
            '           </div>' +
            '       </div>' +
            '       <div class="row">' +
            '           <div class="col-2">' +
            '               <div class="subsmall">Fechas</div>' +
            '           </div>' +
            '           <div class="col-4">' +
            '               <div class="little">' +
            '                   Vigencia: ' + 'd.GestionInicio' +  ' - ' + 'd.GestionFin' + '<br>' +
            '                   Aprobacion: ' + d.FechaAprobacion +
            '               </div>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '</div>'
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
                    className: 'dt-small dt-control',
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
            ],
        });

    })

    $('.tablaMateriass tbody').on('click', 'td.dt-control', function () {
        console.log('adsasdasdasd')
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });








    $('#nuevaConfiguracion').click(function (){
        let habilitado
        gestiones = "<option value=''>Selecionar gestion base</option>"
        table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
            let data = this.data();
            gestiones = gestiones + "<option value='" + data.GestionAcademica  +"'>"+data.GestionAcademica+"</option>"
            if (data.CodigoEstado === 'V')
                habilitado = true
        } );

        if (habilitado)
        {
            $('#modalNuevaConfiguracion').modal('show')
        }
        $('#gestionBase').empty().append(gestiones)
    })

    $(".btnEditarConfiguracion").click(function (ev) {
        let codigoCarrera = $("#carreras").val();
        let nombreCarrera = $("#carreras").find("option:selected").text();
        let codigoSede = $("#sedes").val();
        let nombreSede = $("#sedes").find("option:selected").text();
        let datos = new FormData();
        datos.append("carrera", codigoCarrera);
        datos.append("sede", codigoSede);
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/buscar-configuracion-vigente-ajax",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                $("#nombreCarreraConfModif").text(nombreCarrera);
                $("#sedeConfigModif").text(nombreSede);
                $("#gestionAnteriorConfModif").val(respuesta["GestionAnterior"]);
                $("#mesAnteriorConfModif").val(respuesta["MesAnterior"]);
                $("#gestionAcademicaAnteriorConfModif").val(respuesta["GestionAcademicaAnterior"]);
            },
            error: function (respuesta) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "Ocurrio un error al cargar los datos del cargo con código " + codigo + ". Comuniquese con el administrador del sistema.",
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                }).then(function () {
                    $('#modalActualizarCargo').modal('hide');
                });
            }
        });
    });

    /*=============================================
    ACTUALIZAR PLANIFICACION DE UNA CARRERA
    =============================================*/
    $("#btnActualizarConfiguracion").click(function () {
        let codigoCarrera = $("#carreras").val();
        let nombreCarrera = $("#nombreCarreraConfModif").text();
        let codigoSede = $("#sedes").val();
        let gestionPlanificacion = $("#gestionAcademicaPlanificacionConf").text();
        let gestionAnterior = $("#gestionAnteriorConfModif").val();
        let mesAnterior = $("#mesAnteriorConfModif").val();
        let gestionAcademicaAnterior = $("#gestionAcademicaAnteriorConfModif").val();
        let datos = new FormData();
        datos.append("codigocarrera", codigoCarrera);
        datos.append("codigosede", codigoSede);
        datos.append("gestionacademica", gestionPlanificacion);
        datos.append("gestionanterior", gestionAnterior);
        datos.append("mesanterior", mesAnterior);
        datos.append("gestionacademicaanterior", gestionAcademicaAnterior);
        $.ajax({
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/actualizar-carga-horaria-configuracion-ajax",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta === "ok") {
                    $("#modalModificarConfiguracion").modal('hide');
                    Swal.fire({
                        icon: "success",
                        title: "Actualización Completada",
                        text: "La configuración de la planificación de la carrera de " + nombreCarrera + " ha sido guardado correctamente.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                    });
                }
                else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrio un error al actualizar la configuración de la planificación. Comuniquese con el administrador del sistema.",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    }).then(function () {
                        //acciones
                    });
                }
            }
        });
    });





});