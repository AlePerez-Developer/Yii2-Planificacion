$(document).ready(function (){
    let tabla;
    let inputMeta, opcionesMeta;
    let metaTotal, metaProgramada

    $('.tablaListaIndicadoresEstrategicos tbody').on('click','.btnProgramar', function (){
        let objectBtn = $(this)
        let codigo = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoIndicadorEstrategico", codigo);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-estrategico/buscar-indicador-estrategico",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let ind = JSON.parse(JSON.stringify(data.ind));
                    $('#objetivoEstrategico').val('(' + ind.CodigoObjetivo + ') - ' + data.Objetivo)
                    $('#metaIndicadorModal').val(ind.Meta)
                    $('#metaProgIndicadorModal').val(ind.metaProgramada)
                    $('#codigoIndicadorModal').val(ind.Codigo)
                    $('#descripcionIndicador').val(ind.Descripcion)
                    metaTotal = parseInt(ind.Meta,10)
                    metaProgramada = parseInt(ind.metaProgramada, 10)
                    if (metaTotal === metaProgramada){
                        $( "#metaIndicadorModal" ).addClass( "completo" )
                    } else {
                        $( "#metaIndicadorModal" ).removeClass( "completo" )
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
            tabla = $(".tablaIndicadoresGestion").DataTable({
                destroy: true,
                ajax: {
                    method: "POST",
                    data: function ( d ) {
                        d.indicador = codigo
                    },
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    url: 'index.php?r=Planificacion/indicador-estrategico-gestion/listar-indicadores-estrategicos-gestiones',
                    dataSrc: '',
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(GenerarMensajeError( thrownError + ' >' +xhr.responseText)))
                        $('#programarIndicadorEstrategicoGestion').modal('show');
                        DetenerSpiner(objectBtn)
                    }
                },
                initComplete: function () {
                    $('#programarIndicadorEstrategicoGestion').modal('show');
                    DetenerSpiner(objectBtn)
                },
                columnDefs: [
                    { className: "dt-small", targets: "_all" },
                ],
                columns: [
                    {
                        className: 'dt-small dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'CodigoProgramacionGestion',
                        width: 30
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Gestion'
                    },
                    {
                        className: 'dt-small dt-center',
                        data: 'Meta',
                        width: 200
                    },
                    {
                        className: 'dt-small',
                        data: 'IndicadorEstrategico',
                        visible: false
                    },
                    {
                        className: 'dt-small dt-acciones dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'CodigoProgramacionGestion',
                        render: function (data, type, row) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-warning btn-sm btnP" codigo="' + data + '" meta="' + row.Meta + '" data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                                '</div>'
                                : data;
                        },
                    },
                    {
                        className: 'dt-small dt-acciones dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'CodigoProgramacionGestion',
                        render: function (data, type, row) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-info btn-sm btnP" codigo="' + data + '" meta="' + row.Meta + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-eye"></i></button>' +
                                '</div>'
                                : data;
                        },
                    }
                ],
            });
        });
    })

    function restaurarMeta(){
        let td;
        let colIndex, rowIndex, metaAntigua
        td = opcionesMeta.closest('td');
        metaAntigua = $('#metaInput').attr('meta')
        colIndex = tabla.cell(td).index().column;
        rowIndex = tabla.cell(td).index().row;
        tabla.cell(rowIndex, colIndex).data(metaAntigua)
        opcionesMeta.remove()
        inputMeta = ''
    }

    function atualizarMeta(){
        let metaNueva = $('#metaInput').val()
        let codigo = $('#metaInput').attr("codigo");
        let datos = new FormData();
        datos.append('codigo',codigo)
        datos.append('metaProgramada',metaNueva)
        $.ajax({
            url: 'index.php?r=Planificacion/indicador-estrategico-gestion/guardar-meta-programada',
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data){
                if (data.respuesta === RTA_CORRECTO) {
                    let metaProg = JSON.parse(JSON.stringify(data.metaProg));
                    $('#metaProgIndicadorModal').val(metaProg.metaProg)
                    $(".tablaIndicadoresGestion").DataTable().ajax.reload(null, false);
                    if (metaTotal === metaProg.metaProg){
                        $( "#metaIndicadorModal" ).addClass( "completo" )
                    } else {
                        $( "#metaIndicadorModal" ).removeClass( "completo" )
                    }
                    inputMeta = '';
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',thrownError + ' >:' + xhr.responseText)
            }
        })
    }

    $(document).on('click', '.btnP', function(){
        if (inputMeta){
            restaurarMeta()
        }
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let meta = objectBtn.attr("meta");

        let td = $(this).closest('td');
        let colIndex = tabla.cell(td).index().column;
        let rowIndex = tabla.cell(td).index().row;

        inputMeta = "<div id='opcionesMeta' class='input-group'>" +
                    "<input type='text' id='metaInput' codigo='"+codigo+"' meta='"+meta+"'  value='"+meta+"' class='form-control input-sm num' style='width: 100px; height: 25px'>" +
                    "<button class='btn btn-outline-success center' type='button' id='guardarMeta' style='width: 25px; height: 25px'><span class='fa fa-check-circle'></span></button>" +
                    "<button class='btn btn-outline-danger center' type='button' id='revertirMeta' style='width: 25px; height: 25px'><span class='fa fa-times-circle'></span></button>" +
                "</div>"
        tabla.cell(rowIndex, colIndex-2).data(inputMeta)
        opcionesMeta = $('#opcionesMeta')
        $('#metaInput').select()
    })

    $(document).on("keypress", '#metaInput', function(e) {
        let regex = new RegExp("^[0-9]*$");
        let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (e.which === 13) {
            atualizarMeta()
        } else {
            if (!regex.test(key)) {
                event.preventDefault();
                return false;
            }
        }
    });

    $(document).on("click", '#guardarMeta', function() {
        atualizarMeta()
    });

    $(document).on("click", '#revertirMeta', function (){
        restaurarMeta()
    });

    $(document).on('hide.bs.modal','.programargestion', function () {
        $(".tablaListaIndicadoresEstrategicos").DataTable().ajax.reload(null, false);
        inputMeta = '';
    })
})