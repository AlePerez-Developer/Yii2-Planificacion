$(document).ready(function () {
    const baseUrl = 'index.php?r=Planificacion/permiso-menu/';
    const usuariosUrl = 'index.php?r=Planificacion/usuario-seguridad/listar-todo';

    cargarCombos();

    $('#btnCargar').on('click', cargarPermisos);
    $('#btnGuardarPermisos').on('click', guardarPermisos);
    $('#btnMarcarVer').on('click', function () {
        $('.chk-ver').prop('checked', true);
    });

    async function cargarCombos() {
        const catalogos = await ajaxPromise({
            url: baseUrl + 'listar-catalogos',
            data: new FormData()
        });
        const usuarios = await ajaxPromise({
            url: usuariosUrl,
            data: new FormData()
        });
        llenar('#idUsuario', usuarios.data || [], 'IdUsuario', item => `${item.CodigoUsuario} - ${item.NombrePersona || ''}`, 'Usuario');
        llenar('#idGestion', catalogos.data.gestiones || [], 'IdGestion', 'Gestion', 'Gestión');
        llenar('#idEstadoPoa', catalogos.data.estadosPoa || [], 'IdEstadoPoa', 'Codigo', 'Estado POA');
        llenar('#idUnidadEjecutora', catalogos.data.unidades || [], 'IdUnidadEjecutora', 'Compuesto', 'Unidad');
    }

    async function cargarPermisos() {
        const datos = contexto();
        const response = await ajaxPromise({url: baseUrl + 'listar', data: datos});
        const filas = (response.data || []).map(m => `
            <tr data-id="${m.IdMenu}">
                <td>${m.Nombre || ''}</td>
                <td><small>${m.Ruta || ''}</small></td>
                <td class="text-center"><input type="checkbox" class="chk-ver" ${Number(m.PuedeVer) === 1 ? 'checked' : ''}></td>
                <td class="text-center"><input type="checkbox" class="chk-crear" ${Number(m.PuedeCrear) === 1 ? 'checked' : ''}></td>
                <td class="text-center"><input type="checkbox" class="chk-editar" ${Number(m.PuedeEditar) === 1 ? 'checked' : ''}></td>
                <td class="text-center"><input type="checkbox" class="chk-eliminar" ${Number(m.PuedeEliminar) === 1 ? 'checked' : ''}></td>
            </tr>`).join('');
        $('#tablaPermisos').html(filas);
    }

    async function guardarPermisos() {
        const datos = contexto();
        $('#tablaPermisos tr').each(function (i) {
            const fila = $(this);
            datos.append(`items[${i}][idMenu]`, fila.data('id'));
            datos.append(`items[${i}][puedeVer]`, fila.find('.chk-ver').is(':checked') ? 1 : 0);
            datos.append(`items[${i}][puedeCrear]`, fila.find('.chk-crear').is(':checked') ? 1 : 0);
            datos.append(`items[${i}][puedeEditar]`, fila.find('.chk-editar').is(':checked') ? 1 : 0);
            datos.append(`items[${i}][puedeEliminar]`, fila.find('.chk-eliminar').is(':checked') ? 1 : 0);
        });
        await ajaxPromise({
            url: baseUrl + 'guardar',
            data: datos,
            successMsg: 'Permisos guardados.'
        });
    }

    function contexto() {
        const datos = new FormData();
        datos.append('idUsuario', $('#idUsuario').val());
        datos.append('idUnidadEjecutora', $('#idUnidadEjecutora').val());
        datos.append('idGestion', $('#idGestion').val());
        datos.append('idEstadoPoa', $('#idEstadoPoa').val());
        return datos;
    }

    function llenar(selector, items, id, texto, placeholder) {
        const label = typeof texto === 'function'
            ? texto
            : item => item[texto];
        const opciones = [`<option value="">${placeholder}</option>`]
            .concat((items || []).map(i => `<option value="${i[id]}">${label(i)}</option>`));
        $(selector).html(opciones.join(''));
    }
});
