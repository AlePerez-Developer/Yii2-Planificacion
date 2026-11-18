let objInstitucional_s2ObjEstrategico

function opcionesSelectOi($select, placeholder) {
    return {
        theme: 'bootstrap4',
        placeholder: placeholder,
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: false,
        dropdownParent: $select.closest('.da-form-group'),
        templateResult: select2ObjHtmlFormatOi,
        templateSelection: select2ObjHtmlFormatOi,
        matcher: select2ObjMatchSearchOi
    }
}

function select2ObjHtmlFormatOi(repo) {
    if (repo.loading || repo.id === '') {
        return repo.text
    }

    return $(
        `<div class="mi-render-select2">
            <div class="titulo-producto">Código: ${repo.compuesto || ''}</div>
            <div class="titulo-producto">${repo.text || ''}</div>
            <div class="subtitulo-producto">${repo.producto || ''}</div>
        </div>`
    )
}

function select2ObjMatchSearchOi(params, data) {
    if (typeof data.text === 'undefined') {
        return null
    }

    if ($.trim(params.term) === '') {
        return data
    }

    const busqueda = params.term.toLowerCase()
    const texto = (data.text || '').toLowerCase()
    const compuesto = (data.compuesto || '').toLowerCase()
    const producto = (data.producto || '').toLowerCase()

    return texto.includes(busqueda)
        || compuesto.includes(busqueda)
        || producto.includes(busqueda)
        ? data
        : null
}

function ajustarAnchoSelectsOi() {
    $('#modalObjInstitucional select').each(function () {
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
    objInstitucional_s2ObjEstrategico = $('#idObjEstrategico')

    populateS2ObjEstrategico(objInstitucional_s2ObjEstrategico)

    objInstitucional_s2ObjEstrategico.select2(
        opcionesSelectOi(objInstitucional_s2ObjEstrategico, 'Seleccione un objetivo estratégico')
    )

    $('#modalObjInstitucional')
        .on('shown.bs.modal', ajustarAnchoSelectsOi)
        .on('select2:open', 'select', ajustarAnchoSelectsOi)
})
