$(document).ready(function () {
    $('#idObjEstrategico').select2({
        theme: 'bootstrap4',
        placeholder: "Selecciones un objetivo estrategico",
        allowClear: true,
        ajax: {
            type: "POST",
            url: "index.php?r=Planificacion/indicador-estrategico-accion/listar-objs-estrategicos",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (response) {
                if (response.success && response.data) {
                    let dataArray = Array.isArray(response.data) ? response.data : [response.data];

                    return {
                        results: $.map(dataArray, function (item) {
                            return {
                                id: item.IdObjEstrategico,
                                text: item.Objetivo,
                                producto: item.Producto,
                                compuesto: item.Compuesto
                            };
                        })
                    };
                } else {
                    return { results: [] };
                }
            },
        },
        templateResult: mapperFormatoHtml,
        templateSelection: mapperFormatoHtml
    })
})

function mapperFormatoHtml(repo) {
    if (repo.loading) {
        return repo.text;
    }
    if (repo.id === "")
    {
        return repo.text
    }

    return $(`
        <div class='mi-render-select2'>
            <div class='titulo-producto' style='font-weight: bold; color: #333;'>Codigo:   ${repo.compuesto} </div>
            <div class='titulo-producto' style='font-weight: bold; color: #333;'>  ${repo.text} </div>
            <div class='subtitulo-producto' style='font-weight: normal; color: #333;'>  ${repo.producto} </div>
        </div>
    `)
}