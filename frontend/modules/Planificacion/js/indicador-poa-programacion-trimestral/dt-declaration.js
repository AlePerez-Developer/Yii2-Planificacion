let dt_programacionPoaTrimestral = null;

function cargarProgramacionPoaTrimestral() {
    $('#mensajeInicial').hide();
    $('#dticTableLoading').show();
    $('#dticTableContainer').hide();

    if (dt_programacionPoaTrimestral !== null) {
        dt_programacionPoaTrimestral.ajax.reload(mostrarTablaTrimestral);
        return;
    }

    dt_programacionPoaTrimestral = $('#tablaProgramacionPoaTrimestral').DataTable({
        ajax: {
            url: 'index.php?r=Planificacion/indicador-poa-programacion-trimestral/listar-programacion',
            method: 'POST',
            dataType: 'json',
            data: () => ({
                idObjEspecifico: programacionPoaTrimestral_s2ObjEspecifico.val()
            }),
            dataSrc: 'data',
            error: function (xhr) {
                mostrarErrorTrimestral(xhr);
                mostrarTablaTrimestral();
            }
        },
        paging: false,
        scrollX: true,
        responsive: false,
        autoWidth: false,
        order: [[1, 'asc'], [3, 'asc']],
        columns: [
            {title: 'Gestión', data: 'Gestion', className: 'text-center', width: '70px'},
            {title: 'Llave', data: 'Llave', className: 'text-center', width: '120px'},
            {title: 'Descripción de llave', data: 'LlaveDescripcion', className: 'descripcion-cell', width: '180px'},
            {title: 'Código', data: 'IndicadorCodigo', className: 'text-center', width: '75px'},
            {title: 'Indicador POA', data: 'IndicadorDescripcion', className: 'descripcion-cell', width: '220px'},
            {title: 'Meta anual', data: 'MetaProgramada', className: 'text-center meta-programada', width: '90px'},
            trimestre(1, 'MetaPrimerTrimestre', 'T1'),
            trimestre(2, 'MetaSegundoTrimestre', 'T2'),
            trimestre(3, 'MetaTercerTrimestre', 'T3'),
            trimestre(4, 'MetaCuartoTrimestre', 'T4'),
            {
                title: 'Total',
                data: 'TotalTrimestral',
                className: 'text-center',
                width: '85px',
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    const estado = row.ProgramacionCompleta == 1 ? 'completa' : 'pendiente';
                    return `<span class="total-badge ${estado}">${Number(data || 0)}</span>`;
                }
            }
        ],
        createdRow: function (row, data) {
            $(row)
                .removeClass('programacion-completa programacion-pendiente')
                .addClass(
                    data.ProgramacionCompleta == 1
                        ? 'programacion-completa'
                        : 'programacion-pendiente'
                );
        },
        drawCallback: function () {
            agregarGruposTrimestrales(this.api());
        },
        initComplete: mostrarTablaTrimestral
    });
}

function trimestre(numero, atributo, titulo) {
    return {
        title: titulo,
        data: atributo,
        className: 'text-center trimestre-cell',
        width: '85px',
        render: function (data, type, row) {
            if (type !== 'display') return data;
            return `<input type="number" min="0" step="1" readonly
                class="form-control form-control-sm input-meta-poa-trimestre"
                value="${Number(data || 0)}"
                data-original="${Number(data || 0)}"
                data-trimestre="${numero}"
                data-idprogramacion="${row.IdProgramacionIndicadorPoaGestion}">`;
        }
    };
}

function agregarGruposTrimestrales(api) {
    $(api.table().body()).find('tr.programacion-group-row').remove();

    const filas = api.rows({search: 'applied'});
    const datos = filas.data().toArray();
    const nodos = filas.nodes().toArray();
    const grupos = {};
    const total = crearTotales();

    datos.forEach(item => {
        if (!grupos[item.IdLlavePresupuestaria]) {
            grupos[item.IdLlavePresupuestaria] = crearTotales();
        }
        sumarFila(grupos[item.IdLlavePresupuestaria], item);
        sumarFila(total, item);
    });

    let ultimaLlave = null;
    datos.forEach((item, indice) => {
        if (item.IdLlavePresupuestaria === ultimaLlave) return;
        ultimaLlave = item.IdLlavePresupuestaria;
        const subtotal = grupos[item.IdLlavePresupuestaria];
        $(nodos[indice]).before(`
            <tr class="programacion-group-row">
                <td colspan="11">
                    <div class="llave-group-info">
                        <span><strong>${item.Llave}</strong> — ${item.LlaveDescripcion}</span>
                        <span class="llave-group-totales">
                            Anual: <b>${subtotal.anual}</b>
                            <span>T1: <b>${subtotal.t1}</b></span>
                            <span>T2: <b>${subtotal.t2}</b></span>
                            <span>T3: <b>${subtotal.t3}</b></span>
                            <span>T4: <b>${subtotal.t4}</b></span>
                            <span>Total programado: <b>${subtotal.trimestral}</b></span>
                        </span>
                    </div>
                </td>
            </tr>
        `);
    });

    Object.keys(total).forEach(clave => {
        $(`#resumenTrimestral [data-total="${clave}"]`).text(total[clave]);
    });
}

function crearTotales() {
    return {anual: 0, t1: 0, t2: 0, t3: 0, t4: 0, trimestral: 0};
}

function sumarFila(total, item) {
    total.anual += Number(item.MetaProgramada || 0);
    total.t1 += Number(item.MetaPrimerTrimestre || 0);
    total.t2 += Number(item.MetaSegundoTrimestre || 0);
    total.t3 += Number(item.MetaTercerTrimestre || 0);
    total.t4 += Number(item.MetaCuartoTrimestre || 0);
    total.trimestral += Number(item.TotalTrimestral || 0);
}

function mostrarTablaTrimestral() {
    $('#dticTableLoading').hide();
    $('#dticTableContainer').fadeIn(200);
}

function mostrarErrorTrimestral(xhr) {
    const data = xhr.responseJSON || {};
    MostrarMensaje(
        'error',
        GenerarMensajeError(data.message || 'No se pudo cargar la programación trimestral.'),
        data.errors
    );
}
