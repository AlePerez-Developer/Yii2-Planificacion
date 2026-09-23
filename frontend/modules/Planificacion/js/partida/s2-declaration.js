let partida_s2Fuente = $('#idFuente');
let partida_s2Organismo = $('#idOrganismo');

$(document).ready(function () {
    partida_s2Fuente.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una fuente',
        allowClear: true,
        width: '100%'
    });
    partida_s2Organismo.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un organismo',
        allowClear: true,
        width: '100%'
    });

    cargar('index.php?r=Planificacion/fuente/listar-fuentes-s2', partida_s2Fuente);
    cargar('index.php?r=Planificacion/organismo/listar-organismos-s2', partida_s2Organismo);

    function cargar(url, select) {
        $.ajax({
            url,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                select.empty().append(new Option('', '', false, false));
                (response.data || []).forEach(item => {
                    select.append(new Option(item.text, item.id, false, false));
                });
                select.val(null).trigger('change.select2');
            },
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo cargar el catálogo.'), data.errors);
            }
        });
    }
});
