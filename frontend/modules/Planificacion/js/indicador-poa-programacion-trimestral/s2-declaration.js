let programacionPoaTrimestral_s2ObjEspecifico = $('#idObjEspecifico');

$(document).ready(function () {
    programacionPoaTrimestral_s2ObjEspecifico.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo específico',
        allowClear: true,
        width: '100%',
        templateResult: formato,
        templateSelection: formato,
        matcher: buscar
    });

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-objetivos-especificos-s2',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            programacionPoaTrimestral_s2ObjEspecifico
                .empty()
                .append(new Option('', '', false, false));

            (response.data || []).forEach(item => {
                const option = new Option(item.text, item.id, false, false);
                $(option).data('data', item);
                programacionPoaTrimestral_s2ObjEspecifico.append(option);
            });
            programacionPoaTrimestral_s2ObjEspecifico
                .val(null)
                .trigger('change.select2');
        },
        error: function (xhr) {
            const data = xhr.responseJSON || {};
            MostrarMensaje(
                'error',
                GenerarMensajeError(data.message || 'No se pudieron cargar los objetivos.'),
                data.errors
            );
        }
    });

    function formato(repo) {
        if (!repo.id) return repo.text;
        const data = repo.element ? ($(repo.element).data('data') || repo) : repo;
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">Código: ${data.compuesto || ''}</div>
            <div>${data.text || ''}</div>
        </div>`);
    }

    function buscar(params, data) {
        if ($.trim(params.term) === '') return data;
        const item = data.element ? ($(data.element).data('data') || data) : data;
        const contenido = `${item.compuesto || ''} ${item.text || ''}`.toLowerCase();
        return contenido.includes(params.term.toLowerCase()) ? data : null;
    }
});
