$(document).ready(function () {
  let codigoAreaEstrategica = 0;
  let tableBody = $("#tablaListaAreas tbody")

  function reiniciarCampos() {
    $('#formAreaEstrategica *').filter(':input').each(function () {
      $(this).removeClass('is-invalid is-valid');
    });
    $('#formAreaEstrategica').trigger("reset");
  }

  $("#btnCancelar").click(function () {
    $('.icon').toggleClass('opened');
    reiniciarCampos();
    $("#divDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").click(function () {
    const btn = $(this);
    const btnCancel = $('#btnCancelar')

    IniciarSpiner(btn);
    btnCancel.prop('disabled', true);
    try {
      if ($("#formAreaEstrategica").valid()) {
        const hasCode =  codigoAreaEstrategica !== 0;
        hasCode ? actualizarArea() : guardarArea();
      }
    } catch (err) {
      MostrarMensaje('error', GenerarMensajeError(err));
    } finally {
      DetenerSpiner(btn);
      btnCancel.prop('disabled', false);
    }
  });

  $(document).on('click', '#refresh', function(){
    dt_area.ajax.reload();
  })

  /*=============================================
    INSERTA EN LA BD UN NUEVO REGISTRO
    =============================================*/
  function  guardarArea()   {
    let codigo = $("#codigo").val();
    let descripcion = $("#descripcion").val();
    let datos = new FormData();
    datos.append("codigo", codigo);
    datos.append("descripcion", descripcion);
    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/guardar",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function () {
        MostrarMensaje('success', 'Los datos de la nueva Area Estrategica se guardaron correctamente.', null);
        dt_area.ajax.reload(() => {
          $("#btnCancelar").click();
        });
      },
      error: function (xhr) {
        const data = JSON.parse(xhr.responseText)
        MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
      }
    });
  }

  /*=============================================
    ACTUALIZA EL PEI SELECCIONADO EN LA BD
    =============================================*/
  function actualizarArea() {
    let Codigo = $("#codigo").val();
    let descripcion = $("#descripcion").val();
    let datos = new FormData();
    datos.append("codigoAreaEstrategica", codigoAreaEstrategica);
    datos.append("codigo", Codigo);
    datos.append("descripcion", descripcion);
    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/actualizar",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function () {
        MostrarMensaje('success', 'Los datos de la Area Estrategica se actualizaron correctamente.', null);
        dt_area.ajax.reload(() => {
          $("#btnCancelar").click();
        });
      },
      error: function (xhr) {
        const data = JSON.parse(xhr.responseText)
        MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
      }
    });
  }

  /* =============================================
          CAMBIA EL ESTADO DEL REGISTRO
  ===============================================*/
  tableBody.on('click', '#btnEstado', function(){
    let objectBtn = $(this);
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    let codigoAreaEstrategica = dt_row["CodigoAreaEstrategica"];
    IniciarSpiner(objectBtn)

    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/cambiar-estado",
      method: "POST",
      data : {
        codigoAreaEstrategica: codigoAreaEstrategica,
      },
      dataType: "json",
      success: function (data) {
        cambiarEstadoBtn(objectBtn, data["data"]);
        DetenerSpiner(objectBtn)
      },
      error: function (xhr) {
        const data = JSON.parse(xhr.responseText)
        MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        DetenerSpiner(objectBtn)
      }
    });
  });

  /*=============================================
    ELIMINA DE LA BD UN REGISTRO
    =============================================*/
  tableBody.on('click', '#btnEliminar', function(){
    let objectBtn = $(this)
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    let codigoAreaEstrategica = dt_row["CodigoAreaEstrategica"];

    Swal.fire({
      icon: "warning",
      title: "Confirmación eliminación",
      text: "¿Está seguro de eliminar el area estrategica seleccionada?",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      confirmButtonText: 'Borrar',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar'
    }).then(function (resultado) {
      if (resultado.value) {
        IniciarSpiner(objectBtn)
        $.ajax({
          url: "index.php?r=Planificacion/area-estrategica/eliminar",
          method: "POST",
          data : {
            codigoAreaEstrategica: codigoAreaEstrategica,
          },
          dataType: "json",
          success: function () {
            MostrarMensaje('success','El area estrategica ha sido eliminado correctamente.','')
            dt_area.ajax.reload();
            DetenerSpiner(objectBtn)
          },
          error: function (xhr) {
            const data = JSON.parse(xhr.responseText)
            MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
            DetenerSpiner(objectBtn)
          }
        });
      }
    });
  });

  /*=============================================
    BUSCA EL REGISTRO SELECCIONADO EN LA BD
    =============================================*/
  tableBody.on("click", ".btnEditar", function () {
    let objectBtn = $(this)
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    codigoAreaEstrategica = dt_row["CodigoAreaEstrategica"];
    IniciarSpiner(objectBtn)

    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/buscar",
      method: "POST",
      data : {
        codigoAreaEstrategica: codigoAreaEstrategica,
      },
      dataType: "json",
      success: function (data) {
        let area = JSON.parse(JSON.stringify(data["data"]));
        $("#codigo").val(area["Codigo"]);
        $("#descripcion").val(area["Descripcion"]);
        DetenerSpiner(objectBtn)
        $("#btnMostrarCrear").trigger('click');
      },
      error: function (xhr) {
        const data = JSON.parse(xhr.responseText)
        MostrarMensaje('error', GenerarMensajeError(data["message"]), data["errors"])
        DetenerSpiner(objectBtn)
      }
    });
  });
});
