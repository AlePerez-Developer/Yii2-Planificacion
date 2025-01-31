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

        $('#divTabla').attr('hidden',true)

        if ($(this).val() != ''){
            $('#divTabla').attr('hidden',false)

            dataMaterias.gestion = $('#gestion').val()
            dataMaterias.carrera = $("#carreras").val()
            dataMaterias.curso = $("#cursos").val()
            dataMaterias.plan = $("#planes").val()
            dataMaterias.sede = $("#sedes").val()
            dataMaterias.flag = 1

            materiasMatricialesTable.ajax.reload()
        }



    })


})