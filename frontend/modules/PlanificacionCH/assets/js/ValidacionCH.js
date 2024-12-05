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

    $.validator.addMethod("DiferenteQue",
        function (value, element, param) {
            return value !== param;
        });

    $.validator.addMethod("GrupoUnico",
        function(value, element, param) {
            let result = false;
            let datos = new FormData()
            datos.append("gestion", '1/2022')
            datos.append("carrera", dataGrupos.carrera)
            datos.append("plan", dataGrupos.plan)
            datos.append("sigla", dataGrupos.sigla)
            datos.append("tipoGrupo", dataGrupos.tipoGrupo)
            datos.append("grupo", $(param).val())
            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria/verificar-grupo",
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

    $( "#formCargaHorariaPropuesta" ).validate( {
        rules: {
            docentes: {
                required: true,
                DiferenteQue: '0'
            },
            grupo:{
                required: true,
                GrupoUnico: "#grupo"
            },
        },
        messages: {
            docentes: {
                required: "Debe elegir un docente de la lista",
                DiferenteQue:"Debe elegir un docente de la lista"
            },
            grupo: {
                required: "Debe ingresar un grupo",
                GrupoUnico: "el grupo ingresado esta en uso actualmente"
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
});