<?php
namespace cjc\gii;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class JqgridgiiAsset extends AssetBundle {
	public $basePath = '@webroot';
	public $sourcePath = '@web';
	public $css = [ ];
	public $js = [
			'jqgrid_gii.js'
	];
	public function init(){
		$this->sourcePath = __DIR__ . '/assets';
		parent::init();
	}
}
