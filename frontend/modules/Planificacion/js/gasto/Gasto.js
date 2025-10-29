$(document).ready(function () {
  let dt_gasto = $("#tablaListaGastos").DataTable({
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/gasto/listar-todo",
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
        data: "Descripcion",
      },
      {
        className: "dt-small",
        data: "EntidadTransferencia",
        width: 30,
      },
      {
        className: "dt-small dt-estado dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstado",
        render: function (data, type, row) {
          return type === "display" && row.CodigoEstado === ESTADO_VIGENTE
            ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado" codigo="' +
                row.CodigoGasto +
                '" estado="' +
                ESTADO_VIGENTE +
                '">Vigente</button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm btnEstado" codigo="' +
                row.CodigoGasto +
                '" estado="' +
                ESTADO_CADUCO +
                '">Caducado</button>';
        },
      },
      {
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoGasto",
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

  dt_gasto
    .on("order.dt search.dt", function () {
      let i = 1;
      dt_gasto
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  function reiniciarCampos() {
    $("#formGasto *")
      .filter(":input")
      .each(function () {
        $(this).removeClass("is-invalid is-valid");
      });
    $("#codigoGasto").val("");
    $("#formGasto").trigger("reset");
  }

  $("#btnMostrarCrear").off("click.gastoOpen").on("click.gastoOpen", function (e) {
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
    if ($("#formGasto").valid()) {
      if ($("#codigoGasto").val() === "") {
        guardarGasto();
      } else {
        actualizarGasto();
      }
    }
  });

  /*=============================================
     INSERTA EN LA BD UN NUEVO REGISTRO de GASTO
     =============================================*/
  async function guardarGasto() {
    try {
      let datos = new FormData();
      datos.append("descripcion", $("#descripcion").val());
      datos.append("entidadTransferencia", $("#entidadTransferencia").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/gasto/guardar",
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
          response.message || "Gasto guardado correctamente"
        );
        await dt_gasto.ajax.reload(null, false);
        $("#btnCancelar").click();
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
     ACTUALIZA EL GASTO SELECCIONADO EN LA BD
     =============================================*/
  async function actualizarGasto() {
    const objectBtn = $("#btnGuardar");
    try {
      IniciarSpiner(objectBtn);

      const datos = {
        codigoGasto: $("#codigoGasto").val(),
        descripcion: $("#descripcion").val(),
        entidadTransferencia: $("#entidadTransferencia").val(),
      };

      const response = await $.ajax({
        url: "index.php?r=Planificacion/gasto/actualizar",
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
          await dt_gasto.ajax.reload(null, false);
          $("#btnCancelar").click(); // Cerrar formulario
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
        dt_gasto.ajax.reload(null, false);
      }
    } finally {
      DetenerSpiner(objectBtn);
    }
  }
  /*=============================================
     CAMBIA EL ESTADO DEL REGISTRO
     =============================================*/
  $("#tablaListaGastos tbody").on("click", ".btnEstado", async function () {
    const objectBtn = $(this);
    const codigoGasto = objectBtn.attr("codigo");
    const estadoGasto = objectBtn.attr("estado");

    const datos = new FormData();
    datos.append("codigoGasto", codigoGasto);

    try {
      IniciarSpiner(objectBtn);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/gasto/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        if (estadoGasto === ESTADO_VIGENTE) {
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
     ELIMINA DE LA BD UN REGISTRO de GASTO 
     =============================================*/
  $("#tablaListaGastos tbody").on("click", ".btnEliminar", async function () {
    const objectBtn = $(this);
    const codigoGasto = objectBtn.attr("codigo");

    try {
      const resultado = await Swal.fire({
        icon: "warning",
        title: "Confirmación de eliminación",
        text: "¿Está seguro de eliminar el gasto elegido?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Borrar",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
      });

      if (!resultado.isConfirmed) return;

      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoGasto", codigoGasto);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/gasto/eliminar",
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
          "El gasto ha sido borrado correctamente."
        );
        await dt_gasto.ajax.reload(null, false);
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al eliminar gasto:", error);

      let errorMessage = "Error al eliminar el gasto";
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
     BUSCA EL GASTO SELECCIONADO EN LA BD (PARA EDITAR)
     =============================================*/
  $("#tablaListaGastos tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigoGasto = objectBtn.attr("codigo");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoGasto", codigoGasto);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/gasto/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const gasto = response.data || response.gasto;

        $("#codigoGasto").val(gasto.CodigoGasto || "");
        $("#descripcion").val(gasto.Descripcion || "");
        $("#entidadTransferencia").val(gasto.EntidadTransferencia || "");

        $("#btnMostrarCrear").trigger("click");
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al buscar gasto:", error);

      let errorMessage = "Error al cargar los datos del gasto";
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
