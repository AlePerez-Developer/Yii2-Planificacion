let s2Areas = $('#areasEstrategicas')
let s2Politicas = $('#politicasEstrategicas')
$(document).ready(function() {

    populateS2Areas()

    s2Areas.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una area estrategica",
        allowClear: true,
    })

    s2Politicas.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una politica estrategica",
        allowClear: true,
    })

    s2Politicas.prop("disabled", true);
});

function populateS2Areas() {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-estrategico/listar-areas-estrategicas',
        success: function(data){
            s2Areas.empty();

            $.each(data["data"], function(index, item) {
                s2Areas.append(
                    $('<option>', {
                        value: item["CodigoAreaEstrategica"],
                        text: item["Descripcion"]
                    })
                );
            });

            s2Areas.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Politicas(codigoArea,codigoPolitica)
{
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        data: {
            area: codigoArea
        },
        cache: true,
        url: 'index.php?r=Planificacion/obj-estrategico/listar-politicas-estrategicas',
        success: function(data){
            s2Politicas.empty();

            $.each(data["data"], function(index, item) {
                s2Politicas.append(
                    $('<option>', {
                        value: item["CodigoPoliticaEstrategica"],
                        text: item["Descripcion"]
                    })
                );
            });
            s2Politicas.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    }).then(function () {
        $("#politicasEstrategicas").val(codigoPolitica).trigger('change');
    })
}