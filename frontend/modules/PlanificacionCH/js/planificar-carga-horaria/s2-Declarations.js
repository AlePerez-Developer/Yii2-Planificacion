$(document).ready(function(){
    $('#facultades').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una facultad",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-facultades',
            processResults: function (data) {
                return {
                    results: data['facultades']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
    })

    $('#carreras').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una carrera",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                let facultad  = $('#facultades').select2('data')
                return {
                    facultad: facultad[0].id,
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-carreras',
            processResults: function (data) {
                return {
                    results: data['carreras']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
    })

    $('#sedes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una sede",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                let carrera  = $('#carreras').select2('data')
                return {
                    carrera: carrera[0].id,
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-sedes',
            processResults: function (data) {
                return {
                    results: data['sedes']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
    })

    $('#planes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un plan",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                let carrera  = $('#carreras').select2('data')
                return {
                    carrera: carrera[0].id,
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-planes-estudios',
            processResults: function (data) {
                return {
                    results: data['planes']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
        templateResult: function (repo){
            return (repo.loading)?repo.text:'Numero plan de estudios: ' + repo.id
        },
        templateSelection: function (repo) {
            return (repo.id)?'Numero plan de estudios: ' + repo.id: repo.text;
        }
    })

    $('#cursos').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un curso",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                let carrera  = $('#carreras').select2('data')
                let plan  = $('#planes').select2('data')
                return {
                    carrera: carrera[0].id,
                    plan: plan[0].id,
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-cursos',
            processResults: function (data) {
                data['cursos'].unshift({'id':0,'text':'Mostrar todos los cursos'})
                return {
                    results: data['cursos']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
        templateResult: function (repo){
            let texto = (repo.id === 0)?'-- Mostrar todos los cursos --':'Curso N°: ' + repo.id
            return (repo.loading)?repo.text:texto
        },
        templateSelection: function (repo) {
            let texto = (repo.id === '0')?'-- Mostrar todos los cursos --':'Curso N°: ' + repo.id
            return (repo.id)?texto: repo.text;
        }
    })

    $('#materias').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una materia",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                let facultad  = $('#facultades').select2('data')
                return {
                    facultad: facultad[0].id,
                    q: params.term,
                    page: params.page
                };
            },
            cache: true,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria-matricial/listar-materias-select',
            processResults: function (data) {
                return {
                    results: data['materias']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
        templateResult: function (repo){
            return (repo.loading)?repo.text:repo.text + ' ( ' + repo.id + ' ) ';
        },
        templateSelection: function (repo) {
            return (repo.id)?repo.text + ' ( ' + repo.id + ' ) ': repo.text;
        }
    })

    $('#docentes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un docente",
        allowClear: true,
        dropdownParent: $('#modalPlanificar'),
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 700,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-docentes',
            processResults: function (data) {
                return {
                    results: data['docentes']
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + '(' + ajaxOptions + ') ' + ' > ' + xhr.responseText))
            },
        },
        templateResult: formatoLista,
        templateSelection: formatoSeleccion
    })

    function formatoLista (repo) {
        if (repo.loading) {
            return repo.text;
        }

        return $.trim(repo.text).toUpperCase()
    }

    function formatoSeleccion (repo) {
        $('#lblCi').text(repo.id)
        $('#lblCondicion').text($.trim(repo.condicion).toUpperCase())
        if (repo.id !== '')
            $('#docImage').attr('src','http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_' + $.trim(repo.id) + '.jpg');
        else
            $('#docImage').attr('src','img/logo.jpg');

        return $.trim(repo.text).toUpperCase()
    }
})