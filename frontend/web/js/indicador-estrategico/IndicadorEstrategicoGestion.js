$(document).ready(function (){
    let input, opcionesMeta;
    let tabla;
    let metaTotal, metaProgramada
    $('.tablaListaIndicadoresEstrategicos tbody').on('click','.btnProgramar', function (){
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
                $('#codigoIndicadorModal').val(data.Codigo)
                $('#descripcionIndicador').val(data.Descripcion)
                metaTotal = parseInt(data.Meta,10)
                metaProgramada = parseInt(data.metaProgramada, 10)
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let rta = xhr.responseText;
                let mensaje;
                if (rta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el indicador estrategico seleccionado.";
                } else if (rta === "errorEnvio") {
                    mensaje = "Error: No se enviaron los datos de forma correcta.";
                } else if (rta === "errorCabecera") {
                    mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                } else {
                    mensaje = thrownError;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Alerta...',
                    text: mensaje,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Cerrar'
                })
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
                },
                columnDefs: [
                    { className: "dt-small", targets: "_all" },
                ],
                fixedColumns: true,
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
        input = ''
    }

    function atualizarMeta(){
        let metaNueva = $('#metaInput').val()
        if (metaNueva !== ''){
            if (metaTotal === 0){

            } else {
                if ( ( parseInt(metaNueva,10) + metaProgramada ) <= metaTotal ) {
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
                        success: function (respuesta){
                            if (respuesta === "ok") {
                                $(".tablaIndicadoresGestion").DataTable().ajax.reload(null, false);
                                input = '';
                            }
                            else {
                                let mensaje;
                                if (respuesta === "errorValidacion") {
                                    mensaje = "Error: Ocurrio un error al validar los datos enviados";
                                } else if (respuesta === "errorEnvio") {
                                    mensaje = "Error: No se enviaron los datos de forma correcta.";
                                } else if (respuesta === "errorCabecera") {
                                    mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                                } else if (respuesta === "errorNoEncontrado") {
                                    mensaje = "Error: No se encontro el indicador estrategico seleccionado.";
                                } else if (respuesta === "errorSql") {
                                    mensaje = "Error: Ocurrio un error en la sentencia SQL";
                                } else {
                                    mensaje = respuesta;
                                }
                                Swal.fire({
                                    icon: "error",
                                    title: "Advertencia...",
                                    text: mensaje,
                                    showCancelButton: false,
                                    confirmButtonColor: "#3085d6",
                                    confirmButtonText: "Cerrar"
                                });
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            Swal.fire({
                                icon: "error",
                                title: "Advertencia...",
                                text: thrownError + ' >:' + xhr.responseText,
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "Cerrar"
                            });
                        }
                    })
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia...",
                        text: "La meta programada excede el valor de la meta total del indicador",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        } else {
            Swal.fire({
                icon: "warning",
                title: "Advertencia...",
                text: "No puede guardar un valor vacio para la meta",
                showCancelButton: false,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Cerrar"
            });
        }
    }

    $(document).on('click', '.btnP', function(){
        if (input){
            restaurarMeta()
        }
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let meta = objectBtn.attr("meta");

        let td = $(this).closest('td');
        let colIndex = tabla.cell(td).index().column;
        let rowIndex = tabla.cell(td).index().row;

        input = "<div id='opcionesMeta' class='input-group'>" +
                    "<input type='text' id='metaInput' codigo='"+codigo+"' meta='"+meta+"'  value='"+meta+"' class='form-control input-sm num' style='width: 100px; height: 25px'>" +
                    "<button class='btn btn-outline-success center' type='button' id='guardarMeta' style='width: 25px; height: 25px'><span class='fa fa-check-circle'></span></button>" +
                    "<button class='btn btn-outline-danger center' type='button' id='revertirMeta' style='width: 25px; height: 25px'><span class='fa fa-times-circle'></span></button>" +
                "</div>"
        tabla.cell(rowIndex, colIndex-2).data(input)
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

    /*$(document).on('click', '#osolala tbody td', function () {
        //alert('asdasd')

        var cell = tabla.cell(this);
        //alert(tabla.cell(this).data());
        var myString = "<input type='text'>"
       //tabla.cell( td ).data(myString).draw();
        cell.data(myString).draw();
        // note - call draw() to update the table's draw state with the new data
    });*/




    /*$('#example tbody').on('change', 'td input', function () {
        var val = $(this).val();
        var td = $(this).closest('td');
        var row = table.row( $(td) );
        var data = row.data();

        var myString = val + ': ' + data.School;

        table.cell( td ).data(myString).draw();

    } );*/

    $('#cerrarModal').click(function (){
        //tabla.destroy()
        //$(".tablaIndicadoresGestion").destroy()
        input = '';
    })

})