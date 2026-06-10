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
        placeholder: "Elija un objetivo estrategico",
        allowClear: true,
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
});