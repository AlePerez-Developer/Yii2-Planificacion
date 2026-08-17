$(document).ready(function () {
    const formulario = Number($('#itemsDescatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/item-descatalogado/';
    let idOperacion = '';
    let tablaItems = null;

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
                title: 'Ítems', data: null, orderable: false, searchable: false,
                render: () => '<button class="btn btn-sm btn-primary btn-items"><i class="fas fa-list"></i> Gestionar</button>'
            }
        ]
    });

    $('#idGasto, #idFuente, #idOrganismo').select2({
        theme: 'bootstrap4', dropdownParent: $('#modalItems'), allowClear: true, width: '100%'
    });
    cargarCatalogo(base + 'listar-gastos', $('#idGasto'));
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

    $('#precio').on('input', function () {
        this.value = normalizarDecimal(this.value);
    });

    $('#formItemDescatalogado').on('submit', function (event) {
        event.preventDefault();
        const cantidad = Number($('#cantidad').val());
        const precio = Number($('#precio').val());
        if (!idOperacion || !$('#idGasto').val() || !$('#idFuente').val() || !$('#idOrganismo').val()
            || !Number.isInteger(cantidad) || cantidad < 1 || !Number.isFinite(precio) || precio <= 0) {
            return Swal.fire('Datos incompletos', 'Complete todos los campos con valores válidos.', 'warning');
        }
        $.ajax({
            url: base + 'guardar', method: 'POST', dataType: 'json',
            data: {
                idItemDescatalogado: $('#idItemDescatalogado').val(),
                idOperacion,
                idGasto: $('#idGasto').val(),
                idFuente: $('#idFuente').val(),
                idOrganismo: $('#idOrganismo').val(),
                cantidad,
                precio: $('#precio').val(),
                descripcion: $('#descripcion').val(),
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
        $('#idItemDescatalogado').val(row.IdItemDescatalogado);
        $('#idGasto').val(row.IdGasto).trigger('change');
        $('#idFuente').val(row.IdFuente).trigger('change.select2');
        cargarOrganismos(row.IdFuente, row.IdOrganismo);
        $('#cantidad').val(row.cantidad);
        $('#precio').val(Number(row.Precio || 0).toFixed(2));
        $('#descripcion').val(row.Descripcion || '');
    }).on('click', '.btn-delete-item', function () {
        const row = tablaItems.row($(this).closest('tr')).data();
        Swal.fire({title: '¿Eliminar ítem?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar'})
            .then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: base + 'eliminar', method: 'POST', dataType: 'json',
                    data: {idItemDescatalogado: row.IdItemDescatalogado, idOperacion, formulario},
                    success: () => {
                        tablaItems.ajax.reload(null, false);
                        tablaOperaciones.ajax.reload(null, false);
                    },
                    error: mostrarError
                });
            });
    });

    function cargarItems() {
        if (tablaItems) return tablaItems.ajax.reload();
        tablaItems = $('#tablaItems').DataTable({
            ajax: {
                url: base + 'listar-items', method: 'POST',
                data: () => ({idOperacion, formulario}), dataSrc: 'data', error: mostrarError
            },
            columns: [
                {
                    title: 'Gasto', data: null,
                    render: (data, type, row) => type === 'display'
                        ? `<b>${row.IdGasto || ''}</b> - ${row.GastoDescripcion || ''}<br><small>${row.EntidadTransferencia || ''}</small>`
                        : `${row.IdGasto || ''} ${row.GastoDescripcion || ''} ${row.EntidadTransferencia || ''}`
                },
                {title: 'Descripción', data: 'Descripcion', defaultContent: ''},
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

    function cargarCatalogo(url, select) {
        $.post(url).done(response => {
            select.empty().append(new Option('', '', false, false));
            (response.data || []).forEach(item => {
                const texto = item.EntidadTransferencia
                    ? `${item.id} - ${item.text} (${item.EntidadTransferencia})`
                    : `${item.id} - ${item.text}`;
                select.append(new Option(texto, item.id, false, false));
            });
            select.val(null).trigger('change.select2');
        }).fail(mostrarError);
    }

    function cargarOrganismos(idFuente, seleccionado) {
        const select = $('#idOrganismo');
        select.empty().append(new Option('', '', false, false)).trigger('change.select2');
        if (!idFuente) return;
        $.post(base + 'listar-organismos', {idFuente}).done(response => {
            (response.data || []).forEach(item => select.append(new Option(`${item.id} - ${item.text}`, item.id, false, false)));
            select.val(seleccionado || null).trigger('change.select2');
        }).fail(mostrarError);
    }

    function limpiarFormulario() {
        $('#idItemDescatalogado').val('');
        $('#formItemDescatalogado')[0].reset();
        $('#idGasto, #idFuente, #idOrganismo').val(null).trigger('change.select2');
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

    function mostrarError(xhr) {
        const response = xhr.responseJSON || {};
        Swal.fire('No se pudo completar', response.message || response.mensaje || 'Ocurrió un error.', 'error');
    }
});
