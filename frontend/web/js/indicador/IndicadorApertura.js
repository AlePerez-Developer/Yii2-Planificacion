$(document).ready(function(){
    let table = $('#tablaIndicadoresAperturas').DataTable();

    /*=======================================================
        REGISTRO DE UNIDADES
    ========================================================*/
    $(".tablaListaIndicadores tbody").on("click", ".btnUnidad", function () {
        let codigo = $(this).attr("codigo");
        $("#codigoIndicador").val(codigo)
        table.destroy();
        $('#tablaIndicadoresAperturas').empty()
        table = $(".tablaIndicadoresAperturas").DataTable({
            ajax: {
                method: "POST",
                dataType: 'json',
                cache: false,
                url: 'index.php?r=Planificacion/indicador-apertura/listar-unidades',
                data: function ( d ) {
                    d.indicador = $("#codigoIndicador").val()
                },
                dataSrc: '',
            },
            columnDefs: [
                { className: "dt-small", targets: "_all" },
                { className: "dt-center", targets: [0,1,3,4] },
                { orderable: false, targets: [0,3,4] },
                { searchable: false, targets: [0,3,4] }
            ],
            columns: [
                { data: 'CodigoUsuario'},
                { data: 'Da'},
                { data: 'Descripcion'},
                {
                    data: 'MetaObligatoria',
                    render: function (data, type, row, meta){
                        return ((type === 'display') && (row.check !== '0'))
                        ? '<input type="text" onkeypress="oso()" class="form-control num" id="i' + row.CodigoUnidad + '" codigoUnidad=' + row.CodigoUnidad + ' size="5" inicial = "' + row.MetaObligatoria + '"  value = "' + row.MetaObligatoria + '" >'
                        : '<input type="text" class="form-control num" id="i' + row.CodigoUnidad + '" codigoUnidad=' + row.CodigoUnidad + ' size="5" inicial = "' + row.MetaObligatoria + '"  value = "' + row.MetaObligatoria + '" disabled>'
                    }
                },
                {
                    data: 'check',
                    render: function (data, type, row, meta) {
                        return  ((type === 'display') && (data !== '0'))
                            ? '<input class="form-check-input" type="checkbox" codigoUnidad=' + row.CodigoUnidad + ' checked>'
                            : '<input class="form-check-input" type="checkbox" codigoUnidad=' + row.CodigoUnidad + '>';
                    },

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

    $('#indicadoresUnidades').on('change', ':checkbox', function(e) {
        let object = $(this);
        let estado = this.checked
        let codigoUnidad = object.attr("codigoUnidad");
        let codigoIndicador = $('#codigoIndicador').val();
        let input = $('#i'+ codigoUnidad)
        let inicial = input.attr("inicial");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        datos.append("codigoIndicador", codigoIndicador);
        $.ajax({
            url: "index.php?r=Planificacion/indicador-apertura/actualizar-indicador-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                if (respuesta !== "ok") {
                    Swal.fire({
                        icon: "error",
                        title: "Error en el proceso",
                        text: "No se pudo realizar la programacion del indicador a la unidad seleccionada, intente actualizar la pagina y si el problema persiste comuniquese con el adminsitrador del sistema",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    })
                    input.prop( "disabled", estado )
                    input.val(inicial)
                    $(object).prop( "checked", !estado );
                } else {
                    if (estado)
                       $('#i'+ codigoUnidad).prop( "disabled", false )
                    else {
                        input.val('0')
                        input.attr('inicial','0')
                        input.prop( "disabled", true )
                    }
                }
            }
        }).fail(function (){
            Swal.fire({
                icon: "error",
                title: "Error en el proceso",
                text: "No se pudo realizar la programacion del indicador a la unidad seleccionada, intente actualizar la pagina y si el problema persiste comuniquese con el adminsitrador del sistema",
                showCancelButton: false,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Cerrar"
            })
            input.prop( "disabled", estado )
            input.val(inicial)
            $(object).prop( "checked", !estado );
        })
    });


    $('#indicadoresUnidades').on('focusout', '.num', function(e) {
        let input = $(this)
        if (isNaN(parseInt($(this).val(), 10))){
            $(this).val('0')
        }
        if ( $(this).val() !== $(this).attr('inicial') ) {
            let inicial = $(this).attr('inicial')
            let meta = $(this).val()
            let codigoUnidad = $(this).attr("codigoUnidad");
            let codigoIndicador = $('#codigoIndicador').val();
            let datos = new FormData()
            datos.append("codigoUnidad", codigoUnidad);
            datos.append("codigoIndicador", codigoIndicador);
            datos.append('meta', meta)
            $.ajax({
                url: "index.php?r=Planificacion/indicador-apertura/actualizar-meta-unidad",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    if (respuesta !== "ok") {
                        Swal.fire({
                            icon: "error",
                            title: "Error en el proceso",
                            text: "No se pudo realizar la programacion de la meta a la unidad seleccionada, intente actualizar la pagina y si el problema persiste comuniquese con el adminsitrador del sistema",
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "Cerrar"
                        })
                        $(this).val(inicial)
                    } else {
                        input.attr('inicial', meta)
                    }
                }
            }).fail(function (){
                Swal.fire({
                    icon: "error",
                    title: "Error en el proceso",
                    text: "No se pudo realizar la programacion de la meta a la unidad seleccionada, intente actualizar la pagina y si el problema persiste comuniquese con el adminsitrador del sistema",
                    showCancelButton: false,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Cerrar"
                })
                $(this).val(inicial)
            })
        }
    })

    function oso() {
        console.log('s')
        let regex = new RegExp("^[0-9,.]*$");
        let key = String.fromCharCode(!this.charCode ? this.which : this.charCode);
        if (!regex.test(key)) {
            this.preventDefault();
            return false;
        }
    }

});

