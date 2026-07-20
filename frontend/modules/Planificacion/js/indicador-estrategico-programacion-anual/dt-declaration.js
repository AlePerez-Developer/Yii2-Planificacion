/*global idObjEstrategico*/
let dt_listaIndicadores

$(document).ready(function () {
    let openedRow = null;

    /***********Datatable Lista Indicadores***************/
    function format(d) {
        let id = d.Codigo;
        return `
            <div class="slider" style="display:none">
                <div class="p-3">
                    <!-- Loader específico para este indicador -->
                    <div id="loader_${id}" class="p-4">
                        <div class="table-loading"></div>
                        <div class="table-loading"></div>
                    </div>
                    <!-- Contenedor de tabs único -->
                    <div id="tabs_container_${id}" class="tab-container">
                    </div>
                </div>
            </div>`;
    }

    dt_listaIndicadores = $("#tablaListaIndicadores").DataTable({
        initComplete: function () {
            $("div.dt-search").append(`
                <button id="refreshTable" class="btn-refresh">
                    <i class="fas fa-sync-alt fa-spin"></i>
                </button>`
            );

            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },
        ajax: {
            method: "POST",
            dataType: "json",
            data: {
                'idObjEstrategico': idObjEstrategico
            },
            url: "index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-indicadores",
            dataSrc: "data",
            error: function (xhr) {
                const data = xhr.responseJSON || {mensaje: "Error desconocido"};
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"] || []);
                dt_listaIndicadores.processing(false);
            }
        },
        columns: [
            {
                data: "CodigoUsuario",
                className: "dt-dtic-text-center",
                width: "60px",
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<div class="badge-codigo">${data}</div>`;
                }
            },
            {data: 'Codigo', visible: false},
            {
                data: null,
                className: 'expandible',
                render: function (data, type, row) {

                    const metaGlobal = parseFloat(row["Meta"]);
                    const metaProgramada = parseFloat(row["MetaProgramada"]);

                    let colorClass = 'bg-warning';
                    if (metaGlobal > metaProgramada) colorClass = 'bg-danger';
                    if (metaGlobal === metaProgramada) colorClass = 'bg-info';

                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                        <div class="dtic-code-container">
                            <span class="dtic-code-text">Indicador N°</span>
                            <div class="dtic-code-badge">
                                ${row["Codigo"]}
                            </div>                                  
                        </div>
                        
                        <div class="dtic-item-main">
                            ${row["Descripcion"]}
                        </div>
                        
                        <div class="dtic-item-sub mb-1">
                            ${row["accionesEstrategicas"]["Descripcion"]} ${row["LineaBase"]} ${row["AccionDescripcion"]} ${row["Meta"]}  
                        </div>
                        
                        <!-- DESC -->
                        <div class="dtic-item-sub2 mb-2">
                            <span>OBJETIVO:</span> 
                            <b>${row["objetivosEstrategicos"]["areaEstrategica"]["Codigo"] + row["objetivosEstrategicos"]["politicaEstrategica"]["Codigo"] + row["objetivosEstrategicos"]["Codigo"]}</b> 
                            <span>${row["objetivosEstrategicos"]["Objetivo"]}</span>
                        </div>          
                        
                        <div class="acc-footer">                                    
                            <div class="meta-box-left dtic-item-sub">
                                <span class="meta-badge-text">Meta Global</span>
                                <span class="meta-badge">${row["Meta"]}</span>
                                <span class="meta-badge-text">Meta Programada</span>
                                <span id="metaProg_${row["IdIndicadorEstrategico"]}" 
                                        class="meta-badge ${colorClass}" 
                                        data-meta-global="${metaGlobal}">
                                    ${row["MetaProgramada"]}
                                </span>
                            </div>
    
                            <div class="result-box">
                                <span class="badge-result">${row["catUnidadesIndicadores"]["Descripcion"]}</span>
                                <span class="badge-result">${row["catTiposResultados"]["Descripcion"]}</span>
                                <span class="badge-result">${row["catCategoriasIndicadores"]["Descripcion"]}</span>
                            </div>            
                        </div>                         
                    `;
                }
            }
        ]
    });

    $(document).on("click", "#refreshTable", function () {
        dt_listaIndicadores.ajax.reload();
    });

    dt_listaIndicadores.on('order.dt search.dt', function () {
        let i = 1;
        dt_listaIndicadores.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();

    $('#tablaListaIndicadores tbody').on('click', 'td.expandible', function () {
        const tr = $(this).closest('tr');
        const currentRow = dt_listaIndicadores.row(tr);

        if (currentRow.child.isShown()) {
            closeChildRow(currentRow);
            openedRow = null;
            return;
        }

        if (openedRow && openedRow.child && openedRow.child.isShown()) {
            closeChildRow(openedRow);
        }

        currentRow.child(format(currentRow.data()), 'no-padding').show();
        tr.addClass('shown');

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180);

        openedRow = currentRow;
        const dtRow = currentRow.data();

        cargarTabs(dtRow.IdIndicadorEstrategico, dtRow.Codigo);
    });

    function closeChildRow(row) {
        const tr = $(row.node());

        $('div.slider', row.child())
            .stop(true, true)
            .slideUp(180, function () {
                row.child.hide();
                tr.removeClass('shown');
            });
    }


    function cargarTabs(idIndicadorEstrategico, codigoIndicador) {
        let contenedor = $("#tabs_container_" + codigoIndicador);
        let loader = $("#loader_" + codigoIndicador);

        $.post('index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-gestiones', {idIndicadorEstrategico: idIndicadorEstrategico}, function (data) {
            let respuesta = data.data;
            if (!respuesta || respuesta.length === 0) {
                loader.remove();
                contenedor.html('<div class="alert alert-info">No hay gestiones.</div>').fadeIn(250);
                return;
            }

            let nav = `<ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">`
            let body = `<div class="tab-content" id="pills-tabContent">`

            respuesta.forEach((g, i) => {
                let active = i === 0 ? 'active' : '';
                let show = i === 0 ? 'show' : '';
                let tableId = `tbl_${codigoIndicador}_${g.Gestion}`;
                let paneId = `pane_${codigoIndicador}_${g.Gestion}`;

                nav += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${active}  dtic-tab" 
                            id="nav-${paneId}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#nav-${paneId}" 
                            data-codigoindicador="${codigoIndicador}"
                            data-gestion="${g.Gestion}"
                            data-tableid = "${tableId}"
                            type="button" role="tab" 
                            aria-controls="nav-${paneId}"
                            aria-selected="${i === 0}">
                            ${g["Gestion"]}
                            <span id="sum_${tableId}" class="meta-badge bg-danger ms-2">${g.MetaProgramada}</span>
                        </button>
                    </li>`;

                body += `
                    <div class="tab-pane fade ${show} ${active}" id="nav-${paneId}" role="tabpanel" aria-labelledby="nav-${paneId}-tab">
                        <div class="text-end mb-3 mt-3">
                            <button class="btn-crear btnNuevaFila" 
                                    data-codigoindicador="${codigoIndicador}" 
                                    data-gestion="${g.Gestion}">
                                <i class="fa fa-plus"></i> Agregar llaves
                            </button>
                        </div>
                        <div class="table-responsive table-container">
                            <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100">
                                <thead>
                                    <tr>
                                        <th>Llave</th>
                                        <th>Descripcion</th>
                                        <th>Meta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>                    
                    </div>`;
            });

            nav += `</ul>`
            body += `</div>`;

            loader.remove();
            contenedor.html(nav + body).fadeIn(250);
            initDataTableGestion(codigoIndicador, respuesta[0]['Gestion'], `tbl_${codigoIndicador}_${respuesta[0]['Gestion']}`);
        });
    }

    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function () {
        let btn = $(this);
        let codigoIndicador = btn.data('codigoindicador');
        let gestion = btn.data('gestion');
        let tableId = btn.data('tableid');

        initDataTableGestion(codigoIndicador, gestion, tableId);
    });


    function initDataTableGestion(codigoIndicador, gestion, tableId) {
        let selector = `#${tableId}`;

        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload();
            return;
        }

        $(selector).DataTable({
            destroy: true,
            ajax: {
                url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-programacion',
                method: 'POST',
                dataType: "json",
                data: {
                    codigoIndicador: codigoIndicador,
                    gestion: gestion
                },
            },
            columns: [
                {
                    className: 'dt-small',
                    data: 'Llave',
                    width: 150
                },
                {
                    className: 'dt-small',
                    data: 'Descripcion'
                },
                {
                    className: 'dt-small dt-estado dt-center',
                    data: 'Meta',
                    width: 100,
                    render: function (data) {
                        return `
                            <input type="number" 
                                   readonly
                                   class="form-control form-control-sm input-meta" 
                                   value="${data}" 
                                   data-codigoindicador="${codigoIndicador}"
                                   data-gestion="${gestion}">
                            `;
                    }
                },
                {
                    className: 'dt-small dt-estado dt-center',
                    orderable: false,
                    searchable: false,
                    data: null,
                    render: function () {
                        return `<button  type="button" class="btn-cancel btnQuitar"
                                    data-toggle="tooltip" 
                                    data-codigoindicador="${codigoIndicador}"
                                    data-gestion="${gestion}"
                                    title="Click! para cambiar el estado del registro">
                                <span class="btn_text">Quitar</span>
                              </button>`;
                    },
                    visible: true
                },

            ],
            dom: 't',
            paging: false,
        });
    }

    $(document).on('click', '.btnNuevaFila', function () {
        const btn = $(this);

        // 1. Extraer datos del botón que disparó el evento
        const codigoIndicador = btn.data('codigoindicador');
        const gestion = btn.data('gestion');

        // Buscamos el ID de la tabla que está en el mismo panel que el botón
        const tableIdOriginal = btn.closest('.tab-pane').find('table').attr('id');


        // 2. Rellenar los campos del modal declarado en la vista
        $('#modal_Indicador').val(codigoIndicador);
        $('#modal_Gestion').val(gestion);
        $('#modal_tableIdOriginal').val(tableIdOriginal);


        // 3. Inicializar la tabla interna del modal
        initTableModal(codigoIndicador, gestion);

        // 4. Mostrar el modal (usando el ID del HTML fijo)
        $('#modalLlaves').modal('show');
    });

    function initTableModal(codigoIndicador, gestion)
    {
        const dt_ModalDetalle = $('#tblModalDetalle')

        if ($.fn.DataTable.isDataTable('#tblModalDetalle')) {
            dt_ModalDetalle.DataTable().destroy();
            $('#tblModalDetalle thead select').remove();
        }

        dt_ModalDetalle.DataTable({
            initComplete: function () {
                this.api()
                    .columns([0])
                    .every(function () {
                        let column = this;

                        if ($(column.header()).find('select').length > 0) {
                            return;
                        }

                        let select = $(
                            '<select class="form-control form-control-sm"><option value="">Buscar DA...</option></select>'
                        )
                            .appendTo($(column.header()))
                            .on("change", function () {
                                let val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? "^" + val : "", true, false).draw();
                            });

                        let prefijosUnicos = new Set();

                        column
                            .data()
                            .each(function (d) {
                                if (d) {
                                    let prefijo = d.substring(0, 2);

                                    let num = parseInt(prefijo, 10);
                                    if (!isNaN(num) && num >= 1) {
                                        prefijosUnicos.add(prefijo);
                                    }
                                }
                            });

                        Array.from(prefijosUnicos)
                            .sort()
                            .forEach(function (prefijo) {
                                select.append('<option value="' + prefijo + '">' + prefijo + "</option>");
                            });
                    });
            },
            ajax: {
                url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-llaves',
                method: 'POST',
                data: {
                    codigoIndicador: codigoIndicador,
                    gestion: gestion
                },
            },
            columns: [
                {
                    className: 'dt-small dt-center',
                    orderable: false,
                    data: 'Llave',
                    width: 180
                },
                {
                    className: 'dt-small dt-left',
                    data: 'Descripcion'
                },
                {
                    className: 'dt-small dt-estado dt-center',
                    orderable: false,
                    searchable: false,
                    data: 'Estado',
                    render: function (data, type, row) {
                        return ((type === 'display') && (row["Estado"] === '0'))
                            ? `<button id="btnEstado" type="button" class="btn-toggle-estado estado-on btnEstado"
                                data-indicador= ${codigoIndicador}
                                data-gestion= ${gestion}
                                data-idllave = ${row['IdLlavePresupuestaria']}
                                data-toggle="tooltip" 
                                title="Click! para registrar la nueva llave presupuestaria">
                            <span class="btn_text">Agregar</span>
                          </button>`
                            : `<button id="btnEstado" type="button" class="btn-toggle-estado estado-off btnEstado"
                                data-indicador= ${codigoIndicador}
                                data-gestion= ${gestion}
                                data-idllave = ${row['IdLlavePresupuestaria']}
                                data-toggle="tooltip" title="Click! para quitar la llave presupuestaria">
                            <span class="btn_text">Quitar</span>
                          </button>`;
                    },
                    visible: true
                }
            ]
        });
    }
})

$('#modalLlaves').on('hidden.bs.modal', function () {
    const tableId = $('#modal_tableIdOriginal').val();

    // Recargar la DataTable que quedó de fondo
    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
        $(`#${tableId}`).DataTable().ajax.reload();
    }
});

