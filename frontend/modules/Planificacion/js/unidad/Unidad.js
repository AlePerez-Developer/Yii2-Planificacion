$(document).ready(function () {
  function format(d) {
    return (
      "<dl>" +
      '<dt class="dt-small">Vigencia</dt>' +
      '<dd class="dt-small"> De: ' +
      d.FechaInicio +
      " Hasta: " +
      d.FechaFin +
      "</dd>" +
      "</dl>"
    );
  }

  let table = $("#tablaListaUnidades").DataTable({
    initComplete: function () {
      this.api()
        .columns([2, 3])
        .every(function () {
          let column = this;
          let select = $(
            '</br><select><option value="">Buscar...</option></select>'
          )
            .appendTo($(column.header()))
            .on("change", function () {
              let val = $.fn.dataTable.util.escapeRegex($(this).val());
              column.search(val ? "^" + val + "$" : "", true, false).draw();
            });
          column
            .data()
            .unique()
            .sort()
            .each(function (d, j) {
              select.append('<option value="' + d + '">' + d + "</option>");
            });
        });
    },
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/unidad/listar-todo",
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
        width: 30,
        render: function (data, type, row, meta) {
          return meta.row + 1;
        },
      },
      {
        className: "dt-control dt-small dt-center",
        orderable: false,
        searchable: false,
        data: null,
        defaultContent: "",
      },
      {
        className: "dt-small dt-center",
        orderable: false,
        data: "Da",
      },
      {
        className: "dt-small dt-center",
        orderable: false,
        data: "Ue",
      },
      {
        className: "dt-small",
        orderable: false,
        data: "Descripcion",
      },
      {
        className: "dt-small dt-center",
        orderable: false,
        searchable: false,
        data: "Organizacional",
        render: function (data, type, row, meta) {
          return type === "display" &&
            (row.Organizacional === "1" || row.Organizacional === 1)
            ? "SI"
            : "NO";
        },
      },
      {
        className: "dt-estado dt-small dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstado",
        render: function (data, type, row, meta) {
          return type === "display" && row.CodigoEstado === ESTADO_VIGENTE
            ? '<button type="button" class="btn btn-outline-success btn-sm  btnEstado" codigo="' +
                row.CodigoUnidad +
                '" estado =  "' +
                ESTADO_VIGENTE +
                '" >Vigente</button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm  btnEstado" codigo="' +
                row.CodigoUnidad +
                '" estado = "' +
                ESTADO_CADUCO +
                '" >Caducado</button>';
        },
      },
      {
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoUnidad",
        render: function (data, type, row, meta) {
          return type === "display"
            ? '<div class="btn-group" role="group" aria-label="Basic example">' +
                '<button type="button" class="btn btn-outline-warning btn-sm  btnEditar" codigo="' +
                data +
                '" data-toggle="tooltip" title="Click! para editar el registro"><i class="fa fa-pen-fancy"></i></button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm  btnEliminar" codigo="' +
                data +
                '" data-toggle="tooltip" title="Click! para eliminar el registro"><i class="fa fa-trash-alt"></i></button>' +
                "</div>"
            : data;
        },
      },
    ],
  });

  table
    .on("order.dt search.dt", function () {
      let i = 1;
      table
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  $("#tablaListaUnidades tbody").on("click", "td.dt-control", function () {
    var tr = $(this).closest("tr");
    var row = table.row(tr);

    if (row.child.isShown()) {
      row.child.hide();
    } else {
      row.child(format(row.data())).show();
    }
  });

  function reiniciarCampos() {
    $("#formUnidad *")
      .filter(":input")
      .each(function () {
        $(this).removeClass("is-invalid is-valid");
      });
    $("#codigoUnidad").val("");
    $("#formUnidad").trigger("reset");
  }

  $("#btnMostrarCrear")
    .off("click.unidadOpen")
    .on("click.unidadOpen", function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      const icono = $(".icon");
      icono.addClass("opened");
      $("#divDatos").show(500);
      $("#divTabla").hide(500);
    });

  $(document).off("click.unidadToggle");

  $("#btnCancelar").click(function () {
    $(".icon").removeClass("opened");
    reiniciarCampos();
    $("#divDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").click(function () {
    if ($("#formUnidad").valid()) {
      if ($("#codigoUnidad").val() === "") {
        guardarUnidad();
      } else {
        actualizarUnidad();
      }
    }
  });

  $("#fechaInicio").change(function () {
    $("#formUnidad").validate().element("#fechaInicio");
    $("#formUnidad").validate().element("#fechaFin");
  });

  $("#fechaFin").change(function () {
    $("#formUnidad").validate().element("#fechaInicio");
    $("#formUnidad").validate().element("#fechaFin");
  });

  async function guardarUnidad() {
    try {
      let datos = new FormData();
      datos.append("da", $("#da").val());
      datos.append("ue", $("#ue").val());
      datos.append("descripcion", $("#descripcion").val());
      datos.append(
        "organizacional",
        $("#organizacional").is(":checked") ? 1 : 0
      );
      datos.append("fechaInicio", $("#fechaInicio").val());
      datos.append("fechaFin", $("#fechaFin").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/unidad/guardar",
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
          response.message ||
            "Los datos de la nueva unidad se guardaron correctamente."
        );
        await table.ajax.reload(function (){
          $("#btnCancelar").click();
        });

      } else {
        MostrarMensaje("error", response.message || "Error al guardar unidad");
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message ||
          GenerarMensajeError(error.statusText) ||
          "Error desconocido al guardar"
      );
    }
  }

  $("#tablaListaUnidades tbody").on("click", ".btnEstado", async function () {
    const objectBtn = $(this);
    const codigoUnidad = objectBtn.attr("codigo");
    const estado = objectBtn.attr("estado");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoUnidad", codigoUnidad);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/unidad/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        if (estado === ESTADO_VIGENTE) {
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
        MostrarMensaje("error", response.message || "Error al cambiar estado");
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

  $("#tablaListaUnidades tbody").on("click", ".btnEliminar", async function () {
    const objectBtn = $(this);
    const codigoUnidad = objectBtn.attr("codigo");

    try {
      const resultado = await Swal.fire({
        icon: "warning",
        title: "Confirmación eliminación",
        text: "¿Está seguro de eliminar la unidad seleccionada?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Borrar",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
      });

      if (!resultado.isConfirmed) return;

      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoUnidad", codigoUnidad);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/unidad/eliminar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        await MostrarMensaje(
          "success",
          "La unidad ha sido borrada correctamente."
        );
        await table.ajax.reload(null, false);
      } else {
        await MostrarMensaje(
          "error",
          response.message || "Error al eliminar unidad"
        );
      }
    } catch (error) {
      await MostrarMensaje(
        "error",
        GenerarMensajeError(error.statusText + " >" + error.responseText)
      );
    } finally {
      DetenerSpiner(objectBtn);
    }
  });

  $("#tablaListaUnidades tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigoUnidad = objectBtn.attr("codigo");
    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoUnidad", codigoUnidad);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/unidad/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        const unidad = response.data;
        $("#codigoUnidad").val(unidad.CodigoUnidad);
        $("#da").val(unidad.Da);
        $("#ue").val(unidad.Ue);
        $("#descripcion").val(unidad.Descripcion);
        $("#organizacional").prop(
          "checked",
          unidad.Organizacional === 1 || unidad.Organizacional === "1"
        );
        $("#fechaInicio").val(unidad.FechaInicio);
        $("#fechaFin").val(unidad.FechaFin);
        $("#btnMostrarCrear").trigger("click");
      } else {
        MostrarMensaje(
          "error",
          response.message || "No se pudo obtener la unidad"
        );
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

  async function actualizarUnidad() {
    const objectBtn = $("#btnGuardar");
    try {
      IniciarSpiner(objectBtn);
      let datos = new FormData();
      datos.append("codigoUnidad", $("#codigoUnidad").val());
      datos.append("da", $("#da").val());
      datos.append("ue", $("#ue").val());
      datos.append("descripcion", $("#descripcion").val());
      datos.append(
        "organizacional",
        $("#organizacional").is(":checked") ? 1 : 0
      );
      datos.append("fechaInicio", $("#fechaInicio").val());
      datos.append("fechaFin", $("#fechaFin").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/unidad/actualizar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success) {
        MostrarMensaje("success", "La unidad se actualizó correctamente.");
        await table.ajax.reload(function (){
          $("#btnCancelar").click();
        });
      } else {
        MostrarMensaje(
          "error",
          response.message || "Error al actualizar unidad"
        );
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message ||
          GenerarMensajeError(error.statusText) ||
          "Error desconocido al actualizar"
      );
    } finally {
      DetenerSpiner(objectBtn);
    }
  }
});
