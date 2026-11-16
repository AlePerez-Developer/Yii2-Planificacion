const FODA_INST_TABS = [
    {tipo: 'Fortaleza', label: 'Fortalezas', tableId: 'tablaFodaFortaleza', paginationId: 'fodaPaginationFortaleza'},
    {tipo: 'Oportunidad', label: 'Oportunidades', tableId: 'tablaFodaOportunidad', paginationId: 'fodaPaginationOportunidad'},
    {tipo: 'Debilidad', label: 'Debilidades', tableId: 'tablaFodaDebilidad', paginationId: 'fodaPaginationDebilidad'},
    {tipo: 'Amenaza', label: 'Amenazas', tableId: 'tablaFodaAmenaza', paginationId: 'fodaPaginationAmenaza'}
]

let dtFodaInstitucionByTipo = {}
let fodaInstCache = []
let fodaInstTipoActivo = 'Fortaleza'
let fodaInstFiltroTexto = ''
let fodaInstFiltroCodigoEstado = ''
let fodaInstFiltroIncidencia = ''

function fodaInstEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function fodaInstEstadoMeta(codigo) {
    const vigente = fodaInstEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function fodaInstIncidenciaClase(incidencia) {
    return String(incidencia || '').toLowerCase()
}

function fodaInstIniciales(descripcion) {
    if (!descripcion) {
        return 'FO'
    }
    const palabras = descripcion.trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function fodaInstLabelTipo(tipo) {
    const tab = FODA_INST_TABS.find(function (t) {
        return t.tipo === tipo
    })
    return tab ? tab.label : (tipo || '')
}

function cerrarMenusEstadoFodaInst(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleFoda(paginaActual, totalPaginas, ventana) {
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

function numerarFilasFodaInst(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarPaginacionFodaInst(api, paginationId, labelPlural) {
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
    const rango = rangoPaginasVisibleFoda(paginaActual, totalPaginas, 5)
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

function renderEstadoDropdownFodaInst(codigoActual) {
    const actual = fodaInstEstadoMeta(codigoActual)
    const vigente = fodaInstEstadoMeta(ESTADO_VIGENTE)
    const caduco = fodaInstEstadoMeta(ESTADO_CADUCO)

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

function renderIncidenciaBadgeFodaInst(incidencia) {
    const valor = incidencia || ''
    const clase = fodaInstIncidenciaClase(valor)
    return `
        <span class="incidencia-badge ${clase}">
            <span class="dot"></span>${valor || '—'}
        </span>
    `
}

function renderAccionesFodaInst() {
    return `
        <div class="actions">
            <button type="button" class="btn-action-new edit">✏ Editar</button>
            <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
        </div>
    `
}

function filtrarFilasFodaInst(rows) {
    return rows.filter(function (row) {
        if (fodaInstFiltroCodigoEstado && row.CodigoEstado !== fodaInstFiltroCodigoEstado) {
            return false
        }
        if (fodaInstFiltroIncidencia && row.Incidencia !== fodaInstFiltroIncidencia) {
            return false
        }
        if (!fodaInstFiltroTexto) {
            return true
        }
        const termino = fodaInstFiltroTexto.toLowerCase()
        const descripcion = (row.Descripcion || '').toLowerCase()
        const incidencia = (row.Incidencia || '').toLowerCase()
        return descripcion.indexOf(termino) !== -1 || incidencia.indexOf(termino) !== -1
    })
}

function actualizarContadoresFodaInst() {
    const conteoTipo = {Fortaleza: 0, Oportunidad: 0, Debilidad: 0, Amenaza: 0}
    let vigentes = 0
    let caducos = 0
    let totalActivo = 0

    fodaInstCache.forEach(function (row) {
        if (conteoTipo.hasOwnProperty(row.Tipo)) {
            conteoTipo[row.Tipo]++
        }
        if (row.Tipo !== fodaInstTipoActivo) {
            return
        }
        totalActivo++
        if (fodaInstEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    Object.keys(conteoTipo).forEach(function (tipo) {
        $('[data-count-tipo="' + tipo + '"]').text(conteoTipo[tipo])
    })

    const label = fodaInstLabelTipo(fodaInstTipoActivo).toLowerCase()
    $('#fodaSubtitleTotal').text(totalActivo + ' ' + label + ' registradas')
    $('#fodaCountVigentes').text(vigentes)
    $('#fodaCountCaducos').text(caducos)
}

function mostrarCargaTablaFodaInst() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaFodaInst() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function refrescarTablasFodaInst() {
    FODA_INST_TABS.forEach(function (tab) {
        const dt = dtFodaInstitucionByTipo[tab.tipo]
        if (!dt) {
            return
        }
        const rows = filtrarFilasFodaInst(
            fodaInstCache.filter(function (row) {
                return row.Tipo === tab.tipo
            })
        )
        dt.clear()
        dt.rows.add(rows)
        dt.draw(false)
    })
    actualizarContadoresFodaInst()
}

function cargarListadoFodaInst(callback) {
    $.ajax({
        method: 'POST',
        dataType: 'json',
        url: 'index.php?r=Planificacion/foda-institucion/listar-todo',
        success: function (response) {
            fodaInstCache = Array.isArray(response.data) ? response.data : []
            refrescarTablasFodaInst()
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

function crearDataTableFodaInst(tab) {
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
                    const estado = fodaInstEstadoMeta(row.CodigoEstado)
                    return `
                        <div class="card-row foda-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${fodaInstIniciales(row.Descripcion)}</div>
                                <div>
                                    <p class="patient-name">${row.Descripcion || ''}</p>
                                    <p class="patient-meta">${tab.tipo}</p>
                                </div>
                            </div>
                            <div>${renderIncidenciaBadgeFodaInst(row.Incidencia)}</div>
                            <div>${renderEstadoDropdownFodaInst(row.CodigoEstado)}</div>
                            ${renderAccionesFodaInst()}
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
            numerarFilasFodaInst(api)
            actualizarPaginacionFodaInst(api, tab.paginationId, tab.label.toLowerCase())
        }
    })
}

function activarTabFodaInst(tipo) {
    fodaInstTipoActivo = tipo
    $('.foda-tab-btn').removeClass('active')
    $('.foda-tab-btn[data-tipo="' + tipo + '"]').addClass('active')
    $('.foda-tab-pane').removeClass('active')
    $('.foda-tab-pane[data-tipo="' + tipo + '"]').addClass('active')
    actualizarContadoresFodaInst()
    const dt = dtFodaInstitucionByTipo[tipo]
    if (dt) {
        dt.columns.adjust().draw(false)
    }
}

$(document).ready(function () {
    FODA_INST_TABS.forEach(function (tab) {
        dtFodaInstitucionByTipo[tab.tipo] = crearDataTableFodaInst(tab)
    })

    mostrarCargaTablaFodaInst()
    cargarListadoFodaInst(function () {
        ocultarCargaTablaFodaInst()
        if (typeof initFodaInstitucionFiltros === 'function') {
            initFodaInstitucionFiltros()
        }
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaFodaInst()
        cargarListadoFodaInst(function () {
            ocultarCargaTablaFodaInst()
            btn.prop('disabled', false)
        })
    })

    $(document).on('click', function () {
        cerrarMenusEstadoFodaInst()
    })
})
