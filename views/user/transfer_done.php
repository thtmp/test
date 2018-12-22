<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', 'User') . ': ' . $model->getUsername();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transfer'), 'url' => ['user/transfer']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Done');
?>
<div class="user-transfer">
    <?php Yii::$app->session->setFlash(
        'success',
        (Yii::$app->session->getFlash('transfer-done', false) ? (Yii::t('app', 'Transaction completed successfully.' . '<br>')) : '')
           . Yii::t('app', 'Current balance') . ': ' . Yii::$app->formatter->asDecimal($model->balance, 2)
    ); ?>
</div>
