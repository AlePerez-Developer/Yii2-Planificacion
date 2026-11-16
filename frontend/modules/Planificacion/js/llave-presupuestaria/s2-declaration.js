let llavePresupuestaria_s2UnidadEjecutora = $('#idUnidadEjecutora')
let llavePresupuestaria_s2Programa = $('#idPrograma')
let llavePresupuestaria_s2Proyecto = $('#idProyecto')
let llavePresupuestaria_s2Actividad = $('#idActividad')

$(document).ready(function () {
    populateS2UnidadesEjecutoras(llavePresupuestaria_s2UnidadEjecutora)
    populateS2Programas(llavePresupuestaria_s2Programa)

    llavePresupuestaria_s2UnidadEjecutora.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija una unidad ejecutora',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalLlavePresupuestaria'),
        templateResult: formatoUnidad,
        templateSelection: formatoUnidad
    })

    llavePresupuestaria_s2Programa.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija un programa',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalLlavePresupuestaria')
    })

    llavePresupuestaria_s2Proyecto.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija un proyecto',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalLlavePresupuestaria')
    }).prop('disabled', true)

    llavePresupuestaria_s2Actividad.select2({
        theme: 'bootstrap4',
        placeholder: 'Elija una actividad',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalLlavePresupuestaria')
    }).prop('disabled', true)

    function formatoUnidad(repo) {
        if (!repo.id) {
            return repo.text
        }
        const option = repo.element ? $(repo.element) : $()
        const compuesto = option.data('key') || repo.text
        const descripcion = option.data('descripcion') || ''
        return $(`<div class="mi-render-select2">
            <div class="titulo-producto">${compuesto}</div>
            <div class="subtitulo-producto">${descripcion}</div>
        </div>`)
    }
})
