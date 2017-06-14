<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */
use yii\helpers\Html;
use yii\gii\generators\model\Generator;

echo $form->field ( $generator, 'tableName' )->textInput ( [
		'table_prefix' => $generator->getTablePrefix ()
] );
echo $form->field ( $generator, 'useTablePrefix' )->checkbox ();

echo $form->field ( $generator, 'modelClass' );
echo $form->field ( $generator, 'searchModelClass' );
echo $form->field ( $generator, 'controllerClass' );
echo $form->field ( $generator, 'viewPath' );
echo $form->field ( $generator, 'baseControllerClass' );
echo $form->field ( $generator, 'indexWidgetType' )->dropDownList ( [ 
		'grid' => 'GridView',
		//'list' => 'ListView' 
] );
echo $form->field ( $generator, 'enableI18N' )->checkbox ();
echo $form->field ( $generator, 'enablePjax' )->checkbox ();
echo $form->field ( $generator, 'messageCategory' );

echo $form->field ( $generator, 'generateRelations' )->dropDownList ( [
		Generator::RELATIONS_NONE => 'No relations',
		Generator::RELATIONS_ALL => 'All relations',
		Generator::RELATIONS_ALL_INVERSE => 'All relations with inverse'
] );

//页面标题设置
echo $form->field ( $generator, 'Titleindex' );
echo $form->field ( $generator, 'CDesc' );
//jqgrid配置
echo $form->field ( $generator, 'JKey' );
echo $form->field ( $generator, 'JShowattr' )->textarea(["rows"=>8]);
echo Html::label('[{"label":"要显示的名称","name":"字段"},{"label":"要显示的名称","name":"字段"},{"label":"要显示的名称","name":"字段"},{"label":"要显示的名称","name":"字段"}]',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JXedit' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","xedit":true},{"attr":"字段","xedit":true},{"attr":"字段","xedit":true},{"attr":"字段","xedit":true},{"attr":"字段","xedit":true}]',null,['style'=>'color:#CC6699;']);

//指定日期字段
echo $form->field ( $generator, 'JSdateattr' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段"},{"attr":"字段"}]',null,['style'=>'color:#CC6699;']);

//指定img字段
echo $form->field ( $generator, 'JImage' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段"},{"attr":"字段"}]',null,['style'=>'color:#CC6699;']);


echo $form->field ( $generator, 'JSearch' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","search":true},{"attr":"字段","search":true},{"attr":"字段","search":true},{"attr":"字段","search":true},{"attr":"字段","search":true}]',null,['style'=>'color:#CC6699;']);


echo $form->field ( $generator, 'JEditable' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","editable":true},{"attr":"字段","editable":true},{"attr":"字段","editable":true},{"attr":"字段","editable":true},{"attr":"字段","editable":true}]',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JEdittype' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","edittype":"text","editoptions":{"size":10,"maxlength":15}},{"attr":"字段","edittype":"textarea","editoptions":{"rows":5,"cols":50}},{"attr":"字段","edittype":"checkbox","editoptions":{"value":"Yes:No"}},{"attr":"字段","edittype":"button","editoptions":{"value":"MyButton"}},{"attr":"字段","edittype":"select","editoptions":{"value":"FE:FedEx; IN:InTime; TN:TNT"}}]',null,['style'=>'color:#CC6699;']);


//select 类型时候 指定另外表的字段
echo $form->field ( $generator, 'JSelectattr' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"要作用在的字段","modelclass":"backend\\\models\\\Test","value":"id","label":"name"},{"attr":"要作用在的字段","modelclass":"backend\\\models\\\Test","value":"id","label":"name"}]',null,['style'=>'color:#CC6699;']);


echo $form->field ( $generator, 'JCheckboxtxt' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","yes":"是","no":"否"}]',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JEditrules' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","conf":{"edithidden":true,"required":true}},{"attr":"字段","conf":{"minValue":6,"maxValue":50}}]',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JFormoptions' )->textarea(["rows"=>8]);
echo Html::label('[{"attr":"字段","conf":{"elmprefix":"(*)前面显示","elmsuffix":"(*)后面显示","label":"显示文字","rowpos":1,"colpos":2}},{"attr":"字段","conf":{"elmprefix":"(*)前面显示","elmsuffix":"(*)后面显示","label":"显示文字","rowpos":1,"colpos":2}}]',null,['style'=>'color:#CC6699;']);

//echo $form->field ( $generator, 'JSearchoptions' )->textarea(["rows"=>8]);
//echo Html::label('{"dataEvents":[{"type":"click","data":{"i":7}},{"type":"keypress","data":{"i":7}}]}',null,['style'=>'color:#CC6699;']);


echo $form->field ( $generator, 'JNavGrid' )->textarea(["rows"=>8]);
echo Html::label('{"edit":true,"add":true,"del":true,"search":false,"refresh":true,"view":true,"position":"left","cloneToTop":false}',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JQta' )->textarea(["rows"=>8]);
echo Html::label('{"viewrecords":true,"autowidth":true,"rowNum":20,"rowList":[15,30,50],"rownumbers":true,"rownumWidth":35}',null,['style'=>'color:#CC6699;']);

//x-editable开始
//echo $form->field ( $generator, 'XE' )->textarea(["rows"=>8]);
//echo Html::label('{"view":true,"del":true,"viewtxt":"查看","deltxt":"删除"}',null,['style'=>'color:#CC6699;']);
//x-editable结束

echo $form->field ( $generator, 'JOpaction' )->textarea(["rows"=>8]);
echo Html::label('{"view":true,"del":true,"viewtxt":"查看","deltxt":"删除"}',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JDetailheading' )->textarea(["rows"=>8]);
echo Html::label('详情',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JDetailheadingAttr' )->textarea(["rows"=>8]);
echo Html::label('字段',null,['style'=>'color:#CC6699;']);

echo $form->field ( $generator, 'JDetailPanelType' )->dropDownList ( [
		'TYPE_PRIMARY' => 'TYPE_PRIMARY',
		'TYPE_ACTIVE'=>'TYPE_ACTIVE',
		'TYPE_DANGER'=>'TYPE_DANGER',
		'TYPE_INFO'=>'TYPE_INFO',
		'TYPE_DEFAULT'=>'TYPE_DEFAULT',
		'TYPE_SUCCESS'=>'TYPE_SUCCESS',
		'TYPE_WARNING'=>'TYPE_WARNING',
] );

echo $form->field ( $generator, 'JDetailAttributes' )->textarea(["rows"=>8]);
echo Html::label('分组写法1:[{"group":true,"label":"分组名称","rowOptions":{"class":"info"}},{"columns":[{"attribute":"id","format":"raw","valueColOptions":{"style":"width:30%"}},{"attribute":"name","format":"raw","valueColOptions":{"style":"width:30%"}}  ]}]
		<br/>
		分组写法2:[{"group":true,"label":"分组名称","rowOptions":{"class":"info"}},{"attribute":"id","format":"raw","value":"<span class=\"label label-danger\">No</span>","type":"DetailView::INPUT_SWITCH","widgetOptions":{"pluginOptions":{"onText":"是","offText":"否"}},"inputContainer":{"class":"col-sm-6"}},{"attribute":"sale_amount","label":"Sale Amount ($)","format":["decimal",2],"inputContainer":{"class":"col-sm-6"}}]
		<br/>
		图片写法：[{"group":true,"label":"分组名称","rowOptions":{"class":"info"}},{"attribute":"name","format":"raw","currtype":"img","valueColOptions":{"style":"width:30%"}}]
		<br/>
		checkbox写法：[{"group":true,"label":"分组名称","rowOptions":{"class":"info"}},{"attribute":"name","format":"raw","currtype":"checkbox","type":"DetailView::INPUT_SWITCH","widgetOptions":{"pluginOptions":{"onText":"是","offText":"否"}},"inputContainer":{"class":"col-sm-6"}}]
		<br/>
		日期写法： [{"group":true,"label":"分组名称","rowOptions":{"class":"info"}},{"attribute":"name","format":"date","type":"DetailView::INPUT_DATE","widgetOptions":{"pluginOptions":{"format":"yyyy-mm-dd"}},"inputContainer":{"class":"col-sm-6"}}]
		<br/>
		下拉框写法：{"group":true,"label":"分组名称3","rowOptions":{"class":"info"}},
{"attribute":"name","format":"raw","currtype":"select","controller":"backend\\models\\Test","selkey":"id","selval":"name","type":"DetailView::INPUT_SELECT2","widgetOptions":{"pluginOptions":{"allowClear":true, "width":"100%"},"options":{"placeholder":"请选择"},"data":"INPUT_SELECT2"},"inputContainer":{"class":"col-sm-6"}}
		<br/>
		日期写法1 只显示年月日：{"type":DetailView::INPUT_DATE,"widgetOptions":{"pluginOptions":{"format":"yyyy-mm-dd"}}}
		<br/>
		日期写法2 只显示年月日十分秒：{"type":DetailView::INPUT_DATETIME,"options":{"placeholder":"请选择"},"widgetOptions":{"pluginOptions":{"autoclose":true,"format":"yyyy-mm-dd hh:ii:ss"}}}
        
		',null,['style'=>'color:#CC6699;']);

