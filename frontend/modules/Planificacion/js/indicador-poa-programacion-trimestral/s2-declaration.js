let programacionPoaTrimestral_s2Llave = $('#idLlavePresupuestaria');
let programacionPoaTrimestral_s2Indicador = $('#idIndicadorPoa');

$(document).ready(function () {
    inicializarSelect(
        programacionPoaTrimestral_s2Llave,
        'Seleccione una llave presupuestaria',
        formatoLlave
    );
    inicializarSelect(
        programacionPoaTrimestral_s2Indicador,
        'Seleccione un indicador POA',
        formatoIndicador
    );

    cargarOpciones(
        'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-llaves-s2',
        programacionPoaTrimestral_s2Llave
    );
    cargarOpciones(
        'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-indicadores-s2',
        programacionPoaTrimestral_s2Indicador
    );

    function inicializarSelect(select, placeholder, template) {
        select.select2({
            theme: 'bootstrap4',
            placeholder,
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalRelacionPoa'),
            templateResult: template,
            templateSelection: template,
            matcher: buscar
        });
    }

    function cargarOpciones(url, select) {
        $.ajax({
            url,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                select.empty().append(new Option('', '', false, false));
                (response.data || []).forEach(item => {
                    const option = new Option(item.text, item.id, false, false);
                    $(option).data('data', item);
                    select.append(option);
                });
                select.val(null).trigger('change.select2');
            }
        });
    }

    function formatoLlave(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">${data.text || ''}</div>
            <div class="subtitulo-producto">${data.descripcion || ''}</div>
        </div>`);
    }

    function formatoIndicador(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">Indicador ${data.codigo || ''}</div>
            <div>${data.text || ''}</div>
            <div class="subtitulo-producto">Meta: ${data.meta ?? 0}</div>
        </div>`);
    }

    function buscar(params, data) {
        if ($.trim(params.term) === '') return data;
        const item = obtenerData(data);
        const contenido = Object.values(item).join(' ').toLowerCase();
        return contenido.includes(params.term.toLowerCase()) ? data : null;
    }

    function obtenerData(repo) {
        return repo.element ? ($(repo.element).data('data') || repo) : repo;
    }
});
