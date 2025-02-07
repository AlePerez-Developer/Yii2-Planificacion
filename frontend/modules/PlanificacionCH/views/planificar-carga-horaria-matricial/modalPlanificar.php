<style>
    .docNombreList{
        font-size: 14px;
        font-family: bold;
    }
    .docNombre{
        font-size: 14px;
        font-family: bold;
    }
    img {
        width: 100px;
        height: 100px;
    }

</style>
<!-- Modal -->
<div class="modal fade modalPlanificar" id="modalPlanificar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h1 class="modal-title fs-5">Planificacion de carga horaria - grupos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <div class="card " style="width: 60rem;">
                    <div class="card-header bg-gradient-primary">Datos de grupo</div>
                    <div class="card-body">
                        <input type="text" id="codigoCrear"  disabled hidden>

                        <form id="formCargaHorariaPropuestaMatricial" action="" method="post">


                            <div class="form-row">

                                <div class="form-group col-sm-2" >
                                    <img id="docImage" class="form-control" src="img/logo.jpg" alt="Imagen de perfil Docente" style="height: 100%" >
                                </div>

                                <div class="form-group col-sm-10">
                                    <div class="form-row">
                                        <div class="form-group col-12">
                                            <label for="docentes" class="form-label">Seleccione el docente</label>
                                            <select id="docentes" name="docentes" class="form-control">
                                                <option></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-2"> <label class="form-label">Ci: </label> </div>
                                        <div class="form-group col-10"><label id="lblCi">5493446 Ch</label></div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-2"> <label class="form-label">Condicion: </label></div>
                                        <div class="form-group col-10"><label id="lblCondicion">docente contrato</label></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row"">
                                <div class="col-sm-6">
                                    <label for="carreras" style="font-size: 12px">Seleccione la carrera</label>
                                    <select id="carreras" name="carreras" style="width: 95%">
                                        <option></option>
                                    </select>
                                </div>

                                <div class="col-sm-6" id="divPlanes" hidden>
                                    <label for="planes" style="font-size: 12px">Seleccione la materia</label>
                                    <select id="planes" name="planes" class="form-control" style="width: 95%">
                                        <option></option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="grupo" class="control-label">Grupo</label>
                                    <input type="text" class="form-control input-sm" style="width:20%" id="grupo" name="grupo">
                                </div>
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
