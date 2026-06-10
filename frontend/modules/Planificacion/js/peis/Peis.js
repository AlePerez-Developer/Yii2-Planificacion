$(document).ready(function () {
    const PEI_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let idPei = PEI_EMPTY_GUID;
    let baseUrl = "index.php?r=Planificacion/peis/"
    let dtEvents = $('#tablaListaPeis')
    let btnToggleForm = $('#btnMostrarCrear');

    function reiniciarCampos() {
        $('#formPei *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formPei').trigger("reset");
        idPei = PEI_EMPTY_GUID;
    }

    function mensajeAccion(accion) {
        return `Los datos del Pei se ${accion}on correctamente.`;
    }

    $("#btnCancelar").click(function () {
        btnToggleForm.removeClass('opened').addClass('closed')
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

        const hasCode =  idPei !== PEI_EMPTY_GUID;
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

    $('#gestionInicio, #gestionFin').on('change keyup', function () {
        const target = this.id === 'gestionInicio' ? '#gestionFin' : '#gestionInicio';
        $(target).valid();
    });

    $(document).on('click', '#refresh', function(){
        dt_pei.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){
        let objectBtn = $(this);
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdPei"];

        const datos = new FormData();
        datos.append("idPei", rowId);

        try {
            await ajaxPromise({
                url: baseUrl + "cambiar-estado",
                data: datos,
                spinnerBtn: objectBtn,
                successMsg: 'Estado actualizado correctamente.',
            }).then((data) => {
                cambiarEstadoBtnDtic(objectBtn, data.data);
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
    dtEvents.on('click', '.btn-delete', function(){
        let objectBtn = $(this)
        const dt_row = dt_pei.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdPei"];

        const datos = new FormData();
        datos.append("idPei", rowId);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el PEI seleccionado?",
            theme: 'bootstrap-5',
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: 'Borrar',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
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
    dtEvents.on('click', '.btn-edit', async function(){
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

    $.validator.addMethod("MayorQue", function (value, element, param) {
        const valActual = parseInt(value, 10) || 0;
        const valComparar = parseInt($(param).val(), 10) || 0;
        return valActual > valComparar;
    });

    $.validator.addMethod("MenorQue", function (value, element, param) {
        const valActual = parseInt(value, 10) || 0;
        const valComparar = parseInt($(param).val(), 10) || 0;
        return valActual < valComparar;
    });
})