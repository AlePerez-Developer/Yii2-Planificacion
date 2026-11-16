let dt_actividad

function actividadEsVigente(codigo) {
    return codigo === ESTADO_VIGENTE
}

function actividadEstadoMeta(codigo) {
    const vigente = actividadEsVigente(codigo)
    return {
        codigo: vigente ? ESTADO_VIGENTE : ESTADO_CADUCO,
        clase: vigente ? 'vigente' : 'cancelada',
        texto: vigente ? 'Vigente' : 'Caduco',
        colorAvatar: vigente ? '#10b981' : '#f43f5e'
    }
}

function numerarFilasActividad(api) {
    if (!api) {
        return
    }
    const start = parseInt(api.page.info().start, 10) || 0
    api.column(0, {page: 'current', search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
        $(cell).find('.row-num').text(start + i + 1)
    })
}

function actualizarContadoresActividad() {
    if (typeof dt_actividad === 'undefined' || !dt_actividad) {
        return
    }

    let total = 0
    let vigentes = 0
    let caducos = 0

    dt_actividad.rows({search: 'none'}).data().each(function (row) {
        total++
        if (actividadEsVigente(row.CodigoEstado)) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#actividadSubtitleTotal').text(total + ' actividades registradas')
    $('#actividadCountVigentes').text(vigentes)
    $('#actividadCountCaducos').text(caducos)
}

function mostrarCargaTablaActividad() {
    $('#dticTableContainer').hide()
    $('#dticTableLoading').show()
}

function ocultarCargaTablaActividad() {
    $('#dticTableLoading').hide()
    $('#dticTableContainer').fadeIn(250)
}

function actividadIniciales(descripcion) {
    if (!descripcion) {
        return 'AC'
    }
    const palabras = descripcion.trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function cerrarMenusEstadoActividad(excepto) {
    $('.estado-wrap').not(excepto).removeClass('open')
}

function rangoPaginasVisibleActividad(paginaActual, totalPaginas, ventana) {
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

function actualizarPaginacionActividad(api) {
    const $paginacion = $('#actividadPagination')
    if (!$paginacion.length || !api) {
        return
    }

    const info = api.page.info()
    const total = info.recordsDisplay
    const start = total === 0 ? 0 : info.start + 1
    const end = info.end
    const paginaActual = info.page
    const totalPaginas = info.pages
    const rango = rangoPaginasVisibleActividad(paginaActual, totalPaginas, 5)

    if (total === 0) {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>0</strong> de <strong>0</strong> actividades'
        )
    } else {
        $paginacion.find('.pagination-info').html(
            'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> actividades'
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

function popularFiltroProgramaActividad() {
    const $filtro = $('#filtroPrograma')
    if (!$filtro.length || typeof dt_actividad === 'undefined' || !dt_actividad) {
        return
    }

    const valorActual = $filtro.val() || ''
    const opciones = {}

    dt_actividad.rows({search: 'none'}).data().each(function (row) {
        if (!row.IdPrograma) {
            return
        }
        const codigo = (row.programa && row.programa.Codigo) ? row.programa.Codigo : ''
        const descripcion = (row.programa && row.programa.Descripcion) ? row.programa.Descripcion : ''
        opciones[row.IdPrograma] = {
            id: row.IdPrograma,
            texto: (codigo ? '(' + codigo + ') - ' : '') + descripcion
        }
    })

    $filtro.empty().append('<option value="">Todos los programas</option>')

    Object.keys(opciones).sort(function (a, b) {
        return (opciones[a].texto || '').localeCompare(opciones[b].texto || '')
    }).forEach(function (id) {
        $filtro.append($('<option>', {
            value: opciones[id].id,
            text: opciones[id].texto
        }))
    })

    if (valorActual && $filtro.find('option[value="' + valorActual + '"]').length) {
        $filtro.val(valorActual)
    } else {
        $filtro.val('')
    }
}

function renderEstadoDropdownActividad(codigoActual) {
    const actual = actividadEstadoMeta(codigoActual)
    const vigente = actividadEstadoMeta(ESTADO_VIGENTE)
    const caduco = actividadEstadoMeta(ESTADO_CADUCO)

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
    dt_actividad = $('#tablaListaActividades').DataTable({
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
            emptyTable: 'No hay actividades registradas',
            zeroRecords: 'No se encontraron coincidencias'
        },
        order: [[1, 'asc']],
        orderFixed: {
            pre: [[1, 'asc']]
        },
        initComplete: function () {
            ocultarCargaTablaActividad()
            actualizarContadoresActividad()
            numerarFilasActividad(this.api())
            actualizarPaginacionActividad(this.api())
            popularFiltroProgramaActividad()
            if (typeof initActividadListadoFiltros === 'function') {
                initActividadListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasActividad(this.api())
            actualizarContadoresActividad()
            actualizarPaginacionActividad(this.api())
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/actividad/listar-todo',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return row.Descripcion
                    }

                    const estado = actividadEstadoMeta(row.CodigoEstado)
                    const codigoPrograma = (row.programa && row.programa.Codigo) ? row.programa.Codigo : ''
                    const descPrograma = (row.programa && row.programa.Descripcion) ? row.programa.Descripcion : ''

                    return `
                        <div class="card-row">
                            <span class="row-num"></span>
                            <div class="patient">
                                <div class="avatar" style="background:${estado.colorAvatar}">${actividadIniciales(row.Descripcion)}</div>
                                <div>
                                    <p class="patient-name">${row.Descripcion} <span class="code-badge">${row.Codigo}</span></p>
                                    <p class="patient-meta">Programa: ${codigoPrograma}${descPrograma ? ' — ' + descPrograma : ''}</p>
                                </div>
                            </div>
                            <div>${renderEstadoDropdownActividad(row.CodigoEstado)}</div>
                            <div class="actions">
                                <button type="button" class="btn-action-new edit">✏ Editar</button>
                                <button type="button" class="btn-action-new delete">🗑 Eliminar</button>
                            </div>
                        </div>
                    `
                }
            },
            {data: 'Codigo', visible: false},
            {data: 'Descripcion', visible: false},
            {data: 'IdPrograma', visible: false, searchable: false},
            {data: 'CodigoEstado', visible: false},
            {data: 'IdActividad', visible: false, searchable: false}
        ]
    })

    $(document).on('click', '#refreshTable', function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaActividad()
        dt_actividad.ajax.reload(function () {
            actualizarContadoresActividad()
            popularFiltroProgramaActividad()
            ocultarCargaTablaActividad()
            btn.prop('disabled', false)
        }, false)
    })

    $(document).on('click', function () {
        cerrarMenusEstadoActividad()
    })
})
