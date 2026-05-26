$(document).ready(function () {
    let baseUrl = "index.php?r=Planificacion/indicador-estrategico-programacion/"

    function mensajeAccion(accion) {
        return `Los datos de la Política Estratégica se ${accion}ron correctamente.`;
    }

    /* =============================================
            CAMBIA EL ESTADO DEL REGISTRO
    ===============================================*/
    $(document).on('click', 'tbody #btnEstado', async function () {
        const btn = $(this);

        const idLlavePresupuestaria = btn.data('idllave');
        const idIndicadorEstrategico = btn.data('idindicador');
        const idGestion = btn.data('idgestion');

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);
        datos.append("idIndicadorEstrategico", idIndicadorEstrategico);
        datos.append("idGestion", idGestion);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: btn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstado(btn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', 'tbody .btnQuitar', function () {
        let objectBtn = $(this)
        const idLlave = objectBtn.data('llave');
        const id = objectBtn.data('id');
        const idindicador = objectBtn.data('idindicador')


        const datos = new FormData();
        datos.append("idLlave", idLlave);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la programacion?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + "eliminar",
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: $('#' + id).DataTable()
                    });
                    actualizarSumaGlobal(idindicador)
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });

    });

    // 1. Al hacer CLICK: Habilitar y Seleccionar todo
    $(document).on('click', '.input-editable-smart', function () {
        const input = $(this);

        if (input.prop('readonly')) {
            input.prop('readonly', false)
                .css({'background-color': '#fff', 'border-color': '#80bdff'})
                .select(); // Selecciona todo el texto automáticamente
        }
    });

// 2. Al presionar ENTER: Guardar
    $(document).on('keypress', '.input-editable-smart', function (e) {
        if (e.which === 13) { // Tecla Enter
            const input = $(this);
            const nuevoValor = input.val();
            const idRegistro = input.data('id');
            const idIndicador = input.data('idindicador')

            e.preventDefault();

            // Bloqueo visual mientras procesa
            input.prop('disabled', true);

            $.ajax({
                url: 'index.php?r=Planificacion/indicador-estrategico-programacion/guardar-meta',
                method: 'POST',
                data: {id: idRegistro, valor: nuevoValor},
                success: function (resp) {
                    if (resp.message === 'ok') {
                        // Volver al estado readonly exitoso
                        input.prop('readonly', true)
                            .prop('disabled', false)
                            .css({'background-color': 'transparent', 'border-color': 'transparent'})
                            .addClass('is-valid');

                        setTimeout(() => input.removeClass('is-valid'), 2000);

                        // Recalcular sumas si tienes la función lista
                        actualizarSumaGlobal(idIndicador);
                    } else {
                        alert("Error: " + resp.mensaje);
                        input.prop('disabled', false).focus();
                    }
                },
                error: function () {
                    input.prop('disabled', false).focus();
                }
            });
        }
    });

    function actualizarSumaGlobal(idIndicadorEstrategico)
    {
        console.log(idIndicadorEstrategico)
        $.ajax({
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion/calcular-meta',
            method: 'POST',
            data: {idIndicadorEstrategico: idIndicadorEstrategico},
            success: function (resp) {
                const nuevoTotal = parseFloat(resp.data);
                const badge = $(`#metaProg_${idIndicadorEstrategico}`);
                const metaGlobal = parseFloat(badge.data('meta-global'))

                badge.html(nuevoTotal);

                // 2. Limpiar clases de colores previas
                badge.removeClass('bg-warning bg-danger bg-info');

                // 3. Aplicar nueva lógica de colores
                if (metaGlobal > nuevoTotal) {
                    badge.addClass('bg-danger');
                } else if (metaGlobal === nuevoTotal) {
                    badge.addClass('bg-info');
                } else {
                    // Meta < MetaProgramada (Exceso)
                    badge.addClass('bg-warning');
                }

                // 4. Feedback visual (Opcional: un pequeño parpadeo)
                badge.fadeOut(100).fadeIn(100);


            },
            error: function () {
                input.prop('disabled', false).focus();
            }
        });

    }

// 3. Al perder el FOCO (Blur): Volver a readonly sin guardar si no se presionó Enter
    $(document).on('blur', '.input-editable-smart', function () {
        $(this).prop('readonly', true)
            .css({'background-color': 'transparent', 'border-color': 'transparent'});
    });

    function cambiarEstado(objectBtn, data) {
        if (data === 0) {
            objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
            objectBtn.find('.btn_text').html('Agregar')
        } else {
            objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
            objectBtn.find('.btn_text').html('Quitar')
        }
    }

})