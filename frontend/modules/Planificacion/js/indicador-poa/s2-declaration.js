let indicadorPoa_s2ObjEspecifico = $('#idObjEspecifico');

let indicadorPoa_s2TipoResultado = $('#idTipoResultado')
let indicadorPoa_s2CategoriaIndicador = $('#idCategoriaIndicador')
let indicadorPoa_s2UnidadIndicador = $('#idUnidadIndicador')

$(document).ready(function () {
    indicadorPoa_s2ObjEspecifico.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo específico',
        allowClear: true,
        width: '100%',
        templateResult: select2ObjHtmlFormat,
        templateSelection: select2ObjHtmlFormat,
        matcher: select2ObjMatchSearch
    });

    populateS2ObjEspecifico(indicadorPoa_s2ObjEspecifico)

    populateS2TiposResultados(indicadorPoa_s2TipoResultado)
    populateS2CategoriasIndicadores(indicadorPoa_s2CategoriaIndicador)
    populateS2UnidadesIndicadores(indicadorPoa_s2UnidadIndicador)

    indicadorPoa_s2TipoResultado.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un tipo de resultado",
        allowClear: true,
    })

    indicadorPoa_s2CategoriaIndicador.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una categoria de indicador",
        allowClear: true,
    })

    indicadorPoa_s2UnidadIndicador.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una unidad de indicador",
        allowClear: true,
    })

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
