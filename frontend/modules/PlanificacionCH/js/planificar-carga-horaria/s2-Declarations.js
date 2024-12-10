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

        var $container = $(
            '<div class="row">\n' +
            '        <div class="col-sm-1" style="height: 70px;width: 70px">\n' +
            '            <img src="http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_' + $.trim(repo.id) + '.jpg\" style="height: 70px; width: 70px">\n' +
            '        </div>\n' +
            '        <div class="col-sm-8">\n' +
            '            <div class="row">\n' +
            '                <div class="col-7 sNombre">' + $.trim(repo.text).toUpperCase() + '</div>\n' +
            '            </div>\n' +
            '            <div class="row">\n' +
            '                <div class="col-1 sCi"><b>CI: </b></div>\n' +
            '                <div class="col-3 sCi">' + $.trim(repo.id) + '</div>\n' +
            '            </div>\n' +
            '            <div class="row">\n' +
            '                <div class="col-7 sCi">' + $.trim(repo.condicion).toUpperCase() + '</div>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '    </div>'
        );
        return $container;
    }

    function formatRepoSelection (repo) {
        return repo.full_name || repo.text;
    }
})