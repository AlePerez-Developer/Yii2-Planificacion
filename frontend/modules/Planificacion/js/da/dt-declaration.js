let dt_da

function daEstadoEtiqueta(codigo) {
    return codigo === ESTADO_VIGENTE ? 'Vigentes Vigente' : 'Canceladas Caduca Caducado'
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
        if (row.CodigoEstado === ESTADO_VIGENTE) {
            vigentes++
        } else if (row.CodigoEstado === ESTADO_CADUCO) {
            caducos++
        }
    })

    $('#daSubtitleTotal').text(total + ' direcciones administrativas registradas')

    $('.stats .stat').each(function () {
        const etiqueta = $(this).find('.stat-label').text().trim().toLowerCase()
        if (etiqueta.indexOf('vigent') !== -1) {
            $(this).find('.stat-count').text(vigentes)
        } else if (etiqueta.indexOf('cancel') !== -1) {
            $(this).find('.stat-count').text(caducos)
        }
    })
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

$(document).ready(function () {
    dt_da = $("#tablaListaDas").DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        initComplete: function () {
            ocultarCargaTablaDa()
            actualizarContadoresDa()
            if (typeof initDaListadoFiltros === 'function') {
                initDaListadoFiltros()
            }
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

                    const numero = meta.settings._iDisplayStart + meta.row + 1
                    const vigente = row['CodigoEstado'] === ESTADO_VIGENTE
                    const estadoClase = vigente ? 'vigente' : 'cancelada'
                    const estadoTexto = vigente ? 'Vigente' : 'Caducado'
                    const colorAvatar = vigente ? '#0ea5e9' : '#f43f5e'

                    return `
                        <div class="card-row">
                            <span class="row-num">${numero}</span>
                            <div class="patient">
                                <div class="avatar" style="background:${colorAvatar}">${daIniciales(row['Descripcion'])}</div>
                                <div>
                                    <p class="patient-name">${row['Descripcion']} <span class="code-badge">${row['Da']}</span></p>
                                    <p class="patient-meta">Código DA: ${row['Da']}</p>
                                </div>
                            </div>                            
                            <div><span class="estado ${estadoClase}"><span class="dot"></span>${estadoTexto} ▾</span></div>
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
            {data: 'IdDa', visible: false, searchable: false}
        ]
    })

    dt_da.on('xhr.dt', function () {
        actualizarContadoresDa()
    })

    $(document).on("click", "#refreshTable", function () {
        const btn = $(this)
        btn.prop('disabled', true)
        mostrarCargaTablaDa()
        dt_da.ajax.reload(function () {
            ocultarCargaTablaDa()
            btn.prop('disabled', false)
        }, false)
    })
})
