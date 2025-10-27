function ajaxPromise({
                         url,
                         method = 'POST',
                         data,
                         spinnerBtn = null,
                         cancelBtn = null,
                         successMsg = '',
                         reloadTable = null,
                         onSuccess = null
                     }) {
    if (spinnerBtn) IniciarSpiner(spinnerBtn);
    if (cancelBtn) cancelBtn.prop('disabled', true);

    return new Promise((resolve, reject) => {
        $.ajax({
            url,
            method,
            data,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',

            success: function (response) {
                if (successMsg) glbToast.success(successMsg);
                if (reloadTable) reloadTable.ajax.reload(() => {
                    if (cancelBtn) cancelBtn.click();
                });
                if (onSuccess) onSuccess(response);
                resolve(response);
            },

            error: function (xhr) {
                let mensaje = 'Error inesperado';
                let errores = null;

                try {
                    const data = JSON.parse(xhr.responseText);
                    mensaje = data.message || mensaje;
                    errores = data.errors || null;
                } catch (e) {
                    mensaje = xhr.responseText || mensaje;
                }

                MostrarMensaje('error', GenerarMensajeError(mensaje), errores);
                reject({ mensaje, errores });
            },

            complete: function () {
                if (spinnerBtn) DetenerSpiner(spinnerBtn);
                if (cancelBtn) cancelBtn.prop('disabled', false);
            }
        });
    });
}

$('document').ready(function () {})