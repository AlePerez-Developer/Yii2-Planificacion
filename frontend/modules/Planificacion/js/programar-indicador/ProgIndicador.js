$(document).ready(function () {
    let baseUrl = "index.php?r=Planificacion/programar-indicador/"

    let idObj = appConfig.idObj;

    const datos = new FormData();
    datos.append("idObjEstrategico", idObj);

    try {
        ajaxPromise({
            url: baseUrl + "listar-todo",
            data: datos,
        }).then((data) => {
            response = data.data
            let html = `
                <div class="accordion accordion-flush" id="accordionPlan">
            `;

            if (response.length === 0) {
                html += `
                    <div class="alert alert-warning">
                        No existen registros.
                    </div>
                `;
            }

            $.each(response, function (i, row) {

                let idCollapse = 'collapse_' + row.Codigo;
                let idTabs = 'tabs_' + row.Codigo;

                <!-- REEMPLAZA SOLO EL HTML DEL ACCORDION DENTRO DEL $.each(response...) -->

                html += `
<div class="accordion-item border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <!-- ENCABEZADO MEJORADO -->
    <h2 class="accordion-header">

        <button class="accordion-button collapsed px-4 py-3 custom-accordion-header"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#${idCollapse}">

            <div class="w-100">

                <!-- FILA SUPERIOR -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div class="d-flex flex-wrap gap-2">

                        <span class="badge bg-primary fs-6 px-3 py-2">
                            Código ${row.Codigo}
                        </span>

                        <span class="badge bg-success fs-6 px-3 py-2">
                            Meta ${row.Meta}
                        </span>

                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        <span class="badge bg-warning text-dark px-3 py-2">
                            ${row.catCategoriasIndicadores.Descripcion}
                        </span>

                        <span class="badge bg-info text-dark px-3 py-2">
                            ${row.catTiposResultados.Descripcion}
                        </span>

                        <span class="badge bg-secondary px-3 py-2">
                            ${row.catUnidadesIndicadores.Descripcion}
                        </span>

                    </div>

                </div>

                <!-- DESCRIPCIÓN -->
                <div class="mt-3 pe-4">

                    <div class="fw-semibold text-dark">
                        ${row.Descripcion}
                    </div>

                </div>

            </div>

        </button>

    </h2>

    <!-- BODY -->
    <div id="${idCollapse}"
         class="accordion-collapse collapse"
         data-codigo="${row.Codigo}">

        <div class="accordion-body bg-light">



            <div id="${idTabs}">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2">Cargando gestiones...</span>
                </div>
            </div>

        </div>

    </div>

</div>
`;
            });

            html += `</div>`;

            $('#contenedorPrincipal').html(html);

            $('#loaderGeneral').hide();
            $('#contenedorPrincipal').fadeIn();

        });
    } catch (err) {
        console.error("Error al procesar:", err);
    }


    /* ==========================================================
       CARGAR TABS SOLO UNA VEZ AL ABRIR ACCORDION
    ========================================================== */
    $(document).on('shown.bs.collapse', '.accordion-collapse', function () {

        let codigoMeta = $(this).data('codigo');
        let contenedor = $('#tabs_' + codigoMeta);

        if (contenedor.data('loaded') === true)
            return;

        cargarGestiones(codigoMeta, contenedor);

    });


    function cargarGestiones(codigoMeta, contenedor) {
        $.ajax({

            url: baseUrl + "gestiones",
            type: 'POST',
            data: {
                codigoMeta: codigoMeta
            },
            dataType: 'json',

            success: function (response) {

                let nav = `<ul class="nav nav-tabs">`;
                let body = `<div class="tab-content border border-top-0 p-3">`;

                $.each(response, function (i, row) {

                    let active = i === 0 ? 'active' : '';
                    let show = i === 0 ? 'show active' : '';

                    let tabId = `tab_${codigoMeta}_${row.gestion}`;

                    nav += `
                    <li class="nav-item">
                        <button class="nav-link ${active}"
                                data-bs-toggle="tab"
                                data-bs-target="#${tabId}">
                            Gestión ${row.gestion}
                        </button>
                    </li>
                `;

                    body += `
                    <div class="tab-pane fade ${show}" id="${tabId}">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <h6 class="mb-0">
                                    Gestión ${row.gestion}
                                </h6>
                            </div>

                            <button class="btn btn-primary btn-sm btnAbrirModal"
                                    data-codigo="${codigoMeta}"
                                    data-gestion="${row.gestion}">
                                Ver detalle
                            </button>

                        </div>

                        <hr>

                        <p class="text-muted mb-0">
                            Información resumida de la gestión ${row.gestion}
                        </p>

                    </div>
                `;
                });

                nav += `</ul>`;
                body += `</div>`;

                contenedor.html(nav + body);
                contenedor.data('loaded', true);

            },

            error: function () {
                contenedor.html(`
                <div class="alert alert-danger">
                    Error al cargar gestiones.
                </div>
            `);
            }

        });
    }


    /* ==========================================================
       MODAL DINÁMICO
    ========================================================== */
    $(document).on('click', '.btnAbrirModal', function () {

        let codigo = $(this).data('codigo');
        let gestion = $(this).data('gestion');

        let modal = new bootstrap.Modal(
            document.getElementById('modalDetalle')
        );

        $('#contenidoModal').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <div class="mt-2">Cargando detalle...</div>
        </div>
    `);

        modal.show();

        $.ajax({

            url: urlModal,
            type: 'GET',
            data: {
                codigo: codigo,
                gestion: gestion
            },

            success: function (html) {
                $('#contenidoModal').html(html);
            },

            error: function () {
                $('#contenidoModal').html(`
                <div class="alert alert-danger">
                    No se pudo cargar el detalle.
                </div>
            `);
            }

        });

    });


    /* ==========================================================
       UTILITARIOS
    ========================================================== */
    function mostrarErrorGeneral() {
        $('#loaderGeneral').hide();

        $('#contenedorPrincipal')
            .html(`
            <div class="alert alert-danger">
                Error al cargar la información principal.
            </div>
        `)
            .show();
    }

})