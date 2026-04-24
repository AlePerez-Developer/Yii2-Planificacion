let dt_pei
$(document).ready(function () {
    dt_pei = $("#tablaListaPeis").DataTable({
         initComplete: function () {
            $("div.dt-search").append(`
            <button type="button"
                    id="refresh"
                    class="btn btn-outline-primary btn-sm ms-2"
                    title="Recargar tabla">
                <i class="fas fa-sync fa-spin"></i>
            </button>
        `);

            // Tooltip Bootstrap 5
            $('[title]').tooltip();
        },

        ajax:{
            method:"POST",
            dataType:"json",
            url:"index.php?r=Planificacion/peis/listar-todo",
            dataSrc:"data"
        },

        columns:[

            {
                title: '#',
                data:"CodigoUsuario",
                className:"text-center",
                width:"60px",
                render:function(data){
                    return `<span class="badge-codigo">${data}</span>`;
                }
            },

            {
                title: '<center>Descripcion</center>',
                data:null,
                render:function(data,type,row){

                    if(type!=="display"){
                        return row.Descripcion;
                    }

                    return `
                    <div class="pei-main">
                        ${row.Descripcion}
                    </div>

                    <div class="pei-sub">
                        Gestión ${row.GestionInicio} - ${row.GestionFin}
                    </div>

                    <div class="pei-sub">
                        Aprobación: ${row.FechaAprobacion}
                    </div>
                `;
                }
            },

            {
                title: 'Estado',
                data:"CodigoEstado",
                className:"text-center",
                width:"160px",
                render:function(data,type,row){

                    if(type!=="display"){
                        return data;
                    }

                    return row.CodigoEstado == ESTADO_VIGENTE
                        ? `<span class="badge-vigente btnEstado">Vigente</span>`
                        : `<span class="badge-caducado btnEstado">Caducado</span>`;
                }
            },

            {
                title: 'Acciones',
                data:"IdPei",
                className:"text-center",
                width:"130px",
                orderable:false,
                render:function(data){

                    return `
                    <button class="btn-action btn-edit btnEditar">
                        <i class="fa fa-pen"></i>
                    </button>

                    <button class="btn-action btn-delete btnEliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
                }
            }
        ]
    });

    dt_pei.on('order.dt search.dt', function () {
        let i = 1;
        dt_pei.cells(null, 0, {search: 'applied', order: 'applied'}).every(function () {
            this.data(i++);
        });
    }).draw();
})
