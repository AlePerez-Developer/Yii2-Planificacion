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

function cambiarEstadoBtn(objectBtn, data){
    if (data === ESTADO_VIGENTE) {
        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
        objectBtn.find('.btn_text').html('Vigente')
    } else {
        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
        objectBtn.find('.btn_text').html('Caducado')
    }
}

function populateS2Areas(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-estrategico/listar-areas-estrategicas',
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

$(document).ready(function () {})