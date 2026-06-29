let row
$(document).ready(function () {
    let baseUrl = "index.php?r=Planificacion/indicador-estrategico-accion/"
    let dtEvents = $('#tablaListaIndicadoresEstrategicosAccion')

    indicadorEstrategicoAccion_s2ObjEstrategico.on('change', async function () {
        dt_indEstrategicoAccion.ajax.reload();
    })

    dtEvents.on('click', '.btn-programar', async function(){
        let objectBtn = $(this);
        const dt_row = dt_indEstrategicoAccion.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdIndicadorEstrategico"];
        row=rowId

        indicadorEstrategicoAccion_s2AccionEstrategica.val(dt_row["IdAccionEstrategica"]).trigger('change')
        $('#accionDescripcion').val(dt_row["AccionDescripcion"]);

        $('#modalAsignacion').modal('show');
    })


    $('#modalAsignacion').on('hidden.bs.modal', function () {
        if ($.fn.DataTable.isDataTable(dt_indEstrategicoAccion)) {
            dt_indEstrategicoAccion.ajax.reload();
        }
    });

    $(document).on('click', '.guardar', async function () {
        let objectBtn = $(this);
        const id = row
        const accion = indicadorEstrategicoAccion_s2AccionEstrategica.select2('data')[0].id
        const frase = $('#accionDescripcion').val();


        const datos = new FormData();
        datos.append("id", id);
        datos.append("accion", accion);
        datos.append("frase", frase);

        try {
            await ajaxPromise({
                url: baseUrl + "guardar-accion",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
console.log('terminado')
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    })

})