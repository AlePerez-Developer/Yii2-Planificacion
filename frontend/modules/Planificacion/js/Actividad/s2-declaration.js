let actividad_s2Programa = $('#programas')

$(document).ready(function() {

    populateS2Programas(actividad_s2Programa)

    actividad_s2Programa.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un programa",
        allowClear: true,
    })
});