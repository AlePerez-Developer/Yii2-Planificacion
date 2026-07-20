let programacionTrimestral_s2ObjEstrategico = $('#idObjEstrategico');

$(document).ready(function () {
    programacionTrimestral_s2ObjEstrategico.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo estratégico',
        allowClear: true,
        width: '100%',
        templateResult: select2ObjHtmlFormat,
        templateSelection: select2ObjHtmlFormat,
        matcher: select2ObjMatchSearch
    });

    populateS2ObjEstrategico(programacionTrimestral_s2ObjEstrategico);

    function select2ObjHtmlFormat(repo) {
        if (repo.loading || repo.id === '') {
            return repo.text;
        }

        return $(
            `<div class="mi-render-select2">
                <div class="titulo-producto">Código: ${repo.compuesto || ''}</div>
                <div class="titulo-producto">${repo.text || ''}</div>
                <div class="subtitulo-producto">${repo.producto || ''}</div>
            </div>`
        );
    }

    function select2ObjMatchSearch(params, data) {
        if (typeof data.text === 'undefined') {
            return null;
        }

        if ($.trim(params.term) === '') {
            return data;
        }

        const busqueda = params.term.toLowerCase();
        const texto = (data.text || '').toLowerCase();
        const compuesto = (data.compuesto || '').toLowerCase();
        const producto = (data.producto || '').toLowerCase();

        return texto.includes(busqueda)
            || compuesto.includes(busqueda)
            || producto.includes(busqueda)
            ? data
            : null;
    }
});
