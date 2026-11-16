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
        if (typeof indicadorEstrategicoAccion_s2AccionEstrategica !== 'undefined') {
            indicadorEstrategicoAccion_s2AccionEstrategica.val(null).trigger('change')
        }
        idIndicadorEstrategico = ID_EMPTY_GUID
    }

    indicadorEstrategicoAccion_s2ObjEstrategico.on('change', async function () {
        const idObjEstrategico = indicadorEstrategicoAccion_s2ObjEstrategico.select2('data')[0]?.id
        const holder = $('#mensajeInicial')
        const load = $('#dticTableLoading')
        const contain = $('#dticTableContainer')

        if (!idObjEstrategico){
            contain.hide();
            load.hide();
            holder.show();
            $('#indAccionSubtitleTotal').text('Seleccione un objetivo estratégico')
            return;
        }

        holder.hide();
        load.show();
        contain.hide();

        dt_indEstrategicoAccion.one('draw', function () {
            load.hide();
            contain.fadeIn(180);
        });

        dt_indEstrategicoAccion.ajax.reload();
    })

    dtEvents.on('click', '.btn-programar', async function () {
        let objectBtn = $(this);

        const dt_row = dt_indEstrategicoAccion.row(objectBtn.closest('tr')).data()
        idIndicadorEstrategico = dt_row["IdIndicador"];

        indicadorEstrategicoAccion_s2AccionEstrategica.val(dt_row["IdAccionEstrategica"]).trigger('change')

        $('#modalAsignacion').modal('show');
    })

    $(document).on('click', '.guardar', async function () {
        let objectBtn = $(this);

        if (!$("#formAsignacionAccion").valid()) return;

        const datos = new FormData();
        datos.append("idIndicadorEstrategico", idIndicadorEstrategico);
        datos.append("idAccionEstrategica", indicadorEstrategicoAccion_s2AccionEstrategica.select2('data')[0].id);

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
            idAccionEstrategica: {
                required: true
            }
        },
        messages: {
            idAccionEstrategica: {
                required: "Debe seleccionar una opcion para la unidad del indicador",
            }
        },
        errorElement: "div",

        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }
    });

})