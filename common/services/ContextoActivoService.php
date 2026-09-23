<?php

namespace common\services;

use common\models\Estado;
use common\models\seguridad\Modulo;
use common\models\seguridad\UsuarioContextoActivo;
use Yii;
use yii\helpers\ArrayHelper;

class ContextoActivoService
{
    public function obtenerOpcionesNavbar(): array
    {
        $usuario = Yii::$app->user->identity;
        $contexto = Yii::$app->userContext->contexto();
        $moduloActivo = Yii::$app->userContext->moduloActivo();

        $gestiones = [];
        $estadosPoa = [];
        $unidades = [];

        if ($moduloActivo && $usuario) {
            $gestiones = ArrayHelper::map(
                $usuario->getGestionesPermitidas(),
                'IdGestion',
                'Gestion'
            );

            if ($contexto?->IdGestion) {
                $estadosPoa = ArrayHelper::map(
                    $usuario->getEstadosPoaPermitidos($contexto->IdGestion),
                    'IdEstadoPoa',
                    'Codigo'
                );
            }

            if ($contexto?->IdGestion && $contexto?->IdEstadoPoa) {
                $unidades = ArrayHelper::map(
                    $usuario->getLlavesPermitidas($contexto->IdGestion, $contexto->IdEstadoPoa),
                    'IdUnidadEjecutora',
                    'Compuesto'
                );
            }
        }

        return [
            'gestiones' => $gestiones,
            'estadosPoa' => $estadosPoa,
            'unidades' => $unidades,
        ];
    }

    public function seleccionarModulo(string $id): ?Modulo
    {
        $contexto = $this->obtenerOCrearContexto();
        $contexto->IdModulo = $id;
        $contexto->CodigoEstado = Estado::ESTADO_VIGENTE;
        $contexto->FechaHoraActualizacion = date('d/m/Y H:i:s');
        $this->guardarContexto($contexto);

        return Modulo::findOne($id);
    }

    public function cambiarGestion(string $id): void
    {
        $contexto = $this->obtenerContexto();
        if (!$contexto) {
            return;
        }

        $contexto->IdGestion = $id;
        $contexto->IdEstadoPoa = null;
        $contexto->IdUnidadEjecutora = null;
        $contexto->FechaHoraActualizacion = date('d/m/Y H:i:s');
        $this->guardarContexto($contexto);
    }

    public function cambiarEstadoPoa(string $id): void
    {
        $contexto = $this->obtenerContexto();
        if (!$contexto) {
            return;
        }

        $contexto->IdEstadoPoa = $id;
        $contexto->IdUnidadEjecutora = null;
        $contexto->FechaHoraActualizacion = date('d/m/Y H:i:s');
        $this->guardarContexto($contexto);
    }

    public function cambiarUnidadEjecutora(string $id): void
    {
        $contexto = $this->obtenerContexto();
        if (!$contexto) {
            return;
        }

        $contexto->IdUnidadEjecutora = $id;
        $contexto->FechaHoraActualizacion = date('d/m/Y H:i:s');
        $this->guardarContexto($contexto);
    }

    private function obtenerContexto(): ?UsuarioContextoActivo
    {
        return UsuarioContextoActivo::findOne([
            'IdUsuario' => Yii::$app->user->id,
        ]);
    }

    private function obtenerOCrearContexto(): UsuarioContextoActivo
    {
        $contexto = $this->obtenerContexto();
        if ($contexto) {
            return $contexto;
        }

        $contexto = new UsuarioContextoActivo();
        $contexto->IdUsuario = Yii::$app->user->id;
        $contexto->Usuario = Yii::$app->user->identity->IdUsuario;
        $contexto->CodigoEstado = Estado::ESTADO_VIGENTE;

        return $contexto;
    }

    private function guardarContexto(UsuarioContextoActivo $contexto): void
    {
        $contexto->Usuario = Yii::$app->user->identity->IdUsuario;
        $contexto->save(false);
    }
}
