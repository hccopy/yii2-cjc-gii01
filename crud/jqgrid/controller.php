<?php
/**
 * This is the template for generating a CRUD controller class file.
 */
use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename ( $generator->controllerClass );
$modelClass = StringHelper::basename ( $generator->modelClass );
$searchModelClass = StringHelper::basename ( $generator->searchModelClass );
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey ();
$urlParams = $generator->generateUrlParams ();
$actionParams = $generator->generateActionParams ();
$actionParamComments = $generator->generateActionParamComments ();

$Selectattr = Json::decode($generator->JSelectattr,true);

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use cjc\gii\JqGridActiveAction;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n"?>
{
	private $modelname = '<?= Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>';
	public $enableCsrfValidation = false;
	private $dateattr = '<?= $generator->JSdateattr ?>';
	
	public function actions()
	{
		return [
				'jqgrid' => [
					'class' => JqGridActiveAction::className(),
					'model' => <?= Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>::className(),
					'scope' => function ($query) {
						$query->select(['*']);
					},
//					'scope' => function ($query) {
//						$query->select([Bespokeconsult::tableName().'.*',Companyinfo::tableName().'.company_name'])->joinWith([
//							'marksone'=>function($query){
//								$query->select([Bespokemarks::tableName().'.*',Companyinfo::tableName().'.company_name'])->joinWith('companyinfo')->asArray();
//							}
//						]);
//					},
//					'columns'=>function(){
//						return ArrayHelper::merge(array_keys(Bespokeconsult::getTableSchema()->columns), ['company_name']);
//					},
//					'queryAliases'=>[
//						'company_name'=>Companyinfo::tableName().'.company_name'
//					],
					'dateattr'=>json_decode($this->dateattr,true)
				],
		];
	}
	
	public function actionIndex(){
		$data = [];
<?php if($Selectattr){ ?>
<?php foreach($Selectattr as $item){ ?>
<?php if(isset($item['attr']) && isset($item['modelclass']) && isset($item['value']) && isset($item['label'])){ ?>
		
		$data['<?= $item['attr'] ?>'] = ArrayHelper::map(\<?= $item['modelclass'] ?>::find()->orderBy('<?= $item['value'] ?>')->asArray()->all(), '<?= $item['value'] ?>', '<?= $item['label'] ?>');
<?php } ?>
<?php } ?>
<?php } ?>
		return $this->render('index',[
			'data'=>Json::htmlEncode($data)
		]);
	}
	
    public function actionView()
    {
    	if(Yii::$app->getRequest()->isPost){
    		$res['error'] = 1;
    		
    		$serviceType = Yii::$app->getRequest()->post('serviceType',0);
    		$model = <?= Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>::find()->where(['serviceType'=>$serviceType])->one();
    		!$model && $model= new <?= Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>();
    		
    		if($model->load(Yii::$app->getRequest()->post()) && $model->save()){
    			$res['error'] = 0;
    		}else{
    			$res['msg'] = $model->getFirstErrors();
    		}
    		
    		Yii::$app->getResponse()->format = yii\web\Response::FORMAT_JSON;
    		return $res;
    	}else{
	        return $this->renderAjax('view', [
	            'model' => $this->findModel(yii::$app->getRequest()->get('id',0)),
	        ]);
    	}
    }
    
    public function actionSave(<?= $actionParams ?>){
    	$model = $this->findModel(<?= $actionParams ?>);
    	$post = Yii::$app->request->post();
    	if ($model->load($post) && $model->save()) {
    		Yii::$app->session->setFlash('kv-detail-success', Yii::t('base', 'savesuccess'));
    	}else{
    		Yii::$app->session->setFlash('kv-detail-warning', implode(' , ', $model->getFirstErrors()));
    	}
    	return $this->redirect(['view','id'=>$model->id]);
    }
    
    public function actionXsave(){
    	Yii::$app->getResponse ()->format = 'json';
    	$res ['error'] = 1;
    	$res ['msg'] = '';
    	$id = Yii::$app->getRequest()->get('pk',0);
    	$name = Yii::$app->getRequest()->get('name',false);
    	$value = Yii::$app->getRequest()->get('value',false);
    	if($id && $name!==false && $value!==false){
    		$model = $this->findModel ( $id );
    		$model->$name = $value;
    		if ($model->save()) {
    			$res ['error'] = 0;
    		}
    		$msg = $model->getFirstErrors();
    		$res ['msg'] = $msg?implode(' , ', $msg):'';
    	}
    	return $res;
    }   
    
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count ( $pks ) === 1) {
	$condition = '$id';
} else {
	$condition = [ ];
	foreach ( $pks as $pk ) {
		$condition [] = "'$pk' => \$$pk";
	}
	$condition = '[' . implode ( ', ', $condition ) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            return new <?= $modelClass ?>();
        }
    }
}
