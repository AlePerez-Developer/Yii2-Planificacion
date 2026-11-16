let dt_indEstrategicoAccion

function indAccionIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function numerarFilasIndAccion(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresIndAccion() {
    if (typeof dt_indEstrategicoAccion === 'undefined' || !dt_indEstrategicoAccion) {
        return
    }
    const total = dt_indEstrategicoAccion.rows({search: 'none'}).data().length
    $('#indAccionSubtitleTotal').text(total + ' indicadores en el objetivo')
}

function rangoPaginasVisibleIndAccion(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionIndAccion(api) {
    const $paginacion = $('#indAccionPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleIndAccion(paginaActual, totalPaginas, 5)

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

function textoAccionEstrategica(row) {
    const accion = row.accionesEstrategicas || {}
    const codigo = (accion.Codigo != null && accion.Codigo !== '')
        ? accion.Codigo
        : (row.AccionCodigo != null && row.AccionCodigo !== '' ? row.AccionCodigo : '—')
    const descripcion = accion.Descripcion
        || row.AccionDescripcion
        || 'Sin acción asignada'
    return 'AET: ' + codigo + ' - ' + descripcion
}

function inicializarTablaIndAcciones() {
    // compat: la tabla ya se crea en document.ready
}

$(document).ready(function () {
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000'

    dt_indEstrategicoAccion = $('#tablaListaIndicadoresEstrategicosAccion').DataTable({
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
            emptyTable: 'No hay indicadores para el objetivo seleccionado',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            numerarFilasIndAccion(this.api())
            actualizarContadoresIndAccion()
            actualizarPaginacionIndAccion(this.api())
        },
        drawCallback: function () {
            numerarFilasIndAccion(this.api())
            actualizarContadoresIndAccion()
            actualizarPaginacionIndAccion(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            data: function () {
                return {
                    idObjEstrategico: (typeof indicadorEstrategicoAccion_s2ObjEstrategico !== 'undefined'
                        ? indicadorEstrategicoAccion_s2ObjEstrategico.val()
                        : null) || ID_EMPTY_GUID
                }
            },
            url: 'index.php?r=Planificacion/indicador-estrategico-accion/listar-indicadores',
            dataSrc: 'data',
            error: function (xhr) {
                const data = JSON.parse(xhr.responseText)
                MostrarMensaje('error', GenerarMensajeError(data['mensaje']), data['errors'])
                dt_indEstrategicoAccion.processing(false)
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

                    const unidad = (indicador.catUnidadesIndicadores && indicador.catUnidadesIndicadores.Descripcion)
                        ? indicador.catUnidadesIndicadores.Descripcion
                        : ''
                    const tipo = (indicador.catTiposResultados && indicador.catTiposResultados.Descripcion)
                        ? indicador.catTiposResultados.Descripcion
                        : ''
                    const categoria = (indicador.catCategoriasIndicadores && indicador.catCategoriasIndicadores.Descripcion)
                        ? indicador.catCategoriasIndicadores.Descripcion
                        : ''
                    const lineaBase = indicador.LineaBase != null ? indicador.LineaBase : ''
                    const meta = indicador.Meta != null ? indicador.Meta : ''

                    return `
                        <div class="card-row iea-card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:#0284c7">${indAccionIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${descripcion} <span class="code-badge">${row.Codigo || ''}</span></p>
                                    <p class="patient-meta">${textoAccionEstrategica(row)}</p>
                                    <p class="patient-meta">Línea base: ${lineaBase} · Meta: ${meta}</p>
                                    <div class="iea-badges">
                                        ${unidad ? `<span class="iea-badge">${unidad}</span>` : ''}
                                        ${tipo ? `<span class="iea-badge">${tipo}</span>` : ''}
                                        ${categoria ? `<span class="iea-badge">${categoria}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="actions">
                                <button type="button" class="btn-action-new assign btn-programar" title="Asignar acción estratégica">
                                    ✏ Asignar acción
                                </button>
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Codigo', visible: false},
            {data: 'IdIndicador', visible: false, searchable: false},
            {data: 'IdAccionEstrategica', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        if (!$.fn.DataTable.isDataTable('#tablaListaIndicadoresEstrategicosAccion')) {
            return
        }
        const btn = $(this)
        btn.prop('disabled', true)
        dt_indEstrategicoAccion.ajax.reload(function () {
            btn.prop('disabled', false)
        }, false)
    })
})
