$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const formulario = Number($('#itemsDescatalogadosPage').data('formulario'));
    const base = 'index.php?r=Planificacion/items-descatalogados/';
    const tabla = $('#tablaListaItemsDescatalogados');
    let idItem = EMPTY;
    let operaciones = [];

    cargarOperaciones();

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnReportePdf').on('click', function () {
        window.open($('#itemsDescatalogadosPage').data('reporte'), '_blank');
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formItemDescatalogado').valid()) return;
        const asignaciones = recogerAsignaciones();
        if (!asignaciones.some(item => Number(item.cantidad) > 0)) {
            MostrarMensaje('error', 'Debe ingresar cantidad en al menos una operación.');
            return;
        }
        const datos = new FormData();
        datos.append('idItem', idItem);
        datos.append('idGasto', desc_s2Gasto.val() || '');
        datos.append('idFuente', desc_s2Fuente.val() || '');
        datos.append('idOrganismo', desc_s2Organismo.val() || '');
        datos.append('idFuenteUniversitaria', desc_s2FuenteUniversitaria.val() || '');
        datos.append('idUnidadMedida', desc_s2UnidadMedida.val() || '');
        datos.append('descripcion', $('#descripcion').val());
        datos.append('precio', $('#precio').val());
        datos.append('formulario', formulario);
        datos.append('asignaciones', JSON.stringify(asignaciones));

        await ajaxPromise({
            url: base + (idItem === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Ítem guardado correctamente.',
            reloadTable: dt_itemsDescatalogados
        });
    });

    tabla.on('click', 'td.details-control, td:not(:last-child)', function (e) {
        if ($(e.target).closest('.btn-action').length) return;
        const tr = $(this).closest('tr');
        const row = dt_itemsDescatalogados.row(tr);
        if (!row.data()) return;
        if (row.child.isShown()) {
            row.child.hide();
            tr.find('.details-control i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            return;
        }
        row.child(htmlOperaciones(row.data().operaciones || [])).show();
        tr.find('.details-control i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    });

    tabla.on('click', '.btn-delete', function (e) {
        e.stopPropagation();
        const row = dt_itemsDescatalogados.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar ítem',
            text: 'Se marcará como eliminado y se quitarán todas las asignaciones a operaciones.',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            const datos = new FormData();
            datos.append('idItem', row.IdItem);
            datos.append('formulario', formulario);
            await ajaxPromise({
                url: base + 'eliminar',
                data: datos,
                spinnerBtn: $(this),
                successMsg: 'Ítem eliminado correctamente.',
                reloadTable: dt_itemsDescatalogados
            });
        });
    });

    tabla.on('click', '.btn-edit', async function (e) {
        e.stopPropagation();
        const row = dt_itemsDescatalogados.row($(this).closest('tr')).data();
        const datos = new FormData();
        datos.append('idItem', row.IdItem);
        datos.append('formulario', formulario);
        const response = await ajaxPromise({
            url: base + 'buscar',
            data: datos
        });
        await llenarFormulario(response.data);
        if ($('#btnMostrarCrear').hasClass('closed')) {
            $('#btnMostrarCrear').trigger('click');
        }
    });

    $('#formItemDescatalogado').validate({
        ignore: [],
        rules: {
            idGasto: {required: true},
            descripcion: {required: true, maxlength: 500},
            idFuente: {required: true},
            idOrganismo: {required: true},
            idFuenteUniversitaria: {required: true},
            idUnidadMedida: {required: true},
            precio: {required: true, number: true, min: 0.01}
        },
        messages: {
            idGasto: 'Seleccione un gasto.',
            descripcion: 'Ingrese la descripción.',
            idFuente: 'Seleccione una fuente.',
            idOrganismo: 'Seleccione un organismo.',
            idFuenteUniversitaria: 'Seleccione una fuente universitaria.',
            idUnidadMedida: 'Seleccione una unidad de medida.',
            precio: 'Ingrese un precio válido.'
        },
        errorPlacement: function (error, element) {
            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'));
                return;
            }
            error.insertAfter(element);
        }
    });

    function cargarOperaciones() {
        $.ajax({
            url: base + 'listar-operaciones',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                operaciones = response.data || [];
                renderAccordion();
            },
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudieron cargar las operaciones.'), data.errors);
            }
        });
    }

    function renderAccordion(cantidades = {}) {
        const contenedor = $('#accordionOperaciones');
        if (!operaciones.length) {
            contenedor.html('<p class="text-muted">No hay operaciones vigentes en la unidad.</p>');
            return;
        }
        const html = operaciones.map((op, index) => {
            const id = `op-${op.IdOperacion}`;
            const show = index === 0 ? 'show' : '';
            const cantidad = cantidades[op.IdOperacion] ?? '';
            return `
                <div class="card">
                    <div class="card-header p-2">
                        <button class="btn btn-link text-left w-100" type="button" data-toggle="collapse" data-target="#${id}">
                            ${op.Codigo || ''} - ${op.Descripcion || 'Sin descripción'}
                        </button>
                    </div>
                    <div id="${id}" class="collapse ${show}" data-parent="#accordionOperaciones">
                        <div class="card-body">
                            <label>Cantidad</label>
                            <input type="number" min="0" step="0.01" class="form-control operacion-cantidad"
                                   data-id="${op.IdOperacion}" value="${cantidad}">
                        </div>
                    </div>
                </div>`;
        }).join('');
        contenedor.html(html);
    }

    function recogerAsignaciones() {
        const asignaciones = [];
        $('.operacion-cantidad').each(function () {
            asignaciones.push({
                idOperacion: $(this).data('id'),
                cantidad: $(this).val() || 0
            });
        });
        return asignaciones;
    }

    async function llenarFormulario(data) {
        idItem = data.IdItem;
        desc_s2Gasto.val(data.IdGasto).trigger('change');
        $('#descripcion').val(data.Descripcion || '');
        $('#precio').val(data.Precio || '');
        desc_s2Fuente.val(data.IdFuente).trigger('change');
        await esperarOpciones(desc_s2Organismo);
        desc_s2Organismo.val(data.IdOrganismo).trigger('change');
        await esperarOpciones(desc_s2FuenteUniversitaria);
        desc_s2FuenteUniversitaria.val(data.IdFuenteUniversitaria).trigger('change.select2');
        desc_s2UnidadMedida.val(data.IdUnidadMedida).trigger('change.select2');
        const cantidades = {};
        (data.asignaciones || []).forEach(asig => {
            cantidades[asig.IdOperacion] = asig.Cantidad;
        });
        renderAccordion(cantidades);
    }

    function esperarOpciones(select) {
        return new Promise(resolve => {
            let intentos = 0;
            const timer = setInterval(() => {
                intentos += 1;
                if (select.find('option').length > 1 || intentos > 20) {
                    clearInterval(timer);
                    resolve();
                }
            }, 150);
        });
    }

    function htmlOperaciones(lista) {
        if (!lista.length) {
            return '<div class="p-2 text-muted">Sin operaciones asignadas.</div>';
        }
        const filas = lista.map(op => `
            <tr>
                <td>${op.Codigo || ''}</td>
                <td>${op.Descripcion || ''}</td>
                <td class="text-right">${formatoMontoDesc(op.Cantidad)}</td>
            </tr>
        `).join('');
        return `
            <table class="item-child-table table table-sm mb-0">
                <thead>
                    <tr><th>Código</th><th>Operación</th><th class="text-right">Cantidad</th></tr>
                </thead>
                <tbody>${filas}</tbody>
            </table>`;
    }

    function limpiar() {
        idItem = EMPTY;
        $('#formItemDescatalogado')[0].reset();
        $('#detalleGasto').hide().empty();
        $('#formItemDescatalogado').validate().resetForm();
        desc_s2Gasto.val(null).trigger('change');
        desc_s2Fuente.val(null).trigger('change');
        desc_s2UnidadMedida.val(null).trigger('change.select2');
        renderAccordion();
    }
});
