let indicadorPoa_s2TipoResultado = $('#idTipoResultado');
let indicadorPoa_s2CategoriaIndicador = $('#idCategoriaIndicador');
let indicadorPoa_s2UnidadIndicador = $('#idUnidadIndicador');

$(document).ready(function () {
    populateS2TiposResultados(indicadorPoa_s2TipoResultado);
    populateS2CategoriasIndicadores(indicadorPoa_s2CategoriaIndicador);
    populateS2UnidadesIndicadores(indicadorPoa_s2UnidadIndicador);

    indicadorPoa_s2TipoResultado.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija un tipo de resultado',
        allowClear: true,
        width: '100%'
    });

    indicadorPoa_s2CategoriaIndicador.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija una categoría de indicador',
        allowClear: true,
        width: '100%'
    });

    indicadorPoa_s2UnidadIndicador.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija una unidad de indicador',
        allowClear: true,
        width: '100%'
    });
});
