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
            bottom: {
                className: 'pagination',
                features: ['info', 'paging']
            }
        },
        pageLength: 15,
        pagingType: 'simple_numbers',
        language: {
            paginate: {
                previous: '‹ Anterior',
                next: 'Siguiente ›'
            },
            emptyTable: 'No hay direcciones administrativas registradas',
            zeroRecords: 'No se encontraron coincidencias'
        },
        infoCallback: function (settings, start, end, max, total) {
            if (total === 0) {
                return 'Mostrando <strong>0</strong> de <strong>0</strong> direcciones administrativas'
            }
            return 'Mostrando <strong>' + start + '–' + end + '</strong> de <strong>' + total + '</strong> direcciones administrativas'
        },
        order: [[1, 'asc']],
        orderFixed: {
            pre: [[1, 'asc']]
        },
        initComplete: function () {
            ocultarCargaTablaDa()
            actualizarContadoresDa()
            numerarFilasDa(this.api())
            if (typeof initDaListadoFiltros === 'function') {
                initDaListadoFiltros()
            }
        },
        drawCallback: function () {
            numerarFilasDa(this.api())
            actualizarContadoresDa()
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
                                <button type="button" class="btn-action-new edit btn-edit">✏ Editar</button>
                                <button type="button" class="btn-action-new delete btn-delete">🗑 Eliminar</button>
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
