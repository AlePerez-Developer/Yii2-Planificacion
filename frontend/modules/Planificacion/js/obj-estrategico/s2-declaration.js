let objEstrategico_s2AreaEstrategica
let objEstrategico_s2PoliticaEstrategica

function opcionesSelectOe($select, placeholder) {
    return {
        theme: 'bootstrap4',
        placeholder: placeholder,
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: false,
        dropdownParent: $select.closest('.da-form-group')
    }
}

function ajustarAnchoSelectsOe() {
    $('#modalObjEstrategico select').each(function () {
        const $select = $(this)
        const $grupo = $select.closest('.da-form-group')
        const ancho = $grupo.innerWidth()
        if (!ancho) {
            return
        }
        $select.next('.select2-container').css({
            width: '100%',
            maxWidth: '100%'
        })
        const select2 = $select.data('select2')
        if (select2 && select2.$dropdown) {
            select2.$dropdown.css('width', ancho + 'px')
        }
    })
}

$(document).ready(function () {
    objEstrategico_s2AreaEstrategica = $('#areasEstrategicas')
    objEstrategico_s2PoliticaEstrategica = $('#politicasEstrategicas')

    populateS2Areas(objEstrategico_s2AreaEstrategica)

    objEstrategico_s2AreaEstrategica.select2(
        opcionesSelectOe(objEstrategico_s2AreaEstrategica, 'Elija un área estratégica')
    )

    objEstrategico_s2PoliticaEstrategica.select2(
        opcionesSelectOe(objEstrategico_s2PoliticaEstrategica, 'Elija una política estratégica')
    )

    objEstrategico_s2PoliticaEstrategica.prop('disabled', true)

    $('#modalObjEstrategico')
        .on('shown.bs.modal', ajustarAnchoSelectsOe)
        .on('select2:open', 'select', ajustarAnchoSelectsOe)
})
