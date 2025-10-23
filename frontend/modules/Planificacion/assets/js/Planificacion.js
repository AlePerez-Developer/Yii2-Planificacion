const RTA_CORRECTO = "ok"
const ESTADO_VIGENTE = 'V'
const ESTADO_CADUCO = 'C'
const ESTADO_ELIMINADO = 'E'

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

function GenerarMensajeError(Mensaje){
    if (Mensaje === "errorValidacion") {
        return "Error: Ocurrio un error al validar los datos enviados";
    } else if (Mensaje === "errorEnvio") {
        return "Error: No se enviaron los datos de forma correcta.";
    } else if (Mensaje === "errorCabecera") {
        return "Error: Ocurrio un error en el llamado del procedimiento";
    } else if (Mensaje === "errorNoEncontrado") {
        return "Error: No se encontro el registro en la base de datos.";
    } else if (Mensaje === "errorSql") {
        return "Error: Ocurrio un error en la sentencia SQL";
    } else if (Mensaje === "errorExiste") {
        return "Error: El valor ingresado ya existe en la base de datos";
    } else if (Mensaje === "errorDB") {
        return "Error: Se presento un error en la base de datos del proyecto y no se puede continuar";
    } else if (Mensaje === "errorGeneral") {
        return "Error: Se presento un error inesperado en el proceso y no se puede continuar";
    } else if (Mensaje === "errorMeta") {
        return "Error: la meta nueva excede la cantidad total";
    } else if (Mensaje === "errorEnUso") {
        return "Error: el registro esta en uso en otro formulario y no puede ser eliminado";
    } else if (Mensaje === "errorGestionInicio") {
        return "Error: No se puede modificar el valor dela Gestion de inicio, Afecta la programacion de indicadores estrategicos";
    } else if (Mensaje === "errorGestionFin") {
        return "Error: No se puede modificar el valor dela Gestion final, Afecta la programacion de indicadores estrategicos";
    } else {
        return  Mensaje;
    }
}

function IniciarSpiner(Btn){
    Btn.append('<span class="loader"> <span class="loader-spinner"></span> </span>')
    Btn.find('i').css("display", "none")
    Btn.find('.btn_text').css("display", "none")
    Btn.prop( "disabled", true );
}

function DetenerSpiner(Btn){
    Btn.find('.loader').remove()
    Btn.find('i').removeAttr("style")
    Btn.find('.btn_text').removeAttr("style")
    Btn.prop( "disabled", false );
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

function cambiarEstadoBtn(objectBtn, data){
    if (data === ESTADO_VIGENTE) {
        objectBtn.addClass('btn-outline-success').removeClass('btn-outline-danger');
        objectBtn.find('.btn_text').html('Vigente')
    } else {
        objectBtn.removeClass('btn-outline-success').addClass('btn-outline-danger')
        objectBtn.find('.btn_text').html('Caducado')
    }
}

function renderItem(key, value, ul) {
    if (typeof value === "object" && value !== null) {
        const li = $("<li class='error0'>").text('Campo ' + key + ":");
        const sublist = $("<ul class='error'>");
        $.each(value, function(subKey, subValue) {
            renderItem(subKey, subValue, sublist);
        });
        li.append(sublist);
        ul.append(li);
    } else {
        ul.append($("<li class='error1'>").text(/*key + ": " +*/ value));
    }
}
function MostrarMensaje(icono, mensaje, errores){
    let titulo
    switch(icono) {
        case 'success':
            titulo = 'Todo correcto.......'
            break;
        case 'error':
            titulo = 'Ocurrio un error....'
            break;
        case 'warning':
            titulo = 'Advertencia.........'
            break;
        case 'info':
            titulo = 'Atencion.....'
            break;
        case 'question':
            titulo = '??????...........'
            break;
    }

    const contenedor = $("<div>");
    if (errores){
        contenedor.append("<label class='errorl'>Errores detectados:</label>" +
            "<ul></ul>"
        );
        const lista = contenedor.find("ul");
        $.each(errores, function(key, value) {
            renderItem(key, value, lista);
        });
    }

    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        footer:contenedor.html(),
        showCancelButton: false,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Cerrar"
    })
}

let glbToast = toastr
glbToast.options = {
    "title": "carajo",
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "500",
    "timeOut": "4000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"}

function populateS2Areas(select2) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        delay: 100,
        cache: true,
        url: 'index.php?r=Planificacion/obj-estrategico/listar-areas-estrategicas',
        success: function(data){
            select2.empty();

            $.each(data["data"], function(index, item) {
                select2.append(
                    $('<option>', {
                        value: item["IdAreaEstrategica"],
                        text: '(' + item['Codigo'] + ') - ' + item["Descripcion"]
                    })
                );
            });

            select2.val(null).trigger('change');
        },
        error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        },
    });
}
$(document).ready(function(){
});