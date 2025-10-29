$(document).ready(function () {
  let dt_programa = $("#tablaListaProgramas").DataTable({
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/programa/listar-todo",
      dataSrc: "data",
      error: function (xhr, ajaxOptions, thrownError) {
        MostrarMensaje(
          "error",
          GenerarMensajeError(thrownError + " >" + xhr.responseText)
        );
      },
    },
    columns: [
      {
        className: "dt-small dt-center",
        orderable: false,
        searchable: false,
        data: null,
        render: function (data, type, row, meta) {
          return meta.row + 1;
        },
        width: 30,
      },
      {
        className: "dt-small",
        data: "Codigo",
      },
      {
        className: "dt-small",
        data: "Descripcion",
      },
      {
        className: "dt-small dt-estado dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstado",
        render: function (data, type, row) {
          return type === "display" && row.CodigoEstado === ESTADO_VIGENTE
            ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado" codigo="' +
                row.CodigoPrograma +
                '" estado="' +
                ESTADO_VIGENTE +
                '">Vigente</button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm btnEstado" codigo="' +
                row.CodigoPrograma +
                '" estado="' +
                ESTADO_CADUCO +
                '">Caducado</button>';
        },
      },
      {
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoPrograma",
        render: function (data, type) {
          return type === "display"
            ? '<div class="btn-group" role="group">' +
                '<button type="button" class="btn btn-outline-warning btn-sm btnEditar" codigo="' +
                data +
                '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm btnEliminar" codigo="' +
                data +
                '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                "</div>"
            : data;
        },
      },
    ],
  });

  dt_programa
    .on("order.dt search.dt", function () {
      let i = 1;
      dt_programa
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  function reiniciarCampos() {
    $("#formPrograma *")
      .filter(":input")
      .each(function () {
        $(this).removeClass("is-invalid is-valid");
      });
    $("#codigoPrograma").val("");
    $("#formPrograma").trigger("reset");
  }

  $("#btnMostrarCrear").off("click.programaOpen").on("click.programaOpen", function (e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    const icono = $(".icon");
    icono.addClass("opened");
    $("#divDatos").show(500);
    $("#divTabla").hide(500);
  });

  $("#btnCancelar").click(function () {
    $(".icon").removeClass("opened");
    reiniciarCampos();
    $("#divDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").click(function () {
    if ($("#formPrograma").valid()) {
      if ($("#codigoPrograma").val() === "") {
        guardarPrograma();
      } else {
        actualizarPrograma();
      }
    }
  });

  /*=============================================
     INSERTA EN LA BD UN NUEVO REGISTRO DE PROGRAMA
     =============================================*/
  async function guardarPrograma() {
    try {
      let datos = new FormData();
      datos.append("codigo", $("#codigo").val());
      datos.append("descripcion", $("#descripcion").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/programa/guardar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        MostrarMensaje(
          "success",
          response.message || "Programa guardado correctamente"
        );
        await dt_programa.ajax.reload(function (){
          $("#btnCancelar").click();
        });

      } else {
        MostrarMensaje("error", response.message || "Error al guardar");
      }
    } catch (error) {
      console.error("Error detallado:", error);
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message ||
          GenerarMensajeError(error.statusText) ||
          "Error desconocido al guardar"
      );
    }
  }

  /*=============================================
     ACTUALIZA EL PROGRAMA SELECCIONADO EN LA BD
     =============================================*/
  async function actualizarPrograma() {
    const objectBtn = $("#btnGuardar");
    try {
      IniciarSpiner(objectBtn);

      const datos = {
        codigoPrograma: $("#codigoPrograma").val(),
        codigo: $("#codigo").val(),
        descripcion: $("#descripcion").val(),
      };

      const response = await $.ajax({
        url: "index.php?r=Planificacion/programa/actualizar",
        method: "POST",
        data: datos,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje(
          "success",
          response.message || "Actualización exitosa"
        );

        try {
          await dt_programa.ajax.reload(function (){
            $("#btnCancelar").click();
          });// Cerrar formulario
        } catch (reloadError) {
          console.error("Error recargando tabla:", reloadError);
          window.location.reload(); // Fallback
        }
      } else {
        throw new Error(
          response.message || response.respuesta || "Error desconocido"
        );
      }
    } catch (error) {
      console.error("Error en actualización:", error);

      let errorMsg = "Error al actualizar";
      if (error.responseJSON) {
        errorMsg =
          error.responseJSON.message ||
          GenerarMensajeError(error.responseJSON.respuesta) ||
          errorMsg;
      } else if (error.message) {
        errorMsg = error.message;
      }

      await MostrarMensaje("error", errorMsg);

      if (!error.status) {
        dt_programa.ajax.reload(null, false);
      }
    } finally {
      DetenerSpiner(objectBtn);
    }
  }

  /*=============================================
     CAMBIA EL ESTADO DEL REGISTRO
     =============================================*/
  $("#tablaListaProgramas tbody").on("click", ".btnEstado", async function () {
    const objectBtn = $(this);
    const codigoPrograma = objectBtn.attr("codigo");
    const estadoPrograma = objectBtn.attr("estado");

    const datos = new FormData();
    datos.append("codigoPrograma", codigoPrograma);

    try {
      IniciarSpiner(objectBtn);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/programa/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        if (estadoPrograma === ESTADO_VIGENTE) {
          objectBtn
            .removeClass("btn-outline-success")
            .addClass("btn-outline-danger");
          objectBtn.html("Caducado");
          objectBtn.attr("estado", ESTADO_CADUCO);
        } else {
          objectBtn
            .addClass("btn-outline-success")
            .removeClass("btn-outline-danger");
          objectBtn.html("Vigente");
          objectBtn.attr("estado", ESTADO_VIGENTE);
        }
      } else {
        MostrarMensaje("error", GenerarMensajeError(response.respuesta));
      }
    } catch (error) {
      MostrarMensaje(
        "error",
        GenerarMensajeError(error.statusText + " >" + error.responseText)
      );
    } finally {
      DetenerSpiner(objectBtn);
    }
  });

  /*=============================================
     ELIMINA DE LA BD UN REGISTRO DE PROGRAMA
     =============================================*/
  $("#tablaListaProgramas tbody").on("click", ".btnEliminar", async function () {
    const objectBtn = $(this);
    const codigoPrograma = objectBtn.attr("codigo");

    try {
      const resultado = await Swal.fire({
        icon: "warning",
        title: "Confirmación de eliminación",
        text: "¿Está seguro de eliminar el programa elegido?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Borrar",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
      });

      if (!resultado.isConfirmed) return;

      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoPrograma", codigoPrograma);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/programa/eliminar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje(
          "success",
          "El programa ha sido borrado correctamente."
        );
        await dt_programa.ajax.reload(null, false);
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al eliminar programa:", error);

      let errorMessage = "Error al eliminar el programa";
      if (error.responseJSON) {
        errorMessage =
          error.responseJSON.message ||
          GenerarMensajeError(error.responseJSON.respuesta);
      } else if (error.statusText) {
        errorMessage += `: ${error.statusText}`;
      }

      await MostrarMensaje("error", errorMessage);
    } finally {
      DetenerSpiner(objectBtn);
    }
  });

  /*=============================================
     BUSCA EL PROGRAMA SELECCIONADO EN LA BD (PARA EDITAR)
     =============================================*/
  $("#tablaListaProgramas tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigoPrograma = objectBtn.attr("codigo");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoPrograma", codigoPrograma);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/programa/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const programa = response.data || response.programa;

        $("#codigoPrograma").val(programa.CodigoPrograma || "");
        $("#codigo").val(programa.Codigo || "");
        $("#descripcion").val(programa.Descripcion || "");

        $("#btnMostrarCrear").trigger("click");
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al buscar programa:", error);

      let errorMessage = "Error al cargar los datos del programa";
      if (error.responseJSON) {
        errorMessage =
          error.responseJSON.message ||
          GenerarMensajeError(error.responseJSON.respuesta);
      } else if (error.statusText) {
        errorMessage += `: ${error.statusText}`;
      }

      await MostrarMensaje("error", errorMessage);
    } finally {
      DetenerSpiner(objectBtn);
    }
  });
});