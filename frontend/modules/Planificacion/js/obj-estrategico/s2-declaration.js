let objEstrategico_s2AreaEstrategica = $('#areasEstrategicas')
let objEstrategico_s2PoliticaEstrategica = $('#politicasEstrategicas')
$(document).ready(function() {

    populateS2Areas(objEstrategico_s2AreaEstrategica)

    objEstrategico_s2AreaEstrategica.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una area estrategica",
        allowClear: true,
    })

    objEstrategico_s2PoliticaEstrategica.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una politica estrategica",
        allowClear: true,
    })

    objEstrategico_s2PoliticaEstrategica.prop("disabled", true);
});