$(document).ready(function () {
    const idObjEstrategico = $('#idObjEstrategico').val();
    if (!idObjEstrategico) {
        return;
    }

    $('#dticTableLoading').show();
    $('#dticTableContainer').hide();
    inicializarTablaIndicadoresTrimestrales(idObjEstrategico);

    dt_listaIndicadoresTrimestrales.one('draw', function () {
        $('#dticTableLoading').hide();
        $('#dticTableContainer').fadeIn(180);
    });
});
