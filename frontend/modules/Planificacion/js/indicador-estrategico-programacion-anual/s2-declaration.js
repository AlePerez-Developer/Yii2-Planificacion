let programacionAnual_s2ObjEstrategico = $('#idObjEstrategico');

$(document).ready(function () {
    programacionAnual_s2ObjEstrategico.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo estratégico',
        allowClear: true,
        width: '100%',
        templateResult: select2ObjHtmlFormat,
        templateSelection: select2ObjHtmlFormat,
        matcher: select2ObjMatchSearch
    });

    populateS2ObjEstrategico(programacionAnual_s2ObjEstrategico);

    function select2ObjHtmlFormat(data) {
        if (data.loading || data.id === '') {
            return data.text;
        }

        return $(
            `<div class="mi-render-select2">
                <div class="titulo-producto">Código: ${data.compuesto || ''}</div>
                <div class="titulo-producto">${data.text || ''}</div>
                <div class="subtitulo-producto">${data.producto || ''}</div>
            </div>`
        );
    }

    function select2ObjMatchSearch(params, search) {
        if (typeof search.text === 'undefined') {
            return null;
        }

        if ($.trim(params.term) === '') {
            return search;
        }

        const busqueda = params.term.toLowerCase();
        const texto = (search.text || '').toLowerCase();
        const compuesto = (search.compuesto || '').toLowerCase();
        const producto = (search.producto || '').toLowerCase();

        return texto.includes(busqueda)
        || compuesto.includes(busqueda)
        || producto.includes(busqueda)
            ? search
            : null;
    }
});
