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
        let regex = new RegExp("^[\\w ]+$");
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
            if (d1.getTime() > d2.getTime() )
                return true
            else
                return false
        })
    $.validator.addMethod("FechaMenor",
        function (value, element, param){
            let d1 = new Date(value);
            let d2 = new Date($(param).val());
            if (d1.getTime() < d2.getTime() )
                return true
            else
                return false
        })

    $.validator.addMethod("largominimo",
        function (value, element, param){
            if ( value.length > parseInt(param) )
                return true
            else
                return false
        })

    $.validator.addMethod("DiferenteQue",
        function (value, element, param) {
            return value !== "0";
        });

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
                min:2000
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

    $( "#formobjestrategico" ).validate( {
        rules: {
            CodigoPei:{
                required: true,
                DiferenteQue: '0'
            },
            CodigoCOGE: {
                required: true,
                digits: true,
                max: 999,
                largominimo: '2'
            },
            Objetivo:{
                required: true,
                minlength: 2,
                maxlength: 200
            },
        },
        messages: {
            CodigoPei: {
                required: "Debe seleccionar un codigo PEI",
                DiferenteQue:"Debe seleccionar un codigo PEI"
            },
            CodigoCOGE: {
                required: "Debe ingresar un codigo de objetico estrategico (COGE)",
                digits: "Solo se permite numeros enteros",
                max: "Debe ingresar un numero de 3 digitos como maximo",
                largominimo: "Debe ingresar un numero de 3 digitos"
            },
            Objetivo: {
                required: "Debe ingresar la descripcion del objetivo estrategico",
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

    $( "#formunidad" ).validate({
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
                required: "Debe ingresar un nombre para la unidades",
                minlength: "El nombre debe tener almenos 5 letras",
                maxlength: "El nombre debe tener maximo 150 letras"
            },
            nombreCorto: {
                required: "Debe ingresar un nombre corto para la unidades",
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

    $( "#formAperturasProgramaticas" ).validate( {
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
            prg: {
                required: true,
                minlength: 3,
                maxlength: 3
            },
            pry: {
                required: true,
                minlength: 4,
                maxlength: 200
            },
            act: {
                required: true,
                minlength: 3,
                maxlength: 3
            },
            Descripcion: {
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
            organizacional:{
                required: true
            },
            ejecutora:{
                required: true
            }
        },
        messages: {
            da:{
                required: "Debe ingresar la unidad administrativa de la apertura",
                minlength: "La DA debe tener 2 digitos",
                maxlength: "La DA debe tener 2 digitos"
            },
            ue:{
                required: "Debe ingresar la unidad ejecutora de la apertura",
                minlength: "La UE debe tener 3 digitos",
                maxlength: "La UE debe tener 3 digitos"
            },
            prg:{
                required: "Debe ingresar programa de la apertura",
                minlength: "El programa debe tener 3 digitos",
                maxlength: "El programa debe tener 3 digitos"
            },
            pry:{
                required: "Debe ingresar el proyecto de la apertura",
                minlength: "El proyecto debe tener 4 digitos minimamente (0000)",
                maxlength: "El proyecto debe tener 200 digitos maximo (SISIN)"
            },
            act:{
                required: "Debe ingresar la actividad de la apertura",
                minlength: "La actividad debe tener 3 digitos",
                maxlength: "La actividad debe tener 3 digitos"
            },
            Descripcion: {
                required: "Debe ingresar una descripcion para la apertura programatica",
                minlength: "La descripcion debe tener almenos 2 letras",
                maxlength: "La descripcion debe tener maximo 250 letras"
            },
            fechaInicio: {
                required: "Debe ingresar la fecha de inicio de vigencia de la apertura programatica",
                date: "Debe ingresar una fecha valida",
                FechaMenor:'La fecha inicio debe ser anterior a la fecha de incio'
            },
            fechaFin: {
                required: "Debe ingresar la fecha final de vigencia de la apertura programatica",
                date: "Debe ingresar una fecha valida",
                FechaMayor:'La fecha final debe ser posterior a la fecha de incio'
            },
            organizacional: {
                required: "Debe elegir si la apertura programatica es organizacional"
            },
            ejecutora: {
                required: "Debe elegir si la apertura programatica es ejecutora"
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

    $( "#formProyectos" ).validate({
        rules: {
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

    $( "#formActividades" ).validate({
        rules: {
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
            Codigo: {
                required: "Debe ingresar un codigo para la actividad",
                digits: "Solo debe ingresar numeros",
                minlength: "El codigo debe tener almenos 4 numeros",
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
});