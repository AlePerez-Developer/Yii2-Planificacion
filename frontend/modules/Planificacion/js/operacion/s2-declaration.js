let operacion_s2Objetivo = $('#idObjEspecifico');
let operacion_s2Indicador = $('#idIndicador');
let operacion_s2Llave = $('#idLlavePresupuestaria');

$(document).ready(function () {
    operacion_s2Objetivo.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo específico',
        allowClear: true,
        width: '100%',
        templateResult: formatoObjetivo,
        templateSelection: formatoObjetivo,
        matcher: buscar
    });

    operacion_s2Indicador.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un indicador programado',
        allowClear: true,
        width: '100%',
        templateResult: formatoIndicador,
        templateSelection: formatoIndicador,
        matcher: buscar
    });

    operacion_s2Llave.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una llave presupuestaria',
        allowClear: true,
        width: '100%',
        templateResult: formatoLlave,
        templateSelection: formatoLlave,
        matcher: buscar
    });

    cargar(
        'index.php?r=Planificacion/operacion/listar-objetivos-s2',
        operacion_s2Objetivo
    );
    cargar(
        'index.php?r=Planificacion/operacion/listar-indicadores-programados-s2',
        operacion_s2Indicador
    );
    cargar(
        'index.php?r=Planificacion/operacion/listar-llaves-s2',
        operacion_s2Llave
    );

    function cargar(url, select) {
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
            },
            error: mostrarErrorOperacion
        });
    }

    function formatoObjetivo(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">Código: ${data.compuesto || ''}</div>
            <div>${data.text || ''}</div>
        </div>`);
    }

    function formatoIndicador(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="indicador-option-header">
                <span class="badge-indicador">${data.tipoIndicador || ''}</span>
                <strong>Indicador ${data.codigo || ''}</strong>
            </div>
            <div>${data.text || ''}</div>
        </div>`);
    }

    function formatoLlave(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">${data.text || ''}</div>
            <div>${data.descripcion || ''}</div>
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
