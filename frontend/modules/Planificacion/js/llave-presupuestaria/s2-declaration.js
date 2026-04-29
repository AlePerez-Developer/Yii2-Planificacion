let llavePresupuestaria_s2Unidad = $('#unidad')
let llavePresupuestaria_s2Programa = $('#programa')
let llavePresupuestaria_s2Proyecto = $('#proyecto')
let llavePresupuestaria_s2Actividad = $('#actividad')
$(document).ready(function() {

    populateS2Unidades(llavePresupuestaria_s2Unidad)
    populateS2Programas(llavePresupuestaria_s2Programa)

    llavePresupuestaria_s2Unidad.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una unidad",
        allowClear: true,
    })

    llavePresupuestaria_s2Programa.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un programa",
        allowClear: true,
    })

    llavePresupuestaria_s2Proyecto.select2({
        theme: 'bootstrap4',
        placeholder: "Elija un proyecto",
        allowClear: true,
    })

    llavePresupuestaria_s2Actividad.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una actividad",
        allowClear: true,
    })
});