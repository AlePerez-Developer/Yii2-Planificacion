$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let baseUrl = "index.php?r=Planificacion/indicador-estrategico-accion/"
    let idIndicadorEstrategico = ID_EMPTY_GUID
    let dtEvents = $('#tablaListaIndicadoresEstrategicosAccion')

    function ReiniciarCampos(){
        $('#formAsignacionAccion *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formAsignacionAccion').trigger("reset");
        indicadorEstrategico_s2AccionEstrategica.val(null).trigger('change')
        idIndicadorEstrategico = ID_EMPTY_GUID
    }

    indicadorEstrategicoAccion_s2ObjEstrategico.on('change', async function () {
        const idObjEstrategico = indicadorEstrategicoAccion_s2ObjEstrategico.select2('data')[0]?.id
        const holder = $('#mensajeInicial')
        const load = $('#dticTableLoading')
        const contain = $('#dticTableContainer')
        const dt_table = $('#tablaListaIndicadores')

        if (!idObjEstrategico){
            contain.hide();
            load.hide();
            holder.show();
            return;
        }

        openedRow = null;
        holder.hide();

        load.show();
        contain.hide();

        if ($.fn.DataTable.isDataTable(dt_table)) {
            dt_indEstrategicoAccion.ajax.reload();
        } else {
            inicializarTablaIndAcciones();
        }

        dt_indEstrategicoAccion.one('draw', function () {
            load.hide();
            contain.fadeIn(180);
        });

        dt_indEstrategicoAccion.ajax.reload();
    })

    dtEvents.on('click', '.btn-programar', async function () {
        let objectBtn = $(this);

        const dt_row = dt_indEstrategicoAccion.row(objectBtn.closest('tr')).data()
        idIndicadorEstrategico = dt_row["IdIndicadorEstrategico"];

        indicadorEstrategicoAccion_s2AccionEstrategica.val(dt_row["IdAccionEstrategica"]).trigger('change')
        $('#accionDescripcion').val(dt_row["AccionDescripcion"]);

        $('#modalAsignacion').modal('show');
    })

    $(document).on('click', '.guardar', async function () {
        let objectBtn = $(this);

        if (!$("#formAsignacionAccion").valid()) return;

        const datos = new FormData();
        datos.append("idIndicadorEstrategico", idIndicadorEstrategico);
        datos.append("idAccionEstrategica", indicadorEstrategicoAccion_s2AccionEstrategica.select2('data')[0].id);
        datos.append("accionDescripcion", $('#accionDescripcion').val());

        try {
            await ajaxPromise({
                url: baseUrl + "guardar-accion",
                data: datos,
                successMsg: "Asignacion realizada con exito",
                spinnerBtn: objectBtn,
            }).then(() => {
                $('#modalAsignacion').modal('hide');
                ReiniciarCampos()
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    })

    $('#modalAsignacion').on('hidden.bs.modal', function () {
        if ($.fn.DataTable.isDataTable(dt_indEstrategicoAccion)) {
            dt_indEstrategicoAccion.ajax.reload();
        }
        ReiniciarCampos()
    });

    /**
     * Validacion del form
     */
    $("#formAsignacionAccion").validate({
        rules: {
            accionDescripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            idAccionEstrategica: {
                required: true
            }
        },
        messages: {
            accionDescripcion: {
                required: "Debe ingresar la descripcion de la accion estrategica",
                minlength: "La descripcion debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion debe tener maximo 500 caracteres",
            },
            idAccionEstrategica: {
                required: "Debe seleccionar una opcion para la unidad del indicador",
            }
        },
        errorElement: "div",

        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            error.insertAfter(element);
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }
    });

})