let dt_listaIndicadores
let openedRow = null

function paNumero(valor) {
    const n = parseFloat(valor)
    return isNaN(n) ? 0 : n
}

function paFormatoMeta(valor) {
    const n = paNumero(valor)
    return Number.isInteger(n) ? String(n) : String(Math.round(n * 100) / 100)
}

function paTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function paIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function textoAeiProgramacion(row) {
    const accion = row.accionesEstrategicas || {}
    const codigo = (accion.Codigo != null && accion.Codigo !== '')
        ? accion.Codigo
        : (row.AccionCodigo != null && row.AccionCodigo !== '' ? row.AccionCodigo : '—')
    const descripcion = accion.Descripcion
        || row.AccionDescripcion
        || 'Sin acción asignada'
    return 'AEI: ' + codigo + ' - ' + descripcion
}

function paEstadoMeta(metaGlobal, metaProgramada) {
    const total = paNumero(metaGlobal)
    const prog = paNumero(metaProgramada)
    if (total > 0 && prog >= total) {
        return {clase: 'completo', texto: 'Completo'}
    }
    return {clase: 'pendiente', texto: 'Pendiente'}
}

function renderMetaFracPa(metaProgramada, metaGlobal, idSuffix) {
    const estado = paEstadoMeta(metaGlobal, metaProgramada)
    return `
        <div class="pg-meta-wrap" id="paMetaWrap_${idSuffix}">
            <div class="pg-meta-numbers">
                <span class="pg-meta-prog" id="paMetaProg_${idSuffix}">${paFormatoMeta(metaProgramada)}</span>
                <small> / <span id="paMetaTot_${idSuffix}">${paFormatoMeta(metaGlobal)}</span></small>
            </div>
            <span class="pg-badge ${estado.clase}" id="paMetaBadge_${idSuffix}">
                <span class="dot"></span>${estado.texto}
            </span>
        </div>
    `
}

function renderTabMetaBadge(tableId, metaProgramada, metaGlobal) {
    const estado = paEstadoMeta(metaGlobal, metaProgramada)
    return `
        <span id="sum_${tableId}"
              class="pa-tab-meta ${estado.clase}"
              data-meta-global="${paNumero(metaGlobal)}"
              title="Programada / Meta global gestión">
            ${paFormatoMeta(metaProgramada)}/${paFormatoMeta(metaGlobal)}
        </span>
    `
}

function actualizarMetaFracDom(idSuffix, metaProgramada, metaGlobal) {
    const estado = paEstadoMeta(metaGlobal, metaProgramada)
    $('#paMetaProg_' + idSuffix).text(paFormatoMeta(metaProgramada))
    $('#paMetaTot_' + idSuffix).text(paFormatoMeta(metaGlobal))
    const $badge = $('#paMetaBadge_' + idSuffix)
    $badge.removeClass('completo pendiente parcial')
        .addClass(estado.clase)
        .html('<span class="dot"></span>' + estado.texto)
    $('#paMetaWrap_' + idSuffix).fadeOut(80).fadeIn(80)
}

function actualizarTabMetaDom(tableId, metaProgramada, metaGlobal) {
    const estado = paEstadoMeta(metaGlobal, metaProgramada)
    const $badge = $('#sum_' + tableId)
    $badge
        .removeClass('completo pendiente parcial')
        .addClass(estado.clase)
        .attr('data-meta-global', paNumero(metaGlobal))
        .text(paFormatoMeta(metaProgramada) + '/' + paFormatoMeta(metaGlobal))
        .fadeOut(80).fadeIn(80)
}

function format(d) {
    const id = d.Codigo
    return `
        <div class="slider" style="display:none">
            <div class="p-3">
                <div id="loader_${id}" class="p-4">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="tabs_container_${id}" class="tab-container"></div>
            </div>
        </div>`
}

function closeChildRow(row) {
    if (!row || !row.child || !row.child.isShown()) {
        return
    }
    const tr = $(row.node())
    $('div.slider', row.child())
        .stop(true, true)
        .slideUp(180, function () {
            row.child.hide()
            tr.removeClass('shown')
        })
}

function inicializarTablaIndicadores() {
    dt_listaIndicadores = $('#tablaListaIndicadores').DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
            bottom: null,
        },
        paging: false,
        info: false,
        language: {
            emptyTable: 'No hay indicadores para el objetivo seleccionado',
            zeroRecords: 'No se encontraron coincidencias',
        },
        initComplete: function () {
            $('#dticTableLoading').hide()
            $('#dticTableContainer').fadeIn(250)
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            data: function () {
                const selected = programacionAnual_s2ObjEstrategico.select2('data')[0]
                return {idObjEstrategico: selected ? selected.id : null}
            },
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-indicadores',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {mensaje: 'Error desconocido'}
                MostrarMensaje('error', GenerarMensajeError(data.mensaje || data.message), data.errors || [])
                if (dt_listaIndicadores) {
                    dt_listaIndicadores.processing(false)
                }
            },
        },
        columns: [
            {
                data: null,
                className: 'expandible',
                orderable: false,
                render: function (data, type, row) {
                    const indicador = row.indicador || {}
                    const descripcion = indicador.Descripcion || row.Descripcion || ''
                    if (type !== 'display') {
                        return descripcion
                    }

                    const metaGlobal = paNumero(row.MetaGlobal != null ? row.MetaGlobal : row.Meta)
                    const metaProgramada = paNumero(row.MetaProgramada)
                    const idSuffix = row.Codigo

                    return `
                        <div class="card-row iepa-card-row">
                            <span class="row-num">${paTexto(row.Codigo || '')}</span>
                            <div class="patient">
                                <div class="avatar" style="background:#0284c7">${paIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${paTexto(descripcion)} <span class="code-badge">${paTexto(row.Codigo || '')}</span></p>
                                    <p class="patient-meta">${paTexto(textoAeiProgramacion(row))}</p>
                                    <p class="patient-meta pg-hint">Clic para programar llaves por gestión</p>
                                </div>
                            </div>
                            ${renderMetaFracPa(metaProgramada, metaGlobal, idSuffix)}
                        </div>
                    `
                },
            },
            {data: 'Codigo', visible: false},
            {data: 'IdIndicador', visible: false, searchable: false},
        ],
    })
}

function cargarTabs(idIndicadorEstrategico, codigoIndicador) {
    const contenedor = $('#tabs_container_' + codigoIndicador)
    const loader = $('#loader_' + codigoIndicador)

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-gestiones',
        method: 'POST',
        dataType: 'json',
        data: {idIndicadorEstrategico: idIndicadorEstrategico},
        success: function (data) {
            const respuesta = data.data || []
            if (!respuesta.length) {
                loader.remove()
                contenedor.html('<div class="alert alert-info">No hay gestiones.</div>').fadeIn(250)
                return
            }

            let nav = '<ul class="nav nav-pills nav-fill" role="tablist">'
            let body = '<div class="tab-content">'

            respuesta.forEach(function (g, i) {
                const active = i === 0 ? 'active' : ''
                const show = i === 0 ? 'show' : ''
                const tableId = 'tbl_' + codigoIndicador + '_' + g.Gestion
                const paneId = 'pane_' + codigoIndicador + '_' + g.Gestion
                const metaGlobalGestion = paNumero(g.MetaGlobal)
                const metaProgGestion = paNumero(g.MetaProgramada)

                nav += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${active} dtic-tab"
                            id="nav-${paneId}-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-${paneId}"
                            data-codigoindicador="${codigoIndicador}"
                            data-idindicador="${idIndicadorEstrategico}"
                            data-gestion="${g.Gestion}"
                            data-tableid="${tableId}"
                            data-metaglobal="${metaGlobalGestion}"
                            type="button" role="tab">
                            ${g.Gestion}
                            ${renderTabMetaBadge(tableId, metaProgGestion, metaGlobalGestion)}
                        </button>
                    </li>`

                body += `
                    <div class="tab-pane fade ${show} ${active}" id="nav-${paneId}" role="tabpanel">
                        <div class="iepa-tab-toolbar">
                            <span class="iepa-tab-meta">
                                Meta gestión: <strong>${paFormatoMeta(metaProgGestion)}/${paFormatoMeta(metaGlobalGestion)}</strong>
                            </span>
                            <button type="button" class="btn-crear btnNuevaFila"
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
                                        <th>Descripción</th>
                                        <th>Meta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>`
            })

            nav += '</ul>'
            body += '</div>'

            loader.remove()
            contenedor.html(nav + body).fadeIn(250)
            initDataTableGestion(
                codigoIndicador,
                respuesta[0].Gestion,
                'tbl_' + codigoIndicador + '_' + respuesta[0].Gestion
            )
        },
        error: function (xhr) {
            loader.remove()
            const data = xhr.responseJSON || {}
            contenedor.html(
                '<div class="alert alert-danger">No se pudieron cargar las gestiones.</div>'
            ).show()
            MostrarMensaje('error', GenerarMensajeError(data.message || data.mensaje || 'Error'), data.errors || [])
        },
    })
}

function initDataTableGestion(codigoIndicador, gestion, tableId) {
    const selector = '#' + tableId

    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().ajax.reload()
        return
    }

    $(selector).DataTable({
        destroy: true,
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-programacion',
            method: 'POST',
            dataType: 'json',
            data: {
                codigoIndicador: codigoIndicador,
                gestion: gestion,
            },
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {}
                MostrarMensaje('error', GenerarMensajeError(data.message || data.mensaje || 'Error'), data.errors || [])
            },
        },
        columns: [
            {className: 'dt-small', data: 'Llave', width: 150},
            {className: 'dt-small', data: 'Descripcion'},
            {
                className: 'dt-small dt-estado dt-center',
                data: 'Meta',
                width: 150,
                render: function (data) {
                    return `
                        <input type="number"
                               min="0"
                               step="1"
                               readonly
                               class="form-control form-control-sm input-meta"
                               value="${data}"
                               data-codigoindicador="${codigoIndicador}"
                               data-gestion="${gestion}">
                    `
                },
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: null,
                render: function () {
                    return `<button type="button" class="btn-cancel btnQuitar"
                                data-codigoindicador="${codigoIndicador}"
                                data-gestion="${gestion}"
                                title="Quitar programación">
                            <span class="btn_text">Quitar</span>
                          </button>`
                },
            },
        ],
        dom: 't',
        paging: false,
    })
}

function initTableModal(codigoIndicador, gestion) {
    const $tabla = $('#tblModalDetalle')

    if ($.fn.DataTable.isDataTable('#tblModalDetalle')) {
        $tabla.DataTable().destroy()
        $('#tblModalDetalle thead select').remove()
    }

    $tabla.DataTable({
        initComplete: function () {
            this.api().columns([0]).every(function () {
                const column = this
                if ($(column.header()).find('select').length > 0) {
                    return
                }

                const select = $(
                    '<select class="form-control form-control-sm"><option value="">Buscar DA...</option></select>'
                )
                    .appendTo($(column.header()))
                    .on('change', function () {
                        const val = $.fn.dataTable.util.escapeRegex($(this).val())
                        column.search(val ? '^' + val : '', true, false).draw()
                    })

                const prefijosUnicos = new Set()
                column.data().each(function (d) {
                    if (d) {
                        const prefijo = String(d).substring(0, 2)
                        const num = parseInt(prefijo, 10)
                        if (!isNaN(num) && num >= 1) {
                            prefijosUnicos.add(prefijo)
                        }
                    }
                })

                Array.from(prefijosUnicos).sort().forEach(function (prefijo) {
                    select.append('<option value="' + prefijo + '">' + prefijo + '</option>')
                })
            })
        },
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/listar-llaves',
            method: 'POST',
            dataType: 'json',
            data: {
                codigoIndicador: codigoIndicador,
                gestion: gestion,
            },
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {}
                MostrarMensaje('error', GenerarMensajeError(data.message || data.mensaje || 'Error'), data.errors || [])
            },
        },
        language: {
            emptyTable: 'No hay llaves disponibles. Asigne DAs al indicador primero.',
            zeroRecords: 'No se encontraron llaves',
        },
        columns: [
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'Llave',
                width: 180,
            },
            {
                className: 'dt-small dt-left',
                data: 'Descripcion',
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'Estado',
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return data
                    }
                    const agregada = String(row.Estado) === '1'
                    return agregada
                        ? `<button type="button" class="btn-toggle-estado estado-off btnEstado"
                                data-indicador="${codigoIndicador}"
                                data-gestion="${gestion}"
                                data-idllave="${row.IdLlavePresupuestaria}"
                                title="Quitar llave">
                            <span class="btn_text">Quitar</span>
                          </button>`
                        : `<button type="button" class="btn-toggle-estado estado-on btnEstado"
                                data-indicador="${codigoIndicador}"
                                data-gestion="${gestion}"
                                data-idllave="${row.IdLlavePresupuestaria}"
                                title="Agregar llave">
                            <span class="btn_text">Agregar</span>
                          </button>`
                },
            },
        ],
    })
}

$(document).ready(function () {
    $(document).on('click', '#tablaListaIndicadores tbody td.expandible', function () {
        if (!dt_listaIndicadores) {
            return
        }

        const tr = $(this).closest('tr')
        const currentRow = dt_listaIndicadores.row(tr)
        const dtRow = currentRow.data()
        if (!dtRow || !dtRow.IdIndicador) {
            MostrarMensaje('error', 'No se pudo identificar el indicador.')
            return
        }

        if (currentRow.child.isShown()) {
            closeChildRow(currentRow)
            openedRow = null
            return
        }

        if (openedRow && openedRow.child && openedRow.child.isShown()) {
            closeChildRow(openedRow)
        }

        currentRow.child(format(dtRow), 'no-padding').show()
        tr.addClass('shown')

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180)

        openedRow = currentRow
        cargarTabs(dtRow.IdIndicador, dtRow.Codigo)
    })

    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function () {
        const btn = $(this)
        initDataTableGestion(
            btn.data('codigoindicador'),
            btn.data('gestion'),
            btn.data('tableid')
        )
    })

    $(document).on('click', '.btnNuevaFila', function () {
        const btn = $(this)
        const codigoIndicador = btn.data('codigoindicador')
        const gestion = btn.data('gestion')
        const tableIdOriginal = btn.closest('.tab-pane').find('table').attr('id')

        $('#modal_Indicador').val(codigoIndicador)
        $('#modal_Gestion').val(gestion)
        $('#modal_tableIdOriginal').val(tableIdOriginal)

        initTableModal(codigoIndicador, gestion)
        $('#modalLlaves').modal('show')
    })
})

$('#modalLlaves').on('hidden.bs.modal', function () {
    const tableId = $('#modal_tableIdOriginal').val()
    if (tableId && $.fn.DataTable.isDataTable('#' + tableId)) {
        $('#' + tableId).DataTable().ajax.reload()
    }
})
