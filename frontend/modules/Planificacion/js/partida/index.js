$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/partida/';
    const tabla = $('#tablaListaPartidas');
    let idPartida = EMPTY;

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formPartida').valid()) return;
        const datos = new FormData();
        datos.append('idPartida', idPartida);
        datos.append('idFuente', partida_s2Fuente.val() || '');
        datos.append('idOrganismo', partida_s2Organismo.val() || '');
        await ajaxPromise({
            url: baseUrl + (idPartida === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Partida guardada correctamente.',
            reloadTable: dt_partida
        });
    });

    tabla.on('click', '.btn-toggle-estado', async function () {
        const btn = $(this);
        const row = dt_partida.row(btn.closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idPartida', row.IdPartida),
            spinnerBtn: btn,
            successMsg: 'Estado actualizado correctamente.'
        });
        cambiarEstadoBtnDtic(btn, response.data);
    });

    tabla.on('click', '.btn-delete', function () {
        const row = dt_partida.row($(this).closest('tr')).data();
        Swal.fire({
            icon: 'warning',
            title: 'Eliminar partida',
            text: '¿Está seguro de eliminar la partida seleccionada?',
            showCancelButton: true,
            confirmButtonText: 'Borrar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.value) return;
            await ajaxPromise({
                url: baseUrl + 'eliminar',
                data: crearDatos('idPartida', row.IdPartida),
                spinnerBtn: $(this),
                successMsg: 'Partida eliminada correctamente.',
                reloadTable: dt_partida
            });
        });
    });

    tabla.on('click', '.btn-edit', async function () {
        const row = dt_partida.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idPartida', row.IdPartida)
        });
        idPartida = response.data.IdPartida;
        partida_s2Fuente.val(response.data.IdFuente).trigger('change');
        partida_s2Organismo.val(response.data.IdOrganismo).trigger('change');
        $('#btnMostrarCrear').trigger('click');
    });

    $('#formPartida').validate({
        ignore: [],
        rules: {
            idFuente: {required: true},
            idOrganismo: {
                required: true,
                remote: {
                    url: baseUrl + 'verificar-combinacion',
                    type: 'POST',
                    data: {
                        idPartida: () => idPartida,
                        idFuente: () => partida_s2Fuente.val() || '',
                        idOrganismo: () => partida_s2Organismo.val() || ''
                    }
                }
            }
        },
        messages: {
            idFuente: 'Seleccione una fuente.',
            idOrganismo: {
                required: 'Seleccione un organismo.',
                remote: 'Ya existe una partida vigente para esa combinación.'
            }
        },
        errorPlacement: function (error, element) {
            if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2'));
                return;
            }
            error.insertAfter(element);
        }
    });

    function limpiar() {
        idPartida = EMPTY;
        $('#formPartida')[0].reset();
        $('#formPartida').validate().resetForm();
        partida_s2Fuente.val(null).trigger('change');
        partida_s2Organismo.val(null).trigger('change');
    }
});
