let dt_da

function daEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function daEstadoMeta(codigo) {
    const vigente = daEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function numerarFilasDa(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresDa() {
    if (typeof dt_da === 'undefined' || !dt_da) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_da.rows({search: 'none'}).data().each(function (row) {
        total++
        if (daEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#daSubtitleTotal').text(total + ' direcciones administrativas registradas')
    $('#daCountVigentes').text(vigentes)
    $('#daCountCaducos').text(caducos)
}

function mostrarCargaTablaDa() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaDa() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function daIniciales(descripcion) {
    if (!descripcion) {
        return 'DA'
    }
    const palabras = descripcion.trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function cerrarMenusEstadoDa(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisible(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionDa(api) {
    const $paginacion = $('#daPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisible(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> direcciones administrativas'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> direcciones administrativas'
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

function renderEstadoDropdownDa(codigoActual) {
    const actual = daEstadoMeta(codigoActual)
    const vigente = daEstadoMeta(ESTADO_VIGENTE)
    const caduco = daEstadoMeta(ESTADO_CADUCO)

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
    dt_da = $("#tablaListaDas").DataTable({
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
            emptyTable: 'No hay direcciones administrativas registradas',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        orderFixed: {
            pre: [[1, 'asc']]
        },
        initComplete: function () {
            ocultarCargaTablaDa()
            actualizarContadoresDa()
            numerarFilasDa(this.api())
            actualizarPaginacionDa(this.api())
            if (typeof initDaListadoFiltros === 'function') {
                initDaListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasDa(this.api())
            actualizarContadoresDa()
            actualizarPaginacionDa(this.api())
        },

        ajax: {
            method: "POST",
            dataType: "json",
            url: "index.php?r=Planificacion/da/listar-todo",
            dataSrc: "data"
        },

        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    if (type !== 'display') {
                        return row['Descripcion']
                    }

                    const estado = daEstadoMeta(row['CodigoEstado'])

                    return `
                        <div class="card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${daIniciales(row['Descripcion'])}</div>
                                <div>
                                    <p class="patient-name">${row['Descripcion']} <span class="code-badge">${row['Da']}</span></p>
                                    <p class="patient-meta">Código DA: ${row['Da']}</p>
                                </div>
                            </div>
                            <div>${renderEstadoDropdownDa(row['CodigoEstado'])}</div>
                            <div class="actions">
                                <button type="button" class="btn-action-new edit">✏ Editar</button>
                                <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                            </div>
                        </div>
                `
                }
            },
            {data: 'Da', visible: false},
            {data: 'Descripcion', visible: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdDa', visible: false, searchable: false}
        ]
    })

    $(document).on("click", "#refreshTable", function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaDa()
        dt_da.ajax.reload(function () {
            actualizarContadoresDa()
            ocultarCargaTablaDa()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoDa()
    })
})
