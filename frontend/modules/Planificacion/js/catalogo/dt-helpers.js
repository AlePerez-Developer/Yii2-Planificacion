function columnaEstadoCatalogo() {
    return {
        data: 'CodigoEstado',
        className: 'text-center',
        width: '90px',
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
            if (type !== 'display') return data;
            return row.CodigoEstado === ESTADO_VIGENTE
                ? `<button type="button" class="estado-on btn-toggle-estado" title="Cambiar estado">
                    <span class="btn_ico"><i class="fas fa-check-circle"></i></span>
                    <span class="btn_text">Vigente</span>
                   </button>`
                : `<button type="button" class="estado-off btn-toggle-estado" title="Cambiar estado">
                    <span class="btn_ico"><i class="fas fa-times-circle"></i></span>
                    <span class="btn_text">Caducado</span>
                   </button>`;
        }
    };
}

function columnaAccionesCatalogo(campoId) {
    return {
        data: campoId,
        className: 'text-center',
        width: '140px',
        orderable: false,
        searchable: false,
        render: function () {
            return `
                <button class="btn-action btn-edit" title="Editar"><i class="fa fa-pen"></i></button>
                <button class="btn-action btn-delete" title="Eliminar"><i class="fa fa-trash"></i></button>
            `;
        }
    };
}

function crearDatos(nombre, valor) {
    const datos = new FormData();
    datos.append(nombre, valor);
    return datos;
}

function htmlAccionesPoa() {
    let html = '';
    if (window.poaPuedeEditar !== false) {
        html += '<button class="btn-action btn-edit" title="Editar"><i class="fa fa-pen"></i></button>';
    }
    if (window.poaPuedeEliminar !== false) {
        html += '<button class="btn-action btn-delete" title="Eliminar"><i class="fa fa-trash"></i></button>';
    }
    return html;
}
