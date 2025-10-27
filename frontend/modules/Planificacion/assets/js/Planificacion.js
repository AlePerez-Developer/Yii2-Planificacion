const RTA_CORRECTO = "ok"
const ESTADO_VIGENTE = 'V'
const ESTADO_CADUCO = 'C'
const ESTADO_ELIMINADO = 'E'

$("#btnMostrarCrear").click(function () {
    let icono = $('.icon');
    icono.toggleClass('opened');
    if (icono.hasClass("opened")) {
        $("#divDatos").show(500);
        $("#divTabla").hide(500);
    } else {
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    }
});

$(document).ready(function(){});