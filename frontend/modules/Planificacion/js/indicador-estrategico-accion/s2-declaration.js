let indicadorEstrategicoAccion_s2ObjEstrategico = $('#idObjEstrategico')
let indicadorEstrategicoAccion_s2AccionEstrategica = $('#idAccionEstrategica')
$(document).ready(function () {
        indicadorEstrategicoAccion_s2ObjEstrategico.select2({
        theme: 'bootstrap4',
        placeholder: "Selecciones un objetivo estrategico",
        allowClear: true,
        templateResult: select2ObjHtmlFormat,
        templateSelection: select2ObjHtmlFormat,
        matcher: select2ObjMatchSearch
    });

    populateS2ObjEstrategico(indicadorEstrategicoAccion_s2ObjEstrategico);

    indicadorEstrategicoAccion_s2AccionEstrategica.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una accion estrategica",
        allowClear: true,
    })

    populateS2AccionesEstrategicas(indicadorEstrategicoAccion_s2AccionEstrategica)

    function select2ObjHtmlFormat(repo) {
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

    function select2ObjMatchSearch(params, data) {

        if (typeof data.text === 'undefined') {
            return null;
        }

        if ($.trim(params.term) === '') {
            return data;
        }

        let busqueda = params.term.toLowerCase();

        let texto = (data.text || '').toLowerCase();
        let compuesto = (data.compuesto || '').toLowerCase();
        let producto = (data.producto || '').toLowerCase();

        if (texto.indexOf(busqueda) > -1 ||
            compuesto.indexOf(busqueda) > -1 ||
            producto.indexOf(busqueda) > -1) {

            return data;
        }

        return null;
    }
})