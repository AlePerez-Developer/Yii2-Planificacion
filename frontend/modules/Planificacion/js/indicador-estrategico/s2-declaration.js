let indicadorEstrategico_s2ObjEstrategico = $('#idObjEstrategico')

let indicadorEstrategico_s2TipoResultado = $('#idTipoResultado')
let indicadorEstrategico_s2CategoriaIndicador = $('#idCategoriaIndicador')
let indicadorEstrategico_s2UnidadIndicador = $('#idUnidadIndicador')

let indicadorEstrategico_s2AccionEstrategica = $('#idAccionEstrategica')
$(document).ready(function() {

    populateS2ObjEstrategico(indicadorEstrategico_s2ObjEstrategico)

    populateS2TiposResultados(indicadorEstrategico_s2TipoResultado)
    populateS2CategoriasIndicadores(indicadorEstrategico_s2CategoriaIndicador)
    populateS2UnidadesIndicadores(indicadorEstrategico_s2UnidadIndicador)
    populateS2AccionesEstrategicas(indicadorEstrategico_s2AccionEstrategica)

    indicadorEstrategico_s2ObjEstrategico.select2({
        theme: 'bootstrap4',
        placeholder: "Selecciones un objetivo estrategico",
        allowClear: true,
        templateResult: select2HtmlFormat,
        templateSelection: select2HtmlFormat,
        matcher: select2MatchSearch
    })

    indicadorEstrategico_s2TipoResultado.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un tipo de resultado",
        allowClear: true,
    })

    indicadorEstrategico_s2CategoriaIndicador.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una categoria de indicador",
        allowClear: true,
    })

    indicadorEstrategico_s2UnidadIndicador.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una unidad de indicador",
        allowClear: true,
    })

    indicadorEstrategico_s2AccionEstrategica.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una accion estrategica",
        allowClear: true,
    })

    function select2HtmlFormat(repo) {
        if (repo.loading) {
            return repo.text;
        }
        if (repo.id === "")
        {
            return repo.text
        }
        return $(`
        <div class='mi-render-select2'>
            <div class='titulo-producto'>Codigo: ${repo.compuesto} </div>
            <div class='titulo-producto'> ${repo.text} </div>
            <div class='subtitulo-producto'>  ${repo.producto} </div>
        </div>
    `)
    }

    function select2MatchSearch(params, data) {

        if ($.trim(params.term) === '') {
            return data;
        }

        if (typeof data.text === 'undefined') {
            return null;
        }

        let contenidoBusqueda = params.term.toLowerCase();

        let texto = (data.text || '').toLowerCase();
        let compuesto = (data.compuesto || '').toLowerCase();
        let producto = (data.producto || '').toLowerCase();

        if (texto.indexOf(contenidoBusqueda) > -1 ||
            compuesto.indexOf(contenidoBusqueda) > -1 ||
            producto.indexOf(contenidoBusqueda) > -1) {

            return data;
        }

        return null;
    }

});

