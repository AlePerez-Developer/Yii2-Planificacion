let dt_objEstrategico

function oeEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function oeEstadoMeta(codigo) {
    const vigente = oeEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function oeTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function numerarFilasOe(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresOe() {
    if (typeof dt_objEstrategico === 'undefined' || !dt_objEstrategico) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_objEstrategico.rows({search: 'none'}).data().each(function (row) {
        total++
        if (oeEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#oeSubtitleTotal').text(total + ' objetivos estratégicos registrados')
    $('#oeCountVigentes').text(vigentes)
    $('#oeCountCaducos').text(caducos)
}

function mostrarCargaTablaOe() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaOe() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function cerrarMenusEstadoOe(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleOe(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionOe(api) {
    const $paginacion = $('#oePagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleOe(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> objetivos estratégicos'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> objetivos estratégicos'
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

function renderEstadoDropdownOe(codigoActual) {
    const actual = oeEstadoMeta(codigoActual)
    const vigente = oeEstadoMeta(ESTADO_VIGENTE)
    const caduco = oeEstadoMeta(ESTADO_CADUCO)

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

function renderTarjetaOe(row) {
    const area = row.areaEstrategica || {}
    const politica = row.politicaEstrategica || {}
    const pei = row.pei || {}
    const gestion = (pei.GestionInicio && pei.GestionFin)
        ? oeTexto(pei.GestionInicio) + ' - ' + oeTexto(pei.GestionFin)
        : ''

    return `
        <article class="oe-card">
            <div class="oe-card-body">
                <div class="oe-head">
                    <span class="row-num oe-num"></span>
                    <div class="oe-head-text">
                        <div class="oe-badges">
                            <span class="oe-badge-code">Objetivo: ${oeTexto(row.Compuesto)}</span>
                        </div>
                        <h2 class="oe-title">${oeTexto(row.Objetivo)}</h2>
                    </div>
                </div>
                <div class="oe-meta">
                    <div class="oe-meta-item">
                        <i class="fas fa-tag"></i>
                        <span class="oe-meta-label">PRODUCTO:</span>
                        <span class="oe-meta-value">${oeTexto(row.Producto)}</span>
                    </div>
                    <div class="oe-meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <span class="oe-meta-label">Gestión:</span>
                        <span class="oe-meta-value">${gestion}</span>
                    </div>
                    <div class="oe-meta-hint">
                        <i class="fas fa-info-circle"></i>
                        <em>(${oeTexto(row.Indicador_Descripcion)} - ${oeTexto(row.Indicador_Formula)})</em>
                    </div>
                </div>
                <div class="oe-blocks">
                    <div class="oe-block">
                        <div class="oe-block-label">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>ÁREA</span>
                            <span class="oe-chip oe-chip-area">&lt;${oeTexto(area.Codigo)}&gt;</span>
                        </div>
                        <p>${oeTexto(area.Descripcion)}</p>
                    </div>
                    <div class="oe-block">
                        <div class="oe-block-label">
                            <i class="fas fa-landmark"></i>
                            <span>POLÍTICA</span>
                            <span class="oe-chip oe-chip-politica">&lt;${oeTexto(politica.Codigo)}&gt;</span>
                        </div>
                        <p>${oeTexto(politica.Descripcion)}</p>
                    </div>
                </div>
            </div>
            <div class="oe-actions">
                <div>${renderEstadoDropdownOe(row.CodigoEstado)}</div>
                <div class="actions">
                    <button type="button" class="btn-action-new programar btn-programar">📅 Programar</button>
                    <button type="button" class="btn-action-new edit">✏ Editar</button>
                    <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                </div>
            </div>
        </article>
    `
}

$(document).ready(function () {
    dt_objEstrategico = $('#tablaListaObjEstrategicos').DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
            bottom: null,
        },
        responsive: false,
        pageLength: 10,
        paging: true,
        info: false,
        language: {
            emptyTable: 'No hay objetivos estratégicos registrados',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            ocultarCargaTablaOe()
            actualizarContadoresOe()
            numerarFilasOe(this.api())
            actualizarPaginacionOe(this.api())
            if (typeof initOeListadoFiltros === 'function') {
                initOeListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasOe(this.api())
            actualizarContadoresOe()
            actualizarPaginacionOe(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/obj-estrategico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                let mensaje = 'No se pudo cargar el listado'
                try {
                    const data = JSON.parse(xhr.responseText)
                    mensaje = data.message || data.mensaje || mensaje
                    if (typeof MostrarMensaje === 'function') {
                        MostrarMensaje('error', GenerarMensajeError(mensaje), data.errors)
                    }
                } catch (e) {
                    if (typeof MostrarMensaje === 'function') {
                        MostrarMensaje('error', mensaje)
                    }
                }
                ocultarCargaTablaOe()
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return row.Objetivo
                    }
                    return renderTarjetaOe(row)
                }
            },
            {data: 'Compuesto', visible: false},
            {data: 'Objetivo', visible: false},
            {data: 'Producto', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdObjEstrategico', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaOe()
        dt_objEstrategico.ajax.reload(function () {
            actualizarContadoresOe()
            ocultarCargaTablaOe()
            btn.prop('disabled', false)
        }, false)
    })

    $('#oePageLength').on('change', function () {
        const len = parseInt($(this).val(), 10) || 10
        if (typeof dt_objEstrategico !== 'undefined' && dt_objEstrategico) {
            dt_objEstrategico.page.len(len).draw()
        }
    })

    $(document).on('click', function () {
        cerrarMenusEstadoOe()
    })
})
