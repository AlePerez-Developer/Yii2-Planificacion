$(document).ready(function(){

    $("#CodigoObjEstrategico").change(function (){
        let codigo = $("#CodigoObjEstrategico").val();
        if (codigo !== ''){
            let datos = new FormData();
            datos.append("codigo", codigo);
            $.ajax({
                url: "index.php?r=Planificacion/obj-especifico/listar-objsinstitucionales",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    var data = jQuery.parseJSON(respuesta);
                    var sel = $("#CodigoObjInstitucional");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoObjInstitucional'] + '">' + value['Objetivo'] + '</option>');
                    });
                    $('#CodigoObjInstitucional').prop('disabled', false);
                },
            });
        } else {
            $("#CodigoObjInstitucional").val(null).trigger('change');
            $('#CodigoObjInstitucional').prop('disabled', true);
        }
    });

    $("#CodigoObjInstitucional").change(function (val, obj){
        let codigo = $("#CodigoObjInstitucional").val();
        if (codigo !== ''){
            let datos = new FormData();
            datos.append("codigo", codigo);
            $.ajax({
                url: "index.php?r=Planificacion/indicador/listar-objsespecificos",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    var data = jQuery.parseJSON(respuesta);
                    var sel = $("#CodigoObjEspecifico");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoObjEspecifico'] + '">' + '(' + value['CodigoCOGE'] + ') - ' + value['Objetivo'] + '</option>');
                    });
                    $('#CodigoObjEspecifico').prop('disabled', false);
                },
            }).done(function (){
                if (obj !== undefined)
                    $("#CodigoObjEspecifico").val(obj).trigger('change');
            })
        } else {
            $("#CodigoObjEspecifico").val(null).trigger('change');
            $('#CodigoObjEspecifico').prop('disabled', true);
        }
    });

    $('.objestrategicos').select2({
        placeholder: "Elija un objetivo estrategico",
        allowClear: true
    });

    $('.objinstitucional').select2({
        placeholder: "Elija un objetivo institucional",
        allowClear: true
    });

    $('.objinstitucional').select2({
        placeholder: "Elija un objetivo institucional",
        allowClear: true
    });

    $('.objinstitucional').select2({
        placeholder: "Elija un objetivo institucional",
        allowClear: true
    });

    $('.objinstitucional').select2({
        placeholder: "Elija un objetivo institucional",
        allowClear: true
    });

    $('.objespecifico').select2({
        placeholder: "Elija un objetivo especifico",
        allowClear: true
    });

    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formobjespecifico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $(".objestrategicos").val(null).trigger('change');
        $("#codigo").val('');
        $("form").trigger("reset");
    }

    $("#btnMostrarCrearOpe").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")){
            $("#IngresoDatos").show(500);
            $("#Divtabla").hide(500);
        } else {
            $("#IngresoDatos").hide(500);
            $("#Divtabla").show(500);
        }
    });

    $(".btnCancel").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
    });


    $(".btnGuardar").click(function () {
        if ($("#formoperacion").valid()){
            if ($("#codigo").val() === ''){
                GuardarObj();
            } else {
                ActualizarObj();
            }
        }
    });

})