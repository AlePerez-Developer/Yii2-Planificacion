let dt_objEspecifico

function oesEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function oesEstadoMeta(codigo) {
    const vigente = oesEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
    }
}

function oesTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function numerarFilasOes(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresOes() {
    if (typeof dt_objEspecifico === 'undefined' || !dt_objEspecifico) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_objEspecifico.rows({search: 'none'}).data().each(function (row) {
        total++
        if (oesEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#oesSubtitleTotal').text(total + ' objetivos específicos registrados')
    $('#oesCountVigentes').text(vigentes)
    $('#oesCountCaducos').text(caducos)
}

function mostrarCargaTablaOes() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaOes() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function cerrarMenusEstadoOes(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleOes(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionOes(api) {
    const $paginacion = $('#oesPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleOes(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> objetivos específicos'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> objetivos específicos'
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

function renderEstadoOes(codigoActual) {
    const actual = oesEstadoMeta(codigoActual)

    if (window.poaPuedeEditar === false) {
        return `
            <span class="estado ${actual.clase} readonly">
                <span class="dot"></span>${actual.texto}
            </span>
        `
    }

    const vigente = oesEstadoMeta(ESTADO_VIGENTE)
    const caduco = oesEstadoMeta(ESTADO_CADUCO)

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

function renderAccionesOes() {
    let html = `
        <button type="button" class="btn-action-new programar btn-programar">📅 Programar</button>
    `
    if (window.poaPuedeEditar !== false) {
        html += `<button type="button" class="btn-action-new edit">✏ Editar</button>`
    }
    if (window.poaPuedeEliminar !== false) {
        html += `<button type="button" class="btn-action-new delete">🗑 Eliminar</button>`
    }
    return html
}

function renderTarjetaOes(row) {
    const gestion = row.peiGestion || {}
    const gestionTexto = gestion.Gestion != null ? oesTexto(gestion.Gestion) : ''

    return `
        <article class="oes-card">
            <div class="oes-card-body">
                <div class="oes-head">
                    <span class="row-num oes-num"></span>
                    <div class="oes-head-text">
                        <div class="oes-badges">
                            <span class="oes-badge-code">Objetivo: ${oesTexto(row.Compuesto)}</span>
                        </div>
                        <h2 class="oes-title">${oesTexto(row.Objetivo)}</h2>
                    </div>
                </div>
                <div class="oes-meta">
                    <div class="oes-meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <span class="oes-meta-label">Gestión:</span>
                        <span class="oes-meta-value">${gestionTexto}</span>
                    </div>
                </div>
            </div>
            <div class="oes-actions">
                <div>${renderEstadoOes(row.CodigoEstado)}</div>
                <div class="actions">${renderAccionesOes()}</div>
            </div>
        </article>
    `
}

$(document).ready(function () {
    dt_objEspecifico = $('#tablaListaObjEspecificos').DataTable({
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
            emptyTable: 'No hay objetivos específicos registrados',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            ocultarCargaTablaOes()
            actualizarContadoresOes()
            numerarFilasOes(this.api())
            actualizarPaginacionOes(this.api())
            if (typeof initOesListadoFiltros === 'function') {
                initOesListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasOes(this.api())
            actualizarContadoresOes()
            actualizarPaginacionOes(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/obj-especifico/listar-todo',
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
                ocultarCargaTablaOes()
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
                    return renderTarjetaOes(row)
                }
            },
            {data: 'Compuesto', visible: false},
            {data: 'Objetivo', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdObjEspecifico', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaOes()
        dt_objEspecifico.ajax.reload(function () {
            actualizarContadoresOes()
            ocultarCargaTablaOes()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoOes()
    })
})
