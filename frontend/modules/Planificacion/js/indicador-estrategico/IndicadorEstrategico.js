$(document).ready(function(){
    const ID_EMPTY_GUID = '00000000-0000-0000-0000-000000000000';
    let idIndicadorEstrategico = ID_EMPTY_GUID
    let baseUrl = "index.php?r=Planificacion/indicador-estrategico/"
    let dtEvents = $('#tablaListaIndicadoresEstrategicos')
    let btnToggleForm = $('#btnMostrarCrear');

    function ReiniciarCampos(){
        $('#formIndicadorEstrategico *').filter(':input').each(function () {
            $(this).removeClass('is-invalid is-valid');
        });
        $('#formIndicadorEstrategico').trigger("reset");
        indicadorEstrategico_s2ObjEstrategico.val(null).trigger('change')

        indicadorEstrategico_s2CategoriaIndicador.val(null).trigger('change')
        indicadorEstrategico_s2TipoResultado.val(null).trigger('change')
        indicadorEstrategico_s2UnidadIndicador.val(null).trigger('change')
        indicadorEstrategico_s2AccionEstrategica.val(null).trigger('change')
        idIndicadorEstrategico = ID_EMPTY_GUID
    }

    function mensajeAccion(accion) {
        return `Los datos del Indicador Estratégico se ${accion}on correctamente.`;
    }

    $("#btnCancelar").click(function () {
        btnToggleForm.removeClass('opened').addClass('closed')
        ReiniciarCampos();
        $("#divDatos").hide(500);
        $("#divTabla").show(500)
    });

    $("#btnGuardar").click(async function () {
        const btn = $(this);
        const btnCancel = $('#btnCancelar')

        if (!$("#formIndicadorEstrategico").valid()) return;

        const idObjEstrategico = indicadorEstrategico_s2ObjEstrategico.select2('data')[0].id
        const idUnidadIndicador = indicadorEstrategico_s2UnidadIndicador.select2('data')[0].id
        const idCategoriaIndicador = indicadorEstrategico_s2CategoriaIndicador.select2('data')[0].id
        const idTipoResultado = indicadorEstrategico_s2TipoResultado.select2('data')[0].id
        const idAccionEstrategica = indicadorEstrategico_s2AccionEstrategica.select2('data')[0].id
        const codigo = $("#codigo").val();
        const meta = $("#meta").val();
        const lineaBase = $("#lineaBase").val();
        const descripcion = $("#descripcion").val();
        const accionDescripcion = $('#accionDescripcion')
        const datos = new FormData();
        datos.append('idIndicadorEstrategico',idIndicadorEstrategico)
        datos.append("idObjEstrategico", idObjEstrategico);
        datos.append("idUnidadIndicador", idUnidadIndicador);
        datos.append("idCategoriaIndicador", idCategoriaIndicador);
        datos.append("idTipoResultado", idTipoResultado);
        datos.append('idAccionEstrategica',idAccionEstrategica)
        datos.append("codigo", codigo);
        datos.append("meta", meta);
        datos.append("lineaBase", lineaBase);
        datos.append("descripcion", descripcion);
        datos.append("accionDescripcion", accionDescripcion);

        const hasCode =  idIndicadorEstrategico !== ID_EMPTY_GUID;
        let accion = hasCode ? 'actualizar' : 'guardar'

        try {
            await ajaxPromise({
                url: baseUrl + accion,
                data: datos,
                spinnerBtn: btn,
                cancelBtn: btnCancel,
                successMsg: mensajeAccion(accion),
                reloadTable: dt_indEstrategico
            })
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });

    $(document).on('click', '#refresh', function(){
        dt_indEstrategico.ajax.reload();
    })

    /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
    dtEvents.on('click', '.btn-toggle-estado', async function(){

        let objectBtn = $(this);
        const dt_row = dt_indEstrategico.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdIndicadorEstrategico"];

        const datos = new FormData();
        datos.append("idIndicadorEstrategico", rowId);

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
        const dt_row = dt_indEstrategico.row(objectBtn.closest('tr')).data()
        let rowId = dt_row["IdIndicadorEstrategico"];

        const datos = new FormData();
        datos.append("idIndicadorEstrategico", rowId);

        Swal.fire({
            icon: "warning",
            title: "Confirmación eliminación",
            text: "¿Está seguro de eliminar el indicador estrategico seleccionado?",
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
                        reloadTable: dt_indEstrategico
                    })
                } catch (err) {
                    console.error("Error al procesar:", err);
                }
            }
        })
    });

    /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
    dtEvents.on('click', '.btn-edit', async function(){
        let objectBtn = $(this);
        const dt_row = dt_indEstrategico.row(objectBtn.closest('tr')).data()
        idIndicadorEstrategico = dt_row["IdIndicadorEstrategico"];

        const datos = new FormData();
        datos.append("idIndicadorEstrategico", idIndicadorEstrategico);

        try {
            await ajaxPromise({
                url: baseUrl + "buscar",
                data: datos,
                spinnerBtn: objectBtn,
            }).then((data) => {
                let obj = data.data
                indicadorEstrategico_s2ObjEstrategico.val(obj["IdObjEstrategico"]).trigger('change')
                $("#codigo").val(obj["Codigo"]);
                $("#meta").val(obj["Meta"]);
                $("#lineaBase").val(obj["LineaBase"]);
                $("#descripcion").val(obj["Descripcion"]);
                $('#accionDescripcion').val(obj["AccionDescripcion"]);
                indicadorEstrategico_s2TipoResultado.val(obj["IdTipoResultado"]).trigger('change')
                indicadorEstrategico_s2CategoriaIndicador.val(obj["IdCategoriaIndicador"]).trigger('change')
                indicadorEstrategico_s2UnidadIndicador.val(obj["IdUnidadIndicador"]).trigger('change')
                indicadorEstrategico_s2AccionEstrategica.val(obj["IdAccionEstrategica"]).trigger('change')
                $("#btnMostrarCrear").trigger('click');
            });
        } catch (err) {
            console.error("Error al procesar:", err);
        }
    });


    /**
     * Validacion del form
     */
    $( "#formIndicadorEstrategico" ).validate( {
        rules: {
            idObjEstrategico: {
                required: true,
            },
            codigo: {
                required: true,
                digits: true,
                min: 1,
                remote: {
                    url: baseUrl + "verificar-codigo",
                    type: "post",
                    dataType: "json",
                    data: {
                        codigo: function () {
                            return $('#codigo').val(); // valor actual del campo
                        },
                        idIndicadorEstrategico: function () {
                            return idIndicadorEstrategico
                        }
                    }
                }
            },
            meta: {
                required: true,
                digits: true,
                min: 0
            },
            lineaBase: {
                required: true,
                digits: true,
                min: 0
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            accionDescripcion: {
                required: true,
                minlength: 2,
                maxlength: 500,
            },
            idTipoResultado: {
                required: true,
            },
            idCategoriaIndicador: {
                required: true,
            },
            idUnidadIndicador: {
                required: true,
            },
            idAccionEstrategica: {
                required: true
            }
        },
        messages: {
            idObjEstrategico: {
                required: "Debe seleccionar una opcion de objetivo estrategico",
            },
            codigo: {
                required: "Debe ingresar un codigo de indicador estrategico",
                digits: "Solo se permite numeros enteros",
                min: "El codigo debe ser un numero mayor que cero",
                require_from_group: "Debe seleccionar un objetivo estrategico antes de validar el codigo de indicador",
                remote: "El codigo ingresado ya se encuentra en uso",
            },
            meta: {
                required: "Debe ingresar un valor para la meta del indicador",
                digits: "Solo se permite numeros enteros",
                min: "la meta del indicador debe ser un numero mayor igual a cero"
            },
            lineaBase: {
                required: "Debe ingresar un valor para la linea base del indicador",
                digits: "Solo se permite numeros enteros",
                min: "La linea base del indicador debe ser un numer mayor o igual a cero"
            },
            descripcion: {
                required: "Debe ingresar la descripcion del indicador estrategico",
                minlength: "La descripcion del indicador debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion del indicador debe tener maximo 500 caracteres",
            },
            accionDescripcion: {
                required: "Debe ingresar la descripcion de la accion estrategica",
                minlength: "La descripcion debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion debe tener maximo 500 caracteres",
            },
            idTipoResultado: {
                required: "Debe seleccionar una opcion para el tipo de resultado",
            },
            idCategoriaIndicador: {
                required: "Debe seleccionar una opcion para la categoria del indicador",
            },
            idUnidadIndicador: {
                required: "Debe seleccionar una opcion para la unidad del indicador",
            },
            idAccionEstrategica: {
                required: "Debe seleccionar una opcion para la unidad del indicador",
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
    } );
})