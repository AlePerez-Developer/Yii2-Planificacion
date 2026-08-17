let operacionDtic_s2Objetivo = $('#idObjEspecifico');
let operacionDtic_s2IndicadorEstrategico = $('#idIndicadorEstrategico');
let operacionDtic_s2IndicadorPoa = $('#idIndicadorPoa');
let operacionDticS2Ready = null;

$(document).ready(function () {
    operacionDtic_s2Objetivo.select2({
        theme: 'bootstrap4',
        allowClear: true,
        width: '100%',
        placeholder: 'Seleccione un objetivo específico',
        templateResult: formatoObjetivo,
        templateSelection: formatoObjetivo,
        matcher: buscarObjetivo
    });

    operacionDtic_s2IndicadorEstrategico.select2({
        theme: 'bootstrap4',
        allowClear: true,
        width: '100%',
        placeholder: 'Seleccione un indicador estratégico'
    });
    operacionDtic_s2IndicadorPoa.select2({
        theme: 'bootstrap4',
        allowClear: true,
        width: '100%',
        placeholder: 'Seleccione un indicador POA'
    });

    const objetivos = cargarOpciones(
        'index.php?r=Planificacion/operacion-dtic/listar-objetivos-s2',
        operacionDtic_s2Objetivo,
        true
    );
    const estrategicos = cargarOpciones(
        'index.php?r=Planificacion/operacion-dtic/listar-indicadores-estrategicos-s2',
        operacionDtic_s2IndicadorEstrategico
    );

    operacionDticS2Ready = $.when(objetivos, estrategicos);
    operacionDticCargarIndicadoresPoa();

    operacionDtic_s2Objetivo.on('change', function () {
        operacionDticCargarIndicadoresPoa();
    });

    function formatoObjetivo(item) {
        if (item.loading || !item.id) return item.text;
        const data = $(item.element).data('data') || item;

        return $(`
            <div class="mi-render-select2">
                <div class="titulo-producto">Código: ${data.compuesto || ''}</div>
                <div class="titulo-producto">${data.text || ''}</div>
                <div class="subtitulo-producto">${data.producto || ''}</div>
            </div>
        `);
    }

    function buscarObjetivo(params, item) {
        if ($.trim(params.term) === '') return item;
        const data = $(item.element).data('data') || item;
        const texto = [
            data.text || '',
            data.compuesto || '',
            data.producto || ''
        ].join(' ').toLowerCase();

        return texto.includes(params.term.toLowerCase()) ? item : null;
    }
});

function operacionDticCargarIndicadoresPoa(selected = null) {
    return cargarOpciones(
        'index.php?r=Planificacion/operacion-dtic/listar-indicadores-poa-s2',
        operacionDtic_s2IndicadorPoa,
        false,
        {idObjEspecifico: operacionDtic_s2Objetivo.val()},
        selected
    );
}

function cargarOpciones(url, select, guardarData = false, data = {}, selected = null) {
    return $.ajax({
        url,
        method: 'POST',
        dataType: 'json',
        data,
        success: function (response) {
            select.empty().append(new Option('', '', false, false));

            (response.data || []).forEach(item => {
                const option = new Option(
                    item.codigo ? `(${item.codigo}) - ${item.text}` : item.text,
                    item.id,
                    false,
                    item.id === selected
                );

                if (guardarData) {
                    $(option).data('data', item);
                }
                select.append(option);
            });

            select.val(selected).trigger('change.select2');
        }
    });
}
