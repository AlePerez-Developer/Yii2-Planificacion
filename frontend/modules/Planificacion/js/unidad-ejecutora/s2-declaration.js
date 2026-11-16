let unidadEjecutora_s2Da = $('#idDa')

populateS2Da(unidadEjecutora_s2Da)

unidadEjecutora_s2Da.select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione una DA',
    allowClear: true,
    width: '100%',
    dropdownParent: $('#modalUe')
})
