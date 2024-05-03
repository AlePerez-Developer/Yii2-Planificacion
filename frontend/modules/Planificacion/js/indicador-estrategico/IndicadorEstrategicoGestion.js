$(document).ready(function (){
    let tabla;
    let inputMeta, opcionesMeta;
    let metaTotal, metaProgramada

    $('.tablaListaIndicadoresEstrategicos tbody').on('click','.btnProgramar', function (){
        let btn = $(this)
        let codigo = $(this).attr("codigo");
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
            success: function (respuesta) {
                let data = JSON.parse(JSON.stringify(respuesta));
                $('#objetivoEstrategico').val('(' + data.CodigoObjetivo + ') - ' + data.Objetivo)
                $('#metaIndicadorModal').val(data.Meta)
                $('#metaProgIndicadorModal').val(data.metaProgramada)
                $('#codigoIndicadorModal').val(data.Codigo)
                $('#descripcionIndicador').val(data.Descripcion)
                metaTotal = parseInt(data.Meta,10)
                metaProgramada = parseInt(data.metaProgramada, 10)
                if (metaTotal === metaProgramada){
                    $( "#metaIndicadorModal" ).addClass( "completo" )
                } else {
                    $( "#metaIndicadorModal" ).removeClass( "completo" )
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(xhr.responseText))
                btn.find('span').css("display", "none");
                btn.find('i').removeAttr("style")
            }
        }).done(function (){
            tabla = $(".tablaIndicadoresGestion").DataTable({
                destroy: true,
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: null,
                    bottomEnd: null
                },
                ajax: {
                    method: "POST",
                    dataType: 'json',
                    data: function ( d ) {
                        d.indicador = codigo
                    },
                    cache: false,
                    url: 'index.php?r=Planificacion/indicador-estrategico-gestion/listar-indicadores-estrategicos-gestiones',
                    dataSrc: '',
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(xhr.responseText))
                        $('#programarIndicadorEstrategico').modal('show');
                        btn.find('span').css("display", "none");
                        btn.find('i').removeAttr("style")
                    }
                },
                initComplete: function () {
                    $('#programarIndicadorEstrategico').modal('show');
                    btn.find('span').css("display", "none");
                    btn.find('i').removeAttr("style")
                },
                columnDefs: [
                    { className: "dt-small", targets: "_all" },
                ],
                fixedColumns: true,
                autoWidth: false,
                columns: [
                    {
                        className: 'dt-small dt-center',
                        orderable: false,
                        searchable: false,
                        data: 'CodigoProgramacion',
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
                        data: 'CodigoProgramacion',
                        render: function (data, type, row) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-warning btn-sm btnP" codigo="' + data + '" meta="' + row.Meta + '" data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                                '</div>'
                                : data;
                        },
                    },
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
            success: function (respuesta){
                let data = JSON.parse(JSON.stringify(respuesta));
                if (data.rta === "ok") {
                    $('#metaProgIndicadorModal').val(data.metaProg)
                    $(".tablaIndicadoresGestion").DataTable().ajax.reload(null, false);
                    if (metaTotal === data.metaProg){
                        $( "#metaIndicadorModal" ).addClass( "completo" )
                    } else {
                        $( "#metaIndicadorModal" ).removeClass( "completo" )
                    }
                    inputMeta = '';
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.rta))
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

    $('#cerrarModal').click(function (){
        $(".tablaListaIndicadoresEstrategicos").DataTable().ajax.reload(null, false);
        inputMeta = '';
    })

})