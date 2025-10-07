$(document).ready(function() {
    $('#areasEstrategicas').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una area estrategica",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=Planificacion/politica-estrategica/listar-areas-estrategicas',
            processResults: function (data) {
                let mappedData = $.map(data.data, function (obj) {
                    obj.id = obj["CodigoAreaEstrategica"];
                    obj.text = '(' + obj['Codigo'] + ') - ' + obj['Descripcion'];
                    return obj;
                });
                return {
                    results: mappedData
                };
            },
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
            },
        },
    })
});