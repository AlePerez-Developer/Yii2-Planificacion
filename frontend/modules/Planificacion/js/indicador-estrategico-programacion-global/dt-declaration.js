let dt_pg
let openedRowPg = null
let pgFiltroTexto = ''

function pgIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function pgTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function pgNumero(valor) {
    const n = parseFloat(valor)
    if (isNaN(n)) {
        return 0
    }
    return n
}

function pgFormatoMeta(valor) {
    const n = pgNumero(valor)
    if (Number.isInteger(n)) {
        return String(n)
    }
    return String(Math.round(n * 100) / 100)
}

function textoAccionEstrategicaPg(row) {
    const accion = row.accionesEstrategicas || {}
    const codigo = (accion.Codigo != null && accion.Codigo !== '')
        ? accion.Codigo
        : (row.AccionCodigo != null && row.AccionCodigo !== '' ? row.AccionCodigo : '—')
    const descripcion = accion.Descripcion
        || row.AccionDescripcion
        || 'Sin acción asignada'
    return 'AEI: ' + codigo + ' - ' + descripcion
}

function pgEstadoMeta(metaIndicador, metaProgramada) {
    const total = pgNumero(metaIndicador)
    const prog = pgNumero(metaProgramada)
    if (total > 0 && prog >= total) {
        return {clase: 'completo', texto: 'Completo'}
    }
    if (prog > 0) {
        return {clase: 'parcial', texto: 'Parcial'}
    }
    return {clase: 'pendiente', texto: 'Pendiente'}
}

function renderBadgePg(metaIndicador, metaProgramada) {
    const estado = pgEstadoMeta(metaIndicador, metaProgramada)
    return `
        <span class="pg-badge ${estado.clase}">
            <span class="dot"></span>${estado.texto}
        </span>
    `
}

function numerarFilasPg(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresPg() {
    if (typeof dt_pg === 'undefined' || !dt_pg) {
        return
    }

    let total = 0
    let completos = 0
    let parciales = 0
    let pendientes = 0

    dt_pg.rows({search: 'none'}).data().each(function (row) {
        total++
        const estado = pgEstadoMeta(row.Meta, row.MetaProgramada)
        if (estado.clase === 'completo') {
            completos++
        } else if (estado.clase === 'parcial') {
            parciales++
        } else {
            pendientes++
        }
    })

    $('#pgSubtitleTotal').text(total + ' indicadores registrados')
    $('#pgCountCompletos').text(completos)
    $('#pgCountParciales').text(parciales)
    $('#pgCountPendientes').text(pendientes)
}

function mostrarCargaTablaPg() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaPg() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function rangoPaginasVisiblePg(paginaActual, totalPaginas, ventana) {
    const size = ventana || 5
    if (totalPaginas <= 0) {
        return {desde: 0, hasta: -1}
    }
    if (totalPaginas <= size) {
        return {desde: 0, hasta: totalPaginas - 1}
    }
    const hasta = Math.min(totalPaginas - 1, Math.max(paginaActual, size - 1))
    const desde = Math.max(0, hasta - size + 1)
    return {desde: desde, hasta: hasta}
}

function actualizarPaginacionPg(api) {
    const $paginacion = $('#pgPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisiblePg(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> indicadores'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> indicadores'
        )
    }

    const $nav = $paginacion.find('.pagination-nav').empty()

    const $btnAnterior = $('<button type="button" class="page-btn">‹ Anterior</button>')
    if (paginaActual <= 0 || totalPaginas === 0) {
        $btnAnterior.prop('disabled', true)
    } else {
        $btnAnterior.on('click', function () {
            api.page('previous').draw('page')
        })
    }
    $nav.append($btnAnterior)

    for (let i = rango.desde; i <= rango.hasta; i++) {
        const $btnPagina = $('<button type="button" class="page-btn"></button>').text(i + 1)
        if (i === paginaActual) {
            $btnPagina.addClass('current')
        } else {
            $btnPagina.on('click', (function (pageIndex) {
                return function () {
                    api.page(pageIndex).draw('page')
                }
            })(i))
        }
        $nav.append($btnPagina)
    }

    const $btnSiguiente = $('<button type="button" class="page-btn">Siguiente ›</button>')
    if (paginaActual >= totalPaginas - 1 || totalPaginas === 0) {
        $btnSiguiente.prop('disabled', true)
    } else {
        $btnSiguiente.on('click', function () {
            api.page('next').draw('page')
        })
    }
    $nav.append($btnSiguiente)
}

function formatChildPg(row) {
    const id = row.IdIndicador
    return `
        <div class="slider pg-slider" style="display:none">
            <div class="pg-child">
                <div class="pg-child-header">
                    <div class="pg-child-title">Metas por gestión del PEI</div>
                    <div class="pg-child-summary" id="pgSummary_${id}">Cargando...</div>
                </div>
                <div id="loader_pg_${id}" class="p-3">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="gestiones_container_${id}" class="pg-gestiones" style="display:none;"></div>
            </div>
        </div>
    `
}

function aplicarFiltroBusquedaPg() {
    if (typeof dt_pg === 'undefined' || !dt_pg) {
        return
    }
    dt_pg.draw()
}

function initPgListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__pgListadoFiltro === true
    })) {
        aplicarFiltroBusquedaPg()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaProgramacionGlobal') {
            return true
        }
        if (!pgFiltroTexto) {
            return true
        }

        const row = dt_pg.row(dataIndex).data()
        if (!row) {
            return true
        }

        const termino = pgFiltroTexto.toLowerCase()
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = String(row.Descripcion || '').toLowerCase()
        const accion = textoAccionEstrategicaPg(row).toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || accion.indexOf(termino) !== -1
    }
    filtroFn.__pgListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('#buscarProgramacionGlobal').on('input', function () {
        pgFiltroTexto = $(this).val().trim()
        aplicarFiltroBusquedaPg()
    })
}

$(document).ready(function () {
    dt_pg = $('#tablaListaProgramacionGlobal').DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
            bottom: null,
        },
        pageLength: 10,
        paging: true,
        info: false,
        language: {
            emptyTable: 'No hay indicadores estratégicos registrados',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            ocultarCargaTablaPg()
            actualizarContadoresPg()
            numerarFilasPg(this.api())
            actualizarPaginacionPg(this.api())
            initPgListadoFiltros()
        },
        drawCallback: function () {
            numerarFilasPg(this.api())
            actualizarContadoresPg()
            actualizarPaginacionPg(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-global/listar-indicadores',
            dataSrc: 'data',
            error: function (xhr) {
                let mensaje = 'No se pudo cargar el listado'
                try {
                    const data = JSON.parse(xhr.responseText)
                    mensaje = data.message || data.mensaje || mensaje
                    MostrarMensaje('error', GenerarMensajeError(mensaje), data.errors)
                } catch (e) {
                    MostrarMensaje('error', mensaje)
                }
                ocultarCargaTablaPg()
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'expandible',
                render: function (data, type, row) {
                    const descripcion = row.Descripcion || ''
                    if (type !== 'display') {
                        return descripcion
                    }

                    const meta = pgNumero(row.Meta)
                    const programada = pgNumero(row.MetaProgramada)

                    return `
                        <div class="card-row pg-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:#0284c7">${pgIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${pgTexto(descripcion)} <span class="code-badge">${pgTexto(row.Codigo || '')}</span></p>
                                    <p class="patient-meta">${pgTexto(textoAccionEstrategicaPg(row))}</p>
                                    <p class="patient-meta pg-hint">Clic para programar metas por gestión</p>
                                </div>
                            </div>
                            <div class="pg-meta-wrap" id="pgMetaWrap_${row.IdIndicador}">
                                <div class="pg-meta-numbers">
                                    <span class="pg-meta-prog">${pgFormatoMeta(programada)}</span>
                                    <small> / ${pgFormatoMeta(meta)}</small>
                                </div>
                                ${renderBadgePg(meta, programada)}
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Codigo', visible: false},
            {data: 'Descripcion', visible: false},
            {data: 'IdIndicador', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        if (openedRowPg && openedRowPg.child && openedRowPg.child.isShown()) {
            closeChildRowPg(openedRowPg)
            openedRowPg = null
        }
        mostrarCargaTablaPg()
        dt_pg.ajax.reload(function () {
            actualizarContadoresPg()
            ocultarCargaTablaPg()
            btn.prop('disabled', false)
        }, false)
    })
})
