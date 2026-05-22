/*global idObjEstrategico*/
let dt_progind
$(document).ready(function () {

    function format(d) {
        // Usamos el ID del indicador para que NUNCA se repitan los IDs en el DOM
        let id = d.IdIndicadorEstrategico;
        return `
            <div class="slider" style="display:none">
                <div class="p-3">
                    <!-- Loader específico para este indicador -->
                    <div id="loader_${id}" class="p-4">
                        <div class="table-loading"></div>
                        <div class="table-loading"></div>
                    </div>
                    <!-- Contenedor de tabs único -->
                    <div id="tabs_container_${id}" style="display:none; min-height: 300px;; height: 500px">
                    </div>
                </div>
            </div>`;
    }

    dt_progind = $("#oso").DataTable({
        initComplete: function () {
            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },
        ajax: {
            method: "POST",
            dataType: "json",
            data: {
                'idObjEstrategico':idObjEstrategico
            },
            url: "index.php?r=Planificacion/indicador-estrategico-programacion/listar-indicadores",
            dataSrc: "data",
            error: function (xhr) {
                // Si el servidor respondió con JSON, jQuery ya lo parseó en responseJSON
                const data = xhr.responseJSON || { mensaje: "Error desconocido" };
                MostrarMensaje('error', GenerarMensajeError(data["mensaje"]), data["errors"] || []);
                dt_progind.processing(false);
            }
        },
        columns: [
            {
                data: null,
                className: 'expandible',
                render: function (data, type, row) {

                    const metaGlobal = parseFloat(row["Meta"]);
                    const metaProg = parseFloat(row["MetaProgramada"]);

                    // Determinamos la clase inicial
                    let colorClass = 'bg-warning'; // Default (Meta < MetaProgramada según tu lógica)
                    if (metaGlobal > metaProg) colorClass = 'bg-danger';
                    if (metaGlobal === metaProg) colorClass = 'bg-info';

                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                        <div style="display: flex;align-items:center;">
                            <span class="dtic-item-main mr-2">Indicador N° </span>
                                <div class="kpi-circle">
                                    ${row["Codigo"]}
                                </div>                                
                        </div>
                        
                        <div class="dtic-item-main">
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
                        
                        <div class="acc-footer">                                    
                            <div class="meta-box-left dtic-item-sub">
                                <span><strong>Meta Global</strong></span>
                                <span class="meta-badge">${row["Meta"]}</span>
                                <span><strong>Meta Programada</strong></span>
                                <span id="metaProg_${row["IdIndicadorEstrategico"]}" 
                                        class="meta-badge ${colorClass}" 
                                        data-meta-global="${metaGlobal}">
                                    ${row["MetaProgramada"]}
                                </span>
                            </div>
    
                            <div class="result-box">
                                <div class="result-top">
                                    <span class="badge-result">${row["catUnidadesIndicadores"]["Descripcion"]}</span>
                                    <span class="badge-result">${row["catTiposResultados"]["Descripcion"]}</span>
                                </div>
                                <span class="badge-result">${row["catCategoriasIndicadores"]["Descripcion"]}</span>
                            </div>            
                        </div>                         
                    `;
                }
            }
        ]
    });

    // Add event listener for opening and closing details
    $('#oso tbody').on('click', 'td.expandible', function () {
        let tr = $(this).closest('tr');
        let row = dt_progind.row(tr);

        if (row.child.isShown()) {
            // CERRAR CON ANIMACIÓN
            $('div.slider', row.child()).slideUp(function () {
                row.child.hide();
                tr.removeClass('shown');
            });
        } else {
            // --- LÓGICA DE ACORDEÓN ---
            // Cerramos cualquier otra fila que esté abierta antes de abrir la nueva
            dt_progind.rows().every(function () {
                if (this.child.isShown()) {
                    let existingTr = $(this.node());
                    $('div.slider', this.child()).slideUp(function () {
                        this.child.hide(); // 'this' aquí es el objeto row de la iteración
                        existingTr.removeClass('shown');
                    }.bind(this));
                }
            });

            // ABRIR CON ANIMACIÓN
            // Envolvemos el contenido de format(d) en un div.slider oculto
            row.child(format(row.data()), 'no-padding').show();
            tr.addClass('shown');
            $('div.slider', row.child()).slideDown();

            const dt_row = row.data()
            cargarTabs(dt_row["IdIndicadorEstrategico"]);
        }
    });


    function cargarTabs(idIndicador) {
        // Apuntamos al ID dinámico que creamos en format()
        let cont = $("#tabs_container_" + idIndicador);
        let loader = $("#loader_" + idIndicador);

        $.post('index.php?r=Planificacion/indicador-estrategico-programacion/listar-gestiones', { idIndicador: idIndicador }, function (data) {
            let resp = data.data;
            if (!resp || resp.length === 0) {
                loader.remove();
                cont.html('<div class="alert alert-info">No hay gestiones.</div>').fadeIn(250);
                return;
            }

            let nav = `<nav><div class="nav nav-tabs tabs-nav" id="nav-tab" role="tablist">`;
            let body = `<div class="tab-content" id="nav-tabContent">`;

            resp.forEach((g, i) => {
                let active = i === 0 ? 'active' : '';
                let show = i === 0 ? 'show' : '';
                let tableId = `tbl_${idIndicador}_${g.IdGestion}`;
                let paneId = `pane_${idIndicador}_${g.IdGestion}`;

                nav += `
                    <button class="nav-link ${active} tab-btn" 
                        id="nav-${paneId}-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#nav-${paneId}" 
                        data-idindicador="${idIndicador}"
                        data-idgestion="${g.IdGestion}"
                        data-tableid = "${tableId}"
                        type="button" role="tab" 
                        aria-controls="nav-${paneId}" 
                        aria-selected="${active}">${g["Gestion"]}
                    </button>`;

                body += `
                    <div class="tab-pane fade ${show} ${active}" id="nav-${paneId}" role="tabpanel" aria-labelledby="nav-${paneId}-tab">
                        <div class="text-end mb-2">
                            <button class="btn btn-sm btn-primary btnNuevaFila" 
                                    data-idindicador="${idIndicador}" 
                                    data-idgestion="${g.IdGestion}">
                                <i class="fa fa-plus"></i> Agregar llaves
                            </button>
                        </div>
                        <div class="table-responsive table-container">
                            <table id="${tableId}" class="table table-sm table-bordered w-100">
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

            nav += `</div></nav>`;
            body += `</div>`;

            loader.remove();
            cont.html(nav + body).fadeIn(250);

            // Inicializar la tabla de la primera gestión
            initDataTableGestion(idIndicador, resp[0].IdGestion, `tbl_${idIndicador}_${resp[0].IdGestion}`);
            //initTabla(idIndicador, resp[0].IdGestion);
        });
    }

    // Evento para cambiar de pestaña
    $(document).on('click', '.tab-btn', function() {
        let btn = $(this);
        let idIndicador = btn.data('idindicador');
        let idGestion = btn.data('idgestion');
        let paneId = btn.data('pane');
        let tableId = btn.data('tableid');

        // 3. Inicializar o Recargar la DataTable de esa Gestión
        initDataTableGestion(idIndicador, idGestion, tableId);
    });

    function initDataTableGestion(idIndicador, idGestion, tableId) {
        let selector = `#${tableId}`;

        // Si la tabla ya existe, no la reinicializamos, solo la refrescamos si es necesario
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload();
            return;
        }


        $(selector).DataTable({
            destroy: true,
            ajax: {
                url: 'index.php?r=Planificacion/indicador-estrategico-programacion/listar-programacion',
                method: 'POST',
                data: {
                    idIndicadorEstrategico: idIndicador,
                    idGestion: idGestion
                }
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
                    render: function (data, type, row) {
                        return `
                            <input type="number" 
                                   readonly
                                   class="form-control form-control-sm input-editable-smart" 
                                   value="${data}" 
                                   data-id="${row.IdProgramacionIndicadorGestio}" 
                                   data-idindicador = "${idIndicador}"
                                   style="width: 100px; text-align: right; cursor: pointer; border-color: transparent; background: transparent;">
                            `;
                    }
                },
                {
                    className: 'dt-small dt-estado dt-center',
                    orderable: false,
                    searchable: false,
                    data: null,
                    render: function (data, type, row) {
                        return `<button  type="button" class="btn btn-outline-danger btn-sm btnQuitar"
                                    data-toggle="tooltip" 
                                    data-llave = ${row['IdProgramacionIndicadorGestio']}
                                    data-id = ${tableId}
                                    title="Click! para cambiar el estado del registro">
                                <span class="btn_text">Quitar</span>
                              </button>`;
                    },
                    visible: true
                },

            ],
            // Opciones mini para que no ocupen mucho espacio
            dom: 't',
            paging: false,

        });
    }

    function initTableModal(idIndicador, idGestion) {
        if ($.fn.DataTable.isDataTable('#tblModalDetalle')) {
            $('#tblModalDetalle').DataTable().destroy();
        }

        $('#tblModalDetalle').DataTable({
            initComplete: function(){
                this.api()
                    .columns([0])
                    .every(function () {
                        let column = this;
                        let select = $(
                            '</br><select><option value="">Buscar...</option></select>'
                        )
                            .appendTo($(column.header()))
                            .on("change", function () {
                                let val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? "^" + val + "$" : "", true, false).draw();
                            });
                        column
                            .data()
                            .unique()
                            .sort()
                            .each(function (d, j) {
                                select.append('<option value="' + d + '">' + d + "</option>");
                            });
                    });
            },
            ajax: {
                url: 'index.php?r=Planificacion/indicador-estrategico-programacion/listar-llaves',
                method: 'POST',
                data: { idIndicador, idGestion }
            },
            columns: [
                {
                    className: 'dt-small dt-center',
                    orderable: false,
                    data: 'Llave',
                    width: 100
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
                        return ( (type === 'display') && (row["Estado"] === '0'))
                            ? `<button id="btnEstado" type="button" class="btn btn-outline-success btn-sm btnEstado"
                                    data-idindicador= ${ $('#modal_idIndicador').val() }
                                    data-idgestion= ${ $('#modal_idGestion').val() }
                                    data-idllave = ${ row['IdLlavePresupuestaria'] }
                                    data-toggle="tooltip" 
                                    title="Click! para cambiar el estado del registro">
                                <span class="btn_text">Agregar</span>
                              </button>`
                            : `<button id="btnEstado" type="button" class="btn btn-outline-danger btn-sm btnEstado"
                                    data-idindicador= ${ $('#modal_idIndicador').val() }
                                    data-idgestion= ${ $('#modal_idGestion').val() }
                                    data-idllave = ${ row['IdLlavePresupuestaria'] }
                                    data-toggle="tooltip" title="Click! para cambiar el estado del registro">
                                <span class="btn_text">Quitar</span>
                              </button>` ;
                    },
                    visible: true
                }
            ]
        });
    }

    $(document).on('click', '.btnNuevaFila', function () {
        const btn = $(this);

        // 1. Extraer datos del botón que disparó el evento
        const idIndicador = btn.data('idindicador');
        const idGestion = btn.data('idgestion');
        // Buscamos el ID de la tabla que está en el mismo panel que el botón
        const tableIdOriginal = btn.closest('.tab-pane').find('table').attr('id');

        // 2. Rellenar los campos del modal declarado en la vista
        $('#modal_idIndicador').val(idIndicador);
        $('#modal_idGestion').val(idGestion);
        $('#modal_tableIdOriginal').val(tableIdOriginal);

        // Opcional: mostrar texto informativo
        $('#txtIndicador').text(idIndicador);
        $('#txtGestion').text(btn.closest('.tab-pane').attr('id')); // o cualquier lógica para el nombre
        $('#txtGestion').text(idGestion); // o cualquier lógica para el nombre

        // 3. Inicializar la tabla interna del modal
        initTableModal(idIndicador, idGestion);

        // 4. Mostrar el modal (usando el ID del HTML fijo)
        $('#modalProgramacion').modal('show');
    });



})

$(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {
    // Obtenemos los datos que guardamos en el botón
    // Nota: Necesitas agregar estos data-attributes en tu función cargarTabs
    let targetId = $(e.target).data('bs-target');
    let table = $(targetId).find('table.table');

    if (table.length) {
        let tableId = table.attr('id');
        // Aquí llamas a tu función de inicialización
        // Puedes extraer el idIndicador e idGestion del ID de la tabla o de data-attributes
        console.log("Inicializando tabla: " + tableId);
        // initDataTableGestion(idIndicador, idGestion, tableId);
    }
});

$('#modalProgramacion').on('hidden.bs.modal', function () {
    const tableId = $('#modal_tableIdOriginal').val();

    // Recargar la DataTable que quedó de fondo
    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
        $(`#${tableId}`).DataTable().ajax.reload(null, false);
    }

    // Aquí puedes llamar a tu lógica de suma de "Meta"
    const idIndicador = $('#modal_idIndicador').val();
    //actualizarSumaGlobal(idIndicador);
});

