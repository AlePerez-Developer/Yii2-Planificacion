$(document).ready(function () {
    let baseUrl = "index.php?r=Planificacion/indicador-estrategico-programacion-anual/"

    programacionAnual_s2ObjEstrategico.on('change', function(){
        const idObjEstrategico = programacionAnual_s2ObjEstrategico.select2('data')[0]?.id
        const placeHolder = $('#mensajeInicial')
        const loader = $('#dticTableLoading')
        const container = $('#dticTableContainer')
        const dt_table = $('#tablaListaIndicadores')

        if (!idObjEstrategico){
            container.hide();
            loader.hide();
            placeHolder.show();
            return;
        }

        openedRow = null;
        placeHolder.hide();

        loader.show();
        container.hide();

        if ($.fn.DataTable.isDataTable(dt_table)) {
            dt_listaIndicadores.ajax.reload();
        } else {
            inicializarTablaIndicadores();
        }

        dt_listaIndicadores.one('draw', function () {
            loader.hide();
            container.fadeIn(180);
        });
    })

    function mensajeAccion(accion) {
        return `Los datos de la programacion se ${accion}ron correctamente.`;
    }

    /* =============================================
           CAMBIA EL ESTADO DEL REGISTRO
   ===============================================*/
    $(document).on('click', 'tbody #btnEstado', async function () {
        const btn = $(this);

        const idLlavePresupuestaria = btn.data('idllave');
        const codigoIndicador = btn.data('indicador');
        const gestion = btn.data('gestion');

        const datos = new FormData();
        datos.append("idLlavePresupuestaria", idLlavePresupuestaria);
        datos.append("codigoIndicador", codigoIndicador);
        datos.append("gestion", gestion);

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

        const tabla = objectBtn.closest('table');
        const codigo = objectBtn.data('codigoindicador');
        const gestion = objectBtn.data('gestion');

        const dt_table = tabla.DataTable();
        const dt_row = dt_table.row(objectBtn.closest('tr')).data();
        let rowId = dt_row["IdProgramacionIndicadorGestio"];
        let idIndicadorEstrategico = dt_row["IdIndicadorEstrategico"];


        const datos = new FormData();
        datos.append("idProgramacion", rowId);

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
                        reloadTable: dt_table
                    });
                    actualizarSumaGlobal(codigo)
                    actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion)
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });
    });


    $(document).on('click', '.input-meta', function () {
        const input = $(this);

        if (input.prop('readonly')) {
            input.prop('readonly', false)
                .select();
        }
    });

    $(document).on('keypress', '.input-meta', function (e) {
        if (e.which === 13) {
            const objectInput = $(this);
            const meta = objectInput.val();
            const tabla = objectInput.closest('table');

            const dt_table = tabla.DataTable();
            const dt_row = dt_table.row(objectInput.closest('tr')).data();
            let rowId = dt_row["IdProgramacionIndicadorGestio"];
            let idIndicadorEstrategico = dt_row["IdIndicadorEstrategico"];

            const codigo = objectInput.data('codigoindicador');
            const gestion = objectInput.data('gestion')
            e.preventDefault();

            $.ajax({
                url: baseUrl + 'guardar-meta',
                method: 'POST',
                data: {
                    idProgramacion: rowId,
                    meta: meta,
                },
                success: function (resp) {
                    if (resp.message === 'ok') {
                        objectInput.prop('readonly', true)
                            .prop('disabled', false)
                            .addClass('is-valid');

                        setTimeout(() => objectInput.removeClass('is-valid'), 1000);

                        // Recalcular sumas
                        actualizarSumaGlobal(codigo);
                        actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion);
                    } else {
                        alert("Error: " + resp.mensaje);
                        objectInput.prop('disabled', false).focus();
                    }
                },
                error: function () {
                    objectInput.prop('disabled', false).focus();
                }
            });
        }
    });

    function actualizarSumaGlobal(codigo) {
        const badge = $(`#metaProg_${codigo}`);
        const badgeTxt = $(`#metaTxt_${codigo}`);
        $.ajax({
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/calcular-meta',
            method: 'POST',
            data: {codigoIndicador: codigo},
            success: function (resp) {
                const nuevoTotal = parseFloat(resp.data);
                const metaGlobal = parseFloat(badge.data('meta-global'))

                badge.html(nuevoTotal);

                badge.removeClass('bg-warning bg-danger bg-info');
                badgeTxt.removeClass('bg-warning bg-danger bg-info');

                let texto = 'Excedente'
                if (metaGlobal > nuevoTotal) {
                    badge.addClass('bg-danger'); badgeTxt.addClass('bg-danger'); texto = 'Pendiente';
                } else if (metaGlobal === nuevoTotal) {
                    badge.addClass('bg-info'); badgeTxt.addClass('bg-info'); texto = 'Completa';
                } else {
                    badge.addClass('bg-warning'); badgeTxt.addClass('bg-warning');
                }

                badgeTxt.html(texto);

                badge.fadeOut(100).fadeIn(100);
                badgeTxt.fadeOut(100).fadeIn(100);
            },
            error: function () {
                badge.prop('disabled', false).focus();
            }
        });
    }

    function actualizarSumaGestion(idIndicadorEstrategico, codigo, gestion) {
        const badge = $(`#sum_tbl_${codigo}_${gestion}`);
        $.ajax({
            url: 'index.php?r=Planificacion/indicador-estrategico-programacion-anual/calcular-meta-gestion',
            method: 'POST',
            data: {idIndicadorEstrategico: idIndicadorEstrategico, gestion: gestion},
            success: function (resp) {
                const nuevoTotal = parseFloat(resp.data);

                badge.html(nuevoTotal);
                badge.fadeOut(100).fadeIn(100);
            },
            error: function () {
                input.prop('disabled', false).focus();
            }
        });
    }

    $(document).on('blur', '.input-meta', function () {
        $(this).prop('readonly', true).prop('disabled', false)
    });

    function cambiarEstado(objectBtn, data) {
        if (data === 0) {
            objectBtn.addClass('estado-on').removeClass('estado-off');
            objectBtn.find('.btn_text').html('Agregar')
        } else {
            objectBtn.removeClass('estado-on').addClass('estado-off')
            objectBtn.find('.btn_text').html('Quitar')
        }
    }
})
