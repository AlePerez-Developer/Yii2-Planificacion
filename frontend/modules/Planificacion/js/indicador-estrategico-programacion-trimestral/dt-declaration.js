let dt_listaIndicadoresTrimestrales = null
let openedRowTrimestral = null

function ptNumero(valor) {
    const n = parseFloat(valor)
    return isNaN(n) ? 0 : n
}

function ptFormatoMeta(valor) {
    const n = ptNumero(valor)
    return Number.isInteger(n) ? String(n) : String(Math.round(n * 100) / 100)
}

function ptTexto(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html()
}

function ptIniciales(texto) {
    if (!texto) {
        return 'IN'
    }
    const palabras = String(texto).trim().split(/\s+/).slice(0, 2)
    return palabras.map(function (p) {
        return p.charAt(0)
    }).join('').toUpperCase()
}

function textoAeiTrimestral(row) {
    const codigo = (row.AccionCodigo != null && row.AccionCodigo !== '')
        ? row.AccionCodigo
        : ((row.accionesEstrategicas && row.accionesEstrategicas.Codigo != null)
            ? row.accionesEstrategicas.Codigo
            : '—')
    const descripcion = row.AccionDescripcion
        || (row.accionesEstrategicas && row.accionesEstrategicas.Descripcion)
        || 'Sin acción asignada'
    return 'AEI: ' + codigo + ' - ' + descripcion
}

function ptEstadoMeta(metaGlobal, metaProgramada) {
    const total = ptNumero(metaGlobal)
    const prog = ptNumero(metaProgramada)
    if (total > 0 && prog >= total) {
        return {clase: 'completo', texto: 'Completo'}
    }
    return {clase: 'pendiente', texto: 'Pendiente'}
}

function renderMetaFracPt(metaProgramada, metaGlobal, idSuffix) {
    const estado = ptEstadoMeta(metaGlobal, metaProgramada)
    return `
        <div class="pg-meta-wrap" id="ptMetaWrap_${idSuffix}">
            <div class="pg-meta-numbers">
                <span class="pg-meta-prog" id="ptMetaProg_${idSuffix}">${ptFormatoMeta(metaProgramada)}</span>
                <small> / <span id="ptMetaTot_${idSuffix}">${ptFormatoMeta(metaGlobal)}</span></small>
            </div>
            <span class="pg-badge ${estado.clase}" id="ptMetaBadge_${idSuffix}">
                <span class="dot"></span>${estado.texto}
            </span>
        </div>
    `
}

function formatoDetalleTrimestral(row) {
    const id = row.IdIndicadorEstrategico || row.IdIndicador
    return `
        <div class="slider iept-slider" style="display:none;">
            <div class="iept-child">
                <div id="loader_${id}" class="p-3">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="tabs_container_${id}" class="tab-container" style="display:none;"></div>
            </div>
        </div>`
}

function cerrarFilaTrimestral(row) {
    if (!row || !row.child || !row.child.isShown()) {
        return
    }
    const tr = $(row.node())
    const nested = $('table.dtic-gestion-table', row.child())
    if (nested.length && $.fn.DataTable.isDataTable(nested)) {
        nested.DataTable().destroy()
    }
    $('div.slider', row.child())
        .stop(true, true)
        .slideUp(180, function () {
            row.child.hide()
            tr.removeClass('shown')
        })
}

function trimestreColumna(numero, atributo) {
    return {
        data: atributo,
        className: 'dt-center',
        width: 100,
        render: function (data, type, row) {
            if (type !== 'display') {
                return data
            }
            return `<input type="number" min="0" step="1" readonly
                class="form-control form-control-sm input-meta-trimestre"
                value="${ptNumero(data)}"
                data-original="${ptNumero(data)}"
                data-trimestre="${numero}"
                data-idprogramacion="${row.IdProgramacionIndicadorGestion}">`
        },
    }
}

function inicializarTablaProgramacionTrimestre(indicador, gestion) {
    const idIndicador = indicador.IdIndicadorEstrategico || indicador.IdIndicador
    const tableId = 'tbl_trimestral_' + idIndicador
    const selector = '#' + tableId

    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().ajax.reload(null, false)
        return
    }

    $(selector).DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/listar-programacion',
            method: 'POST',
            dataType: 'json',
            data: {
                idIndicadorEstrategico: idIndicador,
            },
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {}
                MostrarMensaje(
                    'error',
                    GenerarMensajeError(data.message || data.mensaje || 'No se pudo cargar la programación trimestral.'),
                    data.errors
                )
            },
        },
        columns: [
            {data: 'CodigoCompuesto', className: 'dt-small', title: 'Llave'},
            {data: 'Descripcion', className: 'dt-small', title: 'Descripción'},
            {
                data: 'MetaProgramada',
                className: 'dt-center meta-programada',
                width: 100,
                title: 'Meta Anual',
            },
            Object.assign(trimestreColumna(1, 'MetaPrimerTrimestre'), {title: '1er Trim.'}),
            Object.assign(trimestreColumna(2, 'MetaSegundoTrimestre'), {title: '2do Trim.'}),
            Object.assign(trimestreColumna(3, 'MetaTercerTrimestre'), {title: '3er Trim.'}),
            Object.assign(trimestreColumna(4, 'MetaCuartoTrimestre'), {title: '4to Trim.'}),
            {
                data: 'TotalTrimestral',
                className: 'dt-center total-trimestral',
                width: 90,
                title: 'Total',
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return data
                    }
                    const completa = Number(row.ProgramacionCompleta) === 1
                    return `<span class="total-badge ${completa ? 'completa' : 'pendiente'}">${ptFormatoMeta(data)}</span>`
                },
            },
            {
                data: null,
                className: 'dt-center',
                orderable: false,
                searchable: false,
                width: 100,
                title: 'Acciones',
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return ''
                    }
                    const tiene = Number(row.TieneProgramacionTrimestral) === 1
                        || ptNumero(row.TotalTrimestral) > 0
                    if (!tiene) {
                        return `<button type="button" class="btn-cancel btnReiniciarTrimestre" disabled title="Sin programación trimestral">
                            <span class="btn_text">Reiniciar</span>
                        </button>`
                    }
                    return `<button type="button" class="btn-cancel btnReiniciarTrimestre"
                                data-idprogramacion="${row.IdProgramacionIndicadorGestion}"
                                title="Reiniciar programación trimestral">
                            <span class="btn_text">Reiniciar</span>
                        </button>`
                },
            },
        ],
        rowCallback: function (row, data) {
            $(row)
                .removeClass('programacion-completa programacion-pendiente')
                .addClass(Number(data.ProgramacionCompleta) === 1 ? 'programacion-completa' : 'programacion-pendiente')
        },
        language: {
            emptyTable: 'No hay llaves programadas anualmente para esta gestión.',
            zeroRecords: 'Sin coincidencias',
        },
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        autoWidth: false,
        dom: 't',
    })
}

/**
 * Carga la gestión activa y muestra la tabla de 4 trimestres.
 */
function cargarTabGestionActiva(rowData) {
    const idIndicador = rowData.IdIndicadorEstrategico || rowData.IdIndicador
    const $loader = $('#loader_' + idIndicador)
    const $contenedor = $('#tabs_container_' + idIndicador)

    $.ajax({
        url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/obtener-gestion-activa',
        method: 'POST',
        dataType: 'json',
        success: function (resp) {
            const gestion = resp.data || {}
            const tableId = 'tbl_trimestral_' + idIndicador
            const labelGestion = gestion.Gestion || 'Gestión activa'

            $loader.remove()
            $contenedor.html(`
                <div class="iept-tab-toolbar">
                    <span class="pa-tab-meta completo">Gestión ${ptTexto(labelGestion)}</span>
                    <span class="iept-tab-hint">Distribuya la meta anual en los 4 trimestres</span>
                </div>
                <div class="table-responsive table-container">
                    <table id="${tableId}" class="table table-sm table-bordered dtic-gestion-table w-100">
                        <thead>
                            <tr>
                                <th>Llave</th>
                                <th>Descripción</th>
                                <th>Meta Anual</th>
                                <th>1er Trim.</th>
                                <th>2do Trim.</th>
                                <th>3er Trim.</th>
                                <th>4to Trim.</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            `).fadeIn(250)

            inicializarTablaProgramacionTrimestre(rowData, gestion)
        },
        error: function (xhr) {
            $loader.remove()
            const data = xhr.responseJSON || {}
            $contenedor.html(
                '<div class="alert alert-danger mb-0">No se pudo cargar la gestión activa.</div>'
            ).show()
            MostrarMensaje(
                'error',
                GenerarMensajeError(data.message || data.mensaje || 'Error al obtener la gestión.'),
                data.errors
            )
        },
    })
}

function inicializarTablaIndicadoresTrimestrales() {
    if ($.fn.DataTable.isDataTable('#tablaListaIndicadoresTrimestrales')) {
        dt_listaIndicadoresTrimestrales.ajax.reload(null, false)
        return
    }

    dt_listaIndicadoresTrimestrales = $('#tablaListaIndicadoresTrimestrales').DataTable({
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
            bottom: null,
        },
        paging: false,
        info: false,
        language: {
            emptyTable: 'No hay indicadores para el objetivo seleccionado',
            zeroRecords: 'No se encontraron coincidencias',
        },
        ajax: {
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-trimestral/listar-indicadores',
            method: 'POST',
            dataType: 'json',
            data: function () {
                return {idObjEstrategico: $('#idObjEstrategico').val()}
            },
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {}
                MostrarMensaje(
                    'error',
                    GenerarMensajeError(data.message || data.mensaje || 'Error al listar indicadores.'),
                    data.errors
                )
            },
        },
        columns: [
            {
                data: null,
                className: 'expandible',
                orderable: false,
                render: function (data, type, row) {
                    const descripcion = row.Descripcion || ''
                    if (type !== 'display') {
                        return descripcion
                    }

                    const metaGlobal = ptNumero(row.MetaGlobal != null ? row.MetaGlobal : row.Meta)
                    const metaProgramada = ptNumero(row.MetaProgramada)
                    const idSuffix = row.Codigo

                    return `
                        <div class="card-row iept-card-row">
                            <span class="row-num">${ptTexto(row.Codigo || '')}</span>
                            <div class="patient">
                                <div class="avatar" style="background:#0284c7">${ptIniciales(descripcion)}</div>
                                <div>
                                    <p class="patient-name">${ptTexto(descripcion)} <span class="code-badge">${ptTexto(row.Codigo || '')}</span></p>
                                    <p class="patient-meta">${ptTexto(textoAeiTrimestral(row))}</p>
                                    <p class="patient-meta pg-hint">Clic para programar los 4 trimestres</p>
                                </div>
                            </div>
                            ${renderMetaFracPt(metaProgramada, metaGlobal, idSuffix)}
                        </div>
                    `
                },
            },
            {data: 'Codigo', visible: false},
            {data: 'IdIndicadorEstrategico', visible: false, searchable: false},
        ],
    })

    $('#tablaListaIndicadoresTrimestrales tbody').on('click', 'td.expandible', function () {
        if (!dt_listaIndicadoresTrimestrales) {
            return
        }

        const tr = $(this).closest('tr')
        const currentRow = dt_listaIndicadoresTrimestrales.row(tr)
        const rowData = currentRow.data()

        if (!rowData || !(rowData.IdIndicadorEstrategico || rowData.IdIndicador)) {
            MostrarMensaje('error', 'No se pudo identificar el indicador.')
            return
        }

        if (currentRow.child.isShown()) {
            cerrarFilaTrimestral(currentRow)
            openedRowTrimestral = null
            return
        }

        if (openedRowTrimestral && openedRowTrimestral.child && openedRowTrimestral.child.isShown()) {
            cerrarFilaTrimestral(openedRowTrimestral)
        }

        currentRow.child(formatoDetalleTrimestral(rowData), 'no-padding').show()
        tr.addClass('shown')

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180)

        openedRowTrimestral = currentRow
        cargarTabGestionActiva(rowData)
    })
}
