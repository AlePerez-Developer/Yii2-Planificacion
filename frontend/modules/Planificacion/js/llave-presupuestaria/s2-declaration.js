let llavePresupuestaria_s2Da = $('#da')
let llavePresupuestaria_s2Ue = $('#ue')
let llavePresupuestaria_s2Programa = $('#programa')
let llavePresupuestaria_s2Proyecto = $('#proyecto')
let llavePresupuestaria_s2Actividad = $('#actividad')
$(document).ready(function() {

    populateS2Da(llavePresupuestaria_s2Da)
    populateS2Ue(llavePresupuestaria_s2Ue)
    populateS2Programas(llavePresupuestaria_s2Programa)

    llavePresupuestaria_s2Da.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una direccion administrativa",
        allowClear: true,
    })

    llavePresupuestaria_s2Ue.select2({
        theme: 'bootstrap4',
        placeholder: "Elija una unidad ejecutora",
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