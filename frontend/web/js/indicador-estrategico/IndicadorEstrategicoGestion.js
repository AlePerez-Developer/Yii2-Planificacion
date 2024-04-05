$(document).ready(function (){
    let input

    let tabla;
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
            },
            error: function (respuesta) {
                let rta = respuesta['responseText'];
                let mensaje;
                if (rta === "errorNoEncontrado") {
                    mensaje = "Error: No se encontro el indicador estrategico seleccionado.";
                } else if (rta === "errorEnvio") {
                    mensaje = "Error: No se enviaron los datos de forma correcta.";
                } else if (rta === "errorCabecera") {
                    mensaje = "Error: Ocurrio un error en el llamado del procedimiento";
                } else {
                    mensaje = rta;
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
                        //render:prepareEditableOrder,
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


    $(document).on('click', '.btnP', function(){
        let objectBtn = $(this);
        let codigo = objectBtn.attr("codigo");
        let meta = objectBtn.attr("meta");

        let td = $(this).closest('td');
        let colIndex = tabla.cell(td).index().column;
        let rowIndex = tabla.cell(td).index().row;

        let inputbox = "<input type='text' id='metaVal' value='"+meta+"' class='form-control input-sm num' style='width: 150px'>"
        tabla.cell(rowIndex, colIndex-2).data(inputbox)
        $('#metaVal').select()
    })


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
        tabla.destroy()
    })

})