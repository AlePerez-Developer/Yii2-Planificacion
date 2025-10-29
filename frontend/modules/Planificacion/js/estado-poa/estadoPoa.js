$(document).ready(function () {
  const tablaEstadosPoa = $("#tablaEstadosPoa").DataTable({
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/estado-poa/listar-todo",
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
        className: "dt-small dt-center",
        data: "Abreviacion",
        width: 60,
      },
      {
        className: "dt-small dt-center",
        data: "EtapaActual",
        width: 60,
      },
      {
        className: "dt-small dt-center",
        data: "EtapaPredeterminada",
        width: 80,
      },
      {
        className: "dt-small dt-center",
        data: "Orden",
        width: 60,
      },
      {
        className: "dt-small dt-estado dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstado",
        render: function (data, type, row) {
          return type === "display" && row.CodigoEstado === ESTADO_VIGENTE
            ? '<button type="button" class="btn btn-outline-success btn-sm btnEstado" codigo="' +
                row.CodigoEstadoPOA +
                '" estado="' +
                ESTADO_VIGENTE +
                '">Vigente</button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm btnEstado" codigo="' +
                row.CodigoEstadoPOA +
                '" estado="' +
                ESTADO_CADUCO +
                '">Caducado</button>';
        },
      },
      {
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstadoPOA",
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

  tablaEstadosPoa
    .on("order.dt search.dt", function () {
      let i = 1;
      tablaEstadosPoa
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  function reiniciarCampos() {
    $("#formEstadoPoa :input").removeClass("is-invalid is-valid");
    $("#codigoEstadoPoa").val("");
    $("#formEstadoPoa").trigger("reset");
  }

  $("#btnMostrarCrear")
    .off("click.estadoPoaOpen")
    .on("click.estadoPoaOpen", function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      const icono = $(".icon");
      icono.addClass("opened");
      $("#divDatos").show(500);
      $("#divTabla").hide(500);
    });

  $("#btnCancelar").on("click.estadoPoa", function () {
    $(".icon").removeClass("opened");
    reiniciarCampos();
    $("#divDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").on("click.estadoPoa", function () {
    if (typeof $("#formEstadoPoa").valid === "function" && !$("#formEstadoPoa").valid()) {
      return;
    }

    if ($("#codigoEstadoPoa").val() === "") {
      guardarEstadoPoa();
    } else {
      actualizarEstadoPoa();
    }
  });

  async function guardarEstadoPoa() {
    try {
      const datos = new FormData();
      datos.append("descripcion", $("#descripcion").val());
      datos.append("abreviacion", $("#abreviacion").val());
      datos.append("etapaActual", $("#etapaActual").val());
      datos.append("etapaPredeterminada", $("#etapaPredeterminada").val());
      datos.append("orden", $("#orden").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/estado-poa/guardar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        MostrarMensaje("success", response.message || "Estado POA guardado correctamente");
        await tablaEstadosPoa.ajax.reload(null, false);
        $("#btnCancelar").trigger("click");
      } else {
        MostrarMensaje("error", response.message || "Error al guardar el Estado POA", response.errors || null);
      }
    } catch (error) {
      console.error("Error al guardar Estado POA:", error);
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message || GenerarMensajeError(error.statusText) || "Error desconocido al guardar",
        errorData.errors || null
      );
    }
  }

  async function actualizarEstadoPoa() {
    const objectBtn = $("#btnGuardar");

    try {
      IniciarSpiner(objectBtn);

      const datos = {
        codigoEstadoPoa: $("#codigoEstadoPoa").val(),
        descripcion: $("#descripcion").val(),
        abreviacion: $("#abreviacion").val(),
        etapaActual: $("#etapaActual").val(),
        etapaPredeterminada: $("#etapaPredeterminada").val(),
        orden: $("#orden").val(),
      };

      const response = await $.ajax({
        url: "index.php?r=Planificacion/estado-poa/actualizar",
        method: "POST",
        data: datos,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje("success", response.message || "Estado POA actualizado correctamente");
        await tablaEstadosPoa.ajax.reload(null, false);
        $("#btnCancelar").trigger("click");
      } else {
        throw new Error(response.message || response.respuesta || "Error desconocido");
      }
    } catch (error) {
      console.error("Error al actualizar Estado POA:", error);
      const errorData = error.responseJSON || {};
      const errorMessage =
        errorData.message || error.message || GenerarMensajeError(error.statusText) || "Error al actualizar";
      await MostrarMensaje("error", errorMessage, errorData.errors || null);
    } finally {
      DetenerSpiner(objectBtn);
    }
  }

  $("#tablaEstadosPoa tbody").on("click", ".btnEstado", async function () {
    const objectBtn = $(this);
    const codigoEstadoPoa = objectBtn.attr("codigo");

    const datos = new FormData();
    datos.append("codigoEstadoPoa", codigoEstadoPoa);

    try {
      IniciarSpiner(objectBtn);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/estado-poa/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        if (objectBtn.attr("estado") === ESTADO_VIGENTE) {
          objectBtn.removeClass("btn-outline-success").addClass("btn-outline-danger");
          objectBtn.text("Caducado");
          objectBtn.attr("estado", ESTADO_CADUCO);
        } else {
          objectBtn.addClass("btn-outline-success").removeClass("btn-outline-danger");
          objectBtn.text("Vigente");
          objectBtn.attr("estado", ESTADO_VIGENTE);
        }
      } else {
        MostrarMensaje("error", GenerarMensajeError(response.message || response.respuesta));
      }
    } catch (error) {
      MostrarMensaje(
        "error",
        GenerarMensajeError(error.statusText + " >" + (error.responseText || "Error"))
      );
    } finally {
      DetenerSpiner(objectBtn);
    }
  });

  $("#tablaEstadosPoa tbody").on("click", ".btnEliminar", async function () {
    const objectBtn = $(this);
    const codigoEstadoPoa = objectBtn.attr("codigo");

    try {
      const resultado = await Swal.fire({
        icon: "warning",
        title: "Confirmación de eliminación",
        text: "¿Está seguro de eliminar el Estado POA seleccionado?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Borrar",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
      });

      if (!resultado.isConfirmed) {
        return;
      }

      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoEstadoPoa", codigoEstadoPoa);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/estado-poa/eliminar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje("success", response.message || "El Estado POA ha sido eliminado correctamente.");
        await tablaEstadosPoa.ajax.reload(null, false);
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta),
          response.errors || null
        );
      }
    } catch (error) {
      console.error("Error al eliminar Estado POA:", error);
      const errorData = error.responseJSON || {};
      let errorMessage = errorData.message || GenerarMensajeError(error.statusText) || "Error al eliminar";
      await MostrarMensaje("error", errorMessage, errorData.errors || null);
    } finally {
      DetenerSpiner(objectBtn);
    }
  });

  $("#tablaEstadosPoa tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigoEstadoPoa = objectBtn.attr("codigo");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoEstadoPoa", codigoEstadoPoa);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/estado-poa/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const estadoPoa = response.data || response.estadoPoa;

        $("#codigoEstadoPoa").val(estadoPoa.CodigoEstadoPOA || "");
        $("#descripcion").val(estadoPoa.Descripcion || "");
        $("#abreviacion").val(estadoPoa.Abreviacion || "");
        $("#etapaActual").val(estadoPoa.EtapaActual || "");
        $("#etapaPredeterminada").val(estadoPoa.EtapaPredeterminada || "");
        $("#orden").val(estadoPoa.Orden || "");

        $("#btnMostrarCrear").trigger("click");
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta),
          response.errors || null
        );
      }
    } catch (error) {
      console.error("Error al buscar Estado POA:", error);
      const errorData = error.responseJSON || {};
      const errorMessage =
        errorData.message || GenerarMensajeError(error.statusText) || "Error al cargar los datos del Estado POA";
      await MostrarMensaje("error", errorMessage, errorData.errors || null);
    } finally {
      DetenerSpiner(objectBtn);
    }
  });
});
