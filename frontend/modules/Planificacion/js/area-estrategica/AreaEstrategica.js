$(document).ready(function () {
  $("#ingresoDatos").hide();
  $("#divTabla").show();

  /*const peiMap = {};
  $("#codigoPei option").each(function () {
    const val = $(this).attr("value");
    const text = $(this).text();
    if (val) peiMap[val] = text;
  });

  let dt_areas = $("#tablaListaAreas").DataTable({
    autoWidth: false,
    ajax: {
      method: "POST",
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      url: "index.php?r=Planificacion/area-estrategica/listar-todo",
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
      const colIdx = 1; // Columna PEI

      function escapeRegex(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      }

      function buildPeiFilter() {
        const headerCell = $(api.column(colIdx).header());
        let $select = headerCell.find("select#filtroCodigoPei");
        const prevVal = $select.length ? ($select.val() || "") : "";
        if ($select.length === 0) {
          $select = $(
            '<select id="filtroCodigoPei"><option value="">PEI...</option></select>'
          );
          headerCell.append($select);
        }

        const dataFks = api.column(colIdx, { search: "none" }).data().toArray();
        const uniqueFks = Array.from(new Set(dataFks));
        const codes = uniqueFks
          .map(function (fk) {
            const text = peiMap[fk] || ""; // Ej: "(100) - Descripción"
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
          $select.append('<option value="' + code + '">' + code + '</option>');
        });

        // Restaurar selección previa si sigue siendo válida
        if (prevVal && codes.indexOf(prevVal) !== -1) {
          $select.val(prevVal);
        } else if (prevVal && codes.indexOf(prevVal) === -1) {
          $select.val("");
        }

        $select.off("change").on("change", function () {
          const val = $(this).val();
          if (!val) {
            api.column(colIdx).search("", true, false).draw();
          } else {
            
            const pattern = "^(\\(" + escapeRegex(val) + "\\)|" + escapeRegex(val) + ")";
            api.column(colIdx).search(pattern, true, false).draw();
          }
        });

        headerCell.off("click.filtroPei").on("click.filtroPei", function (e) {
          if ($(e.target).is('select')) return; // no interferir cuando se usa el select
          const $sel = $(this).find('select#filtroCodigoPei');
          if ($sel.length) {
            if ($sel.val() !== "") {
              $sel.val("").trigger('change');
            } else {
              
              api.column(colIdx).search("", true, false).draw();
            }
          }
        });
      }

      buildPeiFilter();
      
      $('#tablaListaAreas').on('draw.dt', function () {
        buildPeiFilter();
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
        data: "CodigoPei", // FK
        orderable: false,
        render: function (data, type) {
          if (type === "display" || type === "filter") {
            return peiMap[data] || data || "";
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
        className: "dt-small dt-acciones dt-center",
        orderable: false,
        searchable: false,
        data: "CodigoAreaEstrategica",
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

  dt_areas
    .on("order.dt search.dt", function () {
      let i = 1;
      dt_areas
        .cells(null, 0, { search: "applied", order: "applied" })
        .every(function () {
          this.data(i++);
        });
    })
    .draw();*/

  function reiniciarCampos() {
    $("#formAreaEstrategica *")
      .filter(":input")
      .each(function () {
        $(this).removeClass("is-invalid is-valid");
      });
    $("#codigoAreaEstrategica").val("");
    $("#codigoPei").val("").trigger("change");
    $("#Codigo").val("");
    $("#Descripcion").val("");
  }



  $("#btnCancelar").click(function () {
    $(".icon").toggleClass("opened");
    reiniciarCampos();
    $("#ingresoDatos").hide(500);
    $("#divTabla").show(500);
  });

  $("#btnGuardar").click(function () {
    if (typeof $("#formAreaEstrategica").valid === "function" && !$("#formAreaEstrategica").valid()) {
      return;
    }
    if ($("#codigoAreaEstrategica").val() === "") {
      guardarArea();
    } else {
      actualizarArea();
    }
  });

  // Guardar
  async function guardarArea() {
    try {
      let datos = new FormData();
      datos.append("pei_id", $("#codigoPei").val());
      datos.append("codigo", $("#Codigo").val());
      datos.append("descripcion", $("#Descripcion").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/area-estrategica/guardar",
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
          response.message || "Área Estratégica guardada correctamente"
        );
        await dt_areas.ajax.reload(null, false);
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

  // Actualizar
  async function actualizarArea() {
    const objectBtn = $("#btnGuardar");
    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoAreaEstrategica", $("#codigoAreaEstrategica").val());
      datos.append("pei_id", $("#codigoPei").val());
      datos.append("codigo", $("#Codigo").val());
      datos.append("descripcion", $("#Descripcion").val());

      const response = await $.ajax({
        url: "index.php?r=Planificacion/area-estrategica/actualizar",
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
          await dt_areas.ajax.reload(null, false);
          $("#btnCancelar").click();
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
        dt_areas.ajax.reload(null, false);
      }
    } finally {
      DetenerSpiner(objectBtn);
    }
  }

  /* =============================================
     * CAMBIA EL ESTADO DEL REGISTRO
     * =============================================
     */
  $(document).on('click', 'tbody #btnEstado', function(){
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

  $("#tablaListaAreas tbody").on("click", ".btnEliminar", async function () {
    const objectBtn = $(this);
    const codigo = objectBtn.attr("codigo");

    try {
      const resultado = await Swal.fire({
        icon: "warning",
        title: "Confirmación de eliminación",
        text: "¿Está seguro de eliminar el registro?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Borrar",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
      });

      if (!resultado.isConfirmed) return;

      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoAreaEstrategica", codigo);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/area-estrategica/eliminar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        await MostrarMensaje("success", "El registro ha sido borrado correctamente.");
        await dt_areas.ajax.reload(null, false);
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al eliminar:", error);

      let errorMessage = "Error al eliminar el registro";
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

  $("#tablaListaAreas tbody").on("click", ".btnEditar", async function () {
    const objectBtn = $(this);
    const codigo = objectBtn.attr("codigo");

    try {
      IniciarSpiner(objectBtn);

      const datos = new FormData();
      datos.append("codigoAreaEstrategica", codigo);

      const response = await $.ajax({
        url: "index.php?r=Planificacion/area-estrategica/buscar",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
      });

      if (response.success || response.respuesta === RTA_CORRECTO) {
        const reg = response.data || response.area;

        $("#codigoAreaEstrategica").val(reg.CodigoAreaEstrategica || "");
        $("#codigoPei").val(reg.CodigoPei || "").trigger("change");
        $("#Codigo").val(reg.Codigo || "");
        $("#Descripcion").val(reg.Descripcion || "");

        $("#btnMostrarCrear").trigger("click");
      } else {
        await MostrarMensaje(
          "error",
          GenerarMensajeError(response.message || response.respuesta)
        );
      }
    } catch (error) {
      console.error("Error al buscar:", error);

      let errorMessage = "Error al cargar los datos";
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
