let indicadorPoa_s2ObjEspecifico = $('#idObjEspecifico');

$(document).ready(function () {
    indicadorPoa_s2ObjEspecifico.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo específico',
        allowClear: true,
        width: '100%',
        templateResult: formato,
        templateSelection: formato,
        matcher: buscar
    });

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-poa/listar-objetivos-especificos-s2',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            indicadorPoa_s2ObjEspecifico.empty().append(new Option('', '', false, false));
            (response.data || []).forEach(item => {
                const option = new Option(item.text, item.id, false, false);
                $(option).data({compuesto: item.compuesto, producto: item.producto});
                indicadorPoa_s2ObjEspecifico.append(option);
            });
            indicadorPoa_s2ObjEspecifico.trigger('change');
        },
        error: manejarErrorDataTable
    });

    function formato(repo) {
        if (repo.loading || !repo.id) return repo.text;
        const element = repo.element ? $(repo.element) : null;
        const compuesto = repo.compuesto || element?.data('compuesto') || '';
        const producto = repo.producto || element?.data('producto') || '';

        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">Código: ${compuesto}</div>
            <div class="titulo-producto">${repo.text || ''}</div>
            <div class="subtitulo-producto">${producto}</div>
        </div>`);
    }

    function buscar(params, data) {
        if ($.trim(params.term) === '') return data;
        const term = params.term.toLowerCase();
        const element = data.element ? $(data.element) : null;
        const texto = (data.text || '').toLowerCase();
        const compuesto = (data.compuesto || element?.data('compuesto') || '').toLowerCase();
        const producto = (data.producto || element?.data('producto') || '').toLowerCase();
        return texto.includes(term) || compuesto.includes(term) || producto.includes(term) ? data : null;
    }
});
