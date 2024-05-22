$(document).ready(function (){
    $(document).on('click','#tablaIndicadoresGestion .btnProgramarU', function () {
        let objectBtn = $(this)
        let metaTotal = $('#metaIndicadorModal').val()
        let metaProg = $('#metaProgIndicadorModal').val()
        if (metaTotal === metaProg) {
            let datos = new FormData()
            let codigoProgramacionGestion = objectBtn.attr('codigo')
            let metaProgramacionGestion = objectBtn.attr('meta')
            datos.append('codigoProgramacionGestion',codigoProgramacionGestion)
            datos.append('metaProgramacionGestion',metaProgramacionGestion)
            $.ajax({
                url: "index.php?r=Planificacion/indicador-estrategico/buscar-indicador-estrategico2",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (data) {
                    console.log(data)
                    if (data.respuesta === RTA_CORRECTO) {
                        let gestion = JSON.parse(JSON.stringify(data.gestion));
                        let indicador = JSON.parse(JSON.stringify(data.indicador));
                        let obj = JSON.parse(JSON.stringify(data.obj));
                        $('#objetivoEstrategicoUnidad').val('(' + obj.CodigoObjetivo + ')' + ' - ' + obj.Objetivo)
                        $('#gestionUnidad').val(gestion.Gestion)
                        $('#metaTotalGestion').val(gestion.Meta)
                        $('#metaProgUnidad').val('0')
                        $('#descripcionIndicadorUnidad').val(indicador.Descripcion)
                    } else {
                        MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                        DetenerSpiner(objectBtn)
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
                    DetenerSpiner(objectBtn)
                }
            })


            $('#gestionBody').hide()
            $('#gestionFooter').hide()
            $('#unidadBody').show()
            $('#unidadFooter').show()
        } else {
            MostrarMensaje('info','Debe tener la programacion por gestion completa antes de realizar la programacion por unidad')
            DetenerSpiner(objectBtn)
        }
    })

    $(document).on('click','#cerrarModalUnidad', function () {
        $('#gestionBody').show()
        $('#gestionFooter').show()
        $('#unidadBody').hide()
        $('#unidadFooter').hide()
        $("#tablaIndicadoresGestion").DataTable().ajax.reload(null, false);
    })

})
