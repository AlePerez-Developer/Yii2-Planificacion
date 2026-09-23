let desc_s2Gasto = $('#idGasto');
let desc_s2Fuente = $('#idFuente');
let desc_s2Organismo = $('#idOrganismo');
let desc_s2FuenteUniversitaria = $('#idFuenteUniversitaria');
let desc_s2UnidadMedida = $('#idUnidadMedida');

$(document).ready(function () {
    const formulario = Number($('#itemsDescatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/items-descatalogados/';

    [desc_s2Gasto, desc_s2Fuente, desc_s2Organismo, desc_s2FuenteUniversitaria, desc_s2UnidadMedida].forEach(select => {
        select.select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#divDatos')
        });
    });

    const datosFormulario = new FormData();
    datosFormulario.append('formulario', formulario);
    cargarS2(base + 'listar-gastos-s2', desc_s2Gasto, datosFormulario, true);
    cargarS2('index.php?r=Planificacion/fuente/listar-fuentes-s2', desc_s2Fuente);
    cargarS2(base + 'listar-unidades-medida-s2', desc_s2UnidadMedida);

    desc_s2Gasto.on('change', function () {
        const option = $(this).find('option:selected');
        const codigo = option.data('codigo') || '';
        const descripcion = option.data('descripcion') || '';
        const entidad = option.data('entidad') || '';
        if (!$(this).val()) {
            $('#detalleGasto').hide().empty();
            return;
        }
        $('#detalleGasto').html(`
            <dl class="row mb-0">
                <dt class="col-sm-3">Código</dt><dd class="col-sm-9">${codigo}</dd>
                <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">${descripcion}</dd>
                <dt class="col-sm-3">Entidad transferencia</dt><dd class="col-sm-9">${entidad}</dd>
            </dl>
        `).show();
        if (!$('#descripcion').val()) {
            $('#descripcion').val(descripcion);
        }
    });

    desc_s2Fuente.on('change', function () {
        const idFuente = $(this).val() || '';
        desc_s2Organismo.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        desc_s2FuenteUniversitaria.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        if (!idFuente) return;
        const datos = new FormData();
        datos.append('idFuente', idFuente);
        cargarS2(base + 'listar-organismos-s2', desc_s2Organismo, datos);
    });

    desc_s2Organismo.on('change', function () {
        const idFuente = desc_s2Fuente.val() || '';
        const idOrganismo = $(this).val() || '';
        desc_s2FuenteUniversitaria.empty().append(new Option('', '', false, false)).val(null).trigger('change.select2');
        if (!idFuente || !idOrganismo) return;
        const datos = new FormData();
        datos.append('idFuente', idFuente);
        datos.append('idOrganismo', idOrganismo);
        cargarS2(base + 'listar-fuentes-universitarias-s2', desc_s2FuenteUniversitaria, datos);
    });

    function cargarS2(url, select, data, extraData) {
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
                    const option = new Option(item.text, item.id, false, false);
                    if (extraData) {
                        option.setAttribute('data-codigo', item.codigo || '');
                        option.setAttribute('data-descripcion', item.descripcion || '');
                        option.setAttribute('data-entidad', item.entidadTransferencia || '');
                    }
                    select.append(option);
                });
                select.val(current || null).trigger('change.select2');
            },
            error: function (xhr) {
                const dataError = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(dataError.message || 'No se pudo cargar el catálogo.'), dataError.errors);
            }
        });
    }
});
