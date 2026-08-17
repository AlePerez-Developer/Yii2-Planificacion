let programacionPoaAnual_s2ObjEspecifico = $('#idObjEspecifico');
let programacionPoaAnual_s2Llave = $('#idLlavePresupuestaria');
let programacionPoaAnual_s2Indicador = $('#idIndicadorPoa');

$(document).ready(function () {
    inicializarSelect(
        programacionPoaAnual_s2ObjEspecifico,
        'Seleccione un objetivo específico',
        formatoObjetivo
    );
    inicializarSelect(
        programacionPoaAnual_s2Llave,
        'Seleccione una llave presupuestaria',
        formatoLlave,
        $('#modalRelacionPoa')
    );
    inicializarSelect(
        programacionPoaAnual_s2Indicador,
        'Seleccione un indicador POA',
        formatoIndicador,
        $('#modalRelacionPoa')
    );

    cargarOpciones(
        'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-objetivos-especificos-s2',
        programacionPoaAnual_s2ObjEspecifico
    );
    cargarOpciones(
        'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-llaves-s2',
        programacionPoaAnual_s2Llave
    );
    cargarOpciones(
        'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-indicadores-s2',
        programacionPoaAnual_s2Indicador
    );

    function inicializarSelect(select, placeholder, template, dropdownParent = null) {
        select.select2({
            theme: 'bootstrap4',
            placeholder,
            allowClear: true,
            width: '100%',
            dropdownParent,
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
            },
            error: mostrarError
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

    function mostrarError(xhr) {
        const data = xhr.responseJSON || {};
        MostrarMensaje(
            'error',
            GenerarMensajeError(data.message || 'No se pudieron cargar las opciones.'),
            data.errors
        );
    }
});
