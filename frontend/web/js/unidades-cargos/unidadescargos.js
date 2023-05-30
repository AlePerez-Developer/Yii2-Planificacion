$(document).ready(function(){
    let table = $(".tablaListaCargos").DataTable({
        columnDefs: [
            {
                targets: [0,2],
                className: 'dt-center'
            },
            {
                targets: [1],
                className: 'dt-small'
            },
            {
                targets: [0,2],
                searchable: false,
                orderable: false
            }
        ],
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            url: 'index.php?r=Planificacion/cargos/listar-cargos',
            dataSrc: '',
        },
        columns: [
            { data: 'CodigoUsuario' },
            { data: 'NombreCargo' },
            {
                data: 'CodigoCargo',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">' 
                        : data;
                },
            },
        ],
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "",
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
    });

    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();


    function LoadArbol(){
        $arbol.tree('destroy')
        $.ajax({
            url: "index.php?r=Planificacion/unidades/listar-unidades-padre",
            method: "POST",
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
                $arbol.tree({
                    closedIcon: $('<i class="fas fa-arrow-circle-right"></i>'),
                    openedIcon: $('<i class="fas fa-arrow-circle-down"></i>'),
                    autoOpen: 1,
                    data: JSON.parse(respuesta),
                    onCreateLi: (node, $el) => {
                        if (node.matches) {
                            $el.addClass("highlight-node");
                        }
                    }
                });
            }
        });
    }

    var $arbol = $('#unidadPadre');
    LoadArbol()


    $("#search").on("click", () => {
        const searchTerm = $("#search-term").val().toLowerCase();
        const tree = $arbol.tree("getTree");
        if (!searchTerm) {
            foundMatch = false;
            tree.iterate((node) => {
                node['openForMatch'] = false;
                node["matches"] = false;
                return true;
            });
            $arbol.tree("refresh");
            return;
        }
        foundMatch = false;
        tree.iterate((node) => {
            const matches = node.name.toLowerCase().includes(searchTerm);
            node["openForMatch"] = matches;
            node["matches"] = matches;
            if (matches) {
                foundMatch = true;
                if (node.isFolder()) {
                    node.is_open = true;
                }
                let parent = node.parent;
                while (parent) {
                    parent["openForMatch"] = true;
                    parent.is_open = true;
                    parent = parent.parent;
                }
            }
            return true;
        });
        $arbol.tree("refresh");
    });

    $("#IngresoDatos").hide();

    function ReiniciarCampos(){
        $('#formunidad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigo").val('');
        $("#formunidad").trigger("reset");
    }

    $("#btnMostrarCrearUnidad").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")){
            $("#IngresoDatos").show(500);
            $("#Divtabla").hide(500);
        } else {
            $("#IngresoDatos").hide(500);
            $("#Divtabla").show(500);
        }
    });

    $(".btnCancel").click(function () {
        $('.icon').toggleClass('opened');
        ReiniciarCampos();
        $arbol.tree('selectNode', null);
        $('#search').click();
        $("#IngresoDatos").hide(500);
        $("#Divtabla").show(500);
    });

    $(".btnGuardar").click(function () {
        let node = $arbol.tree('getSelectedNode');
        if ($("#formunidad").valid()){
            if ($("#codigo").val() === ''){
                if (node ){
                    GuardarUnidad();
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia...",
                        text: 'Debe seleccionar una unidades padre para la nueva unidades',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Cerrar"
                    });
                }

            } else {
                ActualizarUnidad();
            }
        }
    });
});