const baseUrlPg = 'index.php?r=Planificacion/indicador-estrategico-programacion-global/'

function closeChildRowPg(row) {
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

function actualizarResumenChildPg(idIndicador, metaIndicador, metaProgramada, pendiente) {
    const $summary = $('#pgSummary_' + idIndicador)
    if (!$summary.length) {
        return
    }
    $summary.html(
        'Meta indicador: <strong>' + pgFormatoMeta(metaIndicador) + '</strong>'
        + ' · Programada: <strong>' + pgFormatoMeta(metaProgramada) + '</strong>'
        + ' · Pendiente: <strong>' + pgFormatoMeta(pendiente) + '</strong>'
    )
}

function actualizarBadgeFilaPg(idIndicador, metaIndicador, metaProgramada) {
    const $wrap = $('#pgMetaWrap_' + idIndicador)
    if (!$wrap.length) {
        return
    }
    $wrap.find('.pg-meta-prog').text(pgFormatoMeta(metaProgramada))
    $wrap.find('.pg-badge').replaceWith(renderBadgePg(metaIndicador, metaProgramada))

    if (typeof dt_pg !== 'undefined' && dt_pg) {
        dt_pg.rows().every(function () {
            const row = this.data()
            if (row && row.IdIndicador === idIndicador) {
                row.MetaProgramada = metaProgramada
                row.Meta = metaIndicador
            }
        })
        actualizarContadoresPg()
    }
}

function renderGestionesPg(idIndicador, payload) {
    const gestiones = payload.gestiones || []
    const metaIndicador = pgNumero(payload.metaIndicador)

    if (!gestiones.length) {
        return '<div class="pg-empty">No hay gestiones registradas para el PEI activo.</div>'
    }

    const filas = gestiones.map(function (g) {
        const meta = pgFormatoMeta(g.Meta)
        return `
            <div class="pg-gestion-row"
                 data-id-indicador="${idIndicador}"
                 data-id-gestion="${g.IdGestion}"
                 data-meta-indicador="${metaIndicador}">
                <div class="pg-gestion-year">
                    Gestión ${pgTexto(g.Gestion)}
                    <small>Enter para guardar</small>
                </div>
                <div class="pg-hint-enter">Solo números · puede ser 0</div>
                <div class="pg-meta-input-wrap">
                    <input type="text"
                           class="pg-meta-input"
                           inputmode="decimal"
                           autocomplete="off"
                           value="${meta}"
                           data-original="${meta}"
                           aria-label="Meta gestión ${pgTexto(g.Gestion)}">
                    <span class="pg-save-icon"><i class="fas fa-check"></i></span>
                </div>
            </div>
        `
    }).join('')

    return filas
}

async function cargarGestionesIndicador(idIndicador) {
    const $loader = $('#loader_pg_' + idIndicador)
    const $container = $('#gestiones_container_' + idIndicador)

    const datos = new FormData()
    datos.append('idIndicador', idIndicador)

    try {
        const response = await ajaxPromise({
            url: baseUrlPg + 'listar-gestiones',
            data: datos,
        })
        const payload = response.data || {}
        actualizarResumenChildPg(
            idIndicador,
            payload.metaIndicador,
            payload.metaProgramada,
            payload.pendiente
        )
        $loader.hide()
        $container.html(renderGestionesPg(idIndicador, payload)).fadeIn(200)
    } catch (err) {
        $loader.hide()
        $container.html('<div class="pg-empty">No se pudo cargar el listado de gestiones.</div>').show()
        console.error('Error al cargar gestiones:', err)
    }
}

function normalizarMetaInput(valor) {
    const limpio = String(valor || '').trim().replace(',', '.')
    if (limpio === '') {
        return null
    }
    if (!/^\d+(\.\d+)?$/.test(limpio)) {
        return null
    }
    return parseFloat(limpio)
}

function calcularSumaVisiblePg($rows, exceptGestion, nuevaMeta) {
    let suma = 0
    $rows.each(function () {
        const $row = $(this)
        const idGestion = String($row.data('id-gestion'))
        if (idGestion === String(exceptGestion)) {
            suma += pgNumero(nuevaMeta)
            return
        }
        const valor = normalizarMetaInput($row.find('.pg-meta-input').val())
        suma += valor == null ? 0 : valor
    })
    return suma
}

async function guardarMetaGestion($input) {
    const $row = $input.closest('.pg-gestion-row')
    const idIndicador = $row.data('id-indicador')
    const idGestion = $row.data('id-gestion')
    const metaIndicador = pgNumero($row.data('meta-indicador'))
    const meta = normalizarMetaInput($input.val())

    $row.removeClass('is-error is-saved')

    if (meta === null) {
        $input.addClass('is-invalid')
        $row.addClass('is-error')
        MostrarMensaje('error', 'Ingrese un número válido (0 o mayor).')
        return
    }

    $input.removeClass('is-invalid')

    const $siblings = $row.closest('.pg-gestiones').find('.pg-gestion-row')
    const suma = calcularSumaVisiblePg($siblings, idGestion, meta)
    if (suma > metaIndicador + 0.00001) {
        $input.addClass('is-invalid')
        $row.addClass('is-error')
        const disponible = Math.max(0, metaIndicador - (suma - meta))
        MostrarMensaje(
            'error',
            'La meta programada no puede superar la meta del indicador. Disponible: ' + pgFormatoMeta(disponible) + '.'
        )
        return
    }

    if (String(meta) === String($input.data('original'))) {
        return
    }

    $row.addClass('is-saving')
    $input.prop('disabled', true)

    const datos = new FormData()
    datos.append('idIndicador', idIndicador)
    datos.append('idGestion', idGestion)
    datos.append('meta', meta)

    try {
        const response = await ajaxPromise({
            url: baseUrlPg + 'guardar-meta',
            data: datos,
            successMsg: 'Meta guardada correctamente.',
        })
        const data = response.data || {}
        $input.val(pgFormatoMeta(data.meta != null ? data.meta : meta))
        $input.data('original', $input.val())
        actualizarResumenChildPg(
            idIndicador,
            data.metaIndicador != null ? data.metaIndicador : metaIndicador,
            data.metaProgramada,
            data.pendiente
        )
        actualizarBadgeFilaPg(
            idIndicador,
            data.metaIndicador != null ? data.metaIndicador : metaIndicador,
            data.metaProgramada
        )

        $row.removeClass('is-saving').addClass('is-saved')
        $row.find('.pg-save-icon').html('<i class="fas fa-check"></i>')
        setTimeout(function () {
            $row.removeClass('is-saved')
        }, 900)
    } catch (err) {
        $row.removeClass('is-saving').addClass('is-error')
        $input.addClass('is-invalid')
        console.error('Error al guardar meta:', err)
    } finally {
        $input.prop('disabled', false)
        $input.trigger('focus')
    }
}

$(document).ready(function () {
    $(document).on('click', '#tablaListaProgramacionGlobal tbody td.expandible', function () {
        if (typeof dt_pg === 'undefined' || !dt_pg) {
            return
        }

        const tr = $(this).closest('tr')
        const currentRow = dt_pg.row(tr)

        if (currentRow.child.isShown()) {
            closeChildRowPg(currentRow)
            openedRowPg = null
            return
        }

        if (openedRowPg && openedRowPg.child && openedRowPg.child.isShown()) {
            closeChildRowPg(openedRowPg)
        }

        currentRow.child(formatChildPg(currentRow.data()), 'no-padding').show()
        tr.addClass('shown')

        $('div.slider', currentRow.child())
            .hide()
            .stop(true, true)
            .slideDown(180)

        openedRowPg = currentRow
        cargarGestionesIndicador(currentRow.data().IdIndicador)
    })

    $(document).on('click', '.pg-gestion-row', function (e) {
        e.stopPropagation()
    })

    $(document).on('keydown', '.pg-meta-input', function (e) {
        const permitidas = [
            'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
            'ArrowLeft', 'ArrowRight', 'Home', 'End'
        ]
        if (permitidas.indexOf(e.key) !== -1) {
            if (e.key === 'Enter') {
                e.preventDefault()
                guardarMetaGestion($(this))
            }
            return
        }

        if ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x'].indexOf(e.key.toLowerCase()) !== -1) {
            return
        }

        if (!/^[0-9.]$/.test(e.key)) {
            e.preventDefault()
            return
        }

        if (e.key === '.' && String(this.value).indexOf('.') !== -1) {
            e.preventDefault()
        }
    })

    $(document).on('input', '.pg-meta-input', function () {
        const $input = $(this)
        const limpio = String($input.val()).replace(/[^0-9.]/g, '')
        const partes = limpio.split('.')
        const normalizado = partes.length > 1
            ? partes[0] + '.' + partes.slice(1).join('')
            : partes[0]
        if ($input.val() !== normalizado) {
            $input.val(normalizado)
        }
        $input.removeClass('is-invalid')
        $input.closest('.pg-gestion-row').removeClass('is-error')
    })

    $(document).on('blur', '.pg-meta-input', function () {
        const $input = $(this)
        if (String($input.val()) !== String($input.data('original'))) {
            // No guarda automáticamente al salir; solo Enter.
            return
        }
    })
})
