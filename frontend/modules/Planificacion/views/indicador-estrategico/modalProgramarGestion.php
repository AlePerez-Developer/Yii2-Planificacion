<!-- Modal -->
<div class="modal fade programarIndicadorEstrategicoGestion" id="programarIndicadorEstrategicoGestion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Programacion de indicadores estrategicos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="gestionBody">
                    <div class="card">
                        <div class="card-header border-primary">
                            <div class="container text-center">
                                <div class="row">
                                    <div class="col-12">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="objetivoEstrategico" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="objetivoEstrategico" style="font-size: 14px">Objetivo estrategico</label>
                                        </form>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="codigoIndicadorModal" style="font-size: 12px; background-color: firebrick; color: white" placeholder="" value="" disabled>
                                            <label for="codigoIndicadorModal" style="font-size: 13px; color: white;font-weight: bold">Ind</label>
                                        </form>
                                    </div>
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="metaIndicadorModal" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="metaIndicadorModal" style="font-size: 13px">Total</label>
                                        </form>
                                    </div>
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="metaProgIndicadorModal" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="metaProgIndicadorModal" style="font-size: 13px">Prog.</label>
                                        </form>
                                    </div>
                                    <div class="col-9">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="descripcionIndicador" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="descripcionIndicador" style="font-size: 14px">Descripcion del indicador</label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tablaIndicadoresGestion" class="table-bordered table-striped dt-responsive table-sm tablaIndicadoresGestion" style="width: 100%" >
                                <thead>
                                <th>#</th>
                                <th>Gestion</th>
                                <th>Meta</th>
                                <th>Indicador</th>
                                <th>Acciones</th>
                                <th>Prog. Apertura</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="unidadBody" style="display: none">
                    <div class="card">
                        <div class="card-header border-primary">
                            <div class="container text-center">
                                <div class="row">
                                    <div class="col-12">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="objetivoEstrategicoUnidad" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="objetivoEstrategicoUnidad" style="font-size: 14px">Objetivo estrategico</label>
                                        </form>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="gestionUnidad" style="font-size: 12px; background-color: firebrick; color: white" placeholder="" value="" disabled>
                                            <label for="gestionUnidad" style="font-size: 13px; color: white;font-weight: bold">Gestion</label>
                                        </form>
                                    </div>
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="metaTotalGestion" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="metaTotalGestion" style="font-size: 13px">Total</label>
                                        </form>
                                    </div>
                                    <div class="col-1">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="metaProgUnidad" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="metaProgUnidad" style="font-size: 13px">Prog.</label>
                                        </form>
                                    </div>
                                    <div class="col-9">
                                        <form class="form-floating">
                                            <input type="text" class="form-control" id="descripcionIndicadorUnidad" style="font-size: 12px" placeholder="" value="" disabled>
                                            <label for="descripcionIndicadorUnidad" style="font-size: 14px">Descripcion del indicador</label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                <div class="row align-items-center justify-content-center">
                                    <div class="col-8">
                                        <div class="form-group">
                                            <label for="codigoObjEstrategico">Seleccione el objetivo estrategico</label>
                                            <select class="form-control objEstrategico" id="codigoObjEstrategico" name="codigoObjEstrategico" >
                                                <option></option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <button id="agregarUnidad" class="btn btn-outline-success"><i class="fa fa-plus"></i> Agregar</button>
                                    </div>
                                </div>
                            </div>
                            <table id="tablaIndicadoresUnidad" class="table-bordered table-striped dt-responsive table-sm tablaIndicadoresUnidad" style="width: 100%" >
                                <thead>
                                <th>#</th>
                                <th>Gestion</th>
                                <th>DaUe</th>
                                <th>Da</th>
                                <th>Ue</th>
                                <th>Descripcion</th>
                                <th>Meta</th>
                                <th>Acciones</th>
                                </thead>
                            </table>
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
