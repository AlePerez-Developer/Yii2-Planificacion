$(document).ready(function () {
  const $form = $("#formLlavePresupuestaria");
  const $btnGuardar = $("#btnGuardar");
  const $btnCancelar = $("#btnCancelar");
  const $btnMostrarCrear = $("#btnMostrarCrear");

  const $codigoUnidad = $("#codigoUnidad");
  const $codigoPrograma = $("#codigoPrograma");
  const $codigoProyecto = $("#codigoProyecto");
  const $codigoActividad = $("#codigoActividad");

  const $codigoUnidadOriginal = $("#codigoUnidadOriginal");
  const $codigoProgramaOriginal = $("#codigoProgramaOriginal");
  const $codigoProyectoOriginal = $("#codigoProyectoOriginal");
  const $codigoActividadOriginal = $("#codigoActividadOriginal");
  const $fechaInicio = $("#fechaInicio");

  const proyectosAll = [];
  $codigoProyecto.find("option").each(function () {
    const $option = $(this);
    const value = $option.val();
    if (!value) {
      return;
    }
    proyectosAll.push({
      value: value,
      text: $option.text(),
      programa: Number($option.data("programa")) || null,
    });
  });

  const actividadesAll = [];
  $codigoActividad.find("option").each(function () {
    const $option = $(this);
    const value = $option.val();
    if (!value) {
      return;
    }
    actividadesAll.push({
      value: value,
      text: $option.text(),
      programa: Number($option.data("programa")) || null,
    });
  });

  const unidadMap = buildMap($codigoUnidad);
  const programaMap = buildMap($codigoPrograma);
  const proyectoMap = buildMap($codigoProyecto, "programa");
  const actividadMap = buildMap($codigoActividad, "programa");

  renderProyectos(null);
  renderActividades(null);

  // buildMap hace un mapeo de los select para búsquedas rápidas
  function buildMap($select, extraAttr) {
    const map = {};
    $select.find("option").each(function () {
      const $option = $(this);
      const value = $option.val();
      if (!value) {
        return;
      }
      map[value] = {
        text: $option.text(),
      };
      if (extraAttr) {
        map[value][extraAttr] = $option.data(extraAttr) || null;
      }
    });
    return map;
  }

  //esto sirve para filtrar los proyectos y actividades según el programa seleccionado
  function renderProyectos(programaId, selected = null) {
    const current = selected || $codigoProyecto.val();
    $codigoProyecto.find("option:not(:first)").remove();
    proyectosAll
      .filter(function (item) {
        return !programaId || item.programa === Number(programaId);
      })
      .forEach(function (item) {
        const option = $("<option>")
          .val(item.value)
          .text(item.text)
          .attr("data-programa", item.programa);
        $codigoProyecto.append(option);
      });
    if (current && $codigoProyecto.find('option[value="' + current + '"]').length) {
      $codigoProyecto.val(current);
    } else {
      $codigoProyecto.val("");
    }
  }

  function renderActividades(programaId, selected = null) {
    const current = selected || $codigoActividad.val();
    $codigoActividad.find("option:not(:first)").remove();
    actividadesAll
      .filter(function (item) {
        return !programaId || item.programa === Number(programaId);
      })
      .forEach(function (item) {
        const option = $("<option>")
          .val(item.value)
          .text(item.text)
          .attr("data-programa", item.programa);
        $codigoActividad.append(option);
      });
    if (current && $codigoActividad.find('option[value="' + current + '"]').length) {
      $codigoActividad.val(current);
    } else {
      $codigoActividad.val("");
    }
  }

  function reiniciarCampos() {
    $form[0].reset();
    $form.find(":input").removeClass("is-invalid is-valid");
    $codigoUnidadOriginal.val("");
    $codigoProgramaOriginal.val("");
    $codigoProyectoOriginal.val("");
    $codigoActividadOriginal.val("");
    $fechaInicio.val("");
    renderProyectos(null);
    renderActividades(null);
  }

  $btnMostrarCrear.off("click.llaveOpen").on("click.llaveOpen", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const icono = $(".icon");
    icono.addClass("opened");
    $("#divDatos").show(500);
    $("#divTabla").hide(500);
  });

  $btnCancelar.on("click", function (e) {
    e.preventDefault();
    $(".icon").removeClass("opened");
    reiniciarCampos();
    $("#divDatos").hide(500);
    $("#divTabla").show(500);
  });

  $codigoPrograma.on("change", function () {
    const programaId = $(this).val() || null;
    renderProyectos(programaId);
    renderActividades(programaId);
  });

  function esEdicion() {
    return (
      $codigoUnidadOriginal.val() !== "" &&
      $codigoProgramaOriginal.val() !== "" &&
      $codigoProyectoOriginal.val() !== "" &&
      $codigoActividadOriginal.val() !== ""
    );
  }

  $btnGuardar.on("click", async function () {
    if (typeof $form.valid === "function" && !$form.valid()) {
      return;
    }

    if (esEdicion()) {
      await actualizarLlave();
    } else {
      await guardarLlave();
    }
  });

  /*=============================================
    INSERTA EN LA BD UNA NUEVA LLAVE PRESUPUESTARIA
    =============================================*/
  async function guardarLlave() {
    try {
      const datos = new FormData($form[0]);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/guardar",
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
          response.message || "Llave presupuestaria guardada correctamente"
        );
        await dt_llaves.ajax.reload(null, false);
        $btnCancelar.trigger("click");
      } else {
        MostrarMensaje("error", response.message || "Error al guardar");
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message || "No se pudo completar la operación"
      );
    }
  }

  /*=============================================
    ACTUALIZA UNA LLAVE PRESUPUESTARIA EXISTENTE
    =============================================*/
  async function actualizarLlave() {
    const objectBtn = $btnGuardar;
    try {
      IniciarSpiner(objectBtn);
      const datos = new FormData($form[0]);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/actualizar",
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
          response.message || "Llave presupuestaria actualizada"
        );
        await dt_llaves.ajax.reload(null, false);
        $btnCancelar.trigger("click");
      } else {
        throw new Error(response.message || response.respuesta || "Error desconocido");
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      const mensaje = errorData.message || "No se pudo completar la operación";
      await MostrarMensaje("error", mensaje);
    } finally {
      DetenerSpiner(objectBtn);
    }
  }

  /*=============================================
    CONSTRUYE LA COLUMNA DE ACCIONES PARA LA TABLA
    =============================================*/
  function construirBotones(row) {
    const keys =
      'data-unidad="' + row.CodigoUnidad + '" ' +
      'data-programa="' + row.CodigoPrograma + '" ' +
      'data-proyecto="' + row.CodigoProyecto + '" ' +
      'data-actividad="' + row.CodigoActividad + '"';

    const finalizarDisabled = row.FechaFin ? "disabled" : "";

    return (
      '<div class="btn-group" role="group">' +
      '<button type="button" class="btn btn-outline-warning btn-sm btnEditar" ' +
      keys +
      ' title="Editar"><i class="fa fa-pen-fancy"></i></button>' +
      '<button type="button" class="btn btn-warning btn-sm btnFinalizar font-weight-bold" ' +
      keys +
      " " + finalizarDisabled +
      ' title="Finalizar llave"><i class="fa fa-flag-checkered mr-1"></i>Finalizar</button>' +
      '<button type="button" class="btn btn-outline-danger btn-sm btnEliminar" ' +
      keys +
      ' title="Eliminar"><i class="fa fa-trash-alt"></i></button>' +
      "</div>"
    );
  }

  const dt_llaves = $("#tablaLlavesPresupuestarias").DataTable({
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/llave-presupuestaria/listar-todo",
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
        data: null,
        render: function (data) {
          if (!data) return "";
          const da = data.UnidadDa || "";
          const ue = data.UnidadUe || "";
          const desc = data.UnidadDescripcion || "";
          if (!da && !ue && !desc) {
            return unidadMap[data.CodigoUnidad]?.text || data.CodigoUnidad;
          }
          return "(" + da + "/" + ue + ") - " + desc;
        },
      },
      {
        className: "dt-small",
        data: null,
        render: function (data) {
          if (!data) return "";
          const codigo = data.ProgramaCodigo || "";
          const desc = data.ProgramaDescripcion || "";
          if (!codigo && !desc) {
            return programaMap[data.CodigoPrograma]?.text || data.CodigoPrograma;
          }
          return "(" + codigo + ") - " + desc;
        },
      },
      {
        className: "dt-small",
        data: null,
        render: function (data) {
          if (!data) return "";
          const codigo = data.ProyectoCodigo || "";
          const desc = data.ProyectoDescripcion || "";
          if (!codigo && !desc) {
            return proyectoMap[data.CodigoProyecto]?.text || data.CodigoProyecto;
          }
          return "(" + codigo + ") - " + desc;
        },
      },
      {
        className: "dt-small",
        data: null,
        render: function (data) {
          if (!data) return "";
          const codigo = data.ActividadCodigo || "";
          const desc = data.ActividadDescripcion || "";
          if (!codigo && !desc) {
            return actividadMap[data.CodigoActividad]?.text || data.CodigoActividad;
          }
          return "(" + codigo + ") - " + desc;
        },
      },
      {
        className: "dt-small",
        data: "Descripcion",
      },
      {
        className: "dt-small dt-right",
        data: "TechoPresupuestario",
        render: function (data, type) {
          if (data === null || data === undefined || data === "") {
            return "";
          }
          const valor = parseFloat(data);
          if (Number.isNaN(valor)) {
            return data;
          }
          return valor.toLocaleString("es-BO", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
      },
      {
        className: "dt-small dt-center dt-periodo",
        data: null,
        render: function (data) {
          if (!data) return "";
          const fechaInicio = data.FechaInicio ? data.FechaInicio.substring(0, 10) : "";
          const fechaFin = data.FechaFin ? data.FechaFin.substring(0, 10) : "";
          if (!fechaInicio && !fechaFin) {
            return "";
          }
          return (
            '<div class="text-nowrap">' +
            '<span class="font-weight-bold">Inicio:</span> ' + (fechaInicio || "—") + '<br>' +
            '<span class="font-weight-bold">Fin:</span> ' + (fechaFin || "—") +
            "</div>"
          );
        },
      },
      {
        className: "dt-small dt-estado dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoEstado",
        render: function (data, type, row) {
          const keys =
            'data-unidad="' + row.CodigoUnidad + '" ' +
            'data-programa="' + row.CodigoPrograma + '" ' +
            'data-proyecto="' + row.CodigoProyecto + '" ' +
            'data-actividad="' + row.CodigoActividad + '"';
          if (type !== "display") {
            return data;
          }
          if (row.CodigoEstado === ESTADO_VIGENTE) {
            return (
              '<button type="button" class="btn btn-outline-success btn-sm btnEstado" ' +
              keys +
              ' data-estado="' + ESTADO_VIGENTE + '">Vigente</button>'
            );
          }
          return (
            '<button type="button" class="btn btn-outline-danger btn-sm btnEstado" ' +
            keys +
            ' data-estado="' + ESTADO_CADUCO + '">Caducado</button>'
          );
        },
      },
      {
        className: "dt-small dt-center dt-acciones",
        orderable: false,
        searchable: false,
        data: null,
        render: function (data, type, row) {
          return type === "display" ? construirBotones(row) : data;
        },
      },
    ],
  });

  dt_llaves
    .on("order.dt search.dt", function () {
      let i = 1;
      dt_llaves
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  /*=============================================
    OBTIENE LOS IDENTIFICADORES A PARTIR DEL BOTÓN
    =============================================*/
  function obtenerPayloadDesdeBoton($btn) {
    return {
      codigoUnidad: $btn.data("unidad"),
      codigoPrograma: $btn.data("programa"),
      codigoProyecto: $btn.data("proyecto"),
      codigoActividad: $btn.data("actividad"),
    };
  }

  /*=============================================
    CAMBIA EL ESTADO (VIGENTE/CADUCADO) DE LA LLAVE
    =============================================*/
  $("#tablaLlavesPresupuestarias tbody").on("click", ".btnEstado", async function () {
    const $btn = $(this);
    const estadoActual = $btn.data("estado");
    const payload = obtenerPayloadDesdeBoton($btn);

    const datos = new FormData();
    datos.append("codigoUnidad", payload.codigoUnidad);
    datos.append("codigoPrograma", payload.codigoPrograma);
    datos.append("codigoProyecto", payload.codigoProyecto);
    datos.append("codigoActividad", payload.codigoActividad);

    try {
      IniciarSpiner($btn);
      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const nuevoEstado = estadoActual === ESTADO_VIGENTE ? ESTADO_CADUCO : ESTADO_VIGENTE;
        $btn.data("estado", nuevoEstado);
        if (nuevoEstado === ESTADO_VIGENTE) {
          $btn
            .removeClass("btn-outline-danger")
            .addClass("btn-outline-success")
            .text("Vigente");
        } else {
          $btn
            .removeClass("btn-outline-success")
            .addClass("btn-outline-danger")
            .text("Caducado");
        }
      } else {
        MostrarMensaje("error", GenerarMensajeError(response.message || response.respuesta));
      }
    } catch (error) {
      MostrarMensaje(
        "error",
        GenerarMensajeError(error.statusText + " >" + error.responseText)
      );
    } finally {
      DetenerSpiner($btn);
    }
  });

  /*=============================================
    FINALIZA LA LLAVE PRESUPUESTARIA (SET FECHA FIN)
    =============================================*/
  $("#tablaLlavesPresupuestarias tbody").on("click", ".btnFinalizar", async function () {
    const $btn = $(this);
    if ($btn.prop("disabled")) {
      return;
    }

    const payload = obtenerPayloadDesdeBoton($btn);

    const datos = new FormData();
    datos.append("codigoUnidad", payload.codigoUnidad);
    datos.append("codigoPrograma", payload.codigoPrograma);
    datos.append("codigoProyecto", payload.codigoProyecto);
    datos.append("codigoActividad", payload.codigoActividad);

    try {
      const confirmar = await Swal.fire({
        icon: "question",
        title: "Confirmar finalización",
        text: "La fecha fin se establecerá con el día actual. ¿Desea continuar?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, finalizar",
        cancelButtonText: "Cancelar",
      });

      if (!confirmar.isConfirmed) {
        return;
      }

      IniciarSpiner($btn);
      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/finalizar",
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
          response.message || "Llave presupuestaria finalizada"
        );
        await dt_llaves.ajax.reload(null, false);
      } else {
        MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message || "No se pudo completar la operación"
      );
    } finally {
      DetenerSpiner($btn);
    }
  });

  /*=============================================
    ELIMINA (SOFT DELETE) LA LLAVE PRESUPUESTARIA
    =============================================*/
  $("#tablaLlavesPresupuestarias tbody").on("click", ".btnEliminar", async function () {
    const $btn = $(this);
    const payload = obtenerPayloadDesdeBoton($btn);

    try {
      const confirmar = await Swal.fire({
        icon: "warning",
        title: "Confirmación de eliminación",
        text: "¿Está seguro de eliminar la llave seleccionada?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Borrar",
        cancelButtonText: "Cancelar",
      });

      if (!confirmar.isConfirmed) {
        return;
      }

      const datos = new FormData();
      datos.append("codigoUnidad", payload.codigoUnidad);
      datos.append("codigoPrograma", payload.codigoPrograma);
      datos.append("codigoProyecto", payload.codigoProyecto);
      datos.append("codigoActividad", payload.codigoActividad);

      IniciarSpiner($btn);
      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/eliminar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje("success", "La llave ha sido eliminada correctamente.");
        await dt_llaves.ajax.reload(null, false);
      } else {
        MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message || "No se pudo completar la operación"
      );
    } finally {
      DetenerSpiner($btn);
    }
  });

  /*=============================================
    BUSCA Y CARGA LA LLAVE PARA EDICIÓN
    =============================================*/
  $("#tablaLlavesPresupuestarias tbody").on("click", ".btnEditar", async function () {
    const $btn = $(this);
    const payload = obtenerPayloadDesdeBoton($btn);

    try {
      IniciarSpiner($btn);

      const datos = new FormData();
      datos.append("codigoUnidad", payload.codigoUnidad);
      datos.append("codigoPrograma", payload.codigoPrograma);
      datos.append("codigoProyecto", payload.codigoProyecto);
      datos.append("codigoActividad", payload.codigoActividad);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/llave-presupuestaria/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const llave = response.data || {};

        $codigoUnidadOriginal.val(llave.CodigoUnidad || "");
        $codigoProgramaOriginal.val(llave.CodigoPrograma || "");
        $codigoProyectoOriginal.val(llave.CodigoProyecto || "");
        $codigoActividadOriginal.val(llave.CodigoActividad || "");

        $codigoUnidad.val(llave.CodigoUnidad || "");
        $codigoPrograma.val(llave.CodigoPrograma || "").trigger("change");
        renderProyectos(llave.CodigoPrograma || null, llave.CodigoProyecto || null);
        renderActividades(llave.CodigoPrograma || null, llave.CodigoActividad || null);
        $codigoProyecto.val(llave.CodigoProyecto || "");
        $codigoActividad.val(llave.CodigoActividad || "");

        $("#descripcion").val(llave.Descripcion || "");
        $("#techoPresupuestario").val(llave.TechoPresupuestario || "");
    $fechaInicio.val(llave.FechaInicio ? llave.FechaInicio.substring(0, 10) : "");

        $btnMostrarCrear.trigger("click");
      } else {
        MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      const errorData = error.responseJSON || {};
      MostrarMensaje(
        "error",
        errorData.message || "No se pudo completar la operación"
      );
    } finally {
      DetenerSpiner($btn);
    }
  });
});
