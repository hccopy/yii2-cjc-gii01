<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Json;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$streplace = [" ","　","\t","\n","\r"];
$urlParams = $generator->generateUrlParams ();
$nameAttribute = $generator->getNameAttribute ();
$modelname = Inflector::camel2words(StringHelper::basename($generator->modelClass));
$controllername = Inflector::camel2words(StringHelper::basename($generator->controllerClass));
$controllername = trim(str_replace('Controller', "",$controllername));
$controllermodel = strtolower($controllername).'/jqgrid';
$urlmodel = strtolower($modelname).'/jqgrid';
$JNavGrid = str_replace($streplace,"",$generator->JNavGrid);
$JShowattr = str_replace($streplace,"",$generator->JShowattr);
$JEditable = str_replace($streplace,"",$generator->JEditable);
$JSearch = str_replace($streplace,"",$generator->JSearch);
$JXedit = str_replace($streplace,"",$generator->JXedit);
$JEdittype = str_replace($streplace,"",$generator->JEdittype);
$JEditrules = str_replace($streplace,"",$generator->JEditrules);
$JFormoptions = str_replace($streplace,"",$generator->JFormoptions);
$JSdateattr = str_replace($streplace,"",$generator->JSdateattr);
$JCheckboxtxt = str_replace($streplace,"",$generator->JCheckboxtxt);
$JOpaction = str_replace($streplace,"",$generator->JOpaction);
$JImage = str_replace($streplace,"",$generator->JImage);
$JQta = str_replace($streplace,"",$generator->JQta);
$navgrid = Json::decode($JNavGrid);
$valuearr = [
		'key'=>$generator->JKey,
		'showattr'=>Json::decode($JShowattr),
		'search'=>Json::decode($JSearch),
		'xedit'=>Json::decode($JXedit),
		'editable'=>Json::decode($JEditable),
		'edittype'=>Json::decode($JEdittype),
		'editrules'=>Json::decode($JEditrules),
		'formoptions'=>Json::decode($JFormoptions),
		'dateattr'=>Json::decode($JSdateattr),
		'checkboxtxt'=>Json::decode($JCheckboxtxt),
		'opaction'=>Json::decode($JOpaction),
		'image'=>Json::decode($JImage),
		'navgrid' => $navgrid,
		'qta' => Json::decode($JQta),
];
$opts = Json::encode($valuearr);
$url = Yii::$app->urlManager->createUrl ( ['/homedecorate/'.$controllermodel,'action'=>'request'] );
$addurl = (isset($navgrid['add']) && $navgrid['add'])?Yii::$app->urlManager->createUrl(['/homedecorate/'.$controllermodel,'action'=>'add']):'';
$updateurl = (isset($navgrid['edit']) && $navgrid['edit'])?Yii::$app->urlManager->createUrl(['/homedecorate/'.$controllermodel,'action'=>'edit']):'';
$delurl = (isset($navgrid['del']) && $navgrid['del'])?Yii::$app->urlManager->createUrl(['/homedecorate/'.$controllermodel,'action'=>'del']):'';
$viewurl = Yii::$app->urlManager->createUrl([strtolower('/homedecorate/'.$controllername).'/view']);
$xesaveurl = Yii::$app->urlManager->createUrl([strtolower('/homedecorate/'.$controllername).'/xsave']);

echo "<?php\n";
?>
use yii\helpers\Html;
use frontend\assets\publicasset\JqgridAsset;
use frontend\assets\publicasset\LaddAsset;
use cjc\gii\JqgridgiiAsset;

use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : ''?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : ''?>
/* @var $dataProvider yii\data\ActiveDataProvider */

JqgridAsset::register ( $this );
JqgridgiiAsset::register ( $this );
LaddAsset::register ( $this );

$this->title = '<?= $generator->Titleindex?$generator->Titleindex:$generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs ( "var mn = '<?= $modelname ?>',url = '<?= $url ?>',addurl='<?= $addurl ?>',upurl='<?= $updateurl ?>',delurl='<?= $delurl ?>',xesaveurl='<?= $xesaveurl ?>',viewurl='<?= $viewurl ?>'
_opts=<?= $generator->generateString($opts) ?>,selectdata='{$data}';", 
\yii\web\View::POS_HEAD );
$js=<<<js
	function viewdetail(dtd,id){
		if(!id) id=0;
     	$.get(viewurl,{id:id},function(res){
			$('div#tab2').html(res);
			dtd.resolve();
		});
	}
	function add(){
		var id = arguments[0] ? arguments[0] : 0;
		var dtd = $.Deferred();
		App.blockUI({
			boxed: true,
			message: '请稍后'
		});
		$.when(viewdetail(dtd,id)).done(function(){
			$('#tabtwo').tab('show');
			App.unblockUI();
		});
	}
	$(document).ready(function(){
//		operationfun = function(cellvalue, options, cell){
//			var t = $("#jqGrid").getGridParam('userData');
//			var d = '<a href="javascript:void(0);" data-id="'+cell[colkey]+'" id="opactiondel">删除</a>';
//			var v = '<span><a href="javascript:;" name="oplink" id="op" data-id="'+cell[colkey]+'">查看详情</a></span> | ';
//			return v+d;
//		}
//		operationfungrid = function(){
//			$('a[name="oplink"]').each(function(){
//				var othis = this;
//				$(othis).click(function(){
//					var opid = $(othis).attr('id'),id=$(othis).attr('data-id');
//					if(opid=='op'){
//						add(id);
//					}
//				});
//			});
//		}
	});
js;
$this->registerJs('var selectvals=<?= $generator->JSelectattr?$generator->JSelectattr:'[]' ?>;',\yii\web\View::POS_HEAD);
$this->registerJs($js,\yii\web\View::POS_END);
?>

<h1 class="page-title">
	<?= "<?= " ?>Html::encode($this->title) ?><small><?= $generator->CDesc ?></small>
</h1>
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : ''?>
<div class="row <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
	<div class="tabbable tabbable-tabdrop">
		<ul class="nav nav-tabs" id="tabul">
			<li class="active">
				<a href="#tab1" data-toggle="tab" id="tabone" onclick='$("#jqGrid").trigger("reloadGrid");' aria-expanded="true"><?= "<?= " ?>Html::encode($this->title) ?></a>
			</li>
			<li class="">
				<a href="#tab2" data-toggle="tab" id="tabtwo" aria-expanded="true">查看详情</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab1">
				<div class="row bespokedecorate-index">
					<div class="col-md-12">
						<div class="portlet light bordered">
							<div class="portlet-title">
								<div class="caption font-dark">
									<i class="icon-settings font-dark"></i> <span
										class="caption-subject bold uppercase"><?= "<?= " ?>Html::encode($this->title) ?>列表</span>
								</div>
								<div class="actions">
									<a href="javascript:;" onclick="add();" class="btn btn-circle btn-default"><i class="fa fa-plus"></i>添加</a>
								</div>
							</div>
							<div class="portlet-body">
								<table id="jqGrid"></table>
								<div id="jqGridPager"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="tab2"></div>
		</div>
	</div>
</div>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : ''?>
