<?php


use yii\helpers\Html;

$this->beginPage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>

    <?php $this->head() ?>
</head>

<body>

<?php $this->beginBody() ?>

<div class="container mt-5">
    <?= $content ?>
</div>

<?php $this->endBody() ?>

</body>
</html>

<?php $this->endPage() ?>
