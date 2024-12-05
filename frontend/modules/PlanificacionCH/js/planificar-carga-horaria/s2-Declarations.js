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
            templateResult: formatRepo,
        },
    })

    function formatRepo (rep){
        if (repo.loading) {
            return 'asssssssssssssssss';
        }

        var $container = $(
            "<div class='select2-result-repository flex-container'>" +
            "<div class='select2-result-repository__avatar flex-child magenta'><img src='http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_" + $.trim(repo.id) + ".jpg' /></div>" +
            "<div class='select2-result-repository__meta flex-child green'>" +
            "<div class='select2-result-repository__title'></div>" +
            "<div class='select2-result-repository__description'></div>" +
            "</div>" +
            "</div>"
        );

        $container.find(".select2-result-repository__title").text(repo.text);
        $container.find(".select2-result-repository__description").text(repo.id);

        return 'asdasdasdasd';
    }


})