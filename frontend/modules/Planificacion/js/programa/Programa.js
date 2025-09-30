$(document).ready(function () {
    let table = $("#tablaListaProgramas").DataTable({
        ajax: {
            method: "POST",
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            url: 'index.php?r=Planificacion/programa/listar-programas',
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
                className: 'dt-small dt-center',
                data: 'Codigo',
                width: 50
            },
            {
                className: 'dt-small',
                data: 'Descripcion'
            },
            {
                className: 'dt-small dt-estado dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoEstado',
                render: function (data, type, row) {
                    return ( (type === 'display') && (row.CodigoEstado === ESTADO_VIGENTE))
                        ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado" codigo="' + row.CodigoPrograma + '" estado =  "' + ESTADO_VIGENTE + '" >Vigente</button>'
                        : '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" codigo="' + row.CodigoPrograma + '" estado =  "' + ESTADO_CADUCO + '" >Caducado</button>' ;
                },
            },
            {
                className: 'dt-small dt-acciones dt-center',
                orderable: false,
                searchable: false,
                data: 'CodigoPrograma',
                render: function (data, type) {
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

    function reiniciarCampos() {
        $('#formPrograma *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $("#codigoPrograma").val('');
        $("#formPrograma").trigger("reset");
    }

    /*$("#btnMostrarCrear").click(function () {
        let icono = $('.icon');
        icono.toggleClass('opened');
        if (icono.hasClass("opened")) {
            $("#divDatos").show(500);
            $("#divTabla").hide(500);
        } else {
            $("#divDatos").hide(500);
            $("#divTabla").show(500);
        }
    });*/

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(function () {
        if ($("#formPrograma").valid()) {
            if ($("#codigoPrograma").val() === '') {
                guardarPrograma();
            } else {
                actualizarPrograma()
            }
        }
    });

    /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO de PROGRAMA
    =============================================*/
    function guardarPrograma() {
        let codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        $.ajax({
            url: "index.php?r=Planificacion/programa/guardar-programa",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','Los datos del nuevo programa se guardaron correctamente.')
                    $("#tablaListaProgramas").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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

    /*=============================================
    CAMBIA EL ESTADO DEL REGISTRO
    =============================================*/
    $("#tablaListaProgramas tbody").on("click", ".btnEstado", function () {
        let objectBtn = $(this);
        let codigoPrograma = objectBtn.attr("codigo");
        let estadoPrograma = objectBtn.attr("estado");
        let datos = new FormData();
        datos.append("codigoPrograma", codigoPrograma);
        IniciarSpiner(objectBtn)
        $.ajax({
            url: "index.php?r=Planificacion/programa/cambiar-estado-programa",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    if (estadoPrograma === ESTADO_VIGENTE) {
                        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                        objectBtn.html('Caducado')
                        objectBtn.attr('estado', ESTADO_CADUCO);
                    } else {
                        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger')
                        objectBtn.html('Vigente')
                        objectBtn.attr('estado', ESTADO_VIGENTE)
                    }
                    DetenerSpiner(objectBtn)
                }
                else {
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
    ELIMINA DE LA BD UN REGISTRO de PROGRAMA
    =============================================*/
    $("#tablaListaProgramas tbody").on("click", ".btnEliminar", function () {
        let objectBtn = $(this)
        let codigoPrograma = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoPrograma", codigoPrograma);
        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el programa elegido?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.value) {
                IniciarSpiner(objectBtn)
                $.ajax({
                    url: "index.php?r=Planificacion/programa/eliminar-programa",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.respuesta === RTA_CORRECTO) {
                            MostrarMensaje('success','El programa ha sido borrado correctamente.')
                            $("#tablaListaProgramas").DataTable().ajax.reload();
                            DetenerSpiner(objectBtn)
                        }
                        else {
                            MostrarMensaje('error',GenerarMensajeError(data.respuesta))
                            DetenerSpiner(objectBtn)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        MostrarMensaje('error',GenerarMensajeError(thrownError + ' >' + xhr.responseText))
                        DetenerSpiner(objectBtn)
                    }
                });
            }
        });
    });

    /*=============================================
    BUSCA EL PROGRAMA SELECCIONADO EN LA BD
    =============================================*/
    $("#tablaListaProgramas tbody").on("click", ".btnEditar", function () {
        let objectBtn = $(this)
        let codigoPrograma = objectBtn.attr("codigo");
        let datos = new FormData();
        datos.append("codigoPrograma", codigoPrograma);
        $.ajax({
            url: "index.php?r=Planificacion/programa/buscar-programa",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    let programa = JSON.parse(JSON.stringify(data.programa));
                    $("#codigoPrograma").val(programa.CodigoPrograma);
                    $("#codigo").val(programa.Codigo);
                    $("#descripcion").val(programa.Descripcion);
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
    ACTUALIZA LA ACTIVIDAD SELECCIONADO EN LA BD
    =============================================*/
    function actualizarPrograma() {
        let codigoPrograma = $("#codigoPrograma").val();
        let codigo = $("#codigo").val();
        let descripcion = $("#descripcion").val();
        let datos = new FormData();
        datos.append("codigoPrograma", codigoPrograma);
        datos.append("codigo", codigo);
        datos.append("descripcion", descripcion);
        $.ajax({
            url: "index.php?r=Planificacion/programa/actualizar-programa",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (data) {
                if (data.respuesta === RTA_CORRECTO) {
                    MostrarMensaje('success','El programa se actualizó correctamente.')
                    $("#tablaListaProgramas").DataTable().ajax.reload(async () => {
                        $("#btnCancelar").click()
                    })
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