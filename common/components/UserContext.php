<?php

use yii\web\IdentityInterface;
use yii\web\User;

class UserContext
{
    public static function usuario(): User|IdentityInterface|null
    {
        return Yii::$app->user->identity;
    }

    public static function persona()
    {
        return self::usuario()->persona;
    }

    public static function nombreCompleto(): string
    {
        $p = self::persona();

        return "{$p->Nombres} {$p->Paterno} {$p->Materno}";
    }
}
