<?php
/** @var ActiveDataProvider $dataProvider */

/** @var bool $show_all */

use backend\models\User;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$form = ActiveForm::begin([
    'id'      => 'user-search-form',
    'options' => ['class' => 'form-horizontal'],
]);
?>
    <div class="form-group">
        <div class="col-lg-11">
            <?= Html::a(Html::checkbox('show_all', $show_all, ['label' => 'Показать всех']), ['/users', 'show_all' => (int)!$show_all]); ?>
        </div>
        <div class="col-lg-1">
        </div>
    </div>
<?php ActiveForm::end();

Pjax::begin([
    'id' => 'users-list',
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'rowOptions'   => function(User $user)
    {
        if ($user->del) {
            return ['class' => 'danger', 'title' => 'Удален'];
        }
    },
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label'  => 'Логин',
            'format' => 'raw',
            'value'  => function(User $user)
            {
                return Html::a($user->login, "/users/user/{$user->id}");
            },
        ],
        [
            'label'  => 'ФИО',
            'format' => 'raw',
            'value'  => function(User $user)
            {
                return Html::a(implode(' ', array_filter([$user->surname, $user->name, $user->secondName])), "/users/user/{$user->id}");
            },
        ],
        'phone:text:Телефон',
        [
            'class'    => 'yii\grid\ActionColumn',
            'header'   => 'Действия',
            'template' => '{user} {delete}',
            'buttons'  => [
                'user'   => function($url, User $user)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-pencil"></span>',
                        ["/users/user/{$user->id}"],
                        ['title' => 'Редактировать']
                    );
                },
                'delete' => function($url, User $user)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-' . ($user->del ? 'ok' : 'trash') . '"></span>',
                        '#',
                        [
                            'title'          => $user->del ? 'Восстановить' : 'Удалить',
                            'aria-label' => $user->del ? 'Восстановить' : 'Удалить',
                            'onclick' => "
                                $.ajax('{$url}', {
                                    type: 'POST'
                                }).done(function(data) {
                                    if (data.hasOwnProperty('err') && data.err) {
                                        alert(data.err);
                                    } else {
                                        $.pjax.reload({container: '#users-list'});
                                    }
                                });
                                return false;
                            ",
                        ]
                    );
                },
            ],
        ],
    ],
    'summary'      => Yii::$app->params['gridViewSummary'],
]);
Pjax::end();