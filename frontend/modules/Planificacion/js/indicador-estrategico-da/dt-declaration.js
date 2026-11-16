let dt_indDa
let openedRowIndDa = null
let indDaFiltroTexto = ''

function indDaIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function textoAccionEstrategicaIndDa(row) {
    const accion = row.accionesEstrategicas || {}
    const codigo = (accion.Codigo != null && accion.Codigo !== '')
        ? accion.Codigo
        : (row.AccionCodigo != null && row.AccionCodigo !== '' ? row.AccionCodigo : '—')
    const descripcion = accion.Descripcion
        || row.AccionDescripcion
        || 'Sin acción asignada'
    return 'AEI: ' + codigo + ' - ' + descripcion
}

function numerarFilasIndDa(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresIndDa() {
    if (typeof dt_indDa === 'undefined' || !dt_indDa) {
        return
    }
    const total = dt_indDa.rows({search: 'none'}).data().length
    $('#indDaSubtitleTotal').text(total + ' indicadores registrados')
}

function mostrarCargaTablaIndDa() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaIndDa() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function rangoPaginasVisibleIndDa(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionIndDa(api) {
    const $paginacion = $('#indDaPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleIndDa(paginaActual, totalPaginas, 5)

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

function formatChildIndDa(row) {
    const id = row.IdIndicador
    return `
        <div class="slider ieda-slider" style="display:none">
            <div class="ieda-child">
                <div id="loader_da_${id}" class="p-3">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="das_container_${id}" class="ieda-da-grid" style="display:none;"></div>
            </div>
        </div>
    `
}

function aplicarFiltroBusquedaIndDa() {
    if (typeof dt_indDa === 'undefined' || !dt_indDa) {
        return
    }
    dt_indDa.draw()
}

function initIndDaListadoFiltros() {
    if ($.fn.dataTable.ext.search.some(function (fn) {
        return fn && fn.__indDaListadoFiltro === true
    })) {
        aplicarFiltroBusquedaIndDa()
        return
    }

    const filtroFn = function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaListaIndicadoresDa') {
            return true
        }
        if (!indDaFiltroTexto) {
            return true
        }

        const row = dt_indDa.row(dataIndex).data()
        if (!row) {
            return true
        }

        const termino = indDaFiltroTexto.toLowerCase()
        const indicador = row.indicador || {}
        const codigo = String(row.Codigo || '').toLowerCase()
        const descripcion = String(indicador.Descripcion || row.Descripcion || '').toLowerCase()
        const accion = textoAccionEstrategicaIndDa(row).toLowerCase()

        return codigo.indexOf(termino) !== -1
            || descripcion.indexOf(termino) !== -1
            || accion.indexOf(termino) !== -1
    }
    filtroFn.__indDaListadoFiltro = true
    $.fn.dataTable.ext.search.push(filtroFn)

    $('#buscarIndicadorDa').on('input', function () {
        indDaFiltroTexto = $(this).val().trim()
        aplicarFiltroBusquedaIndDa()
    })
}

$(document).ready(function () {
    dt_indDa = $('#tablaListaIndicadoresDa').DataTable({
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
            ocultarCargaTablaIndDa()
            actualizarContadoresIndDa()
            numerarFilasIndDa(this.api())
            actualizarPaginacionIndDa(this.api())
            initIndDaListadoFiltros()
        },
        drawCallback: function () {
            numerarFilasIndDa(this.api())
            actualizarContadoresIndDa()
            actualizarPaginacionIndDa(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/indicador-estrategico-da/listar-indicadores',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data['mensaje']), data['errors'])
                dt_indDa.processing(false)
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'expandible',
                render: function (data, type, row) {
                    const indicador = row.indicador || {}
                    const descripcion = indicador.Descripcion || row.Descripcion || ''
                    if (type !== 'display') {
                        return descripcion
                    }

                    const asignadas = parseInt(row.DasAsignadas, 10) || 0

                    return `
                        <div class="card-row ieda-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:#0284c7">${indDaIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${descripcion} <span class="code-badge">${row.Codigo || ''}</span></p>
                                    <p class="patient-meta">${textoAccionEstrategicaIndDa(row)}</p>
                                    <p class="patient-meta ieda-hint">Clic para asignar direcciones administrativas</p>
                                </div>
                            </div>
                            <div class="ieda-count-wrap">
                                <span class="ieda-count-badge" id="dasCount_${row.IdIndicador}">${asignadas} DA</span>
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Codigo', visible: false},
            {data: 'IdIndicador', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        if (openedRowIndDa && openedRowIndDa.child && openedRowIndDa.child.isShown()) {
            closeChildRowIndDa(openedRowIndDa)
            openedRowIndDa = null
        }
        mostrarCargaTablaIndDa()
        dt_indDa.ajax.reload(function () {
            actualizarContadoresIndDa()
            ocultarCargaTablaIndDa()
            btn.prop('disabled', false)
        }, false)
    })
})
