let actividad_s2Programa = $('#idPrograma')

populateS2Programas(actividad_s2Programa)

actividad_s2Programa.select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione un programa',
    allowClear: true,
    width: '100%',
    dropdownParent: $('#modalActividad')
})
