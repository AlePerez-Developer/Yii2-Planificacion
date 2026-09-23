let operacion_s2Objetivo = $('#idObjEspecifico');
let operacion_s2Llave = $('#idLlavePresupuestaria');
let operacion_s2IndicadorEstrategico = $('#idIndicadorEstrategico');
let operacion_s2IndicadorPoa = $('#idIndicadorPoa');
let operacionRestaurarIndicador = null;

$(document).ready(function () {
    operacion_s2Objetivo.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un objetivo específico',
        allowClear: true,
        width: '100%',
        dropdownCssClass: 'select2-dropdown-full',
        templateResult: formatoObjetivo,
        templateSelection: formatoObjetivo,
        matcher: buscar
    });

    operacion_s2Llave.select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una llave presupuestaria',
        allowClear: true,
        width: '100%',
        dropdownCssClass: 'select2-dropdown-full',
        templateResult: formatoLlave,
        templateSelection: formatoLlave,
        matcher: buscar
    });

    inicializarIndicador(
        operacion_s2IndicadorEstrategico,
        'Seleccione un indicador estratégico programado'
    );
    inicializarIndicador(
        operacion_s2IndicadorPoa,
        'Seleccione un indicador POA programado'
    );
    vaciarIndicadores();

    cargar(
        'index.php?r=Planificacion/operacion/listar-objetivos-s2',
        operacion_s2Objetivo
    );
    cargar(
        'index.php?r=Planificacion/operacion/listar-llaves-s2',
        operacion_s2Llave
    );

    operacion_s2Llave.on('change', function () {
        const idLlave = $(this).val();
        Promise.all([
            cargarIndicadores('estrategico', operacion_s2IndicadorEstrategico, idLlave),
            cargarIndicadores('poa', operacion_s2IndicadorPoa, idLlave)
        ]).then(() => {
            if (!operacionRestaurarIndicador) {
                sincronizarIndicador();
                return;
            }
            const restaurar = operacionRestaurarIndicador;
            operacionRestaurarIndicador = null;
            const select = restaurar.tipo === 'poa'
                ? operacion_s2IndicadorPoa
                : operacion_s2IndicadorEstrategico;
            const otro = restaurar.tipo === 'poa'
                ? operacion_s2IndicadorEstrategico
                : operacion_s2IndicadorPoa;
            otro.val(null).trigger('change.select2');
            select.val(restaurar.id).trigger('change.select2');
            sincronizarIndicador();
        });
    });

    operacion_s2IndicadorEstrategico.on('change', function () {
        if ($(this).val()) {
            operacion_s2IndicadorPoa.val(null).trigger('change.select2');
        }
        sincronizarIndicador();
    });

    operacion_s2IndicadorPoa.on('change', function () {
        if ($(this).val()) {
            operacion_s2IndicadorEstrategico.val(null).trigger('change.select2');
        }
        sincronizarIndicador();
    });

    function inicializarIndicador(select, placeholder) {
        select.select2({
            theme: 'bootstrap4',
            placeholder,
            allowClear: true,
            width: '100%',
            dropdownCssClass: 'select2-dropdown-full',
            templateResult: formatoIndicador,
            templateSelection: formatoIndicador,
            matcher: buscar
        });
    }

    function cargar(url, select) {
        $.ajax({
            url,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                llenarSelect(select, response.data || []);
            },
            error: mostrarErrorOperacion
        });
    }

    function cargarIndicadores(tipo, select, idLlave) {
        if (!idLlave) {
            vaciarSelect(select);
            return Promise.resolve();
        }

        return $.ajax({
            url: 'index.php?r=Planificacion/operacion/listar-indicadores-programados-s2',
            method: 'POST',
            dataType: 'json',
            data: {
                idLlavePresupuestaria: idLlave,
                tipoIndicador: tipo
            }
        }).then(function (response) {
            llenarSelect(select, response.data || []);
        }).catch(mostrarErrorOperacion);
    }

    function llenarSelect(select, items) {
        select.empty().append(new Option('', '', false, false));
        items.forEach(item => {
            const option = new Option(item.text, item.id, false, false);
            $(option).data('data', item);
            select.append(option);
        });
        select.val(null).trigger('change.select2');
        select.prop('disabled', false);
    }

    function vaciarSelect(select) {
        select.empty().append(new Option('', '', false, false));
        select.val(null).trigger('change.select2');
        select.prop('disabled', true);
    }

    function vaciarIndicadores() {
        vaciarSelect(operacion_s2IndicadorEstrategico);
        vaciarSelect(operacion_s2IndicadorPoa);
        sincronizarIndicador();
    }

    function formatoObjetivo(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">Código: ${escapar(data.compuesto || '')}</div>
            <div>${escapar(data.text || '')}</div>
        </div>`);
    }

    function formatoLlave(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">${escapar(data.text || '')}</div>
            <div>${escapar(data.unidad || '')}</div>
            <div class="select2-sub">Programa: ${escapar(data.programa || '')}</div>
            <div class="select2-sub">Proyecto: ${escapar(data.proyecto || '')}</div>
            <div class="select2-sub">Actividad: ${escapar(data.actividad || '')}</div>
        </div>`);
    }

    function formatoIndicador(repo) {
        if (!repo.id) return repo.text;
        const data = obtenerData(repo);
        const usado = Number(data.usado) === 1;
        return $(`<div class="mi-render-select2 ${usado ? 'indicador-usado' : 'indicador-disponible'}">
            <div class="indicador-option-header">
                <span class="badge-indicador">${escapar(data.tipoIndicador || '')}</span>
                <strong>Indicador ${escapar(data.codigo || '')}</strong>
                <span class="badge-uso ${usado ? 'usado' : 'disponible'}">
                    ${usado ? 'Ya utilizado' : 'Sin usar'}
                </span>
            </div>
            <div>${escapar(data.text || '')}</div>
            <div class="select2-sub">
                Meta gestión: <b>${escapar(data.meta ?? 0)}</b>
            </div>
            <div class="select2-sub">
                Programación trimestral: T1 ${escapar(data.t1 ?? 0)} · T2 ${escapar(data.t2 ?? 0)} · T3 ${escapar(data.t3 ?? 0)} · T4 ${escapar(data.t4 ?? 0)}
            </div>
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

    function escapar(valor) {
        return $('<div>').text(String(valor)).html();
    }
});

function sincronizarIndicador() {
    $('#idIndicador').val(
        operacion_s2IndicadorEstrategico.val() || operacion_s2IndicadorPoa.val() || ''
    );
}

function vaciarSelectsIndicadorOperacion() {
    operacion_s2IndicadorEstrategico.empty().append(new Option('', '', false, false));
    operacion_s2IndicadorPoa.empty().append(new Option('', '', false, false));
    operacion_s2IndicadorEstrategico.val(null).trigger('change.select2').prop('disabled', true);
    operacion_s2IndicadorPoa.val(null).trigger('change.select2').prop('disabled', true);
    sincronizarIndicador();
}
