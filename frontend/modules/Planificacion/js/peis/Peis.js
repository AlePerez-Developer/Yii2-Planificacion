$(document).ready(function () {
    let idPei = '00000000-0000-0000-0000-000000000000';
    let baseUrl = "index.php?r=Planificacion/peis/"

    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPei').trigger("reset");
        idPei = '00000000-0000-0000-0000-000000000000';
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

        if (!$("#formPei").valid()) return;

        const datos = new FormData();
        datos.append('idPei', idPei)
        datos.append("descripcion", $("#descripcion").val());
        datos.append("fechaAprobacion", $("#fechaAprobacion").val());
        datos.append("gestionInicio", $("#gestionInicio").val());
        datos.append("gestionFin", $("#gestionFin").val());

        const hasCode =  idPei !== '00000000-0000-0000-0000-000000000000';
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_pei
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $('#gestionInicio').on('change keyup', function () {
        $('#gestionFin').valid();
    });

    $('#gestionFin').on('change keyup', function () {
        $('#gestionInicio').valid();
    });

    $(document).on('click', '#refresh', function(){
        dt_pei.ajax.reload()
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    $(document).on('click', 'tbody #btnEstado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let idPei = dt_row["IdPei"];

        const datos = new FormData();
        datos.append("idPei", idPei);

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
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let idPei = dt_row["IdPei"];

        const datos = new FormData();
        datos.append("idPei", idPei);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el PEI seleccionado?",
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
                        reloadTable: dt_pei
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
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        idPei = dt_row["IdPei"];

        const datos = new FormData();
        datos.append("idPei", idPei);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let pei = data.data
                $("#descripcion").val(pei["Descripcion"]);
                $("#fechaAprobacion").val(pei["FechaAprobacion"]);
                $("#gestionInicio").val(pei["GestionInicio"]);
                $("#gestionFin").val(pei["GestionFin"]);
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /**
     * Validacion del form
     */

    $( "#formPei" ).validate( {
        rules: {
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500
            },
            fechaAprobacion:{
                required: true,
                date: true
            },
            gestionInicio:{
                required: true,
                digits: true,
                min:2001,
                MenorQue: "#gestionFin"
            },
            gestionFin:{
                required: true,
                digits: true,
                min:2002,
                MayorQue: "#gestionInicio"
            }
        },
        messages: {
            descripcion: {
                required: "Debe ingresar una descripcion para el PEI",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 500 letras"
            },
            fechaAprobacion: {
                required: "Debe ingresar la fecha de aprobacion del PEI",
                date: "Debe ingresar una fecha valida"
            },
            gestionInicio: {
                required: "Debe ingresar la gestion de inicio del PEI",
                digits: "Solo debe ingresar el numero de año",
                min:"Debe ingresar un año valido mayor al 2000",
                MenorQue: "La gestion de incio debe ser menor que la gestion de fin"
            },
            gestionFin: {
                required: "Debe ingresar la gestion final del PEI",
                digits: "Solo debe ingresar el numero de año",
                min:"Debe ingresar un año valido mayor al 2000",
                MayorQue:"La gestion final debe ser mayor que la gestion de inicio"
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

    $.validator.addMethod("MayorQue",
        function (value, element, param) {
            let $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        });

    $.validator.addMethod("MenorQue",
        function (value, element, param) {
            let $otherElement = $(param);
            return parseInt(value, 10) < parseInt($otherElement.val(), 10);
        });
})