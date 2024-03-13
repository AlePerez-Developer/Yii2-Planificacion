$(document).ready(function (){


    /*table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();*/



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
            let osotablex = $(".osotabla").DataTable({
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: null,
                    bottomEnd: null
                },
                ajax: {
                    method: "POST",
                    dataType: 'json',
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
                        data: 'Meta'
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
                        render: function (data, type) {
                            return type === 'display'
                                ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                                '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><span class="fa fa-pen-fancy"></span></button>' +
                                '</div>'
                                : data;
                        },
                    },
                ],

            });

        });
    })
})