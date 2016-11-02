<?php
namespace cjc\gii;

use yii\base\Application;
use yii\Base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
	public function bootstrap($app){
		if ($app->hasModule('gii')) {
			if (!isset($app->getModule('gii')->generators['enhanced-gii'])) {
				$app->getModule('gii')->generators['cjc-gii-crud']['class'] = 'cjc\gii\crud\Generator';
			}
		}
	}
}