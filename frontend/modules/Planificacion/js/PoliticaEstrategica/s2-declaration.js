let politicas_s2AreasEstrategicas = $('#areasEstrategicas')

$(document).ready(function() {

    populateS2Areas(politicas_s2AreasEstrategicas)

    politicas_s2AreasEstrategicas.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una area estrategica",
        allowClear: true,
    })

});