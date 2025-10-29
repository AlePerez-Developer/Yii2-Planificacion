(function ($) {
    const $container = $('#contexto-poa-dropdown');
    if (!$container.length) {
        return;
    }

    const endpoint = $container.data('contextoPoaUrl');
    const $list = $('#contextoPoaList');
    const $feedback = $('#contextoPoaFeedback');
    const $refresh = $('#contextoPoaRefresh');
    const $close = $('#contextoPoaClose');
    const dropdownToggleElement = document.getElementById('contextoPoaToggle');
    const dropdownInstance = dropdownToggleElement && window.bootstrap
        ? window.bootstrap.Dropdown.getOrCreateInstance(dropdownToggleElement)
        : null;

    let isLoading = false;
    let hasLoaded = false;
    let selectedCodigo = null;

    function setFeedback(message, isError) {
        if (!message) {
            $feedback.addClass('d-none').removeClass('text-danger').addClass('text-muted').text('');
            return;
        }

        $feedback.toggleClass('text-danger', Boolean(isError));
        $feedback.toggleClass('text-muted', !isError);
        $feedback.removeClass('d-none').text(message);
    }

    function renderItems(items) {
        $list.empty();
        setFeedback('');

        if (!Array.isArray(items) || !items.length) {
            setFeedback('No hay estados POA configurados.', false);
            return;
        }

        let $predeterminadoItem = null;

        items.forEach(function (item) {
            const descripcion = (item.Descripcion || '').trim();
            const abreviacion = (item.Abreviacion || '').trim();
            const esPredeterminado = Number(item.EtapaPredeterminada) === 1;
            const codigo = Number.isFinite(Number(item.CodigoEstadoPOA)) ? Number(item.CodigoEstadoPOA) : null;

            const $li = $('<li class="contexto-poa-item" role="button"></li>');
            const $header = $('<div class="d-flex align-items-center justify-content-between"></div>');

            $header.append('<span class="text-uppercase fw-semibold">' + (descripcion || 'Sin descripcion') + '</span>');

            if (abreviacion) {
                $header.append('<span class="badge contexto-poa-abreviacion">' + abreviacion + '</span>');
            }

            if (codigo !== null) {
                $li.attr('data-contexto-poa-codigo', String(codigo));
            }

            if (esPredeterminado) {
                $li.attr('title', 'Estado predeterminado');
                $predeterminadoItem = $predeterminadoItem || $li;
            }

            $li.attr('tabindex', '-1');

            $li.append($header);
            $list.append($li);
        });

        let $initialSelection = null;

        if (selectedCodigo !== null) {
            $initialSelection = $list.find('[data-contexto-poa-codigo="' + selectedCodigo + '"]').first();
        }

        if (!$initialSelection || !$initialSelection.length) {
            $initialSelection = $predeterminadoItem || $list.children().first();
        }

        markSelected($initialSelection);
    }

    function renderError(message) {
        $list.empty();
        setFeedback(message || 'No se pudo cargar la informacion.', true);
    }

    function markSelected($item) {
        $list.find('.contexto-poa-item')
            .removeClass('contexto-poa-selected')
            .removeAttr('data-contexto-poa-focus');

        if ($item && $item.length) {
            $item.addClass('contexto-poa-selected');
            $item.attr('data-contexto-poa-focus', 'true');

            const codigoAttr = $item.attr('data-contexto-poa-codigo');
            if (codigoAttr !== undefined && codigoAttr !== '' && !Number.isNaN(Number(codigoAttr))) {
                selectedCodigo = Number(codigoAttr);
            } else {
                selectedCodigo = null;
            }
        } else {
            selectedCodigo = null;
        }
    }

    function requestData(force) {
        if (!endpoint || isLoading) {
            return;
        }

        isLoading = true;

        $.ajax({
            url: endpoint,
            method: 'GET',
            dataType: 'json'
        })
            .done(function (response) {
                if (response && response.success) {
                    renderItems(response.data);
                    hasLoaded = true;
                } else {
                    renderError(response && response.message ? response.message : 'No se pudo obtener la informacion.');
                }
            })
            .fail(function (jqXHR) {
                const errorMessage = jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message
                    ? jqXHR.responseJSON.message
                    : 'Error al cargar el contexto POA.';
                renderError(errorMessage);
            })
            .always(function () {
                isLoading = false;
            });
    }

    $container.on('show.bs.dropdown', function () {
        requestData(false);
    });

    $container.on('shown.bs.dropdown', function () {
        const $focusItem = $list.find('[data-contexto-poa-focus="true"]').first();
        if ($focusItem.length) {
            $focusItem.focus();
        }
    });

    $refresh.on('click', function (event) {
        event.preventDefault();
        hasLoaded = false;
        requestData(true);
    });

    $list.on('click', '.contexto-poa-item', function (event) {
        event.preventDefault();
        event.stopPropagation();
        markSelected($(this));
    });

    $close.on('click', function (event) {
        event.preventDefault();
        if (dropdownInstance) {
            dropdownInstance.hide();
        }
    });
})(jQuery);
