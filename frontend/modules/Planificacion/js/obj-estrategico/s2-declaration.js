let objEstrategico_s2AreaEstrategica
let objEstrategico_s2PoliticaEstrategica
$(document).ready(function() {

    objEstrategico_s2AreaEstrategica = $('#areasEstrategicas')
    objEstrategico_s2PoliticaEstrategica = $('#politicasEstrategicas')

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