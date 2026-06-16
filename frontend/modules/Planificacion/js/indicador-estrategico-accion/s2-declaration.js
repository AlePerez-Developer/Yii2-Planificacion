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
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                if (response.success && response.data) {

                    // Si 'data' es un solo objeto, lo metemos en un array.
                    // (Nota: Select2 usualmente espera una lista [{}], si tu backend ya devuelve un array en 'data', puedes quitar este IF).
                    var dataArray = Array.isArray(response.data) ? response.data : [response.data];

                    return {
                        results: $.map(dataArray, function (item) {
                            return {
                                id: item.IdObjEstrategico,        // Select2 necesita 'id' obligatoriamente
                                text: item.Objetivo, // Select2 necesita 'text' obligatoriamente
                                producto: item.Producto, // Pasamos propiedades extra para el template
                                compuesto: item.Compuesto
                            };
                        })
                    };
                } else {
                    return { results: [] };
                }
            },

        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    })
})


function formatRepo(repo) {
    if (repo.loading) {
        return repo.text;
    }

    /*var $container = $(
        "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__avatar'><img src='" +'asd' + "' /></div>" +
        "<div class='select2-result-repository__meta'>" +
        "<div class='select2-result-repository__title'></div>" +
        "<div class='select2-result-repository__description'></div>" +
        "<div class='select2-result-repository__statistics'>" +
        "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> </div>" +
        "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> </div>" +
        "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> </div>" +
        "</div>" +
        "</div>" +
        "</div>"
    );

    $container.find(".select2-result-repository__title").text(repo.Codigo);
    $container.find(".select2-result-repository__description").text(repo.Objetivo);
    $container.find(".select2-result-repository__forks").append(repo.Resultado + " Forks");
    $container.find(".select2-result-repository__stargazers").append(repo.CodigoUsuario + " Stars");
    $container.find(".select2-result-repository__watchers").append(repo.Codigo + " Watchers");*/

    var $container = $(
        "<div class='select2-result-producto'>" +
        "<div class='select2-result-producto__title'><strong>" + repo.text + "</strong></div>" +
        "<div class='select2-result-producto__meta'>" + repo.compuesto +
        "<small style='color: #777;'>Código: " + repo.id + " | Cat: " + repo.compuesto + "</small>" +
        "</div> -> " +
        "</div>"
    );

    return $container;
}

function formatRepoSelection(repo) {
    var $container = $(
        "<div class='select2-result-producto'>" +
        "<div class='select2-result-producto__title'><strong>" + repo.text + "</strong></div>" +
        "<div class='select2-result-producto__meta'>" + repo.compuesto +
        "<small style='color: #777;'>Código: " + repo.id + " | Cat: " + repo.compuesto + "</small>" +
        "</div> -> " +
        "</div>"
    );

    return $container;
}