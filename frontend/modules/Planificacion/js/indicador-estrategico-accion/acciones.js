$(document).ready(function () {
    let dtEvents = $('#tablaListaIndicadoresEstrategicosAccion')

    indicadorEstrategicoAccion_s2ObjEstrategico.on('change', async function () {
        dt_indEstrategicoAccion.ajax.reload();
    })

    dtEvents.on('click', '.btn-programar', async function(){
        let objectBtn = $(this);
        const dt_row = dt_indEstrategicoAccion.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdIndicadorEstrategico"];


        $('#modalAsignacion').modal('show');

    })


    $('#modalAsignacion').on('hidden.bs.modal', function () {
        if ($.fn.DataTable.isDataTable(dt_indEstrategicoAccion)) {
            dt_indEstrategicoAccion.ajax.reload();
        }
    });

})