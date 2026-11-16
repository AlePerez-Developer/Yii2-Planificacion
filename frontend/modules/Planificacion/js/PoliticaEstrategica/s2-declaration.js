let politicas_s2AreasEstrategicas = $('#idAreaEstrategica')

populateS2Areas(politicas_s2AreasEstrategicas)

politicas_s2AreasEstrategicas.select2({
    theme: 'bootstrap4',
    placeholder: 'Elija un área estratégica',
    allowClear: true,
    width: '100%',
    dropdownParent: $('#modalPoliticaEstrategica')
})
