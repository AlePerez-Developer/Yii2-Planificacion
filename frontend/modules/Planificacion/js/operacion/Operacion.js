$(document).ready(function(){

    $('#codigoObjEstrategico').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo estrategico",
        allowClear: true
    });

    $('#codigoObjInstitucional').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo estrategico",
        allowClear: true
    });

    $('#codigoObjEspecifico').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un objetivo especifico",
        allowClear: true
    });

    $('#codigoPrograma').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un programa",
        allowClear: true
    });

    $('#codigoProyecto').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un proyecto",
        allowClear: true
    });

    $('#codigoActividad').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un actividad",
        allowClear: true
    });

    $("#codigoObjEstrategico").change(function (){
        let codigo = $("#codigoObjEstrategico").val();
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
                    var sel = $("#codigoObjInstitucional");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoObjInstitucional'] + '">' + '(' + value['CodigoCOGE'] + ') - ' + value['Objetivo'] + '</option>');
                    });
                    $('#codigoObjInstitucional').prop('disabled', false);
                },
            });
        } else {
            $("#codigoObjInstitucional").val(null).trigger('change');
            $('#codigoObjInstitucional').prop('disabled', true);

            $("#codigoObjEspecifico").val(null).trigger('change');
            $('#codigoObjEspecifico').prop('disabled', true);
        }
    });

    $("#codigoObjInstitucional").change(function (val, obj){
        let codigo = $("#codigoObjInstitucional").val();
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
                    var sel = $("#codigoObjEspecifico");
                    sel.empty();
                    sel.append('<option></option>');
                    $.each(data, function(index, value) {
                        sel.append('<option value="' + value['CodigoObjEspecifico'] + '">' + '(' + value['CodigoCOGE'] + ') - ' + value['Objetivo'] + '</option>');
                    });
                    $('#codigoObjEspecifico').prop('disabled', false);
                },
            }).done(function (){
                if (obj !== undefined)
                    $("#codigoObjEspecifico").val(obj).trigger('change');
            })
        } else {
            $("#codigoObjEspecifico").val(null).trigger('change');
            $('#codigoObjEspecifico').prop('disabled', true);
        }
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