$.extend($.fn.dataTable.defaults, {
    layout: {
        topStart: {
            search: {
                placeholder: 'Buscar registros..'
            }
        } ,
        topEnd:'pageLength' ,
        bottomStart: 'info',
        bottomEnd: 'paging'
    },
    responsive: true,
    retrieve: true,
    processing: true,
    deferRender: true,
    fixedColumns: true,
    autoWidth: false,
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
            "sFirst": "<span class='fas fa-angle-double-left'></span>",
            "sLast": "<span class='fas fa-angle-double-right'></span>",
            "sNext": "<span class='fas fa-angle-right'></span>",
            "sPrevious": "<span class='fas fa-angle-left'></span>"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    }
});

$(document).ready(function () {})