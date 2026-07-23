function IniciarSpiner(Btn){
    Btn.append('<span class="loader"> <span class="loader-spinner"></span> </span>')
    Btn.find('i').css("display", "none")
    Btn.find('.btn_text').css("display", "none")
    Btn.prop( "disabled", true );
}

function DetenerSpiner(Btn){
    Btn.find('.loader').remove()
    Btn.find('i').removeAttr("style")
    Btn.find('.btn_text').removeAttr("style")
    Btn.prop( "disabled", false );
}

function cambiarEstadoBtnDtic(objectBtn, data){
    if (data === ESTADO_VIGENTE) {
        objectBtn.addClass('estado-on').removeClass('estado-off');
        objectBtn.find('.btn_text').html('Vigente')
    } else {
        objectBtn.removeClass('estado-on').addClass('estado-off')
        objectBtn.find('.btn_text').html('Caducado')
    }
}

function cambiarEstadoBtn(objectBtn, data){
    if (data === ESTADO_VIGENTE) {
        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
        objectBtn.find('.btn_text').html('Vigente')
    } else {
        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
        objectBtn.find('.btn_text').html('Caducado')
    }
}

function DataTable_actualizarFiltroColumna(dtTable,columnIndex,field) {

    let column = dtTable.column(columnIndex);
    let header = $(column.header());

    let select = header.find('select');

    if (select.length === 0) {
        select = $('<select class="form-select form-select-sm">' +
            '<option value="">Mostrar todo...</option>' +
            '</select>')
            .appendTo(header)
            .on('change', function () {
                let val = $(this).val();

                if (val === "") {
                    column.search('').draw();
                } else {
                    column.search(
                        '^' + $.fn.dataTable.util.escapeRegex(val) + '$',
                        true,
                        false
                    ).draw();
                }
            });
    }

    let valorSeleccionado = select.val();

    select.find('option:not(:first)').remove();

    column
        .data()
        .unique()
        .sort()
        .each(function (d) {
            let valor = d[field] ?? d;

            select.append(
                $('<option>', {
                    value: valor,
                    text: valor
                })
            );
        });

    select.val(valorSeleccionado);
}

function populateS2Areas(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/area-estrategica/listar-areas-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdAreaEstrategica"],
                        text: '(' + item['Codigo'] + ') - ' + item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Politicas(idAreaEsteategica, select2, val = null) {
    return $.ajax({
        method: "POST",
        dataType: 'json',
        data: {
            idAreaEstrategica: idAreaEsteategica
        },
        cache: true,
        url: 'index.php?r=Planificacion/politica-estrategica/listar-politicas-s2',

        success: function(data){
            select2.html('');

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdPoliticaEstrategica"],
                        text: '(' + item['Codigo'] + ') - ' + item["Descripcion"]
                    })
                );
            });

            if (val) {
                select2.val(val).trigger('change');
            } else {
                select2.val(null).trigger('change');
            }
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
        complete: function () {
            if (val) select2.val(val).trigger('change');
        }
    });
}

function populateS2ObjEstrategico(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-estrategico/listar-obj-estrategicos-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                let option = $('<option>', {
                    value: item.IdObjEstrategico,
                    text: item.Objetivo
                });

                option.data('data', {
                    id: item.IdObjEstrategico,
                    text: item.Objetivo,
                    producto: item.Producto,
                    compuesto: item.Compuesto
                });

                select2.append(option);
            });
            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2ObjInstitucional(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-institucional/listar-obj-institucionales-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                let option = $('<option>', {
                    value: item.IdObjInstitucional,
                    text: item.Objetivo
                });

                option.data('data', {
                    id: item.IdObjInstitucional,
                    text: item.Objetivo,
                    producto: item.Producto,
                    compuesto: item.Compuesto
                });

                select2.append(option);
            });
            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2ObjEspecifico(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-especifico/listar-obj-especificos-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                let option = $('<option>', {
                    value: item.IdObjEspecifico,
                    text: item.Objetivo
                });

                option.data('data', {
                    id: item.IdObjEspecifico,
                    text: item.Objetivo,
                    producto: item.Producto,
                    compuesto: item.Compuesto
                });

                select2.append(option);
            });
            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2TiposResultados(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/cat-tipos-resultados/listar-todo-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdTipoResultado"],
                        text: item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2CategoriasIndicadores(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/cat-categorias-indicadores/listar-todo-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdCategoriaIndicador"],
                        text: item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2UnidadesIndicadores(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/cat-unidades-indicadores/listar-todo-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdUnidadIndicador"],
                        text: item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2AccionesEstrategicas(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/accion-estrategica/listar-todo-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdAccionEstrategica"],
                        text: item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Da(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/da/listar-das-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdDa"],
                        text: '(' + item["Da"] + ') - ' + item["Descripcion"],
                        'data-key': item["Da"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Ue(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/ue/listar-ues-s2',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdUe"],
                        text: '(' + item["Ue"] + ') - ' + item["Descripcion"],
                        'data-key': item["Ue"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Programas(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/programa/listar-programas-s2',
        success: function(data){
            select2.html('');

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdPrograma"],
                        text: '(' + item["Codigo"] + ') - ' + item["Descripcion"],
                        'data-key': item["Codigo"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}

function populateS2Proyectos(idPrograma, select2, val = null) {
    return $.ajax({
        method: "POST",
        dataType: 'json',
        data: {
            idPrograma: idPrograma
        },
        url: 'index.php?r=Planificacion/proyecto/listar-proyectos-s2',

        success: function(data){

            select2.html('');

            $.each(data["data"], function(index, item) {

                select2.append(
                    $('<option>', {
                        value: item["IdProyecto"],
                        text: '(' + item["Codigo"] + ') - ' + item["Descripcion"],
                        'data-key': item["Codigo"]
                    })
                );
            });

            if (val) {
                select2.val(val).trigger('change');
            } else {
                select2.val(null).trigger('change');
            }
        }
    });
}

function populateS2Actividades(idPrograma,select2, val = null) {
    return $.ajax({
        method: "POST",
        dataType: 'json',
        data: {
            idPrograma: idPrograma
        },
        url: 'index.php?r=Planificacion/actividad/listar-actividades-s2',

        success: function(data){

            select2.html('');

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdActividad"],
                        text: '(' + item["Codigo"] + ') - ' + item["Descripcion"],
                        'data-key': item["Codigo"]
                    })
                );
            });

            if (val) {
                select2.val(val).trigger('change');
            } else {
                select2.val(null).trigger('change');
            }
        }
    });
}

$(document).ready(function () {})