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
            url: "index.php?r=PlanificacionCH/planificar-carga-horaria/buscar-configuracion-vigente-ajax",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                let meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                let numeroMes = parseInt(respuesta["MesAnterior"]);
                $(".search-plan").show();
                $("#gestionAcademicaAnterior").text(respuesta["GestionAcademicaAnterior"]);
                $("#gestionAcademicaPlanificacion").text(respuesta["GestionAcademicaPlanificacion"]);
                $("#gestionAnteriorConf").text(respuesta["GestionAnterior"]);
                $("#mesAnteriorConf").attr("numeromes", numeroMes);
                $("#mesAnteriorConf").text(meses[numeroMes-1]);
                $("#gestionAcademicaAnteriorConf").text(respuesta["GestionAcademicaAnterior"]);
                $("#gestionAcademicaPlanificacionConf").text(respuesta["GestionAcademicaPlanificacion"]);
            }
        });

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

    $("#cursos").change(function () {
        let codigoCarrera = $("#carreras").val();
        let codigoSede = $("#sedes").val();
        let codigoSedeAcad = $("#sedes option[value='" + codigoSede + "']").attr("valueacad");
        let numeroPlanEstudios = $("#planes").val();
        let curso = $(this).val();
        if (codigoCarrera != "" && codigoSede != "" && codigoSedeAcad != "" && numeroPlanEstudios != "" && curso != "") {
            let datos = new FormData();
            datos.append("codigocarrera", codigoCarrera);
            datos.append("codigosede", codigoSede);
            datos.append("codigosedeacad", codigoSedeAcad);
            datos.append("numeroplanestudios", numeroPlanEstudios);
            datos.append("curso", curso);



            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria/listar-materias-ajax",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "html",
                success: function (respuesta) {
                    $("#tablaMaterias tbody tr").remove();
                    $("#contenidoMaterias").append(respuesta);
                    $(".tabla-materias").show();
                }
            });
        } else {
            $(".tabla-materias").hide();
        }
    });


    $('#cursos').change(function () {
        $('#divConfiguracion').attr('hidden',false)
        $('#divTabla').attr('hidden',false)
    })


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