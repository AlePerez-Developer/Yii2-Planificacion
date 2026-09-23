<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\UsuarioAsignacionForm;
use app\modules\Planificacion\formModels\UsuarioSeguridadForm;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use common\models\seguridad\EstadosPoa;
use common\models\seguridad\Modulo;
use common\models\seguridad\Usuario;
use common\models\seguridad\UsuarioModulo;
use common\models\seguridad\UsuarioUnidadGestionEstado;
use Yii;
use yii\db\Expression;

class UsuarioSeguridadService
{
    public function listarTodo(): array
    {
        $data = Usuario::find()->alias('U')
            ->select([
                'U.IdUsuario',
                'U.IdPersona',
                'U.CodigoUsuario',
                'U.Nick',
                'U.TokenPortal',
                'U.CodigoEstado',
                'NombrePersona' => new Expression("CONCAT(P.Nombres, ' ', ISNULL(P.Paterno, ''), ' ', ISNULL(P.Materno, ''))"),
            ])
            ->leftJoin(['P' => 'Personas'], 'P.IdPersona = U.IdPersona')
            ->andWhere(['<>', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['U.CodigoUsuario' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarCatalogos(): array
    {
        return ResponseHelper::success([
            'modulos' => Modulo::find()
                ->select(['IdModulo', 'Nombre'])
                ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->orderBy(['Orden' => SORT_ASC])
                ->asArray()
                ->all(),
            'gestiones' => PeiGestion::find()
                ->select(['IdGestion', 'Gestion'])
                ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->orderBy(['Gestion' => SORT_DESC])
                ->asArray()
                ->all(),
            'estadosPoa' => EstadosPoa::find()
                ->select(['IdEstadoPoa', 'Codigo', 'Descripcion'])
                ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->orderBy(['Orden' => SORT_ASC, 'Codigo' => SORT_ASC])
                ->asArray()
                ->all(),
            'unidades' => UnidadEjecutora::listAll()
                ->andWhere(['u.CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->asArray()
                ->all(),
        ]);
    }

    public function guardar(UsuarioSeguridadForm $form): array
    {
        $modelo = new Usuario([
            'IdPersona' => trim($form->idPersona),
            'CodigoUsuario' => trim($form->codigoUsuario),
            'Nick' => trim($form->nick) ?: null,
            'TokenPortal' => trim($form->tokenPortal) ?: null,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
        ]);
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Usuario guardado.');
    }

    public function actualizar(string $id, UsuarioSeguridadForm $form): array
    {
        $modelo = $this->obtenerModelo($id);
        $modelo->IdPersona = trim($form->idPersona);
        $modelo->CodigoUsuario = trim($form->codigoUsuario);
        $modelo->Nick = trim($form->nick) ?: null;
        $modelo->TokenPortal = trim($form->tokenPortal) ?: null;
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Usuario actualizado.');
    }

    public function obtener(string $id): array
    {
        $modelo = $this->obtenerModelo($id);
        $modulos = UsuarioModulo::find()
            ->select('IdModulo')
            ->where(['IdUsuario' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->column();

        return ResponseHelper::success([
            'IdUsuario' => $modelo->IdUsuario,
            'IdPersona' => $modelo->IdPersona,
            'CodigoUsuario' => $modelo->CodigoUsuario,
            'Nick' => $modelo->Nick,
            'TokenPortal' => $modelo->TokenPortal,
            'modulos' => $modulos,
        ]);
    }

    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModelo($id);
        $modelo->CodigoEstado = $modelo->CodigoEstado === Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo->CodigoEstado, 'Estado actualizado.');
    }

    public function guardarModulos(string $id, array $modulos): array
    {
        $this->obtenerModelo($id);
        $usuarioRegistra = Yii::$app->user->id;
        UsuarioModulo::updateAll(
            ['CodigoEstado' => Estado::ESTADO_ELIMINADO],
            ['IdUsuario' => $id]
        );
        foreach ($modulos as $idModulo) {
            $idModulo = (string)$idModulo;
            if ($idModulo === '') {
                continue;
            }
            $existente = UsuarioModulo::findOne(['IdUsuario' => $id, 'IdModulo' => $idModulo]);
            if ($existente) {
                $existente->CodigoEstado = Estado::ESTADO_VIGENTE;
                $existente->Usuario = $usuarioRegistra;
                $this->guardarUsuarioModulo($existente);
                continue;
            }
            $nuevo = new UsuarioModulo([
                'IdUsuario' => $id,
                'IdModulo' => $idModulo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
                'Usuario' => $usuarioRegistra,
            ]);
            $this->guardarUsuarioModulo($nuevo);
        }
        return ResponseHelper::success(true, 'Módulos actualizados.');
    }

    public function listarAsignaciones(string $id): array
    {
        $this->obtenerModelo($id);
        $data = UsuarioUnidadGestionEstado::find()->alias('A')
            ->select([
                'A.IdUsuarioUnidadGestionEstado',
                'A.IdUnidadEjecutora',
                'A.IdGestion',
                'A.IdEstadoPoa',
                'A.CodigoEstado',
                'Unidad' => new Expression("CONCAT(D.Da, '-', UE.Ue, ' ', UE.Descripcion)"),
                'Gestion' => 'G.Gestion',
                'EstadoPoa' => 'E.Codigo',
            ])
            ->innerJoin(['UE' => UnidadEjecutora::tableName()], 'UE.IdUnidadEjecutora = A.IdUnidadEjecutora')
            ->innerJoin(['D' => 'Das'], 'D.IdDa = UE.IdDa')
            ->innerJoin(['G' => PeiGestion::tableName()], 'G.IdGestion = A.IdGestion')
            ->innerJoin(['E' => EstadosPoa::tableName()], 'E.IdEstadoPoa = A.IdEstadoPoa')
            ->where(['A.IdUsuario' => $id])
            ->andWhere(['<>', 'A.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['D.Da' => SORT_ASC, 'UE.Ue' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardarAsignacion(string $id, UsuarioAsignacionForm $form): array
    {
        $this->obtenerModelo($id);
        $existe = UsuarioUnidadGestionEstado::find()
            ->where([
                'IdUsuario' => $id,
                'IdUnidadEjecutora' => $form->idUnidadEjecutora,
                'IdGestion' => $form->idGestion,
                'IdEstadoPoa' => $form->idEstadoPoa,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();
        if ($existe) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La asignación ya existe.',
                400
            );
        }

        $modelo = new UsuarioUnidadGestionEstado([
            'IdUsuario' => $id,
            'IdUnidadEjecutora' => $form->idUnidadEjecutora,
            'IdGestion' => $form->idGestion,
            'IdEstadoPoa' => $form->idEstadoPoa,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'Usuario' => Yii::$app->user->id,
        ]);
        if (!$modelo->save()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $modelo->getErrors(),
                400
            );
        }
        return ResponseHelper::success($modelo, 'Asignación guardada.');
    }

    public function eliminarAsignacion(string $idAsignacion): array
    {
        $modelo = UsuarioUnidadGestionEstado::find()
            ->where(['IdUsuarioUnidadGestionEstado' => $idAsignacion])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la asignación.',
                404
            );
        }
        $modelo->CodigoEstado = Estado::ESTADO_ELIMINADO;
        $modelo->save(false);
        return ResponseHelper::success($modelo, 'Asignación eliminada.');
    }

    private function obtenerModelo(string $id): Usuario
    {
        $modelo = Usuario::find()
            ->where(['IdUsuario' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el usuario.',
                404
            );
        }
        return $modelo;
    }

    private function guardarModelo(Usuario $modelo): void
    {
        if (!$modelo->save()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $modelo->getErrors(),
                400
            );
        }
    }

    private function guardarUsuarioModulo(UsuarioModulo $modelo): void
    {
        if (!$modelo->save()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $modelo->getErrors(),
                400
            );
        }
    }
}
