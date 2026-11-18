let objEspecifico_s2ObjInstitucional

function opcionesSelectOes($select, placeholder) {
    return {
        theme: 'bootstrap4',
        placeholder: placeholder,
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: false,
        dropdownParent: $select.closest('.da-form-group'),
        templateResult: select2ObjHtmlFormatOes,
        templateSelection: select2ObjHtmlFormatOes,
        matcher: select2ObjMatchSearchOes
    }
}

function select2ObjHtmlFormatOes(repo) {
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

function select2ObjMatchSearchOes(params, data) {
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

function ajustarAnchoSelectsOes() {
    $('#modalObjEspecifico select').each(function () {
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
    objEspecifico_s2ObjInstitucional = $('#idObjInstitucional')

    populateS2ObjInstitucional(objEspecifico_s2ObjInstitucional)

    objEspecifico_s2ObjInstitucional.select2(
        opcionesSelectOes(objEspecifico_s2ObjInstitucional, 'Seleccione un objetivo institucional')
    )

    $('#modalObjEspecifico')
        .on('shown.bs.modal', ajustarAnchoSelectsOes)
        .on('select2:open', 'select', ajustarAnchoSelectsOes)
})
