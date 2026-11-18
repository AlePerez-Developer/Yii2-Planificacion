let dt_objInstitucional

function oiEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function oiEstadoMeta(codigo) {
    const vigente = oiEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function oiTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function numerarFilasOi(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresOi() {
    if (typeof dt_objInstitucional === 'undefined' || !dt_objInstitucional) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_objInstitucional.rows({search: 'none'}).data().each(function (row) {
        total++
        if (oiEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#oiSubtitleTotal').text(total + ' objetivos institucionales registrados')
    $('#oiCountVigentes').text(vigentes)
    $('#oiCountCaducos').text(caducos)
}

function mostrarCargaTablaOi() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaOi() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function cerrarMenusEstadoOi(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleOi(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionOi(api) {
    const $paginacion = $('#oiPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleOi(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> objetivos institucionales'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> objetivos institucionales'
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

function renderEstadoDropdownOi(codigoActual) {
    const actual = oiEstadoMeta(codigoActual)
    const vigente = oiEstadoMeta(ESTADO_VIGENTE)
    const caduco = oiEstadoMeta(ESTADO_CADUCO)

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

function renderTarjetaOi(row) {
    const gestion = row.peiGestion || {}
    const gestionTexto = gestion.Gestion != null ? oiTexto(gestion.Gestion) : ''

    return `
        <article class="oi-card">
            <div class="oi-card-body">
                <div class="oi-head">
                    <span class="row-num oi-num"></span>
                    <div class="oi-head-text">
                        <div class="oi-badges">
                            <span class="oi-badge-code">Objetivo: ${oiTexto(row.Compuesto)}</span>
                        </div>
                        <h2 class="oi-title">${oiTexto(row.Objetivo)}</h2>
                    </div>
                </div>
                <div class="oi-meta">
                    <div class="oi-meta-item">
                        <i class="fas fa-tag"></i>
                        <span class="oi-meta-label">PRODUCTO:</span>
                        <span class="oi-meta-value">${oiTexto(row.Producto)}</span>
                    </div>
                    <div class="oi-meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <span class="oi-meta-label">Gestión:</span>
                        <span class="oi-meta-value">${gestionTexto}</span>
                    </div>
                </div>
            </div>
            <div class="oi-actions">
                <div>${renderEstadoDropdownOi(row.CodigoEstado)}</div>
                <div class="actions">
                    <button type="button" class="btn-action-new edit">✏ Editar</button>
                    <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                </div>
            </div>
        </article>
    `
}

$(document).ready(function () {
    dt_objInstitucional = $('#tablaListaObjInstitucionales').DataTable({
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
            emptyTable: 'No hay objetivos institucionales registrados',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            ocultarCargaTablaOi()
            actualizarContadoresOi()
            numerarFilasOi(this.api())
            actualizarPaginacionOi(this.api())
            if (typeof initOiListadoFiltros === 'function') {
                initOiListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasOi(this.api())
            actualizarContadoresOi()
            actualizarPaginacionOi(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/obj-institucional/listar-todo',
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
                ocultarCargaTablaOi()
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
                    return renderTarjetaOi(row)
                }
            },
            {data: 'Compuesto', visible: false},
            {data: 'Objetivo', visible: false},
            {data: 'Producto', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdObjInstitucional', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaOi()
        dt_objInstitucional.ajax.reload(function () {
            actualizarContadoresOi()
            ocultarCargaTablaOi()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoOi()
    })
})
