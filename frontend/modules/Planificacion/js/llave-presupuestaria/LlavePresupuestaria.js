$(document).ready(function () {
    let idLlavePresupuestaria = '00000000-0000-0000-0000-000000000000'
    let baseUrl = "index.php?r=Planificacion/llave-presupuestaria/"
    let dtEvents = $('#tablaListaLlavesPresupuestarias')

    function reiniciarCampos() {
        $('#formLlavePresupuestaria *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formLlavePresupuestaria').trigger("reset");
        llavePresupuestaria_s2Da.val(null).trigger('change')
        llavePresupuestaria_s2Ue.val(null).trigger('change')
        llavePresupuestaria_s2Programa.val(null).trigger('change')
        llavePresupuestaria_s2Proyecto.val(null).trigger('change')
        llavePresupuestaria_s2Actividad.val(null).trigger('change')

        idLlavePresupuestaria = '00000000-0000-0000-0000-000000000000'
    }

    function mensajeAccion(accion) {
        return `Los datos de la llave presupuestaria se ${accion}ron correctamente.`;
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    llavePresupuestaria_s2Programa.change(async function () {
        let idPrograma = $(this).val();

        llavePresupuestaria_s2Proyecto.val(null).trigger('change');
        llavePresupuestaria_s2Actividad.val(null).trigger('change');

        if (idPrograma) {

            llavePresupuestaria_s2Proyecto.prop("disabled", false);
            llavePresupuestaria_s2Actividad.prop("disabled", false);

            await populateS2Proyectos(
                idPrograma,
                llavePresupuestaria_s2Proyecto
            );

            await populateS2Actividades(
                idPrograma,
                llavePresupuestaria_s2Actividad
            );
        } else {

            llavePresupuestaria_s2Proyecto.prop("disabled", true);
            llavePresupuestaria_s2Actividad.prop("disabled", true);
        }

    })

    $('#da, #ue, #programa, #proyecto, #actividad').change(function() {
        actualizarLlave();

        if (todosLosSelectsTienenValor()) {
            $("#formLlavePresupuestaria")
                .validate()
                .element("#actividad");
        }
    });

    function obtenerValorLlave(selector) {
        let select = $(selector);
        return select.find('option:selected').data('key')
            ?? select.attr('data-default')
            ?? '';
    }

    function actualizarLlave() {
        let partes = [
            obtenerValorLlave('#da'),
            obtenerValorLlave('#ue'),
            obtenerValorLlave('#programa'),
            obtenerValorLlave('#proyecto'),
            obtenerValorLlave('#actividad')
        ];

        $('#llave').val(partes.join('-'));
    }

    function todosLosSelectsTienenValor() {

        let completos = true;

        $('.codigo_group').each(function () {

            let value = $(this).val();

            if (!value) {
                completos = false;
                return false;
            }
        });

        return completos;
    }


    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formLlavePresupuestaria").valid()) return;

        const hasCode = idLlavePresupuestaria !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        const idDa = llavePresupuestaria_s2Da.select2('data')[0].id
        const idUe = llavePresupuestaria_s2Ue.select2('data')[0].id
        const idPrograma = llavePresupuestaria_s2Programa.select2('data')[0].id
        const idProyecto = llavePresupuestaria_s2Proyecto.select2('data')[0].id
        const idActividad = llavePresupuestaria_s2Actividad.select2('data')[0].id

        const descripcion = $("#descripcion").val();
        const organizacional  =  $("#organizacional").is(":checked") ? '1' : '0'
        const llave = $('#llave').val()
        const fechaInicio = $("#fechaInicio").val();

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);
        datos.append('idDa', idDa)
        datos.append('idUe', idUe)
        datos.append("idPrograma", idPrograma);
        datos.append("idProyecto", idProyecto);
        datos.append("idActividad", idActividad);
        datos.append("descripcion", descripcion);
        datos.append("llave", llave);
        datos.append("esOrganizacional", organizacional);
        datos.append("fechaInicio", fechaInicio);

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_llavePresupuestaria
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function () {
        dt_llavePresupuestaria.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstadoBtnDtic(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    dtEvents.on('click', '.btn-delete', function(){
        let objectBtn = $(this)
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la llave presupuestaria seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + "eliminar",
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: dt_llavePresupuestaria
                    });
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });
    });

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnFinalizar', async function () {

        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        let idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        try {
            await ajaxPromise({
                url: baseUrl + "finalizar",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
                reloadTable: dt_llavePresupuestaria
            }).then((data) => {
                cambiarEstadoBtn(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    dtEvents.on('click', '.btn-edit', async function(){
        let objectBtn = $(this);
        const dt_row = dt_llavePresupuestaria.row(objectBtn.closest('tr')).data()
        idLlavePresupuestaria = dt_row["IdLlavePresupuestaria"];
        const datos = new FormData();

        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);

        IniciarSpiner(objectBtn)
        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
            }).then(async (data) => {
                let obj = data.data
                llavePresupuestaria_s2Da.val(obj["IdDa"]).trigger('change')
                llavePresupuestaria_s2Ue.val(obj["IdUe"]).trigger('change')

                llavePresupuestaria_s2Programa
                    .val(obj["IdPrograma"])
                    .trigger('change.select2');

                // habilitar
                llavePresupuestaria_s2Proyecto.prop("disabled", false);
                llavePresupuestaria_s2Actividad.prop("disabled", false);

                // esperar carga proyectos
                await populateS2Proyectos(
                    obj["IdPrograma"],
                    llavePresupuestaria_s2Proyecto,
                    obj["IdProyecto"]
                );

                // esperar carga actividades
                await populateS2Actividades(
                    obj["IdPrograma"],
                    llavePresupuestaria_s2Actividad,
                    obj["IdActividad"]
                );


                $("#descripcion").val(obj["Descripcion"]);

                $("#organizacional").prop(
                    "checked",
                    obj["esOrganizacional"] === 1 || obj["esOrganizacional"] === "1"
                );
                let FechaInicio = obj["FechaInicio"]
                $("#fechaInicio").val(FechaInicio.split(' ')[0]);
                $("#btnMostrarCrear").trigger('click');
                DetenerSpiner(objectBtn)
            });
        } catch (err) {
            console.error("Error al procesar:", err);
            DetenerSpiner(objectBtn)
        }
    });


    /**
     * Validacion del form
     */
    $("#formLlavePresupuestaria").validate({
        rules: {
            da: {
                required: true,
            },
            ue: {
                required: true,
            },
            programa: {
                required: true,
            },
            proyecto: {
                required: true,
            },
            actividad: {
                required: true,
                require_from_group: [5, ".codigo_group"],
                remote: {
                    url: baseUrl + "verificar-llave",
                    type: "post",
                    dataType: "json",
                    data: {
                        idDa: function() {
                            let da = $('#da').select2('data')
                            return da[0].id
                        },
                        idUe: function (){
                            let ue = $('#ue').select2('data')
                            return ue[0].id
                        },
                        idPrograma: function (){
                            let programa = $('#programa').select2('data')
                            return programa[0].id
                        },
                        idProyecto: function (){
                            let proyecto = $('#proyecto').select2('data')
                            return proyecto[0].id
                        },
                        idActividad: function (){
                            let actividad = $('#actividad').select2('data')
                            return actividad[0].id
                        },
                        idLlavePresupuestaria: function (){
                            return idLlavePresupuestaria
                        }
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            fechaInicio: {
                required: true,
            }
        },
        messages: {
            da: {
                required: "Debe seleccionar una direccion administrativa",
            },
            ue: {
                required: "Debe seleccionar una unidad ejecutora",
            },
            programa: {
                required: "Debe seleccionar un programa",
            },
            proyecto: {
                required: "Debe seleccionar un proyecto",
            },
            actividad: {
                required: "Debe seleccionar una actividad",
                require_from_group: "Debe todos los campos que intervienen en la llave presupuestaria",
                remote: "errorLlave"
            },
            descripcion: {
                required: "Debe ingresar la descripcion del indicador estrategico",
                minlength: "La descripcion debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion debe tener maximo 500 caracteres",
            },
            fechaInicio: {
                required: "Debe seleccionar una fecha",
            }
        },
        errorElement: "div",

        errorPlacement: function (error, element) {
            error.addClass( "invalid-feedback" );
            error.insertAfter(element);
            let errorElement = $("#llave")
            errorElement.html("");

            if (
                element.attr("name") === "actividad" &&
                error.text() === "errorLlave"
            ) {
                $('.codigo_group').each(function() {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                });
                errorElement.removeClass("is-valid").addClass("is-invalid");
                error.insertAfter(errorElement);
            }
        },
        highlight: function (error, element) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
            $("#errorLlavePresupuestaria").html("");
        }
    });


})