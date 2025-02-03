$(document).ready(function () {

    $('#facultades').change(function () {
        $('#divCarreras').attr('hidden',true)
        $('#rowDos').attr('hidden',true)
        $('#divSedes').attr('hidden',true)
        $('#divPlanes').attr('hidden',true)
        $('#divCursos').attr('hidden',true)
        $('#divConfiguracion').attr('hidden',true)
        $('#divTabla').attr('hidden',true)

        if ($(this).val() != '') {
            $('#carreras').val(null).trigger('change')
            $('#divCarreras').attr('hidden',false)
        }
    })

    $('#carreras').change(function () {
        $('#divTabla').attr('hidden',true)
        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)
            dataMaterias.gestion = $('#gestion').val()
            dataMaterias.carrera = $("#carreras").val()
            dataMaterias.flag = 1
            tableMaterias.ajax.reload()
        }
    })


})