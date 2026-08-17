$(document).ready(function () {
    const formulario = Number($('#itemsCatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/item-catalogado/';
    let idOperacion = '';
    let tablaItems = null;
    let catalogoSigma = [];

    const tablaOperaciones = $('#tablaOperacionesItems').DataTable({
        ajax: {
            url: base + 'listar-operaciones',
            method: 'POST',
            data: {formulario},
            dataSrc: 'data',
            error: mostrarError
        },
        responsive: false,
        scrollX: true,
        columns: [
            {title: 'Código', data: 'Codigo', width: '70px'},
            {
                title: 'Operación', data: null,
                render: (data, type, row) => type === 'display'
                    ? `<b>${row.Descripcion || 'Sin descripción'}</b><br><small>${row.ObjetivoDescripcion || ''}</small>`
                    : `${row.Codigo || ''} ${row.Descripcion || ''} ${row.ObjetivoDescripcion || ''}`
            },
            {
                title: 'Llave presupuestaria', data: null,
                render: (data, type, row) => type === 'display'
                    ? `<b>${row.Llave || ''}</b><br><small>${row.LlaveDescripcion || ''}</small>`
                    : `${row.Llave || ''} ${row.LlaveDescripcion || ''}`
            },
            {
                title: 'Total programado',
                data: 'TotalProgramado',
                className: 'text-right',
                width: '130px',
                render: formatoMonto
            },
            {title: 'Tipo', data: 'TipoOperacion', width: '110px'},
            {
                title: 'Ítems', data: null, orderable: false, searchable: false, width: '100px',
                render: () => '<button class="btn btn-sm btn-primary btn-items"><i class="fas fa-boxes"></i> Gestionar</button>'
            }
        ]
    });

    inicializarSelects();
    cargarCatalogo(base + 'listar-sigma', $('#idSigma'), data => catalogoSigma = data);
    cargarCatalogo(base + 'listar-fuentes', $('#idFuente'));

    $('#tablaOperacionesItems').on('click', '.btn-items', function () {
        const row = tablaOperaciones.row($(this).closest('tr')).data();
        idOperacion = row.IdOperacion;
        $('#operacionSeleccionada').text(`${row.Codigo || ''} - ${row.Descripcion || 'Sin descripción'} | ${row.Llave || ''}`);
        limpiarFormulario();
        cargarItems();
        $('#modalItems').modal('show');
    });

    $('#idFuente').on('change', function () {
        cargarOrganismos($(this).val(), null);
    });

    $('#idSigma').on('change', function () {
        const item = catalogoSigma.find(x => String(x.id) === String($(this).val()));
        if (!item) return $('#detalleSigma').hide().empty();
        if (!$('#idItemCatalogado').val()) {
            $('#precio').val(Number(item.PrecioReferencial || 0).toFixed(2));
        }
        $('#detalleSigma').html(`
            <div><b>${item.Clase || 'Sin clase'}</b> · ${item.RamaComercial || 'Sin rama'}</div>
            <div>${item.Descripcion || ''}</div>
            <small>${item.Especificacion || 'Sin especificación'} · Gasto: ${item.IdGasto || '—'} ·
                Precio referencial: ${formatoMonto(item.PrecioReferencial)}</small>
        `).show();
    });

    $('#precio').on('input', function () {
        this.value = normalizarDecimal(this.value);
    });

    $('#formItemCatalogado').on('submit', function (event) {
        event.preventDefault();
        const cantidad = Number($('#cantidad').val());
        const precio = Number($('#precio').val());
        if (!idOperacion || !$('#idSigma').val() || !$('#idFuente').val() || !$('#idOrganismo').val()
            || !Number.isInteger(cantidad) || cantidad < 1 || !Number.isFinite(precio) || precio <= 0) {
            return aviso('Complete todos los campos con valores válidos.');
        }
        $.ajax({
            url: base + 'guardar',
            method: 'POST',
            dataType: 'json',
            data: {
                idItemCatalogado: $('#idItemCatalogado').val(),
                idOperacion,
                idSigma: $('#idSigma').val(),
                idFuente: $('#idFuente').val(),
                idOrganismo: $('#idOrganismo').val(),
                cantidad,
                precio: $('#precio').val(),
                formulario
            },
            success: function (response) {
                Swal.fire('Guardado', response.message || 'Ítem guardado.', 'success');
                limpiarFormulario();
                tablaItems.ajax.reload(null, false);
                tablaOperaciones.ajax.reload(null, false);
            },
            error: mostrarError
        });
    });

    $('#btnCancelarItem').on('click', limpiarFormulario);

    $('#tablaItems').on('click', '.btn-edit-item', function () {
        const row = tablaItems.row($(this).closest('tr')).data();
        $('#idItemCatalogado').val(row.IdItemCatalogado);
        $('#idSigma').val(row.IdSigma).trigger('change');
        $('#idFuente').val(row.IdFuente).trigger('change.select2');
        cargarOrganismos(row.IdFuente, row.IdOrganismo);
        $('#cantidad').val(row.cantidad);
        $('#precio').val(Number(row.Precio || 0).toFixed(2));
    }).on('click', '.btn-delete-item', function () {
        const row = tablaItems.row($(this).closest('tr')).data();
        Swal.fire({title: '¿Eliminar ítem?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar'})
            .then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: base + 'eliminar',
                    method: 'POST',
                    dataType: 'json',
                    data: {idItemCatalogado: row.IdItemCatalogado, idOperacion, formulario},
                    success: () => {
                        tablaItems.ajax.reload(null, false);
                        tablaOperaciones.ajax.reload(null, false);
                    },
                    error: mostrarError
                });
            });
    });

    function cargarItems() {
        if (tablaItems) {
            tablaItems.ajax.reload();
            return;
        }
        tablaItems = $('#tablaItems').DataTable({
            ajax: {
                url: base + 'listar-items',
                method: 'POST',
                data: () => ({idOperacion, formulario}),
                dataSrc: 'data',
                error: mostrarError
            },
            columns: [
                {
                    title: 'Ítem SIGMA', data: null,
                    render: (data, type, row) => type === 'display'
                        ? `<b>${row.Clase || ''}</b><br>${row.DescripcionSigma || ''}<br><small>${row.RamaComercial || ''} · ${row.Especificacion || ''}</small>`
                        : `${row.Clase || ''} ${row.DescripcionSigma || ''} ${row.RamaComercial || ''} ${row.Especificacion || ''}`
                },
                {title: 'Fuente', data: 'FuenteDescripcion'},
                {title: 'Organismo', data: 'OrganismoDescripcion'},
                {title: 'Cantidad', data: 'cantidad', className: 'text-right'},
                {title: 'Precio', data: 'Precio', className: 'text-right', render: formatoMonto},
                {
                    title: 'Total', data: null, className: 'text-right',
                    render: row => formatoMonto(Number(row.cantidad || 0) * Number(row.Precio || 0))
                },
                {
                    title: 'Acciones', data: null, orderable: false, searchable: false,
                    render: () => '<button class="btn btn-sm btn-light btn-edit-item"><i class="fa fa-pen"></i></button> <button class="btn btn-sm btn-danger btn-delete-item"><i class="fa fa-trash"></i></button>'
                }
            ]
        });
    }

    function inicializarSelects() {
        $('#idSigma').select2({
            theme: 'bootstrap4', dropdownParent: $('#modalItems'), placeholder: 'Buscar por cualquier campo SIGMA',
            allowClear: true, width: '100%', matcher: matcherCompleto,
            templateResult: item => {
                if (!item.id) return item.text;
                const data = $(item.element).data('item') || item;
                return $(`<div class="sigma-option"><b>${data.Clase || ''}</b> · ${data.Descripcion || ''}<br><small>${data.RamaComercial || ''} | ${data.Especificacion || ''} | ${formatoMonto(data.PrecioReferencial)}</small></div>`);
            }
        });
        $('#idFuente, #idOrganismo').select2({theme: 'bootstrap4', dropdownParent: $('#modalItems'), allowClear: true, width: '100%'});
    }

    function cargarCatalogo(url, select, callback) {
        $.post(url).done(response => {
            const data = response.data || [];
            select.empty().append(new Option('', '', false, false));
            data.forEach(item => {
                const option = new Option(item.text || item.id, item.id, false, false);
                $(option).data('item', item);
                select.append(option);
            });
            select.val(null).trigger('change.select2');
            if (callback) callback(data);
        }).fail(mostrarError);
    }

    function cargarOrganismos(idFuente, seleccionado) {
        const select = $('#idOrganismo');
        select.empty().append(new Option('', '', false, false)).trigger('change.select2');
        if (!idFuente) return;
        $.post(base + 'listar-organismos', {idFuente}).done(response => {
            (response.data || []).forEach(item => select.append(new Option(item.text, item.id, false, false)));
            select.val(seleccionado || null).trigger('change.select2');
        }).fail(mostrarError);
    }

    function matcherCompleto(params, data) {
        if (!$.trim(params.term)) return data;
        const item = $(data.element).data('item') || data;
        return Object.values(item).join(' ').toLowerCase().includes(params.term.toLowerCase()) ? data : null;
    }

    function limpiarFormulario() {
        $('#idItemCatalogado').val('');
        $('#formItemCatalogado')[0].reset();
        $('#idSigma, #idFuente, #idOrganismo').val(null).trigger('change.select2');
        $('#detalleSigma').hide().empty();
    }

    function formatoMonto(value) {
        return new Intl.NumberFormat('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(Number(value || 0));
    }

    function normalizarDecimal(valor) {
        const limpio = String(valor).replace(',', '.').replace(/[^\d.]/g, '');
        const partes = limpio.split('.');
        return partes.length > 1
            ? `${partes.shift()}.${partes.join('').slice(0, 2)}`
            : partes[0];
    }

    function aviso(mensaje) {
        Swal.fire('Datos incompletos', mensaje, 'warning');
    }

    function mostrarError(xhr) {
        const response = xhr.responseJSON || {};
        Swal.fire('No se pudo completar', response.message || response.mensaje || 'Ocurrió un error.', 'error');
    }
});
