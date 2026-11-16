let dt_indEstrategico

function indEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function indEstadoMeta(codigo) {
    const vigente = indEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function indIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function textoAccionEstrategicaInd(row) {
    const accion = row.accionesEstrategicas || {}
    const codigo = (accion.Codigo != null && accion.Codigo !== '')
        ? accion.Codigo
        : (row.AccionCodigo != null && row.AccionCodigo !== '' ? row.AccionCodigo : '—')
    const descripcion = accion.Descripcion
        || row.AccionDescripcion
        || 'Sin acción asignada'
    return 'AEI: ' + codigo + ' - ' + descripcion
}

function numerarFilasInd(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresInd() {
    if (typeof dt_indEstrategico === 'undefined' || !dt_indEstrategico) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_indEstrategico.rows({search: 'none'}).data().each(function (row) {
        total++
        if (indEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#indEstrategicoSubtitleTotal').text(total + ' indicadores registrados')
    $('#indEstrategicoCountVigentes').text(vigentes)
    $('#indEstrategicoCountCaducos').text(caducos)
}

function mostrarCargaTablaInd() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaInd() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function cerrarMenusEstadoInd(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleInd(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionInd(api) {
    const $paginacion = $('#indEstrategicoPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleInd(paginaActual, totalPaginas, 5)

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

function renderEstadoDropdownInd(codigoActual) {
    const actual = indEstadoMeta(codigoActual)
    const vigente = indEstadoMeta(ESTADO_VIGENTE)
    const caduco = indEstadoMeta(ESTADO_CADUCO)

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

$(document).ready(function () {
    dt_indEstrategico = $('#tablaListaIndicadoresEstrategicos').DataTable({
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
            ocultarCargaTablaInd()
            actualizarContadoresInd()
            numerarFilasInd(this.api())
            actualizarPaginacionInd(this.api())
            if (typeof initIndEstrategicoListadoFiltros === 'function') {
                initIndEstrategicoListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasInd(this.api())
            actualizarContadoresInd()
            actualizarPaginacionInd(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/indicador-estrategico/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data['mensaje']), data['errors'])
                dt_indEstrategico.processing(false)
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const indicador = row.indicador || {}
                    const descripcion = indicador.Descripcion || row.Descripcion || ''
                    if (type !== 'display') {
                        return descripcion
                    }

                    const estado = indEstadoMeta(row.CodigoEstado)
                    const unidad = (indicador.catUnidadesIndicadores && indicador.catUnidadesIndicadores.Descripcion)
                        ? indicador.catUnidadesIndicadores.Descripcion
                        : ''
                    const tipo = (indicador.catTiposResultados && indicador.catTiposResultados.Descripcion)
                        ? indicador.catTiposResultados.Descripcion
                        : ''
                    const categoria = (indicador.catCategoriasIndicadores && indicador.catCategoriasIndicadores.Descripcion)
                        ? indicador.catCategoriasIndicadores.Descripcion
                        : ''
                    const lineaBase = indicador.LineaBase != null ? indicador.LineaBase
                        : (row.LineaBase != null ? row.LineaBase : '')
                    const meta = indicador.Meta != null ? indicador.Meta
                        : (row.Meta != null ? row.Meta : '')

                    return `
                        <div class="card-row ie-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${indIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${descripcion} <span class="code-badge">${row.Codigo || ''}</span></p>
                                    <p class="patient-meta">${textoAccionEstrategicaInd(row)}</p>
                                    <p class="patient-meta">Línea base: ${lineaBase} · Meta: ${meta}</p>
                                    <div class="ie-badges">
                                        ${unidad ? `<span class="ie-badge">${unidad}</span>` : ''}
                                        ${tipo ? `<span class="ie-badge">${tipo}</span>` : ''}
                                        ${categoria ? `<span class="ie-badge">${categoria}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div>${renderEstadoDropdownInd(row.CodigoEstado)}</div>
                            <div class="actions">
                                <button type="button" class="btn-action-new edit">✏ Editar</button>
                                <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Codigo', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdIndicador', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaInd()
        dt_indEstrategico.ajax.reload(function () {
            actualizarContadoresInd()
            ocultarCargaTablaInd()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoInd()
    })
})
