let dt_itemsOperacion = null;

$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/operacion-dtic-item/';
    let idOperacionActual = null;

    $('#tablaOperacionesDtic').on('click', '.btn-items', function () {
        const row = dt_operacionDtic.row($(this).closest('tr')).data();
        idOperacionActual = row.IdOperacion;
        $('#descripcionOperacionItems').text(
            `${row.CodigoCompuesto || row.Codigo || ''} - ${row.Descripcion || ''}`
        );
        $('#modalAsignarItems').modal('show');
        cargarTabla();
    });

    $('#tablaItemsOperacion').on('click', '.btn-save-item', async function () {
        const row = dt_itemsOperacion.row($(this).closest('tr')).data();
        const input = $(this).closest('tr').find('.cantidad-asignada');
        const cantidad = Number(input.val());

        if (!Number.isInteger(cantidad) || cantidad <= 0) {
            MostrarMensaje(
                'error',
                'La cantidad asignada debe ser un número entero mayor que cero.'
            );
            return;
        }

        const datos = new FormData();
        datos.append('idOperacion', idOperacionActual);
        datos.append('idProgramacionItem', row.IdProgramacionItem);
        datos.append('cantidadAsignada', cantidad);

        await ajaxPromise({
            url: baseUrl + 'guardar',
            data: datos,
            spinnerBtn: $(this),
            successMsg: row.IdProgramacionItemOperacion
                ? 'Cantidad actualizada correctamente.'
                : 'Ítem asignado correctamente.',
            reloadTable: dt_itemsOperacion
        });
    });

    $('#tablaItemsOperacion').on('click', '.btn-remove-item', function () {
        const row = dt_itemsOperacion.row($(this).closest('tr')).data();

        Swal.fire({
            icon: 'warning',
            title: 'Confirmación',
            text: '¿Quitar este ítem de la operación?',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const datos = new FormData();
            datos.append('idOperacion', idOperacionActual);
            datos.append('idProgramacionItem', row.IdProgramacionItem);

            await ajaxPromise({
                url: baseUrl + 'quitar',
                data: datos,
                successMsg: 'Ítem quitado correctamente.',
                reloadTable: dt_itemsOperacion
            });
        });
    });

    $('#modalAsignarItems').on('hidden.bs.modal', function () {
        idOperacionActual = null;
    });

    function cargarTabla() {
        $('#itemsTableLoading').show();
        $('#itemsTableContainer').hide();

        if (dt_itemsOperacion !== null) {
            dt_itemsOperacion.ajax.reload(() => mostrarTabla());
            return;
        }

        dt_itemsOperacion = $('#tablaItemsOperacion').DataTable({
            ajax: {
                url: baseUrl + 'listar',
                method: 'POST',
                dataType: 'json',
                data: () => ({idOperacion: idOperacionActual}),
                dataSrc: 'data',
                error: function (xhr) {
                    const data = xhr.responseJSON || {};
                    MostrarMensaje(
                        'error',
                        GenerarMensajeError(data.message || 'No se pudieron cargar los ítems.'),
                        data.errors
                    );
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        if (type !== 'display') {
                            return `${row.TipoItem || ''} ${row.Descripcion || ''}`;
                        }

                        return `
                            <div class="dtic-code-container">
                                <span class="badge-result">${row.TipoItem || 'Ítem'}</span>
                            </div>
                            <div class="dtic-item-main">${row.Descripcion || ''}</div>
                        `;
                    }
                },
                {
                    data: 'Cantidad',
                    title: 'Cantidad programada',
                    className: 'text-center',
                    width: '140px'
                },
                {
                    data: 'PrecioUnitario',
                    title: 'Precio unitario',
                    className: 'text-right',
                    width: '140px',
                    render: data => Number(data || 0).toLocaleString('es-BO', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                },
                {
                    data: null,
                    title: 'Cantidad asignada',
                    className: 'text-center',
                    width: '180px',
                    render: (data, type, row) => type === 'display'
                        ? `<input type="number" min="1" step="1"
                                  class="form-control cantidad-asignada"
                                  value="${row.CantidadAsignada || 1}">`
                        : (row.CantidadAsignada || 0)
                },
                {
                    data: null,
                    title: 'Acciones',
                    className: 'text-center',
                    width: '130px',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (type !== 'display') return '';

                        const guardar = `
                            <button class="btn-action btn-save-item"
                                    title="${row.IdProgramacionItemOperacion ? 'Actualizar' : 'Asignar'}">
                                <i class="fa ${row.IdProgramacionItemOperacion ? 'fa-save' : 'fa-plus'}"></i>
                            </button>
                        `;
                        const quitar = row.IdProgramacionItemOperacion
                            ? `<button class="btn-action btn-remove-item" title="Quitar">
                                   <i class="fa fa-times"></i>
                               </button>`
                            : '';

                        return guardar + quitar;
                    }
                }
            ],
            initComplete: mostrarTabla
        });
    }

    function mostrarTabla() {
        $('#itemsTableLoading').hide();
        $('#itemsTableContainer').fadeIn(200);
    }
});
