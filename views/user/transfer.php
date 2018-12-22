<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', 'User') . ': ' . $model->getUsername();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Transfer');
?>
<div class="user-transfer">

    <?php
        $maxTransferAmount = Yii::$app->formatter->asDecimal($model->balance + $model::MAX_NEGATIVE_BALANCE, 2);
        $currentBalance = Yii::t('app', 'Current balance') . ': ' . Yii::$app->formatter->asDecimal($model->balance, 2);
    ?>

    <?php if ($maxTransferAmount > 0): ?>

        <?php Yii::$app->session->setFlash('success', $currentBalance . '<br>' . Yii::t('app', 'Maximum transferable amount') . ': ' . $maxTransferAmount); ?>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'transferAmount')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'targetUser')->textInput(['maxlength' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Transfer'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    <?php else: ?>

        <?php Yii::$app->session->setFlash('error', Html::tag('b', $currentBalance) . '<br>' . Yii::t('app', "Sorry, you don't have sufficient balance to transfer.")); ?>

    <?php endif; ?>
</div>
