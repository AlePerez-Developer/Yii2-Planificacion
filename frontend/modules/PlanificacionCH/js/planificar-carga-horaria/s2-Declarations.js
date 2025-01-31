$(document).ready(function(){
    $('#facultades').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una facultad",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 500,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-facultades',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.facultades
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
    })

    $('#carreras').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una carrera",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 500,
            data: function (params) {
                return {
                    facultad: $('#facultades').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-carreras',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.carreras
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
    })

    $('#sedes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija una sede",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 500,
            data: function (params) {
                return {
                    carrera: $('#carreras').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-sedes',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.sedes
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            }
        },
    })

    $('#planes').select2({
        theme: 'bootstrap4',
        placeholder: "Elija un plan",
        allowClear: true,
        ajax: {
            method: "POST",
            dataType: 'json',
            delay: 500,
            data: function (params) {
                return {
                    carrera: $('#carreras').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-planes-estudios',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.planes
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
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
            delay: 500,
            data: function (params) {
                return {
                    carrera: $('#carreras').val(),
                    plan: $('#planes').val(),
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-cursos',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.cursos
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            },
        },
        templateResult: function (repo){
            return (repo.loading)?repo.text:'Curso N°: ' + repo.id
        },
        templateSelection: function (repo) {
            return (repo.id)?'Curso N°: ' + repo.id: repo.text;
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
            delay: 500,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            cache: false,
            url: 'index.php?r=PlanificacionCH/planificar-carga-horaria/listar-docentes',
            dataSrc: '',
            processResults: function (data) {
                return {
                    results: data.docentes
                };
            },
            error: function (xhr, ajaxOptions, thrownError) {
                MostrarMensaje('error',GenerarMensajeError( thrownError + ' >' +xhr.responseText))
            },
        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    })

    function formatRepo (repo) {
        if (repo.loading) {
            return repo.text;
        }

        let $container = $(
            '<div class="row">' +
            '<label class="docNombreList" >'+ $.trim(repo.text).toUpperCase()+' </label>'+
            '</div>'
        );

        return $container;
    }

    function formatRepoSelection (repo) {
        $('#lblCi').text(repo.id)
        $('#lblCondicion').text($.trim(repo.condicion).toUpperCase())
        if (repo.id !== '')
            $('#docImage').attr('src','http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_' + $.trim(repo.id) + '.jpg');
        else
            $('#docImage').attr('src','img/logo.jpg');

        let $container = $(
            '<div class="row">' +
            '<label class="docNombre" >'+ $.trim(repo.text).toUpperCase()+' </label>'+
            '</div>'
        );
        return $container;
    }
})