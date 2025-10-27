$(document).ready(function () {
    let idPoliticaEstrategica = '00000000-0000-0000-0000-000000000000';
    let baseUrl = "index.php?r=Planificacion/politica-estrategica/"

    function reiniciarCampos() {
        $('#formPoliticaEstrategica *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPoliticaEstrategica').trigger("reset");
        politicas_s2AreasEstrategicas.val(null).trigger('change')
        idPoliticaEstrategica = '00000000-0000-0000-0000-000000000000';
    }

    function mensajeAccion(accion) {
        return `Los datos de la Política Estratégica se ${accion}ron correctamente.`;
    }

    $("#btnCancelar").click(function () {
        $('.icon').toggleClass('opened');
        reiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500);
    });

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formPoliticaEstrategica").valid()) return;

        const idAreaEstrategica = $('#areasEstrategicas').select2('data')[0].id;
        const datos = new FormData();
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);
        datos.append("idAreaEstrategica", idAreaEstrategica);
        datos.append("codigo", $("#codigo").val());
        datos.append("descripcion", $("#descripcion").val());

        const hasCode =  idPoliticaEstrategica !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_politica
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_politica.ajax.reload();
    })

    /* =============================================
          CAMBIA EL ESTADO DEL REGISTRO
    ===============================================*/
    $(document).on('click', 'tbody #btnEstado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        let idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];

        const datos = new FormData();
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstadoBtn(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    $(document).on('click', 'tbody #btnEliminar', function(){
        let objectBtn = $(this)
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        let idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];

        const datos = new FormData();
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar la politica estrategica seleccionada?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar'
        }).then(async function (resultado) {
            if (resultado.value) {
                try {
                    await ajaxPromise({
                        url: baseUrl + "eliminar",
                        data: datos,
                        spinnerBtn: objectBtn,
                        successMsg: mensajeAccion('eliminar'),
                        reloadTable: dt_politica
                    });
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        });
    });

    /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    $(document).on('click', 'tbody #btnEditar', async function(){
        let objectBtn = $(this)
        const dt_row = dt_politica.row(objectBtn.closest('tr')).data()
        idPoliticaEstrategica = dt_row["IdPoliticaEstrategica"];

        const datos = new FormData();
        datos.append("idPoliticaEstrategica", idPoliticaEstrategica);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let politica = data.data
                politicas_s2AreasEstrategicas.val(politica['IdAreaEstrategica']).trigger('change');
                $("#codigo").val(politica["Codigo"]);
                $("#descripcion").val(politica["Descripcion"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
    * Validacion del form
    */
    $( "#formPoliticaEstrategica" ).validate( {
        rules: {
            areasEstrategicas: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                range: [1, 9],
                require_from_group: [2, ".codigo_group"],
                remote: {
                    url: "index.php?r=Planificacion/politica-estrategica/verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        codigo: function() {
                            return $('#codigo').val(); // valor actual del campo
                        },
                        idAreaEstrategica: function (){
                            let area = $('#areasEstrategicas').select2('data')
                            return area[0].id
                        },
                        idPoliticaEstrategica: function (){
                            return idPoliticaEstrategica
                        }
                    }
                }
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
        },
        messages: {
            areasEstrategicas: {
                required: "Debe seleccionar una area estrategica",
            },
            codigo: {
                required: "Debe ingresar un codigo de politica estrategica jeje",
                digits: "El codigo solo debe ser numerico",
                range: "El codigo debe estar comprendido entre 1 y 9",
                require_from_group: "Debe seleccionar un area estrategica antes de validar el codigo de politica",
                remote: "El codigo ingresado ya se encuentra en uso o no esta area"
            },
            descripcion: {
                required: "Debe ingresar una descripcion del area estrategica",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 500 letras"
            }
        },
        errorElement: "div",

        errorPlacement: function ( error, element ) {
            error.addClass( "invalid-feedback" );
            error.insertAfter(element);
        },
        highlight: function ( element  ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    });
});