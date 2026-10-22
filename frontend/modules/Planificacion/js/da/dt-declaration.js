let dt_da
$(document).ready(function () {
    dt_da = $("#tablaListaDas").DataTable({
        layout: {
            topStart: null ,
            topEnd:null ,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        initComplete: function () {
            $("#dticTableLoading").hide();
            $("#dticTableContainer").fadeIn(250);
        },

        ajax: {
            method: "POST",
            dataType: "json",
            url: "index.php?r=Planificacion/da/listar-todo",
            dataSrc: "data"
        },

        columns: [

            {
                data: null,
                render: function (data, type, row) {

                    if (type !== "display") {
                        return row["Descripcion"];
                    }

                    return `
                        <div class="card-row">
                            <span class="row-num">1</span>
                            <div class="patient">
                                <div class="avatar" style="background:#0ea5e9">MF</div>
                                <div>
                                    <p class="patient-name">María Fernández López <span class="code-badge">C-001</span></p>
                                    <p class="patient-meta">34 años • 🌐 Boliviana</p>
                                    <p class="patient-motivo">📄 Control general</p>
                                </div>
                            </div>
                            <div class="datetime">jue, 17 oct 2026<small>🕐 09:00</small></div>
                            <div><span class="estado confirmada"><span class="dot"></span>Confirmada ▾</span></div>
                            <div class="actions">
                                <button class="btn-action edit">✏ Editar</button>
                                <button class="btn-action delete">🗑 Eliminar</button>
                            </div>
                        </div>
                `;
                }
            },

        ]
    });


    $(document).on("click", "#refreshTable", function () {
        dt_da.ajax.reload();
    });

    dt_da.on('order.dt search.dt', function () {
        let i = 1;
        dt_da.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
