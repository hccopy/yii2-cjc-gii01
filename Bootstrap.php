<?php
namespace cjc\gii;

use yii\Base\BootstrapInterface;
use ReflectionClass;

class Bootstrap implements BootstrapInterface
{
	public function bootstrap($app){
		if ($app->hasModule('gii')) {
			if (!isset($app->getModule('gii')->generators['enhanced-gii'])) {
				$class = new ReflectionClass ( $this );
				$app->getModule('gii')->generators['cjc-gii-crud']['class'] = 'cjc\gii\crud\Generator';
				$app->getModule('gii')->generators['cjc-gii-crud']['templates']['JqgridCURD'] = dirname ( $class->getFileName () ) . '/crud/jqgrid';
				//model
				$app->getModule('gii')->generators['cjc-gii-model']['class'] = 'cjc\gii\model\Generator';
				$app->getModule('gii')->generators['cjc-gii-model']['templates']['JqgridModel'] = dirname ( $class->getFileName () ) . '/model/jqgrid';
			}
		}
	}
}