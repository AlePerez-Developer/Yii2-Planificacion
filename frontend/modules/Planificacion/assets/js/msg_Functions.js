let glbToast = toastr

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

glbToast.options = {
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

$(document).ready(function () {})