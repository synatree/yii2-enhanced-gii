<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \mootensai\enhancedgii\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$tableSchema = $generator->getTableSchema();
$baseModelClass = StringHelper::basename($generator->modelClass);
$fk = $generator->generateFK($tableSchema);
$inflected = Inflector::camel2id(StringHelper::basename($generator->modelClass));
$singular = $inflected;
$title = ucfirst(($generator->pluralize) ? Inflector::pluralize($singular) : $singular);
echo "<?php\n";
?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\dynagrid\\DynaGrid;" : "yii\\widgets\\ListView;" ?>


$this->title = <?= $generator->generateString($title); ?>;
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);
<?php if (!empty($generator->searchModelClass)): ?>
    $advancedSearchButton = Html::a(<?= $generator->generateString('Advanced Search')?>, '#', [
            'class' => 'btn btn-info',
            'data-bs-target' => '#offcanvasRight_<?=$inflected;?>',
            'data-bs-toggle' => 'offcanvas',
            'aria-controls' => 'offcanvasRight_<?=$inflected;?>'
            ]);        
<?php else: ?>
    $advancedSearchButton = "";
<?php endif; ?>
?>
<div class="<?= Inflector::camel2id($baseModelClass) ?>-index">
    <div class="card mb-3">
        <div class="bg-holder d-none d-lg-block bg-card"
            style="background-image:url(/theme/tradelines/assets/img/icons/spot-illustrations/corner-4.png);"></div>
        <!--/.bg-holder-->
        <div class="card-body position-relative">
            <div class="lottie float-start" style="width: 150px; height: 150px"
                data-options='{"path":"theme/tradelines/assets/img/animated-icons/heart.json"}'></div>

            <div class="row">
                <div class="col-lg-8">
                    <h3><?= $title ?> View</h3>
                    <p class="mt-2">
                        This is where your <?= $title; ?> appear after you have created some. Why not try adding some <?= $title; ?> now?
                    </p><a class="btn btn-link ps-0 btn-sm" href="<?= \yii\helpers\Url::to(['<?=$inflected;?>/create']); ?>"
                        target="_blank">Add A <?= $singular; ?><span class="fas fa-chevron-right ms-1 fs--2"></span></a>
                </div>
            </div>
        </div>
    </div>
<?php if (!empty($generator->searchModelClass)): ?>
    <div class="offcanvas offcanvas-end" id="offcanvasRight_<?=$inflected;?>" tabindex="-1" aria-labelledby="offcanvasRightLabel_<?=$inflected;?>">
        <div class="offcanvas-header">
            <h5 id="offcanvasRightLabel_<?=$inflected;?>">Advanced Search</h5><button class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?= "<?= " ?> $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
    <?php endif; ?>
<?php 
if ($generator->indexWidgetType === 'grid'): 
?>
<?= "<?php \n" ?>
    $gridColumns = [
       
<?php
    if ($generator->expandable && !empty($fk)):
?>
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return \kartik\grid\GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                return Yii::$app->controller->renderPartial('_expand', ['model' => $model]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true
        ],
<?php
    endif;
?>
<?php   
    if ($tableSchema === false) :
        foreach ($generator->getColumnNames() as $name) {
            if (++$count < 6) {
                echo "            '" . $name . "',\n";
            } else {
                echo "            // '" . $name . "',\n";
            }
        }
    else :
        foreach ($tableSchema->getColumnNames() as $attribute): 
            if (!in_array($attribute, $generator->skippedColumns)) :
?>
        <?= $generator->generateGridViewFieldIndex($attribute, $fk, $tableSchema)?>
<?php
            endif;
        endforeach; ?>
        [
            'class' => 'kartik\grid\ActionColumn',
<?php if($generator->saveAsNew): ?>
            'template' => '{save-as-new} {view} {update} {delete}',
            'buttons' => [
                'save-as-new' => function ($url) {
                    return Html::a('<span class="fas fa-copy"></span>', $url, ['title' => 'Save As New']);
                },
            ],
<?php endif; ?>
        ],
    ]; 
<?php 
    endif; 
?>
    ?>
    <?= "<?= " ?>DynaGrid::widget([
        'options' => ['id' => '<?= $inflected; ?>'],
        'matchPanelStyle' => false,
        'toggleButtonGrid' => [
            'label' => "<span class='fas fa-cog'></span>",
        ],
        'storage' => 'db',
        'theme' => 'simple-striped',
        'showPersonalize' => true,
        'gridOptions' => [
            'persistResize' => true,
            'dataProvider' => $dataProvider,
            <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel," : '' ?>
            'pjax' => true,
            'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-<?= $inflected ?>']],
            'panel' => [
                'type' => \kartik\grid\GridView::TYPE_PRIMARY,
                'heading' => '<span class="<?= $generator->generateIconClass($generator->modelClass); ?>"></span>  ' . Html::encode($this->title),
                'before' => $advancedSearchButton . "<div class='float-end'>{dynagrid}</div>",
            ],
        ],
        'columns' => $gridColumns
    ]); ?>
<?php 
else: 
?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return $this->render('_index',['model' => $model, 'key' => $key, 'index' => $index, 'widget' => $widget, 'view' => $this]);
        },
    ]) ?>
<?php 
endif; 
?>

</div>
