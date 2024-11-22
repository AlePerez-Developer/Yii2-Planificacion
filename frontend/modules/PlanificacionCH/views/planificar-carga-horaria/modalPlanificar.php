<!-- Modal -->
<div class="modal fade modalPlanificar" id="modalPlanificar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Planificacion de carga horaria</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header border-primary">
                        <div class="container text-center">
                            <div class="row">
                                <div class="col-12">
                                    <form class="form-floating">
                                        <input type="text" class="form-control" id="inpFacultad" style="font-size: 12px" placeholder="" value="" disabled>
                                        <label for="inpFacultad" style="font-size: 14px">Facultad</label>
                                    </form>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-2">
                                    <form class="form-floating">
                                        <input type="text" class="form-control" id="inpSede" style="font-size: 12px;" placeholder="" value="" disabled>
                                        <label for="inpSede" style="font-size: 13px;">Sede</label>
                                    </form>
                                </div>
                                <div class="col-1">
                                    <form class="form-floating">
                                        <input type="text" class="form-control" id="inpPlan" style="font-size: 12px" placeholder="" value="" disabled>
                                        <label for="inpPlan" style="font-size: 13px">Plan</label>
                                    </form>
                                </div>
                                <div class="col-1">
                                    <form class="form-floating">
                                        <input type="text" class="form-control" id="inpCurso" style="font-size: 12px" placeholder="" value="" disabled>
                                        <label for="inpCurso" style="font-size: 13px">Curso</label>
                                    </form>
                                </div>
                                <div class="col-8">
                                    <form class="form-floating">
                                        <input type="text" class="form-control" id="inpMateria" style="font-size: 12px" placeholder="" value="" disabled>
                                        <label for="inpMateria" style="font-size: 14px">Materia</label>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#teoria" type="button" role="tab" aria-controls="home" aria-selected="true">Grupos de Teoria</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#practica" type="button" role="tab" aria-controls="profile" aria-selected="false">Grupos de Practica</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#laboratorio" type="button" role="tab" aria-controls="contact" aria-selected="false">Grupos de Laboratorio</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="teoria" role="tabpanel" aria-labelledby="home-tab">
                                <div class="card">
                                    <div class="card-header">
                                        <button id="btnTeoria" type="button" class="btn btn-info form-control">Agregar Grupo Nuevo</button>
                                    </div>
                                    <div class="card-body">
                                        <table id="tablaGrpTeoria" class="table table-bordered table-striped dt-responsive " style="width: 100%" >
                                            <thead>
                                                <th style="text-align: center; vertical-align: middle;">#</th>
                                                <th style="text-align: center; vertical-align: middle;">Sigla</th>
                                                <th style="text-align: center; vertical-align: middle;">Grupo</th>
                                                <th style="text-align: center; vertical-align: middle;">Ci</th>
                                                <th style="text-align: center; vertical-align: middle;">Docente</th>
                                                <th style="text-align: center; vertical-align: middle;">Editar</th>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="practica" role="tabpanel" aria-labelledby="profile-tab">

                            </div>
                            <div class="tab-pane fade" id="laboratorio" role="tabpanel" aria-labelledby="contact-tab">

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="gestionFooter">
                    <button type="button" id="cerrarModal" class='btn btn-outline-danger' data-bs-dismiss="modal"><i class='fa fa-times-circle'></i> Cerrar </button>
                </div>
                <div id="unidadFooter" style="display: none">
                    <button type="button" id="cerrarModalUnidad" class='btn btn-outline-danger'><i class='fa fa-times-circle'></i> Volver </button>
                </div>
            </div>
        </div>
    </div>
</div>
