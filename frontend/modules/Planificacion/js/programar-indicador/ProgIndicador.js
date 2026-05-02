let baseUrl = "index.php?r=Planificacion/programar-indicador/"

/* ==========================================================
   APP INDICADORES
========================================================== */

const AppIndicadores = {

    init() {
        if (!window.appConfig || !appConfig.idObj) {
            console.error('IdObjEstrategico no definidos');
            return;
        }

        this.IdObj = appConfig.idObj;
        this.cargarIndicadores();
    },

    /* ==========================================
       INDICADORES
    ========================================== */
    cargarIndicadores() {

        $.get(baseUrl + 'listar-indicadores', {IdObj: this.IdObj}, (resp) => {

            let html = `<div class="accordion" id="accIndicadores">`;

            resp.forEach(row => {
                html += this.templateAccordion(row);
            });

            html += `</div>`;

            $('#contenedor').html(html);

        }).fail(() => {
            MostrarMensaje('error', 'Error cargando indicadores');
        });
    },

    templateAccordion(row) {
        return `
        <div class="accordion-item shadow-sm mb-3 rounded-4">

            <h2 class="accordion-header">
                <button class="accordion-button collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#acc_${row.IdIndicadorEstrategico}">

                    <div class="w-100">

                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-primary">Meta ${row.Meta}</span>
                                <span class="badge bg-success">${row.Tipo}</span>
                            </div>
                            <span class="badge bg-secondary">${row.Categoria}</span>
                        </div>

                        <div class="mt-2 fw-bold">${row.Descripcion}</div>

                    </div>

                </button>
            </h2>

            <div id="acc_${row.IdIndicadorEstrategico}"
                 class="accordion-collapse collapse"
                 data-id="${row.IdIndicadorEstrategico}">

                <div class="accordion-body bg-light">
                    <div id="tabs_${row.IdIndicadorEstrategico}"></div>
                </div>

            </div>
        </div>`;
    },

    /* ==========================================
       TABS
    ========================================== */
    cargarTabs(idIndicador) {

        let cont = $('#tabs_' + idIndicador);
        if (cont.data('loaded')) return;

        $.get(baseUrl + 'listar-gestiones', {}, (resp) => {

            let nav = `<ul class="nav nav-tabs tabs-pei">`;
            let body = `<div class="tab-content tab-box">`;

            resp.forEach((g, i) => {

                let active = i === 0 ? 'active' : '';
                let show = i === 0 ? 'show active' : '';

                let tabId = `tab_${idIndicador}_${g.IdGestion}`;

                nav += `
                <li class="nav-item">
                    <button class="nav-link ${active}"
                            data-bs-toggle="tab"
                            data-idindicador="${idIndicador}"
                            data-idgestion="${g.IdGestion}"
                            data-bs-target="#${tabId}">
                        ${g.Gestion}
                    </button>
                </li>`;

                body += `
                <div class="tab-pane fade ${show}" id="${tabId}">

                    <div class="d-flex justify-content-between mb-3">

                        <h6 class="fw-bold text-primary">
                            Gestión ${g.Gestion}
                        </h6>

                        <button class="btn btn-outline-primary btn-sm btnLlaves"
                                data-idindicador="${idIndicador}"
                                data-idgestion="${g.IdGestion}">
                            + Agregar
                        </button>

                    </div>

                    <table class="table table-sm table-bordered"
                           id="tbl_${idIndicador}_${g.IdGestion}">
                    </table>

                </div>`;
            });

            nav += `</ul>`;
            body += `</div>`;

            cont.html(nav + body);
            cont.data('loaded', true);

            if (resp.length) {
                this.cargarTabla(idIndicador, resp[0].IdGestion);
            }

        });
    },

    /* ==========================================
       LOADER TABLA
    ========================================== */
    mostrarLoaderTabla(idIndicador, idGestion) {

        let tabla = `#tbl_${idIndicador}_${idGestion}`;

        $(tabla).html(`
            <tbody>
                <tr>
                    <td colspan="3">
                        <div class="table-loader">
                            <div class="spinner-border text-primary"></div>
                            <span class="ms-2">Cargando datos...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        `);
    },

    /* ==========================================
       DATATABLE
    ========================================== */
    cargarTabla(idIndicador, idGestion) {

        let id = `#tbl_${idIndicador}_${idGestion}`;

        if ($.fn.DataTable.isDataTable(id)) return;

        this.mostrarLoaderTabla(idIndicador, idGestion);

        $(id).DataTable({

            paging: false,
            searching: false,
            info: false,
            ordering: false,

            ajax: {
                url: baseUrl + 'listar-programacion',
                type: 'POST',
                data: {
                    IdIndicadorEstrategico: idIndicador,
                    IdGestion: idGestion
                }
            },

            columns: [
                {data: 'IdLlavePresupuestaria', title: 'Código'},
                {data: 'Descripcion', title: 'Descripción'},
                {
                    data: 'MetaProgramada',
                    title: 'Meta',
                    render: (data, row) => `
                        <input type="number" min="0"
                               class="form-control form-control-sm inputMeta"
                               data-id="${row.IdProgramacionIndicadorGestion}"
                               value="${data || 0}">
                    `
                }
            ]
        });
    },

    /* ==========================================
       REFRESH SIN RECARGA
    ========================================== */
    refrescarTabla(idIndicador, idGestion) {

        let tabla = `#tbl_${idIndicador}_${idGestion}`;

        if ($.fn.DataTable.isDataTable(tabla)) {
            $(tabla).DataTable().ajax.reload(null, false);
        }
    },

    /* ==========================================
       UPDATE META
    ========================================== */
    actualizarMeta(input) {

        let val = input.val().trim();

        if (val === '' || !/^\d+$/.test(val)) {
            MostrarMensaje('error', 'Solo números positivos');
            input.val(0);
            return;
        }

        input.addClass('border-warning');

        $.post(baseUrl + 'actualizar-meta', {
            id: input.data('id'),
            valor: val
        }, (resp) => {

            if (resp.success) {

                input.removeClass('border-warning').addClass('border-success');

                setTimeout(() => {
                    input.removeClass('border-success');
                }, 1500);

            } else {
                input.removeClass('border-warning');
                MostrarMensaje('error', resp.message, resp.errors);
            }

        }).fail(() => {
            input.removeClass('border-warning');
            MostrarMensaje('error', 'Error servidor');
        });
    }

};


/* ==========================================
   EVENTOS
========================================== */

$(document).ready(() => {
    AppIndicadores.init();
});

$(document).on('shown.bs.collapse', '.accordion-collapse', function () {
    AppIndicadores.cargarTabs($(this).data('id'));
});

$(document).on('shown.bs.tab', '.nav-link', function () {
    AppIndicadores.cargarTabla(
        $(this).data('idindicador'),
        $(this).data('idgestion')
    );
});

$(document).on('change', '.inputMeta', function () {
    AppIndicadores.actualizarMeta($(this));
});


/* ==========================================
   MODAL
========================================== */

$(document).on('click', '.btnLlaves', function () {

    let ind = $(this).data('idindicador');
    let ges = $(this).data('idgestion');

    new bootstrap.Modal('#modalLlaves').show();

    $('#tblLlaves').DataTable({
        destroy: true,
        ajax: 'listar-llaves',
        columns: [
            {data: 'IdLlavePresupuestaria', title: 'Código'},
            {data: 'Descripcion', title: 'Descripción'},
            {
                data: null,
                render: (row) => `
                    <button class="btn btn-success btn-sm btnSelectLlave"
                        data-id="${row.IdLlavePresupuestaria}"
                        data-ind="${ind}"
                        data-ges="${ges}">
                        Seleccionar
                    </button>`
            }
        ]
    });

});

$(document).on('click', '.btnSelectLlave', function () {

    let btn = $(this);

    let idIndicador = btn.data('ind');
    let idGestion = btn.data('ges');

    $.post('insertar-programacion', {
        IdIndicadorEstrategico: idIndicador,
        IdGestion: idGestion,
        IdLlavePresupuestaria: btn.data('id')
    }, function (resp) {

        if (resp.success) {

            MostrarMensaje('success', 'Registro agregado');

            $('#modalLlaves').modal('hide');

            AppIndicadores.refrescarTabla(idIndicador, idGestion);

        } else {
            MostrarMensaje('error', resp.message, resp.errors);
        }

    }).fail(() => {
        MostrarMensaje('error', 'Error al insertar');
    });

});