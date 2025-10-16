$(document).ready(function () {
  let idAreaEstrategica = '00000000-0000-0000-0000-000000000000';
  function reiniciarCampos() {
    $('#formAreaEstrategica *').filter(':input').each(function () {
      $(this).removeClass('is-invalid is-valid');
    });
    $('#formAreaEstrategica').trigger("reset");
    idAreaEstrategica = '00000000-0000-0000-0000-000000000000';
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
        const hasCode =  idAreaEstrategica !== '00000000-0000-0000-0000-000000000000';
        hasCode ? actualizar() : guardar();
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
  function  guardar()   {
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
        // noinspection JSCheckFunctionSignatures
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
  function actualizar() {
    let codigo = $("#codigo").val();
    let descripcion = $("#descripcion").val();
    let datos = new FormData();
    datos.append("idAreaEstrategica", idAreaEstrategica);
    datos.append("codigo", codigo);
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
        // noinspection JSCheckFunctionSignatures
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
  $(document).on('click', 'tbody #btnEstado', function(){
    let objectBtn = $(this);
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    let idAreaEstrategica = dt_row["IdAreaEstrategica"];
    IniciarSpiner(objectBtn)

    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/cambiar-estado",
      method: "POST",
      data : {
        idAreaEstrategica: idAreaEstrategica,
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
  $(document).on('click', 'tbody #btnEliminar', function(){
    let objectBtn = $(this)
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    let idAreaEstrategica = dt_row["IdAreaEstrategica"];

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
            idAreaEstrategica: idAreaEstrategica,
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
  $(document).on("click", "tbody #btnEditar", function () {
    let objectBtn = $(this)
    const dt_row = dt_area.row(objectBtn.closest('tr')).data()
    idAreaEstrategica = dt_row["IdAreaEstrategica"];
    IniciarSpiner(objectBtn)

    $.ajax({
      url: "index.php?r=Planificacion/area-estrategica/buscar",
      method: "POST",
      data : {
        idAreaEstrategica: idAreaEstrategica,
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

  /*
  * Validacion del form
  */
  $( "#formAreaEstrategica" ).validate( {
    rules: {
      codigo: {
        required: true,
        digits: true,
        range: [1, 100],
        verificarCodigoArea: ''
      },
      descripcion: {
        required: true,
        minlength: 2,
        maxlength: 500
      },
    },
    messages: {
      codigo: {
        required: "Debe ingresar un codigo de area estrategica",
        digits: "El codigo solo debe ser numerico",
        range: "El codigo debe estar comprendido entre 1 y 9",
        verificarCodigoArea: "El codigo ingresado ya se encuentra en uso"
      },
      descripcion: {
        required: "Debe ingresar una descripcion del area estrategica",
        minlength: "La descripcion debe tener almenos 2 letras",
        maxlength: "La descripcion debe tener maximo 500 letras"
      }
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
  });

  $.validator.addMethod("verificarCodigoArea",
      function(value) {
        let result = false;
        let datos = new FormData();
        datos.append("codigo", value);
        datos.append("idAreaEstrategica", idAreaEstrategica);
        $.ajax({
          url: "index.php?r=Planificacion/area-estrategica/verificar-codigo",
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

});