$(document).ready(function () {
  $("#ingresoDatos").hide();
  $("#divTabla").show();

  const programaMap = {};
  $("#codigoPrograma option").each(function () {
    const val = $(this).attr("value");
    const text = $(this).text();
    if (val) programaMap[val] = text;
  });

  let dt_proyecto = $("#tablaListaProyectos").DataTable({
    autoWidth: false,
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/proyecto/listar-todo",
      dataSrc: "data",
      error: function (xhr, ajaxOptions, thrownError) {
        MostrarMensaje(
          "error",
          GenerarMensajeError(thrownError + " >" + xhr.responseText)
        );
      },
    },
    initComplete: function () {
      const api = this.api();
      const colIdx = 1; 

      function escapeRegex(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      }

      function buildProgramaFilter() {
        const headerCell = $(api.column(colIdx).header());
        let $select = headerCell.find("select#filtroCodigoPrograma");
        const prevVal = $select.length ? $select.val() || "" : "";
        if ($select.length === 0) {
          $select = $(
            '<select id="filtroCodigoPrograma"><option value="">Programa...</option></select>'
          );
          headerCell.append($select);
        }

        const dataFks = api.column(colIdx, { search: "none" }).data().toArray();
        const uniqueFks = Array.from(new Set(dataFks));
        const codes = uniqueFks
          .map(function (fk) {
            const text = programaMap[fk] || ""; // Ej: "(100) - Descripción"
            let code = null;
            if (text) {
              const m = text.match(/^\(([^)]+)\)/);
              code = m ? m[1] : null;
            }
            if (!code && fk !== undefined && fk !== null && fk !== "") {
              code = String(fk);
            }
            return code;
          })
          .filter(Boolean)
          .sort(function (a, b) {
            const an = Number(a),
              bn = Number(b);
            if (!isNaN(an) && !isNaN(bn)) return an - bn;
            return a.localeCompare(b);
          });

        $select.find('option:not([value=""])').remove();
        codes.forEach(function (code) {
          $select.append('<option value="' + code + '">' + code + "</option>");
        });
        if (prevVal && codes.indexOf(prevVal) !== -1) {
          $select.val(prevVal);
        } else if (prevVal && codes.indexOf(prevVal) === -1) {
          $select.val("");
        }

        // Evento de filtro
        $select.off("change").on("change", function () {
          const val = $(this).val();
          if (!val) {
            api.column(colIdx).search("", true, false).draw();
          } else {
          
            const pattern =
              "^(\\(" + escapeRegex(val) + "\\)|" + escapeRegex(val) + ")";
            api.column(colIdx).search(pattern, true, false).draw();
          }
        });


        headerCell
          .off("click.filtroPrograma")
          .on("click.filtroPrograma", function (e) {
            if ($(e.target).is("select")) return; 
            const $sel = $(this).find("select#filtroCodigoPrograma");
            if ($sel.length) {
              if ($sel.val() !== "") {
                $sel.val("").trigger("change");
              } else {
                api.column(colIdx).search("", true, false).draw();
              }
            }
          });
      }

      buildProgramaFilter();

      $("#tablaListaProyectos").on("draw.dt", function () {
        buildProgramaFilter();
      });
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
        data: "Programa", 
        orderable: false,
        render: function (data, type) {

          if (type === "display" || type === "filter") {
            return programaMap[data] || data || "";
          }
          return data;
        },
        width: 200,
      },
      {
        className: "dt-small",
        data: "Codigo",
        width: 80,
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
                row.CodigoProyecto +
                '" estado="' +
                ESTADO_VIGENTE +
                '">Vigente</button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm btnEstado" codigo="' +
                row.CodigoProyecto +
                '" estado="' +
                ESTADO_CADUCO +
                '">Caducado</button>';
        },
      },
      {
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoProyecto",
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

  dt_proyecto
    .on("order.dt search.dt", function () {
      let i = 1;
      dt_proyecto
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();

  function reiniciarCampos() {
    $("#formProyectos *")
      .filter(":input")
      .each(function () {
        $(this).removeClass("is-invalid is-valid");
      });
    $("#codigoProyecto").val(""); // almacena CodigoProyecto
    $("#codigoPrograma").val("").trigger("change");
    $("#Codigo").val("");
    $("#Descripcion").val("");
  }

  $("#btnMostrarCrear")
    .off("click.proyectoOpen")
    .on("click.proyectoOpen", function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      const icono = $(".icon");
      icono.addClass("opened");
      $("#ingresoDatos").show(500);
      $("#divTabla").hide(500);
    });

  $("#btnCancelar").click(function () {
    $(".icon").removeClass("opened");
    reiniciarCampos();
    $("#ingresoDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").click(function () {
    if (
      typeof $("#formProyectos").valid === "function" &&
      !$("#formProyectos").valid()
    ) {
      return;
    }
    if ($("#codigoProyecto").val() === "") {
      guardarProyecto();
    } else {
      actualizarProyecto();
    }
  });

  // Crear
  async function guardarProyecto() {
    try {
      let datos = new FormData();
      datos.append("programa_id", $("#codigoPrograma").val());
      datos.append("codigo", $("#Codigo").val());
      datos.append("descripcion", $("#Descripcion").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/proyecto/guardar",
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
          response.message || "Proyecto guardado correctamente"
        );
        await dt_proyecto.ajax.reload(function (){
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

  // Actualizar
  async function actualizarProyecto() {
    const objectBtn = $("#btnGuardar");
    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoProyecto", $("#codigoProyecto").val());
      datos.append("programa_id", $("#codigoPrograma").val());
      datos.append("codigo", $("#Codigo").val());
      datos.append("descripcion", $("#Descripcion").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/proyecto/actualizar",
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
          response.message || "Actualización exitosa"
        );

        try {
          await dt_proyecto.ajax.reload(function (){
            $("#btnCancelar").click();
          });
        } catch (reloadError) {
          console.error("Error recargando tabla:", reloadError);
          window.location.reload();
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
        dt_proyecto.ajax.reload(null, false);
      }
    } finally {
      DetenerSpiner(objectBtn);
    }
  }
  // Cambiar estado
  $("#tablaListaProyectos tbody").on("click", ".btnEstado", async function () {
    const objectBtn = $(this);
    const codigoProyecto = objectBtn.attr("codigo");
    const estadoProyecto = objectBtn.attr("estado");

    const datos = new FormData();
    datos.append("codigoProyecto", codigoProyecto);

    try {
      IniciarSpiner(objectBtn);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/proyecto/cambiar-estado",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        if (estadoProyecto === ESTADO_VIGENTE) {
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

  // Eliminar
  $("#tablaListaProyectos tbody").on(
    "click",
    ".btnEliminar",
    async function () {
      const objectBtn = $(this);
      const codigoProyecto = objectBtn.attr("codigo");

      try {
        const resultado = await Swal.fire({
          icon: "warning",
          title: "Confirmación de eliminación",
          text: "¿Está seguro de eliminar el proyecto elegido?",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Borrar",
          cancelButtonColor: "#d33",
          cancelButtonText: "Cancelar",
        });

        if (!resultado.isConfirmed) return;

        IniciarSpiner(objectBtn);

        const datos = new FormData();
        datos.append("codigoProyecto", codigoProyecto);

        const response = await $.ajax({
          url: "index.php?r=Planificacion/proyecto/eliminar",
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
            "El proyecto ha sido borrado correctamente."
          );
          await dt_proyecto.ajax.reload(null, false);
        } else {
          await MostrarMensaje(
            "error",
            GenerarMensajeError(response.message || response.respuesta)
          );
        }
      } catch (error) {
        console.error("Error al eliminar proyecto:", error);

        let errorMessage = "Error al eliminar el proyecto";
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
    }
  );

  // Buscar (editar)
  $("#tablaListaProyectos tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigoProyecto = objectBtn.attr("codigo");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoProyecto", codigoProyecto);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/proyecto/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const proyecto = response.data || response.proyecto;

        $("#codigoProyecto").val(proyecto.CodigoProyecto || "");
        $("#codigoPrograma")
          .val(proyecto.Programa || "")
          .trigger("change");
        $("#Codigo").val(proyecto.Codigo || "");
        $("#Descripcion").val(proyecto.Descripcion || "");

        $("#btnMostrarCrear").trigger("click");
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al buscar proyecto:", error);

      let errorMessage = "Error al cargar los datos del proyecto";
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
