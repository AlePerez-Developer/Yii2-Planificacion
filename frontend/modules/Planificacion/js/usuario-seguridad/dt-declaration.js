let dt_usuario = null;

$(document).ready(function () {
    dt_usuario = $('#tablaListaUsuarios').DataTable({
        initComplete: function () {
            $('#dticTableLoading').hide();
            $('#dticTableContainer').fadeIn(250);
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            url: 'index.php?r=Planificacion/usuario-seguridad/listar-todo',
            dataSrc: 'data',
            error: function (xhr) {
                const data = xhr.responseJSON || {};
                MostrarMensaje('error', GenerarMensajeError(data.message || 'No se pudo listar usuarios.'), data.errors);
            }
        },
        columns: [
            {data: 'CodigoUsuario', className: 'text-center', width: '90px'},
            {data: 'NombrePersona', defaultContent: ''},
            {data: 'Nick', defaultContent: ''},
            columnaEstadoCatalogo(),
            {
                data: 'IdUsuario',
                className: 'text-center',
                orderable: false,
                render: () => `<button class="btn-action btn-edit" title="Editar"><i class="fa fa-pen"></i></button>`
            }
        ]
    });
});
