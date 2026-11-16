let dt_llavePresupuestaria

function llaveEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function llaveEstadoMeta(codigo) {
    const vigente = llaveEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function llaveDatosUnidad(row) {
    const unidad = row.unidadEjecutora || {}
    const da = unidad.das || {}
    const codigoDa = da.Da || row.Da || ''
    const codigoUe = unidad.Ue || row.Ue || ''
    const compuesto = row.Compuesto || ((codigoDa && codigoUe) ? (codigoDa + '-' + codigoUe) : '')
    const descripcion = unidad.Descripcion || row.UnidadDescripcion || ''
    return {
        id: unidad.IdUnidadEjecutora || row.IdUnidadEjecutora || '',
        da: codigoDa,
        ue: codigoUe,
        compuesto: compuesto,
        descripcion: descripcion
    }
}

function numerarFilasLlave(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresLlave() {
    if (typeof dt_llavePresupuestaria === 'undefined' || !dt_llavePresupuestaria) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_llavePresupuestaria.rows({search: 'none'}).data().each(function (row) {
        total++
        if (llaveEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#llaveSubtitleTotal').text(total + ' llaves registradas')
    $('#llaveCountVigentes').text(vigentes)
    $('#llaveCountCaducos').text(caducos)
}

function mostrarCargaTablaLlave() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaLlave() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function llaveIniciales(texto) {
    if (!texto) {
        return 'LP'
    }
    const palabras = texto.trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function cerrarMenusEstadoLlave(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleLlave(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionLlave(api) {
    const $paginacion = $('#llavePagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleLlave(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> llaves'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> llaves'
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

function popularFiltroUnidadEjecutoraLlave() {
    const $filtro = $('#filtroUnidadEjecutora')
    if (!$filtro.length || typeof dt_llavePresupuestaria === 'undefined' || !dt_llavePresupuestaria) {
        return
    }

    const valorActual = $filtro.val() || ''
    const opciones = {}

    dt_llavePresupuestaria.rows({search: 'none'}).data().each(function (row) {
        const ue = llaveDatosUnidad(row)
        if (!ue.id) {
            return
        }
        opciones[ue.id] = {
            id: ue.id,
            texto: (ue.compuesto ? ue.compuesto + ' — ' : '') + ue.descripcion,
            compuesto: ue.compuesto,
            da: ue.da,
            ue: ue.ue,
            descripcion: ue.descripcion
        }
    })

    $filtro.empty().append('<option value="">Todas las unidades ejecutoras</option>')

    Object.keys(opciones).sort(function (a, b) {
        return (opciones[a].texto || '').localeCompare(opciones[b].texto || '')
    }).forEach(function (id) {
        const item = opciones[id]
        $filtro.append($('<option>', {
            value: item.id,
            text: item.texto,
            'data-compuesto': item.compuesto,
            'data-da': item.da,
            'data-ue': item.ue,
            'data-descripcion': item.descripcion
        }))
    })

    if (valorActual && $filtro.find('option[value="' + valorActual + '"]').length) {
        $filtro.val(valorActual)
    } else {
        $filtro.val('')
    }
}

function renderEstadoDropdownLlave(codigoActual) {
    const actual = llaveEstadoMeta(codigoActual)
    const vigente = llaveEstadoMeta(ESTADO_VIGENTE)
    const caduco = llaveEstadoMeta(ESTADO_CADUCO)

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
    dt_llavePresupuestaria = $('#tablaListaLlavesPresupuestarias').DataTable({
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
            emptyTable: 'No hay llaves presupuestarias registradas',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        initComplete: function () {
            ocultarCargaTablaLlave()
            actualizarContadoresLlave()
            numerarFilasLlave(this.api())
            actualizarPaginacionLlave(this.api())
            popularFiltroUnidadEjecutoraLlave()
            if (typeof initLlaveListadoFiltros === 'function') {
                initLlaveListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasLlave(this.api())
            actualizarContadoresLlave()
            actualizarPaginacionLlave(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/llave-presupuestaria/listar-todo',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const ue = llaveDatosUnidad(row)
                    if (type !== 'display') {
                        return ue.compuesto + ' ' + ue.descripcion
                    }

                    const estado = llaveEstadoMeta(row.CodigoEstado)
                    const programa = (row.proyecto && row.proyecto.programa) || {}
                    const proyecto = row.proyecto || {}
                    const actividad = row.actividad || {}
                    const organizacional = (row.esOrganizacional === 1 || row.esOrganizacional === '1') ? 'Sí' : 'No'

                    return `
                        <div class="card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${llaveIniciales(ue.descripcion || ue.compuesto)}</div>
                                <div>
                                    <p class="patient-name">${ue.descripcion || 'Sin descripción'} <span class="code-badge">${ue.compuesto || '—'}</span></p>
                                    <p class="patient-meta">Llave: ${row.Llave || ''}</p>
                                    <p class="patient-meta">Programa: ${programa.Codigo || ''}${programa.Descripcion ? ' — ' + programa.Descripcion : ''}</p>
                                    <p class="patient-meta">Proyecto: ${proyecto.Codigo || ''} · Actividad: ${actividad.Codigo || ''} · Org: ${organizacional}</p>
                                </div>
                            </div>
                            <div>${renderEstadoDropdownLlave(row.CodigoEstado)}</div>
                            <div class="actions">
                                <button type="button" class="btn-action-new edit">✏ Editar</button>
                                <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Llave', visible: false},
            {data: 'IdUnidadEjecutora', visible: false, searchable: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdLlavePresupuestaria', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaLlave()
        dt_llavePresupuestaria.ajax.reload(function () {
            actualizarContadoresLlave()
            popularFiltroUnidadEjecutoraLlave()
            ocultarCargaTablaLlave()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoLlave()
    })
})
