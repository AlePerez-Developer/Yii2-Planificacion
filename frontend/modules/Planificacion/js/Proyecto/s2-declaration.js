let proyecto_s2Programa = $('#idPrograma')

populateS2Programas(proyecto_s2Programa)

proyecto_s2Programa.select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione un programa',
    allowClear: true,
    width: '100%',
    dropdownParent: $('#modalProyecto')
})
