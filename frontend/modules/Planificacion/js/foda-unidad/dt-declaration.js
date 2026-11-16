const FODA_UNIDAD_TABS = [
    {tipo: 'Fortaleza', label: 'Fortalezas', tableId: 'tablaFodaFortaleza', paginationId: 'fodaPaginationFortaleza'},
    {tipo: 'Oportunidad', label: 'Oportunidades', tableId: 'tablaFodaOportunidad', paginationId: 'fodaPaginationOportunidad'},
    {tipo: 'Debilidad', label: 'Debilidades', tableId: 'tablaFodaDebilidad', paginationId: 'fodaPaginationDebilidad'},
    {tipo: 'Amenaza', label: 'Amenazas', tableId: 'tablaFodaAmenaza', paginationId: 'fodaPaginationAmenaza'}
]

let dtFodaUnidadByTipo = {}
let fodaUnidadCache = []
let fodaUnidadTipoActivo = 'Fortaleza'
let fodaUnidadFiltroTexto = ''
let fodaUnidadFiltroCodigoEstado = ''
let fodaUnidadFiltroIncidencia = ''

function fodaUnidadEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function fodaUnidadEstadoMeta(codigo) {
    const vigente = fodaUnidadEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function fodaUnidadIncidenciaClase(incidencia) {
    return String(incidencia || '').toLowerCase()
}

function fodaUnidadIniciales(descripcion) {
    if (!descripcion) {
        return 'FO'
    }
    const palabras = descripcion.trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function fodaUnidadLabelTipo(tipo) {
    const tab = FODA_UNIDAD_TABS.find(function (t) {
        return t.tipo === tipo
    })
    return tab ? tab.label : (tipo || '')
}

function cerrarMenusEstadoFodaUnidad(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleFodaUnidad(paginaActual, totalPaginas, ventana) {
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

function numerarFilasFodaUnidad(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarPaginacionFodaUnidad(api, paginationId, labelPlural) {
    const $paginacion = $('#' + paginationId)
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleFodaUnidad(paginaActual, totalPaginas, 5)
    const entidad = labelPlural || 'registros'

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> ' + entidad
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> ' + entidad
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

function renderEstadoDropdownFodaUnidad(codigoActual) {
    const actual = fodaUnidadEstadoMeta(codigoActual)
    const vigente = fodaUnidadEstadoMeta(ESTADO_VIGENTE)
    const caduco = fodaUnidadEstadoMeta(ESTADO_CADUCO)

    function opcion(meta) {
        const activo = meta.codigo === actual.codigo
        return `
            <button type="button" class="estado-option ${meta.clase}${activo ? ' selected' : ''}" data-estado="${meta.codigo}">
                <span class="dot"></span>
                <span>${meta.texto}</span>
                ${activo ? '<span class="check">✓</span>' : ''}
            </button>
        `
    }

    return `
        <div class="estado-wrap">
            <button type="button" class="estado ${actual.clase} estado-trigger" data-estado="${actual.codigo}">
                <span class="dot"></span>${actual.texto}<span class="chevron">▾</span>
            </button>
            <div class="estado-menu">
                ${opcion(vigente)}
                ${opcion(caduco)}
            </div>
        </div>
    `
}

function renderIncidenciaBadgeFodaUnidad(incidencia) {
    const valor = incidencia || ''
    const clase = fodaUnidadIncidenciaClase(valor)
    return `
        <span class="incidencia-badge ${clase}">
            <span class="dot"></span>${valor || '—'}
        </span>
    `
}

function renderAccionesFodaUnidad() {
    return `
        <div class="actions">
            <button type="button" class="btn-action-new edit">✏ Editar</button>
            <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
        </div>
    `
}

function filtrarFilasFodaUnidad(rows) {
    return rows.filter(function (row) {
        if (fodaUnidadFiltroCodigoEstado && row.CodigoEstado !== fodaUnidadFiltroCodigoEstado) {
            return false
        }
        if (fodaUnidadFiltroIncidencia && row.Incidencia !== fodaUnidadFiltroIncidencia) {
            return false
        }
        if (!fodaUnidadFiltroTexto) {
            return true
        }
        const termino = fodaUnidadFiltroTexto.toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const incidencia = (row.Incidencia || '').toLowerCase()
        return descripcion.indexOf(termino) !== -1 || incidencia.indexOf(termino) !== -1
    })
}

function actualizarContadoresFodaUnidad() {
    const conteoTipo = {Fortaleza: 0, Oportunidad: 0, Debilidad: 0, Amenaza: 0}
    let vigentes = 0
    let caducos = 0
    let totalActivo = 0

    fodaUnidadCache.forEach(function (row) {
        if (conteoTipo.hasOwnProperty(row.Tipo)) {
            conteoTipo[row.Tipo]++
        }
        if (row.Tipo !== fodaUnidadTipoActivo) {
            return
        }
        totalActivo++
        if (fodaUnidadEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    Object.keys(conteoTipo).forEach(function (tipo) {
        $('[data-count-tipo="' + tipo + '"]').text(conteoTipo[tipo])
    })

    const label = fodaUnidadLabelTipo(fodaUnidadTipoActivo).toLowerCase()
    $('#fodaSubtitleTotal').text(totalActivo + ' ' + label + ' registradas')
    $('#fodaCountVigentes').text(vigentes)
    $('#fodaCountCaducos').text(caducos)
}

function mostrarCargaTablaFodaUnidad() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaFodaUnidad() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function refrescarTablasFodaUnidad() {
    FODA_UNIDAD_TABS.forEach(function (tab) {
        const dt = dtFodaUnidadByTipo[tab.tipo]
        if (!dt) {
            return
        }
        const rows = filtrarFilasFodaUnidad(
            fodaUnidadCache.filter(function (row) {
                return row.Tipo === tab.tipo
            })
        )
        dt.clear()
        dt.rows.add(rows)
        dt.draw(false)
    })
    actualizarContadoresFodaUnidad()
}

function cargarListadoFodaUnidad(callback) {
    $.ajax({
        method: 'POST',
        dataType: 'json',
        url: 'index.php?r=Planificacion/foda-unidad/listar-todo',
        success: function (response) {
            fodaUnidadCache = Array.isArray(response.data) ? response.data : []
            refrescarTablasFodaUnidad()
            if (typeof callback === 'function') {
                callback(true)
            }
        },
        error: function (xhr) {
            const data = xhr.responseJSON || {}
            MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo cargar el FODA.'), data.errors)
            if (typeof callback === 'function') {
                callback(false)
            }
        }
    })
}

function crearDataTableFodaUnidad(tab) {
    return $('#' + tab.tableId).DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
            bottom: null
        },
        pageLength: 10,
        paging: true,
        info: false,
        language: {
            emptyTable: 'No hay ' + tab.label.toLowerCase() + ' registradas',
            zeroRecords: 'No se encontraron coincidencias'
        },
        data: [],
        order: [[1, 'asc']],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return row.Descripcion || ''
                    }
                    const estado = fodaUnidadEstadoMeta(row.CodigoEstado)
                    return `
                        <div class="card-row foda-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${fodaUnidadIniciales(row.Descripcion)}</div>
                                <div>
                                    <p class="patient-name">${row.Descripcion || ''}</p>
                                    <p class="patient-meta">${tab.tipo}</p>
                                </div>
                            </div>
                            <div>${renderIncidenciaBadgeFodaUnidad(row.Incidencia)}</div>
                            <div>${renderEstadoDropdownFodaUnidad(row.CodigoEstado)}</div>
                            ${renderAccionesFodaUnidad()}
                        </div>
                    `
                }
            },
            {data: 'Descripcion', visible: false},
            {data: 'Incidencia', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdFoda', visible: false, searchable: false}
        ],
        drawCallback: function () {
            const api = this.api()
            numerarFilasFodaUnidad(api)
            actualizarPaginacionFodaUnidad(api, tab.paginationId, tab.label.toLowerCase())
        }
    })
}

function activarTabFodaUnidad(tipo) {
    fodaUnidadTipoActivo = tipo
    $('.foda-tab-btn').removeClass('active')
    $('.foda-tab-btn[data-tipo="' + tipo + '"]').addClass('active')
    $('.foda-tab-pane').removeClass('active')
    $('.foda-tab-pane[data-tipo="' + tipo + '"]').addClass('active')
    actualizarContadoresFodaUnidad()
    const dt = dtFodaUnidadByTipo[tipo]
    if (dt) {
        dt.columns.adjust().draw(false)
    }
}

$(document).ready(function () {
    FODA_UNIDAD_TABS.forEach(function (tab) {
        dtFodaUnidadByTipo[tab.tipo] = crearDataTableFodaUnidad(tab)
    })

    mostrarCargaTablaFodaUnidad()
    cargarListadoFodaUnidad(function () {
        ocultarCargaTablaFodaUnidad()
        if (typeof initFodaUnidadFiltros === 'function') {
            initFodaUnidadFiltros()
        }
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaFodaUnidad()
        cargarListadoFodaUnidad(function () {
            ocultarCargaTablaFodaUnidad()
            btn.prop('disabled', false)
        })
    })

    $(document).on('click', function () {
        cerrarMenusEstadoFodaUnidad()
    })
})
