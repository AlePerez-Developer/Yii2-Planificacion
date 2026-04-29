let objInstitucional_s2ObjEstrategico = $('#objsEstrategicos')

$(document).ready(function() {

    populateS2ObjEstrategico(objInstitucional_s2ObjEstrategico)

    objInstitucional_s2ObjEstrategico.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo estrategico",
        allowClear: true,
    })
});