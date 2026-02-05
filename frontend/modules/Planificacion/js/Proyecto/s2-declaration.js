let proyecto_s2Programa = $('#programas')

$(document).ready(function() {

    populateS2Programas(proyecto_s2Programa)

    proyecto_s2Programa.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un programa",
        allowClear: true,
    })
});