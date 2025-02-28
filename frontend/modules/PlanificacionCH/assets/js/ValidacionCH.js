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
            datos.append("carrera", dataGrupos.carrera)
            datos.append("sede", dataGrupos.sede)
            datos.append("plan", dataGrupos.plan)
            datos.append("sigla", dataGrupos.sigla)
            datos.append("tipoGrupo", dataGrupos.tipoGrupo)
            datos.append("grupo", $(param).val())
            datos.append("docente", $(param).attr('p'))
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

    $.validator.addMethod("GrupoUnicoMatricial",
        function(value, element, param) {
            let result = false;
            let datos = new FormData()
            datos.append("carrera", $('#carreras').val())
            datos.append("sede", 'SU')
            datos.append("plan", $('#planes').val())
            datos.append("sigla", $('#materias').val())
            datos.append("tipoGrupo", dataGruposMatricial.tipoGrupo)
            datos.append("grupo", $(param).val())
            datos.append("docente", $(param).attr('p'))
            $.ajax({
                url: "index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/verificar-grupo",
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

    $( "#formCargaHorariaPropuestaMatricial" ).validate( {
        rules: {
            docentes: {
                required: true,
                DiferenteQue: '0'
            },
            carreras: {
                required: true,
                DiferenteQue: '0'
            },
            planes: {
                required: true,
                DiferenteQue: '0'
            },
            grupo:{
                required: true,
                GrupoUnicoMatricial: "#grupo"
            },
        },
        messages: {
            docentes: {
                required: "Debe elegir un docente de la lista",
                DiferenteQue:"Debe elegir un docente de la lista"
            },
            carreras: {
                required: "Debe elegir un docente de la lista",
                DiferenteQue:"Debe elegir un docente de la lista"
            },
            planes: {
                required: "Debe elegir un docente de la lista",
                DiferenteQue:"Debe elegir un docente de la lista"
            },
            grupo: {
                required: "Debe ingresar un grupo",
                GrupoUnicoMatricial: "el grupo ingresado esta en uso actualmente"
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