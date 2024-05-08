$(document).ready(function() {
    $('input.txt[type=text]').on('keypress', function (event) {
        let regex = new RegExp("^[\\w ]+$");
        let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $('textarea.txt').on('keypress', function (event) {
        let regex = new RegExp("^[\\w áéíóúÁÉÍÓÚñÑ.]+$");
        let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $('input.num[type=text]').on('keypress', function (event) {
        let regex = new RegExp("^[0-9,.]*$");
        let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $.validator.addMethod("MayorQue",
        function (value, element, param) {
            let $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        });

    $.validator.addMethod("FechaMayor",
        function (value, element, param){
            let d1 = new Date(value);
            let d2 = new Date($(param).val());
            return d1.getTime() > d2.getTime();
        })
    $.validator.addMethod("FechaMenor",
        function (value, element, param){
            let d1 = new Date(value);
            let d2 = new Date($(param).val());
            return d1.getTime() < d2.getTime();
        })

    $.validator.addMethod("largominimo",
        function (value, element, param){
            return value.length > parseInt(param);
        })

    $.validator.addMethod("DiferenteQue",
        function (value, element, param) {
            return value !== param;
        });

    $.validator.addMethod("CodigoIndicadorUnico",
        function(value, element, param) {
            let result = false;
            let indicadorEstrategico = $(param).val();
            let datos = new FormData();
            datos.append("codigo", value);
            datos.append("indicadorEstrategico", indicadorEstrategico);
            $.ajax({
                url: "index.php?r=Planificacion/indicador-estrategico/verificar-codigo",
                method: "POST",
                async: false,
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    result = !!(data);
                }
            });
            return result;
        }
    );

    $.validator.addMethod("CodigoObjetivoUnico",
        function(value, element, param) {
            let result = false;
            let objetivoEstrategico = $(param).val();
            let datos = new FormData();
            datos.append("codigo", value);
            datos.append("objetivoEstrategico", objetivoEstrategico);
            $.ajax({
                url: "index.php?r=Planificacion/obj-estrategico/verificar-codigo",
                method: "POST",
                async: false,
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    result = !!(data);
                }
            });
            return result;
        }
    );

    $( "#formPei" ).validate( {
        rules: {
            descripcionPei: {
                required: true,
                minlength: 2,
                maxlength: 250
            },
            fechaAprobacion:{
                required: true,
                date: true
            },
            gestionInicio:{
                required: true,
                digits: true,
                min:2001
            },
            gestionFin:{
                required: true,
                digits: true,
                min:2000,
                MayorQue: "#gestionInicio"
            }
        },
        messages: {
            descripcionPei: {
                required: "Debe ingresar una descripcion para el PEI",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 250 letras"
            },
            fechaAprobacion: {
                required: "Debe ingresar la fecha de aprobacion del PEI",
                date: "Debe ingresar una fecha valida"
            },
            gestionInicio: {
                required: "Debe ingresar la gestion de inicio del PEI",
                digits: "Solo debe ingresar el numero de año",
                min:"Debe ingresar un año valido mayor al 2000"
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

    } );

    $( "#formObjEstrategico" ).validate( {
        rules: {
            codigoPei:{
                required: true,
                DiferenteQue: '0'
            },
            codigoObjetivo: {
                required: true,
                digits: true,
                max: 999,
                largominimo: '2',
                CodigoObjetivoUnico: '#codigoObjEstrategico'
            },
            objetivo:{
                required: true,
                minlength: 2,
                maxlength: 450
            },
        },
        messages: {
            codigoPei: {
                required: "Debe seleccionar un codigo PEI",
                DiferenteQue:"Debe seleccionar un codigo PEI"
            },
            codigoObjetivo: {
                required: "Debe ingresar un codigo de objetico estrategico (OE)",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero de 3 digitos como maximo",
                largominimo: "Debe ingresar un numero de 3 digitos",
                CodigoObjetivoUnico: "El codigo de objetivo estrategico debe ser unico"
            },
            objetivo: {
                required: "Debe ingresar la descripcion del objetivo estrategico",
                minlength: "El objetivo debe tener por lo menos 2 caracteres",
                maxlength: "El objetivo debe tener maximo 450 caracteres"
            },
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

    $( "#formIndicadorEstrategico" ).validate( {
        rules: {
            codigoObjEstrategico:{
                required: true,
                DiferenteQue: '0'
            },
            codigoIndicador: {
                required: true,
                digits: true,
                max: 999,
                DiferenteQue: '0',
                CodigoIndicadorUnico: "#codigoIndicadorEstrategico"
            },
            metaIndicador: {
                required: true,
                digits: true,
            },
            descripcion:{
                required: true,
                minlength: 2,
                maxlength: 250
            },
            tipoResultado:{
                required: true,
                DiferenteQue: '0'
            },
            tipoIndicador:{
                required: true,
                DiferenteQue: '0'
            },
            categoriaIndicador:{
                required: true,
                DiferenteQue: '0'
            },
            tipoUnidad:{
                required: true,
                DiferenteQue: '0'
            },
        },
        messages: {
            codigoObjEstrategico: {
                required: "Debe seleccionar un codigo de Obj. Estrategico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Estrategico"
            },
            codigoIndicador: {
                required: "Debe ingresar un codigo de indicador",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero entero de maximo 3 digitos",
                DiferenteQue: "Debe ingresar un numero mayor que 0",
                CodigoIndicadorUnico: "el codigo de indicador estrategico ya se encuentra en uso"
            },
            metaIndicador: {
                required: "Debe ingresar una meta para el indicador",
                digits: "Solo se permite numeros enteros",
            },
            descripcion: {
                required: "Debe ingresar la descripcion del indicador",
                minlength: "La descripcion debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion debe tener maximo 250 caracteres"
            },
            tipoResultado: {
                required: "Debe seleccionar un tipo de resultado",
                DiferenteQue:"Debe seleccionar un tipo de resultado"
            },
            tipoIndicador: {
                required: "Debe seleccionar un tipo de indicador",
                DiferenteQue:"Debe seleccionar un tipo de indicador"
            },
            categoriaIndicador: {
                required: "Debe seleccionar una categoria",
                DiferenteQue:"Debe seleccionar una categoria"
            },
            tipoUnidad: {
                required: "Debe seleccionar un tipo de unidad",
                DiferenteQue:"Debe seleccionar un tipo de unidad"
            },
        },
        errorElement: "div",

        errorPlacement: function ( error, element ) {
            var elem = $(element);
            if (elem.hasClass("select2-hidden-accessible")) {
                element = $("#select2-" + elem.attr("id") + "-container").parent();
                error.addClass( "invalid-feedback" );
                error.insertAfter(element);
            } else {
                error.addClass( "invalid-feedback" ).removeAttr("style");
                error.insertAfter(element);
            }
        },
        highlight: function ( element  ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    } );

    $( "#formUnidad" ).validate( {
        rules: {
            da: {
                required: true,
                minlength: 2,
                maxlength: 2
            },
            ue: {
                required: true,
                minlength: 3,
                maxlength: 3
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 250
            },
            fechaInicio:{
                required: true,
                date: true,
                FechaMenor: "#fechaFin"
            },
            fechaFin:{
                required: true,
                date: true,
                FechaMayor: "#fechaInicio"
            },
        },
        messages: {
            da:{
                required: "Debe ingresar la unidad administrativa de la unidad",
                minlength: "La DA debe tener 2 digitos",
                maxlength: "La DA debe tener 2 digitos"
            },
            ue:{
                required: "Debe ingresar la unidad ejecutora de la unidad",
                minlength: "La UE debe tener 3 digitos",
                maxlength: "La UE debe tener 3 digitos"
            },
            descripcion: {
                required: "Debe ingresar una descripcion para la unidad",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 250 letras"
            },
            fechaInicio: {
                required: "Debe ingresar la fecha de inicio de vigencia de la unidad",
                date: "Debe ingresar una fecha valida",
                FechaMenor:'La fecha inicio debe ser anterior a la fecha de incio'
            },
            fechaFin: {
                required: "Debe ingresar la fecha final de vigencia de la unidad",
                date: "Debe ingresar una fecha valida",
                FechaMayor:'La fecha final debe ser posterior a la fecha de incio'
            },
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








    $( "#formobjinstitucional" ).validate( {
        rules: {
            CodigoObjEstrategico:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoCOGE: {
                required: true,
                digits: true,
                max: 99,
                largominimo: '1'
            },
            Objetivo:{
                required: true,
                minlength: 2,
                maxlength: 200
            },
        },
        messages: {
            CodigoObjEstrategico: {
                required: "Debe seleccionar un codigo de Obj. Estrategico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Estrategico"
            },
            CodigoCOGE: {
                required: "Debe ingresar un codigo de objetivo institucional (COGE)",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero de 2 digitos como maximo",
                largominimo: "Debe ingresar un numero de 2 digitos"
            },
            Objetivo: {
                required: "Debe ingresar la descripcion del objetivo institucional",
                minlength: "El objetivo debe tener por lo menos 2 caracteres",
                maxlength: "El objetivo debe tener maximo 200 caracteres"
            },
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

    $( "#formobjespecifico" ).validate( {
        rules: {
            CodigoObjEstrategico:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoObjInstitucional:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoCOGE: {
                required: true,
                digits: true,
                max: 99,
                largominimo: '1'
            },
            Objetivo:{
                required: true,
                minlength: 2,
                maxlength: 200
            },
        },
        messages: {
            CodigoObjEstrategico: {
                required: "Debe seleccionar un codigo de Obj. Estrategico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Estrategico"
            },
            CodigoObjInstitucional: {
                required: "Debe seleccionar un codigo de Obj. Institucional",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Institucional"
            },
            CodigoCOGE: {
                required: "Debe ingresar un codigo de objetivo especifico (COGE)",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero de 2 digitos como maximo",
                largominimo: "Debe ingresar un numero de 2 digitos"
            },
            Objetivo: {
                required: "Debe ingresar la descripcion del objetivo especifico",
                minlength: "El objetivo debe tener por lo menos 2 caracteres",
                maxlength: "El objetivo debe tener maximo 200 caracteres"
            },
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

    $( "#formIndicadores" ).validate( {
        rules: {
            CodigoObjInstitucional:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoObjEspecifico:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoPrograma:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoActividad:{
                required: true,
                DiferenteQue: '0'
            },
            Codigo: {
                required: true,
                digits: true,
                max: 999,
                DiferenteQue: '0'
            },
            Descripcion:{
                required: true,
                minlength: 2,
                maxlength: 200
            },
            Articulacion:{
                required: true,
                DiferenteQue: '0'
            },
            Resultado:{
                required: true,
                DiferenteQue: '0'
            },
            Tipo:{
                required: true,
                DiferenteQue: '0'
            },
            Categoria:{
                required: true,
                DiferenteQue: '0'
            },
            Unidad:{
                required: true,
                DiferenteQue: '0'
            },
        },
        messages: {
            CodigoObjInstitucional: {
                required: "Debe seleccionar un codigo de Obj. Institucional",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Institucional"
            },
            CodigoObjEspecifico: {
                required: "Debe seleccionar un codigo de Obj. Especifico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Especifico"
            },
            CodigoPrograma: {
                required: "Debe seleccionar un codigo de Obj. Especifico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Especifico"
            },
            CodigoActividad: {
                required: "Debe seleccionar un codigo de Obj. Especifico",
                DiferenteQue:"Debe seleccionar un codigo de Obj. Especifico"
            },
            Codigo: {
                required: "Debe ingresar un codigo de indicador",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero entero",
                DiferenteQue: "Debe ingresar un mayor que 0"
            },
            Descripcion: {
                required: "Debe ingresar la descripcion del indicador",
                minlength: "La descripcion debe tener por lo menos 2 caracteres",
                maxlength: "La descripcion debe tener maximo 200 caracteres"
            },
            Articulacion: {
                required: "Debe seleccionar un tipo de articulacion",
                DiferenteQue:"Debe seleccionar un tipo de articulacion"
            },
            Resultado: {
                required: "Debe seleccionar un tipo de resultado",
                DiferenteQue:"Debe seleccionar un tipo de resultado"
            },
            Tipo: {
                required: "Debe seleccionar un tipo de indicador",
                DiferenteQue:"Debe seleccionar un tipo de indicador"
            },
            Categoria: {
                required: "Debe seleccionar una categoria",
                DiferenteQue:"Debe seleccionar una categoria"
            },
            Unidad: {
                required: "Debe seleccionar un tipo de unidad",
                DiferenteQue:"Debe seleccionar un tipo de unidad"
            },
        },
        errorElement: "div",

        errorPlacement: function ( error, element ) {
            var elem = $(element);
            if (elem.hasClass("select2-hidden-accessible")) {
                element = $("#select2-" + elem.attr("id") + "-container").parent();
                error.addClass( "invalid-feedback" );
                error.insertAfter(element);
            } else {
                error.addClass( "invalid-feedback" ).removeAttr("style");
                error.insertAfter(element);
            }
        },
        highlight: function ( element  ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    } );

    $( "#formActividades" ).validate({
        rules: {
            CodigoPrograma:{
                required: true,
                DiferenteQue: ''
            },
            Codigo: {
                required: true,
                digits: true,
                minlength: 3,
                maxlength: 20
            },
            Descripcion:{
                required: true,
                minlength: 5,
                maxlength: 250
            },
        },
        messages: {
            CodigoPrograma: {
                required: "Debe seleccionar un programa",
                DiferenteQue:"Debe seleccionar un programa"
            },
            Codigo: {
                required: "Debe ingresar un codigo para la actividad",
                digits: "Solo debe ingresar numeros",
                minlength: "El codigo debe tener almenos 3 numeros",
                maxlength: "El codigo debe tener maximo 20 numeros"
            },
            Descripcion: {
                required: "Debe ingresar una descripcion para la actividad",
                minlength: "La descripcion debe tener almenos 5 letras",
                maxlength: "la descripcion debe tener maximo 250 letras"
            },
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

    $( "#formProyectos" ).validate({
        rules: {
            codigoPrograma:{
                required: true,
                DiferenteQue: ''
            },
            Codigo: {
                required: true,
                digits: true,
                minlength: 4,
                maxlength: 20
            },
            Descripcion:{
                required: true,
                minlength: 5,
                maxlength: 250
            },
        },
        messages: {
            codigoPrograma: {
                required: "Debe seleccionar un programa",
                DiferenteQue:"Debe seleccionar un programa"
            },
            Codigo: {
                required: "Debe ingresar un codigo para el proyecto",
                digits: "Solo debe ingresar numeros",
                minlength: "El codigo debe tener almenos 4 numeros",
                maxlength: "El codigo debe tener maximo 20 numeros"
            },
            Descripcion: {
                required: "Debe ingresar una descripcion para el proyecto",
                minlength: "La descripcion debe tener almenos 5 letras",
                maxlength: "la descripcion debe tener maximo 250 letras"
            },
        },
        errorElement: "div",

        errorPlacement: function ( error, element ) {
            var elem = $(element);
            if (elem.hasClass("select2-hidden-accessible")) {
                element = $("#select2-" + elem.attr("id") + "-container").parent();
                error.addClass( "invalid-feedback" );
                error.insertAfter(element);
            } else {
                error.addClass( "invalid-feedback" ).removeAttr("style");
                error.insertAfter(element);
            }
        },
        highlight: function ( element  ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    });

    $( "#formProgramas" ).validate({
        rules: {
            Codigo: {
                required: true,
                digits: true,
                minlength: 3,
                maxlength: 20
            },
            Descripcion:{
                required: true,
                minlength: 5,
                maxlength: 250
            },
        },
        messages: {
            Codigo: {
                required: "Debe ingresar un codigo para el programa",
                digits: "Solo debe ingresar numeros",
                minlength: "El codigo debe tener almenos 3 numeros",
                maxlength: "El codigo debe tener maximo 20 numeros"
            },
            Descripcion: {
                required: "Debe ingresar una descripcion para el programa",
                minlength: "La descripcion debe tener almenos 5 letras",
                maxlength: "la descripcion debe tener maximo 250 letras"
            },
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



    $( "#formAperturasProgramaticas" ).validate( {
        rules: {
            unidad:{
                required: true,
                DiferenteQue: '0'
            },
            programa: {
                required: true,
                DiferenteQue: '0'
            },
            proyecto: {
                required: true,
                DiferenteQue: '0'
            },
            actividad: {
                required: true,
                DiferenteQue: '0'
            },
            descripcion: {
                required: true,
                minlength: 2,
                maxlength: 250
            },
            organizacional:{
                required: true
            }
        },
        messages: {
            unidad: {
                required: "Debe seleccionar una unidad para la apertura programatica",
                DiferenteQue:"Debe seleccionar una unidad valida"
            },
            programa: {
                required: "Debe seleccionar un programa para la apertura programatica",
                DiferenteQue:"Debe seleccionar un programa valido"
            },
            proyecto: {
                required: "Debe seleccionar un proyecto para la apertura programatica",
                DiferenteQue:"Debe seleccionar un proyecto valido"
            },
            actividad: {
                required: "Debe seleccionar una actividad para la apertura programatica",
                DiferenteQue:"Debe seleccionar una actividad valida"
            },
            descripcion: {
                required: "Debe ingresar una descripcion para la apertura programatica",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 250 letras"
            },
            organizacional: {
                required: "Debe elegir si la apertura programatica es organizacional"
            },
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










    $( "#formunidadsoa" ).validate({
        rules: {
            nombreUnidad:{
                required: true,
                minlength: 5,
                maxlength: 150
            },
            nombreCorto:{
                required: true,
                minlength: 2,
                maxlength: 150
            },
        },
        messages: {
            nombreUnidad: {
                required: "Debe ingresar un nombre para la unidades-soa",
                minlength: "El nombre debe tener almenos 5 letras",
                maxlength: "El nombre debe tener maximo 150 letras"
            },
            nombreCorto: {
                required: "Debe ingresar un nombre corto para la unidades-soa",
                minlength: "El nombre corto debe tener almenos 2 letras",
                maxlength: "El nombre corto debe tener maximo 150 letras"
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

    $( "#formcargo" ).validate({
        rules: {
            sectorTrabajo: {
                required: true
            },
            nombreCargo:{
                required: true,
                minlength: 5,
                maxlength: 150
            },
            descripcionCargo:{
                required: false,
                minlength: 5,
                maxlength: 1000
            },
            requisitosPrincipales:{
                required: false,
                minlength: 5,
                maxlength: 1000
            },
            requisitosOpcionales:{
                required: false,
                minlength: 5,
                maxlength: 1000
            },
        },
        messages: {
            sectorTrabajo: {
                required: "Debe seleccionar un sector de trabajo"
            },
            nombreCargo: {
                required: "Debe ingresar un nombre para el cargo",
                minlength: "El nombre debe tener almenos 5 letras",
                maxlength: "El nombre debe tener maximo 150 letras"
            },
            descripcionCargo: {
                minlength: "El nombre corto debe tener almenos 5 letras",
                maxlength: "El nombre corto debe tener maximo 1000 letras"
            },
            requisitosPrincipales: {
                minlength: "El nombre corto debe tener almenos 5 letras",
                maxlength: "El nombre corto debe tener maximo 1000 letras"
            },
            requisitosOpcionales: {
                minlength: "El nombre corto debe tener almenos 5 letras",
                maxlength: "El nombre corto debe tener maximo 1000 letras"
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






});