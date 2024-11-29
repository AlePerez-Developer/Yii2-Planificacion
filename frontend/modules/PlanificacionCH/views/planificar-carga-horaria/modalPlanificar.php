<!-- Modal -->
<div class="modal fade modalPlanificar" id="modalPlanificar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h1 class="modal-title fs-5">Planificacion de carga horaria - Crear grupo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <div class="card " style="width: 60rem;">
                    <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                    <div class="card-body">
                        <input type="text" id="codigoCrear"  disabled hidden>
                        <form id="formCargaHorariaPropuesta" action="" method="post">
                            <div class="form-group">
                                <label for="docentes" style="font-size: 12px">Seleccione el docente</label>
                                <select id="docentes" name="docentes" style="width: 100%">
                                    <option></option>
                                </select>

                            </div>
                            <div class="form-group">
                                <label for="grupo" class="control-label">Grupo</label>
                                <input type="text" class="form-control input-sm" style="width:20%" id="grupo" name="grupo">
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><span class='fa fa-check-circle'></span> Guardar </button>
                <button type="button" id="cerrarModal" class='btn btn-outline-danger' data-bs-dismiss="modal"><i class='fa fa-times-circle'></i> Cancelar </button>
            </div>
        </div>
    </div>
</div>
