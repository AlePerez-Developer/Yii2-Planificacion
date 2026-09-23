let items_s2Fuente = $('#idFuente');
let items_s2Organismo = $('#idOrganismo');
let items_s2FuenteUniversitaria = $('#idFuenteUniversitaria');
let items_s2UnidadMedida = $('#idUnidadMedida');

$(document).ready(function () {
    const base = 'index.php?r=Planificacion/items-catalogados/';

    [items_s2Fuente, items_s2Organismo, items_s2FuenteUniversitaria, items_s2UnidadMedida].forEach(select => {
        select.select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#divDatos')
        });
    });

    cargarS2('index.php?r=Planificacion/fuente/listar-fuentes-s2', items_s2Fuente);
    cargarS2(base + 'listar-unidades-medida-s2', items_s2UnidadMedida);

    items_s2Fuente.on('change', function () {
        const idFuente = $(this).val() || '';
        items_s2Organismo.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        items_s2FuenteUniversitaria.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        if (!idFuente) return;
        const datos = new FormData();
        datos.append('idFuente', idFuente);
        cargarS2(base + 'listar-organismos-s2', items_s2Organismo, datos);
    });

    items_s2Organismo.on('change', function () {
        const idFuente = items_s2Fuente.val() || '';
        const idOrganismo = $(this).val() || '';
        items_s2FuenteUniversitaria.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        if (!idFuente || !idOrganismo) return;
        const datos = new FormData();
        datos.append('idFuente', idFuente);
        datos.append('idOrganismo', idOrganismo);
        cargarS2(base + 'listar-fuentes-universitarias-s2', items_s2FuenteUniversitaria, datos);
    });

    function cargarS2(url, select, data) {
        $.ajax({
            url,
            method: 'POST',
            data: data || new FormData(),
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                const current = select.val();
                select.empty().append(new Option('', '', false, false));
                (response.data || []).forEach(item => {
                    select.append(new Option(item.text, item.id, false, false));
                });
                select.val(current || null).trigger('change.select2');
            },
            error: function (xhr) {
                const dataError = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(dataError.message || 'No se pudo cargar el catálogo.'), dataError.errors);
            }
        });
    }

    window.itemsCatalogadosCargarS2 = cargarS2;
});
