$(document).ready(function () {
    function format(d) {
        return (
            '<dl>' +
            '<dt class="dt-small">Vigencia</dt>' +
            '<dd class="dt-small"> De: ' + d.FechaInicio +  ' Hasta: ' + d.FechaFin + '</dd>' +
            '</dl>'
        );
    }

    let table = $("#tablaListaUnidades").DataTable({
        initComplete: function () {
            this.api()
                .columns([2,3])
                .every(function () {
                    let column = this;
                    let select = $('</br><select><option value="">Buscar...</option></select>')
                        .appendTo($(column.header()))
                        .on('change', function () {
                            let val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });
                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                });
        },
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/unidad/listar-unidades',
            dataSrc: '',
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
        columns: [
            {
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoUsuario',
                width: 30
            },
            {
                className: 'dt-control dt-small dt-center',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '',
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'Da'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                data: 'Ue'
            },
            {
                className: 'dt-small',
                orderable: false,
                data: 'Descripcion'
            },
            {
                className: 'dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'Organizacional' ,
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.Organizacional === '1'))
                        ? 'SI'
                        : 'NO' ;
                },
            },
            {
                className: 'dt-estado dt-small dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row, meta) {
                    return ( (type === 'display') && (row.CodigoEstado === ESTADO_VIGENTE))
                        ? '<button type="button" class="btn btn-success btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado =  "' + ESTADO_VIGENTE + '" ></button>'
                        : '<button type="button" class="btn btn-danger btn-sm  btnEstado" codigo="' + row.CodigoUnidad + '" estado = "' + ESTADO_CADUCO + '" ></button>' ;
                },
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoUnidad',
                render: function (data, type, row, meta) {
                    return type === 'display'
                        ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                        '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' + data + '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' + data + '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                        '</div>'
                        : data;
                },
            },
        ],
    });

    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();

    $('#tablaListaUnidades tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
        }
        else {
            row.child(format(row.data())).show();
        }
    });

    function reiniciarCampos() {
        $('#formUnidad *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigoUnidad").val('');
        $('#formUnidad').trigger("reset");
    }

    $("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")) {
            $("#divDatos").show(500);
            $("#divTabla").hide(500);
        } else {
            $("#divDatos").hide(500);
            $("#divTabla").show(500);
        }
    });

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(function () {
        if ($("#formUnidad").valid()) {
            if ($("#codigoUnidad").val() === '') {
                guardarUnidad();
            } else {
                actualizarUnidad()
            }
        }
    });

    $('#fechaInicio').change(function (){
        $("#formUnidad").validate().element('#fechaInicio');
        $("#formUnidad").validate().element('#fechaFin');
    })

    $('#fechaFin').change(function (){
        $("#formUnidad").validate().element('#fechaInicio');
        $("#formUnidad").validate().element('#fechaFin');
    })

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
    function guardarUnidad() {
        let da = $("#da").val();
        let ue = $("#ue").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let datos = new FormData();
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/guardar-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos de la nueva unidad se guardaron correctamente.')
                    $("#tablaListaUnidades").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click();
                    });
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        });
    }

    /*=============================================
    CAMBIA EL ESTADO DEL REGISTRO
    =============================================*/
    $("#tablaListaUnidades tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoUnidad = objectBtn.attr("codigo");
        let estado = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        IniciarSpiner(objectBtn);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/cambiar-estado-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    if (estado === ESTADO_VIGENTE) {
                        objectBtn.removeClass('btn-success').addClass('btn-danger')
                        objectBtn.attr('estado', ESTADO_CADUCO);
                    } else {
                        objectBtn.addClass('btn-success').removeClass('btn-danger');
                        objectBtn.attr('estado', ESTADO_VIGENTE);
                    }
                    DetenerSpiner(objectBtn);
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    $("#tablaListaUnidades tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this);
        let codigoUnidad = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la unidad seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn);
                $.ajax({
                    url: "index.php?r=Planificacion/unidad/eliminar-unidad",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','La unidad ha sido borrada correctamente.')
                            DetenerSpiner(objectBtn);
                            $("#tablaListaUnidades").DataTable().ajax.reload();
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                            DetenerSpiner(objectBtn);
                        }
                    }
                });
            }
        });
    });

    /*=============================================
    BUSCA LA UNIDAD SELECCIONADA EN LA BD
    =============================================*/
    $("#tablaListaUnidades tbody").on("click", ".btnEditar", function () {
        let codigoUnidad = $(this).attr("codigo");
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/buscar-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let unidad = JSON.parse(JSON.stringify(data.unidad));
                    $("#codigoUnidad").val(unidad.CodigoUnidad);
                    $("#da").val(unidad.Da);
                    $("#ue").val(unidad.Ue);
                    $("#descripcion").val(unidad.Descripcion);
                    $("#organizacional").prop( "checked", (unidad.Organizacional === 1))
                    $("#fechaInicio").val(unidad.FechaInicio);
                    $("#fechaFin").val(unidad.FechaFin);
                    DetenerSpiner(objectBtn)
                    $("#btnMostrarCrear").trigger('click');
                } else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                    DetenerSpiner(objectBtn)
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                DetenerSpiner(objectBtn)
            }
        });
    });

    /*=============================================
    ACTUALIZA LA UNIDAD SELECCIONADA EN LA BD
    =============================================*/
    function actualizarUnidad() {
        let codigoUnidad = $("#codigoUnidad").val();
        let da = $("#da").val();
        let ue = $("#ue").val();
        let descripcion = $("#descripcion").val();
        let organizacional = $("#organizacional").is(':checked')?1:0;
        let fechaInicio = $("#fechaInicio").val();
        let FechaFin = $("#fechaFin").val();
        let datos = new FormData();
        datos.append("codigoUnidad", codigoUnidad);
        datos.append("da", da);
        datos.append("ue", ue);
        datos.append("descripcion", descripcion);
        datos.append("organizacional", organizacional);
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", FechaFin);
        $.ajax({
            url: "index.php?r=Planificacion/unidad/actualizar-unidad",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','La unidad se actualizó correctamente.')
                    $("#tablaListaUnidades").DataTable().ajax.reload(async () =>{
                        $("#btnCancelar").click();
                    });
                }
                else {
                    MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
            }
        });
    }
});