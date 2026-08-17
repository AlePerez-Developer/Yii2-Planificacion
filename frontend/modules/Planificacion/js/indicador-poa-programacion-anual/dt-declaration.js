let dt_programacionPoaAnual = null;

function cargarProgramacionPoaAnual() {
    $('#mensajeInicial').hide();
    $('#dticTableLoading').show();
    $('#dticTableContainer').hide();

    if (dt_programacionPoaAnual !== null) {
        dt_programacionPoaAnual.ajax.reload(mostrarTabla);
        return;
    }

    dt_programacionPoaAnual = $('#tablaProgramacionPoaAnual').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-poa-programacion-anual/listar-relaciones',
            method: 'POST',
            dataType: 'json',
            data: () => ({
                idObjEspecifico: programacionPoaAnual_s2ObjEspecifico.val()
            }),
            dataSrc: 'data',
            error: function (xhr) {
                mostrarErrorProgramacionPoa(xhr);
                mostrarTabla();
            }
        },
        paging: false,
        responsive: false,
        autoWidth: false,
        order: [[1, 'asc'], [3, 'asc']],
        columns: [
            {title: 'Gestión', data: 'Gestion', className: 'text-center', width: '80px'},
            {title: 'Llave', data: 'Llave', className: 'text-center', width: '130px'},
            {title: 'Descripción de llave', data: 'LlaveDescripcion'},
            {title: 'Código indicador', data: 'IndicadorCodigo', className: 'text-center', width: '110px'},
            {title: 'Indicador POA', data: 'IndicadorDescripcion'},
            {
                title: 'Meta programada',
                data: 'MetaProgramada',
                className: 'text-center',
                width: '130px',
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    return `<input type="number" min="0" step="1" readonly
                        class="form-control form-control-sm input-meta-poa-anual"
                        value="${Number(data || 0)}"
                        data-original="${Number(data || 0)}"
                        data-idprogramacion="${row.IdProgramacionIndicadorPoaGestion}">`;
                }
            },
            {
                title: 'Acciones',
                data: 'IdProgramacionIndicadorPoaGestion',
                className: 'text-center',
                width: '90px',
                orderable: false,
                searchable: false,
                render: () => `
                    <button class="btn-action btn-delete-programacion" title="Eliminar programación">
                        <i class="fa fa-trash"></i>
                    </button>
                `
            }
        ],
        drawCallback: function () {
            agregarGruposYSubtotales(this.api());
        },
        initComplete: mostrarTabla
    });
}

function agregarGruposYSubtotales(api) {
    $(api.table().body()).find('tr.programacion-group-row').remove();

    const filas = api.rows({search: 'applied'});
    const datos = filas.data().toArray();
    const nodos = filas.nodes().toArray();
    const subtotales = {};
    let totalGeneral = 0;

    datos.forEach(item => {
        const id = item.IdLlavePresupuestaria;
        subtotales[id] = (subtotales[id] || 0) + Number(item.MetaProgramada || 0);
        totalGeneral += Number(item.MetaProgramada || 0);
    });

    let ultimaLlave = null;
    datos.forEach((item, indice) => {
        if (item.IdLlavePresupuestaria === ultimaLlave) return;
        ultimaLlave = item.IdLlavePresupuestaria;
        $(nodos[indice]).before(`
            <tr class="programacion-group-row">
                <td colspan="7">
                    <strong>${item.Llave}</strong> — ${item.LlaveDescripcion}
                    <span class="float-right">
                        Subtotal: <b>${subtotales[item.IdLlavePresupuestaria]}</b>
                    </span>
                </td>
            </tr>
        `);
    });

    $('#resumenAnual').text(`Total programado: ${totalGeneral}`);
}

function mostrarTabla() {
    $('#dticTableLoading').hide();
    $('#dticTableContainer').fadeIn(200);
}

function mostrarErrorProgramacionPoa(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje(
        'error',
        GenerarMensajeError(data.message || 'No se pudo cargar la programación.'),
        data.errors
    );
}
