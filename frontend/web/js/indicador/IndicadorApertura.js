$(document).ready(function(){
    let table = $('#tablaIndicadoresAperturas').DataTable();



    /*=======================================================
        REGISTRO DE UNIDADES
    ========================================================*/
    $(".tablaListaIndicadores tbody").on("click", ".btnUnidad", function () {
        let codigo = $(this).attr("codigo");
        $("#Indicador").val(codigo)
        table.destroy();
        $('#tablaIndicadoresAperturas').empty()
        table = $(".tablaIndicadoresAperturas").DataTable({
            ajax: {
                method: "POST",
                dataType: 'json',
                cache: false,
                url: 'index.php?r=Planificacion/indicador-apertura/listar-unidades',
                data: function ( d ) {
                    d.indicador = $("#Indicador").val()
                },
                dataSrc: '',
            },
            columnDefs: [
                { className: "dt-small", targets: "_all" },
                { className: "dt-center", targets: [0] },
                { orderable: false, targets: [0] },
                { searchable: false, targets: [0] }
            ],
            columns: [
                { data: 'CodigoUsuario'},
                { data: 'Da'},
                { data: 'Descripcion'},
                { data: 'MetaObligatoria' },
                {
                    data: 'check',

                },
            ],
            pageLength : 30,
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "<i class='fa fa-arrow-right'></i>",
                    "sPrevious": "<i class='fa fa-arrow-left'></i>"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        })

        table.on('order.dt search.dt', function () {
            let i = 1;
            table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
                this.data(i++);
            });
        }).draw();

        $('#indicadoresUnidades').modal('show')


    });
});

