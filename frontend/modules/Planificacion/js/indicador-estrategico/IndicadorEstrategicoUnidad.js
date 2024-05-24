$(document).ready(function (){
    let tablaUnidades

    $(document).on('click','#tablaIndicadoresGestion .btnProgramarU', function () {
        let objectBtn = $(this)
        let metaTotal = $('#metaIndicadorModal').val()
        let metaProg = $('#metaProgIndicadorModal').val()

        if (!(objectBtn.attr('meta') > 0)){
            MostrarMensaje('info','La gestion seleccionada no cuenta con una meta programada')
            DetenerSpiner(objectBtn)
            return false
        }
        if (metaTotal !== metaProg) {
            MostrarMensaje('info','Debe tener la programacion por gestion completa antes de realizar la programacion por unidad')
            DetenerSpiner(objectBtn)
            return false;
        }

        let datos = new FormData()
        let codigoProgramacionGestion = objectBtn.attr('codigo')
        let metaProgramacionGestion = objectBtn.attr('meta')
        datos.append('codigoProgramacionGestion',codigoProgramacionGestion)
        datos.append('metaProgramacionGestion',metaProgramacionGestion)
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/buscar-indicador-estrategico-unidades",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let gestion = JSON.parse(JSON.stringify(data.gestion));
                    let indicador = JSON.parse(JSON.stringify(data.indicador));
                    let obj = JSON.parse(JSON.stringify(data.obj));
                    $('#objetivoEstrategicoUnidad').val('(' + obj.CodigoObjetivo + ')' + ' - ' + obj.Objetivo)
                    $('#gestionUnidad').val(gestion.Gestion)
                    $('#metaTotalGestion').val(gestion.Meta)
                    $('#metaProgUnidad').val(data.metaPro)
                    $('#descripcionIndicadorUnidad').val(indicador.Descripcion)
                    if (gestion.Meta === data.metaPro){
                        $( "#metaTotalGestion" ).addClass( "completo" )
                    } else {
                        $( "#metaTotalGestion" ).removeClass( "completo" )
                    }
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        }).done(function (){
            tablaUnidades = $("#tablaIndicadoresUnidad").DataTable({
                layout: {
                    topStart: {
                        search: {
                            placeholder: 'Buscar registros..'
                        }
                    } ,
                    topEnd: null ,
                    bottomStart: null,
                    bottomEnd: null
                },
                ajax: {
                    method: "POST",
                    data: function ( d ) {
                        d.codigoGestion = codigoProgramacionGestion
                    },
                    dataType: 'json',
                    cache: false,
                    url: 'index.php?r=Planificacion/indicador-estrategico-unidad/listar-indicadores-estrategicos-unidades',
                    dataSrc: '',
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(GenerarMensajeError( thrownError + ' >' +xhr.responseText)))
                        MostrarUnidades()
                        DetenerSpiner(objectBtn)
                    }
                },
                initComplete: function () {
                    MostrarUnidades()
                    DetenerSpiner(objectBtn)
                },
                columns: [
                    {
                        className: 'dt-small dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'Codigo',
                        width: 30
                    },
                    {
                        className: 'dt-small dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'ProgramacionGestion',
                        visible: false,
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Apertura',
                        visible: false
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Da'
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Ue'
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Descripcion'
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Meta',
                        width: 200
                    },
                    {
                        className: 'dt-small dt-acciones dt-center',
                        orderable: false,
                        searchable: false,
                        data: null,
                        render: function (data, type, row) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-warning btn-sm btnProgramarM" codigo="' + data + '" meta="' + row.Meta + '" unidad="' + row.ProgUnidad + '" data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                                '</div>'
                                : data;
                        },
                    },
                ],
            });

            tablaUnidades.on('order.dt search.dt', function () {
                let i = 1;
                tablaUnidades.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
                    this.data(i++);
                });
            }).draw();
        });
    })



    $(document).on('click','#cerrarModalUnidad', function () {
        MostrarGestiones()
        tablaUnidades.destroy()
        $("#tablaIndicadoresGestion").DataTable().ajax.reload(null, false);

    })
    function MostrarUnidades() {
        $('#gestionBody').hide()
        $('#gestionFooter').hide()
        $('#unidadBody').show()
        $('#unidadFooter').show()
    }

    function MostrarGestiones() {
        $('#gestionBody').show()
        $('#gestionFooter').show()
        $('#unidadBody').hide()
        $('#unidadFooter').hide()
    }

})
