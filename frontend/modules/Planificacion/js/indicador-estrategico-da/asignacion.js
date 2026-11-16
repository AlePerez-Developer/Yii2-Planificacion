const baseUrlIndDa = 'index.php?r=Planificacion/indicador-estrategico-da/'

function closeChildRowIndDa(row) {
    if (!row || !row.child || !row.child.isShown()) {
        return
    }
    const tr = $(row.node())
    $('div.slider', row.child())
        .stop(true, true)
        .slideUp(180, function () {
            row.child.hide()
            tr.removeClass('shown')
        })
}

function renderDasGrid(idIndicador, das) {
    if (!das || das.length === 0) {
        return '<div class="ieda-empty">No hay direcciones administrativas vigentes.</div>'
    }

    return das.map(function (da) {
        const checked = Number(da.Asignado) === 1 ? 'checked' : ''
        const etiqueta = da.Etiqueta || ((da.Da || '') + ' - ' + (da.Descripcion || ''))
        return `
            <label class="ieda-da-item">
                <input type="checkbox"
                       class="chk-asignar-da"
                       data-id-indicador="${idIndicador}"
                       data-id-da="${da.IdDa}"
                       ${checked}>
                <span class="ieda-da-label">${etiqueta}</span>
            </label>
        `
    }).join('')
}

function actualizarBadgeDas(idIndicador, delta) {
    const $badge = $('#dasCount_' + idIndicador)
    if (!$badge.length) {
        return
    }
    const actual = parseInt(String($badge.text()).replace(/\D/g, ''), 10) || 0
    const nuevo = Math.max(0, actual + delta)
    $badge.text(nuevo + ' DA')

    if (typeof dt_indDa !== 'undefined' && dt_indDa) {
        dt_indDa.rows().every(function () {
            const row = this.data()
            if (row && row.IdIndicador === idIndicador) {
                row.DasAsignadas = nuevo
            }
        })
    }
}

async function cargarDasIndicador(idIndicador) {
    const $loader = $('#loader_da_' + idIndicador)
    const $container = $('#das_container_' + idIndicador)

    const datos = new FormData()
    datos.append('idIndicador', idIndicador)

    try {
        const response = await ajaxPromise({
            url: baseUrlIndDa + 'listar-das',
            data: datos,
        })
        $loader.hide()
        $container.html(renderDasGrid(idIndicador, response.data || [])).fadeIn(200)
    } catch (err) {
        $loader.hide()
        $container.html('<div class="ieda-empty">No se pudo cargar el listado de DAs.</div>').show()
        console.error('Error al cargar DAs:', err)
    }
}

$(document).ready(function () {
    $(document).on('click', '#tablaListaIndicadoresDa tbody td.expandible', function () {
        if (typeof dt_indDa === 'undefined' || !dt_indDa) {
            return
        }

        const tr = $(this).closest('tr')
        const currentRow = dt_indDa.row(tr)

        if (currentRow.child.isShown()) {
            closeChildRowIndDa(currentRow)
            openedRowIndDa = null
            return
        }

        if (openedRowIndDa && openedRowIndDa.child && openedRowIndDa.child.isShown()) {
            closeChildRowIndDa(openedRowIndDa)
        }

        currentRow.child(formatChildIndDa(currentRow.data()), 'no-padding').show()
        tr.addClass('shown')

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180)

        openedRowIndDa = currentRow
        cargarDasIndicador(currentRow.data().IdIndicador)
    })

    $(document).on('click', '.ieda-da-item', function (e) {
        e.stopPropagation()
    })

    $(document).on('change', '.chk-asignar-da', async function (e) {
        e.stopPropagation()

        const $chk = $(this)
        const idIndicador = $chk.data('id-indicador')
        const idDa = $chk.data('id-da')
        const asignar = $chk.is(':checked')

        $chk.prop('disabled', true)

        const datos = new FormData()
        datos.append('idIndicador', idIndicador)
        datos.append('idDa', idDa)

        try {
            await ajaxPromise({
                url: baseUrlIndDa + (asignar ? 'asignar' : 'eliminar'),
                data: datos,
                successMsg: asignar
                    ? 'DA asignada correctamente.'
                    : 'Asignación eliminada correctamente.',
            })
            actualizarBadgeDas(idIndicador, asignar ? 1 : -1)
        } catch (err) {
            $chk.prop('checked', !asignar)
            console.error('Error al actualizar asignación DA:', err)
        } finally {
            $chk.prop('disabled', false)
        }
    })
})
