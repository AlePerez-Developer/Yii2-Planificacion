$(document).ready(function () {
    const EMPTY = '00000000-0000-0000-0000-000000000000';
    const baseUrl = 'index.php?r=Planificacion/usuario-seguridad/';
    let idUsuario = EMPTY;
    let catalogos = {modulos: [], gestiones: [], estadosPoa: [], unidades: []};

    cargarCatalogos();

    $('#btnCancelar').on('click', function () {
        $('#btnMostrarCrear').removeClass('opened').addClass('closed');
        limpiar();
        $('#divDatos').hide(500);
        $('#divTabla').show(500);
    });

    $('#btnMostrarCrear').on('click', function () {
        if ($(this).hasClass('opened')) {
            limpiar();
        }
    });

    $('#btnGuardar').on('click', async function () {
        if (!$('#formUsuario').valid()) return;
        const datos = new FormData();
        datos.append('idUsuario', idUsuario);
        datos.append('idPersona', $('#idPersona').val());
        datos.append('codigoUsuario', $('#codigoUsuario').val());
        datos.append('nick', $('#nick').val());
        datos.append('tokenPortal', $('#tokenPortal').val());
        const response = await ajaxPromise({
            url: baseUrl + (idUsuario === EMPTY ? 'guardar' : 'actualizar'),
            data: datos,
            spinnerBtn: $(this),
            cancelBtn: $('#btnCancelar'),
            successMsg: 'Usuario guardado.'
        });
        idUsuario = response.data.IdUsuario || idUsuario;
        await guardarModulos();
        await recargarAsignaciones();
        dt_usuario.ajax.reload();
    });

    $('#tablaListaUsuarios').on('click', '.btn-edit', async function () {
        const row = dt_usuario.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'buscar',
            data: crearDatos('idUsuario', row.IdUsuario)
        });
        const data = response.data;
        idUsuario = data.IdUsuario;
        $('#idPersona').val(data.IdPersona);
        $('#codigoUsuario').val(data.CodigoUsuario);
        $('#nick').val(data.Nick || '');
        $('#tokenPortal').val(data.TokenPortal || '');
        pintarModulos(data.modulos || []);
        await recargarAsignaciones();
        $('#btnMostrarCrear').trigger('click');
    });

    $('#tablaListaUsuarios').on('click', '.btn-toggle-estado', async function () {
        const row = dt_usuario.row($(this).closest('tr')).data();
        const response = await ajaxPromise({
            url: baseUrl + 'cambiar-estado',
            data: crearDatos('idUsuario', row.IdUsuario),
            successMsg: 'Estado actualizado.'
        });
        cambiarEstadoBtnDtic($(this), response.data);
    });

    $('#btnAgregarAsignacion').on('click', async function () {
        if (idUsuario === EMPTY) {
            MostrarMensaje('warning', 'Guarde primero el usuario.');
            return;
        }
        const datos = new FormData();
        datos.append('idUsuario', idUsuario);
        datos.append('idUnidadEjecutora', $('#idUnidadEjecutora').val());
        datos.append('idGestion', $('#idGestion').val());
        datos.append('idEstadoPoa', $('#idEstadoPoa').val());
        await ajaxPromise({
            url: baseUrl + 'guardar-asignacion',
            data: datos,
            successMsg: 'Asignación agregada.'
        });
        await recargarAsignaciones();
    });

    $('#tablaAsignaciones').on('click', '.btn-del-asig', async function () {
        await ajaxPromise({
            url: baseUrl + 'eliminar-asignacion',
            data: crearDatos('idAsignacion', $(this).data('id')),
            successMsg: 'Asignación eliminada.'
        });
        await recargarAsignaciones();
    });

    $('#formUsuario').validate({
        rules: {
            idPersona: {required: true},
            codigoUsuario: {required: true}
        },
        messages: {
            idPersona: 'Ingrese el Id Persona.',
            codigoUsuario: 'Ingrese el código de usuario.'
        }
    });

    async function cargarCatalogos() {
        const response = await ajaxPromise({url: baseUrl + 'listar-catalogos', data: new FormData()});
        catalogos = response.data || catalogos;
        llenarSelect('#idGestion', catalogos.gestiones, 'IdGestion', 'Gestion', 'Gestión');
        llenarSelect('#idEstadoPoa', catalogos.estadosPoa, 'IdEstadoPoa', 'Codigo', 'Estado POA');
        llenarSelect('#idUnidadEjecutora', catalogos.unidades, 'IdUnidadEjecutora', 'Compuesto', 'Unidad');
        pintarModulos([]);
    }

    function pintarModulos(seleccionados) {
        const set = new Set(seleccionados);
        const html = (catalogos.modulos || []).map(m => `
            <label class="mr-3">
                <input type="checkbox" class="chk-modulo" value="${m.IdModulo}" ${set.has(m.IdModulo) ? 'checked' : ''}>
                ${m.Nombre}
            </label>`).join('');
        $('#listaModulos').html(html);
    }

    async function guardarModulos() {
        if (idUsuario === EMPTY) return;
        const datos = new FormData();
        datos.append('idUsuario', idUsuario);
        $('.chk-modulo:checked').each(function () {
            datos.append('modulos[]', $(this).val());
        });
        await ajaxPromise({url: baseUrl + 'guardar-modulos', data: datos});
    }

    async function recargarAsignaciones() {
        if (idUsuario === EMPTY) {
            $('#tablaAsignaciones').empty();
            return;
        }
        const response = await ajaxPromise({
            url: baseUrl + 'listar-asignaciones',
            data: crearDatos('idUsuario', idUsuario)
        });
        const filas = (response.data || []).map(a => `
            <tr>
                <td>${a.Unidad || ''}</td>
                <td>${a.Gestion || ''}</td>
                <td>${a.EstadoPoa || ''}</td>
                <td><button type="button" class="btn-action btn-del-asig" data-id="${a.IdUsuarioUnidadGestionEstado}"><i class="fa fa-trash"></i></button></td>
            </tr>`).join('');
        $('#tablaAsignaciones').html(filas);
    }

    function llenarSelect(selector, items, id, texto, placeholder) {
        const opciones = [`<option value="">${placeholder}</option>`]
            .concat((items || []).map(i => `<option value="${i[id]}">${i[texto] || i[id]}</option>`));
        $(selector).html(opciones.join(''));
    }

    function limpiar() {
        idUsuario = EMPTY;
        $('#formUsuario')[0].reset();
        pintarModulos([]);
        $('#tablaAsignaciones').empty();
    }
});
