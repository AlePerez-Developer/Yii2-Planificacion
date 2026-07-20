/*global idObjEstrategico*/
let tablas = {};
let baseUrl = "index.php?r=Planificacion/indicador-estrategico-programacion-anual/"
$(document).ready(function () {
    //cargarIndicadores();
});

/* ============================
   INDICADORES
============================ */
function cargarIndicadores() {

    $("#loaderIndicadores").show();
    $("#contenedorAccordion").hide();
    $("#emptyIndicadores").hide();

    const datos = new FormData();
    datos.append("idObjEstrategico", idObjEstrategico);
    try {
        ajaxPromise({
            url: baseUrl + "listar-indicadores",
            data: datos,
        }).then((data) => {
            $("#loaderIndicadores").hide();

            let lista = data.data
            let html = '';

            lista.forEach((row, i) => {
                const metaClass =
                    row['Meta'] > row['MetaProgramada']
                        ? 'bg-danger'
                        : row['Meta'] === row['MetaProgramada']
                            ? 'bg-info'
                            : 'bg-warning';

                html += `
                        <div class="acc-item">
                            <div class="acc-header""
                                 data-index="${i}"
                                 data-idindicador="${row["IdIndicadorEstrategico"]}">
                                <div style="display: flex;align-items:center;">
                                    <span class="dtic-item-main mr-2">Indicador N° </span>
                                    <div class="kpi-circle">
                                        ${row["Codigo"]}
                                    </div>                                
                                </div>
                                
            
                                <!-- DESC -->
                                <div class="acc-desc">
                                    ${row["Descripcion"]}
                                </div>
                                <!-- DESC -->
                                <div class="dtic-item-sub">
                                    Linea Base: ${row["LineaBase"]}
                                </div>
                                
                                <!-- DESC -->
                                <div class="dtic-item-sub">
                                    Objetivo:   ${row["objetivosEstrategicos"]["areaEstrategica"]["Codigo"] + row["objetivosEstrategicos"]["politicaEstrategica"]["Codigo"] + row["objetivosEstrategicos"]["Codigo"]}   -   ${row["objetivosEstrategicos"]["Objetivo"]}
                                </div>                                
            
                                <!-- FOOTER -->
                                <div class="acc-footer">                                    
                                    <div class="meta-box-left dtic-item-sub">
                                        <span>Meta Global</span>
                                        <span class="meta-badge">${row["Meta"]}</span>
                                        <span>Meta Programada</span>
                                        <span id="metaProgramada" class="meta-badge ${metaClass}">${row["MetaProgramada"]}</span>
                                    </div>
            
                                    <div class="result-box">
                                        <div class="result-top">
                                            <span class="badge-result">${row["catUnidadesIndicadores"]["Descripcion"]}</span>
                                            <span class="badge-result">${row["catTiposResultados"]["Descripcion"]}</span>
                                        </div>
                                        <span class="badge-result">${row["catCategoriasIndicadores"]["Descripcion"]}</span>
                                    </div>            
                                </div>            
                            </div>

                            <div class="acc-body" id="body_${i}">
                                <div id="tabs_${row["IdIndicadorEstrategico"]}"></div>
                            </div>

                        </div>`;
            });
            $("#contenedorAccordion").html(html).fadeIn(300);
        });
    } catch (err) {
        console.error("Error al procesar:", err);
    }
}

/* ============================
   ACCORDION
============================ */
$(document).on('click', '.acc-header', function () {

    let index = $(this).data('index');
    let idIndicador = $(this).data('idindicador');
    let body = $("#body_" + index);

    if (body.is(':visible')) {
        body.slideUp();
        return;
    }

    $(".acc-body").slideUp();
    body.slideDown();

    if (!body.data('loaded')) {
        cargarTabs(idIndicador);
        body.data('loaded', true);
    }
});

/* ============================
   TABS
============================ */
function cargarTabs(idIndicador) {

    let cont = $("#tabs_" + idIndicador);

    $.post(baseUrl + 'listar-gestiones', {idIndicador}, function (data) {
        let resp = data.data
        let nav = `<div class="tabs-nav">`;
        let body = `<div>`;

        resp.forEach((g, i) => {

            let active = i === 0 ? 'active' : '';
            let tableId = `tbl_${idIndicador}_${g.IdGestion}`;

            nav += `
            <button class="tab-btn ${active}"
                data-idindicador="${idIndicador}"
                data-idgestion="${g.IdGestion}"
                data-table="${tableId}">
                ${g["Gestion"]}
            </button>`;

            body += `
            <div class="tab-pane ${active}">

                <div class="text-end mb-2">
                    <button class="btn btn-primary btnNuevaFila"
                        data-idindicador="${idIndicador}"
                        data-idgestion="${g.IdGestion}">
                        + Nueva fila
                    </button>
                </div>

                <div class="table-container">
                    <div class="table-scroll">
                        <table id="${tableId}" class="table w-100"></table>
                    </div>
                </div>

            </div>`;
        });

        nav += `</div>`;
        body += `</div>`;

        cont.html(nav + body);

        if (resp.length) {
            initTabla(idIndicador, resp[0].IdGestion);
        }
    });
}

/* ============================
   CAMBIO TAB
============================ */
$(document).on('click', '.tab-btn', function () {

    let btn = $(this);
    let idIndicador = btn.data('idindicador');
    let idGestion = btn.data('idgestion');
    let tableId = btn.data('table');

    btn.siblings().removeClass('active');
    btn.addClass('active');

    let wrapper = btn.closest('#tabs_' + idIndicador);

    wrapper.find('.tab-pane').removeClass('active');
    wrapper.find(`#${tableId}`).closest('.tab-pane').addClass('active');

    if (!tablas[tableId]) {
        initTabla(idIndicador, idGestion);
    } else {
        tablas[tableId].ajax.reload(null, false);
    }

    setTimeout(() => {
        tablas[tableId].columns.adjust();
    }, 100);
});

/* ============================
   DATATABLE
============================ */
function initTabla(idIndicador, idGestion) {

    let id = `#tbl_${idIndicador}_${idGestion}`;

    tablas[`tbl_${idIndicador}_${idGestion}`] = $(id).DataTable({

        paging: false,
        searching: false,
        info: false,

        ajax: {
            url: baseUrl + 'listar-programacion',
            type: 'POST',
            data: {
                idIndicadorEstrategico: idIndicador,
                idGestion: idGestion
            }
        },

        columns: [
            {data: 'Llave', title: 'Llave'},
            {data: 'Descripcion', title: 'Descripción'},
            {
                data: 'Meta',
                render: (d, row) => `
                    <input type="number"
                        class="inputMeta"
                        data-id="${row.IdProgramacionIndicadorGestion}"
                        value="${d || 0}">
                `
            }
        ]
    });
}

$(document).on('click', '.btnNuevaFila', function () {

    let idIndicador = $(this).data('idindicador');
    let idGestion = $(this).data('idgestion');

    let tableId = `#tbl_${idIndicador}_${idGestion}`;
    let table = tablas[`tbl_${idIndicador}_${idGestion}`];

    let rowNode = table.row.add({
        Llave: '',
        Descripcion: '',
        Meta: ''
    }).draw(false).node();

    $(rowNode).addClass('fila-nueva');

    $(rowNode).html(`
        <td colspan="3">
            <div class="row g-2 align-items-center">

                <div class="col-md-6">
                    <input type="text" class="form-control inputLlave"
                        placeholder="Buscar código o descripción...">
                </div>
                
                <div class="col-md-3">
                    <input type="number" class="form-control inputMetaNueva"
                        placeholder="Meta">
                </div>

                <div class="col-md-3 text-end">
                    <button class="btn btn-danger btnEliminarFila">✕</button>
                </div>

            </div>
        </td>
    `);

    cargarAutocomplete($(rowNode).find('.inputLlave'));
});

function cargarAutocomplete(input) {

    input.autocomplete({
        minLength: 2,
        source: function (request, response) {

            $.post(baseUrl + 'buscar-llaves', {
                term: request.term
            }, function (data) {

                response($.map(data, function (item) {
                    return {
                        label: item.Llave + ' | ' + item.Descripcion,
                        value: item.Llave + ' | ' + item.Descripcion,
                        id: item.IdLlavePresupuestaria
                    };
                }));
            });
        },

        select: function (e, ui) {
            $(this).data('id', ui.item.id);
        }
    });
}

$(document).on('keypress', '.inputMetaNueva', function (e) {

    if (e.which !== 13) return;

    let fila = $(this).closest('tr');

    let inputLlave = fila.find('.inputLlave');
    let idLlave = inputLlave.data('id');

    let meta = $(this).val();

    let btn = fila.closest('.tab-pane').find('.btnNuevaFila');

    let idIndicador = btn.data('idindicador');
    let idGestion = btn.data('idgestion');

    if (!idLlave) {
        MostrarMensaje('warning', 'Seleccione una llave válida');
        return;
    }

    if (!/^\d+$/.test(meta)) {
        MostrarMensaje('error', 'Meta inválida');
        return;
    }

    $.post(baseUrl + 'guardar-programacion', {
        IdIndicadorEstrategico: idIndicador,
        IdGestion: idGestion,
        IdLlavePresupuestaria: idLlave,
        Meta: meta
    }, function (resp) {
        if (resp.message === 'ok') {

            MostrarMensaje('success', 'Registro guardado');

            let tableId = `tbl_${idIndicador}_${idGestion}`;
            tablas[tableId].ajax.reload(null, false);

        } else {
            MostrarMensaje('error', resp.message);
        }
    });
});

$(document).on('click', '.btnEliminarFila', function () {
    $(this).closest('tr').remove();
});