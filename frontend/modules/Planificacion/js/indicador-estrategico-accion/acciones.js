$(document).ready(function () {

    indicadorEstrategicoAccion_s2ObjEstrategico.on('change', async function () {
        dt_indEstrategicoAccion.ajax.reload();
    })

})