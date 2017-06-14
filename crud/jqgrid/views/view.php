<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$streplace = [" ","　","\t","\n","\r"];
$class = $generator->modelClass;
$pks = $class::primaryKey ();
$urlParams = $generator->generateUrlParams ();
$Curtitle = $generator->Titleindex?$generator->Titleindex:$generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))));

$attrstr = str_replace($streplace,"",$generator->JDetailAttributes);
$attributes = Json::decode($attrstr);


$newdetailattr = [];
if($attributes){
	foreach($attributes as $key=>$item){
		if(isset($item['group'])){
			$newdetailattr[] = $item;
		}else if(isset($item['columns']) && $item['columns']){
			$temp['columns'] = [];
			foreach ($item['columns'] as $ck=>$cv){
				if(isset($cv['attribute']) && !in_array($cv['attribute'], $pks)){
					if(isset($item['currtype'])){
						if($item['currtype']=='img'){
							$item['widgetOptions'] = ['class'=>'myutil\widgets\Fileinput'];
							$item['value'] = '<img src="{a}'.'$model->'.$item['attribute'].'{b}" style="width:40px;height:40px;" />';
						}else if($item['currtype']=='checkbox'){
							$yes = isset($item['widgetOptions']['pluginOptions']['onText'])?$item['widgetOptions']['pluginOptions']['onText']:'是';
							$no = isset($item['widgetOptions']['pluginOptions']['offText'])?$item['widgetOptions']['pluginOptions']['offText']:'否';
							$item['value'] = '{ac}$model->'.$item['attribute'].'{bc}?"<span class=\'label label-success\'>'.$yes.'</span>":"<span class=\'label label-danger\'>'.$no.'</span>{spanlast}';
						}else if($item['currtype']=='select' && $item['controller']){
							if(isset($item['selkey']) && isset($item['selval'])){
								$item['widgetOptions']['data'] = '{asel}ArrayHelper::map('.$item['controller'].'::find()->orderBy("id")->asArray()->all(){douhao}"'.$item['selkey'].'","'.$item['selval'].'"){bsel}';
							}
						}
					}
					$temp['columns'][] = $cv;
				}
			}
			$temp['columns'] && $newdetailattr[] = $temp;
		}else if(isset($item['attribute']) && !in_array($item['attribute'], $pks)){
			if(isset($item['currtype'])){
				if($item['currtype']=='img'){
					$item['widgetOptions'] = ['class'=>'myutil\widgets\Fileinput'];
					$item['value'] = '<img src="{a}'.'$model->'.$item['attribute'].'{b}" style="width:40px;height:40px;" />';
				}else if($item['currtype']=='checkbox'){
					$yes = isset($item['widgetOptions']['pluginOptions']['onText'])?$item['widgetOptions']['pluginOptions']['onText']:'是';
					$no = isset($item['widgetOptions']['pluginOptions']['offText'])?$item['widgetOptions']['pluginOptions']['offText']:'否';
					$item['value'] = '{ac}$model->'.$item['attribute'].'{bc}?"<span class=\'label label-success\'>'.$yes.'</span>":"<span class=\'label label-danger\'>'.$no.'</span>{spanlast}';
				}else if($item['currtype']=='select' && $item['controller']){
					if(isset($item['selkey']) && isset($item['selval'])){
						$item['widgetOptions']['data'] = '{asel}ArrayHelper::map('.$item['controller'].'::find()->orderBy("id")->asArray()->all(){douhao}"'.$item['selkey'].'","'.$item['selval'].'"){bsel}';
					}
				}
			}
			$newdetailattr[] = $item;
		}
	}
}

$varq = var_export($newdetailattr,true).'tags';
$patterns[] = '/\'currtype\' => \'img\'/';
$patterns[] = '/\\\\\\\\/';
$patterns[] = '/{a}/';
$patterns[] = '/{b}/';
$patterns[] = '/array \(/';
$patterns[] = '/\),/';
$patterns[] = '/([0-9]) =>/';
$patterns[] = '/\)tags/';
$patterns[] = '/\'DetailView::INPUT_SWITCH\'/';
$patterns[] = '/\'{ac}/';
$patterns[] = '/{bc}?/';
$patterns[] = '/{spanlast}\'/';
$patterns[] = '/\'DetailView::INPUT_DATE\'/';
$patterns[] = '/\'DetailView::INPUT_SELECT2\'/';
$patterns[] = '/{douhao}/';
$patterns[] = '/\'{asel}/';
$patterns[] = '/{bsel}\'/';

$replacements[] = '\'type\'=>DetailView::INPUT_WIDGET';
$replacements[] = "\\\\";
$replacements[] = "'.";
$replacements[] = ".'";
$replacements[] = "[";
$replacements[] = "],";
$replacements[] = "";
$replacements[] = "]";
$replacements[] = "DetailView::INPUT_SWITCH";
$replacements[] = "";
$replacements[] = "";
$replacements[] = '"';
$replacements[] = 'DetailView::INPUT_DATE';
$replacements[] = 'DetailView::INPUT_SELECT2';
$replacements[] = ',';
$replacements[] = '';
$replacements[] = '';

$detailattr = preg_replace($patterns,$replacements,$varq);

//{"attribute":"name","format":"raw","currtype":"select","controller":"backend\\models\\Test","selkey":"id","selval":"name","type":"DetailView::INPUT_SELECT2","widgetOptions":{"pluginOptions":{"allowClear":true, "width":"100%"},"options":"请选择","data":"INPUT_SELECT2"},"inputContainer":{"class":"col-sm-6"}}

echo "<?php\n";
?>
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use myutil\helpers\MString;

$js=<<<js
    $('select#select2').select2({
        width: null
    });
	$("#maxdemo").maxlength({
		limitReachedClass:"label label-danger",
		alwaysShow:!0,
		placement:"top-left"
	});
    $(".date-picker").datepicker({
       	autoclose: true,
        format: "yyyy-mm-dd",
       	forceParse:false,
		language:'zh-CN',
        pickerPosition:'top-left',
    });
	$("#execute_time").datetimepicker({
		autoclose: true,
		format: "yyyy-mm-dd hh:ii:ss",
		forceParse:true,
		pickerPosition:'top-left'
	});
    $('#is_auto').bootstrapSwitch({
    	onText:"是",
        offText:"否",
        animate:true,
        onSwitchChange:function(event,state){
        	if(state==true){  
        		$(this).val("1");  
        	}else{  
        		$(this).val("0");  
        	}
        }
	});
	$('button#savebtn').click(function(){
		var othis = this;
		var l = Ladda.create(othis);
		l.start();
		var params = $('form#editform').serializeArray();
		Jsd.ajax(viewurl,params,function(res){
			if(res.error==1){
				Jsd.showError(Jsd.errormsg(res.msg));
			}else{
				Jsd.showSuccess('保存成功');
			}
			l.stop();
		});
	});
js;
$this->registerJs($js,yii\web\View::POS_END);
?>
<form class="form-horizontal" method="POST" novalidate="novalidate" role="form" id="editform">
	<?php echo'<?='; ?> <?php echo 'Html::hiddenInput("_csrf",Yii::$app->request->getCsrfToken())'; ?> ?>
	<div class="form-body">
		<div id="bootstrap_alerts"></div>
		<!-- input -->
		<div class="form-group">
			<label class="col-md-3 control-label">demo<span class="required" aria-required="true"> * </span></label>
			<div class="col-md-7">
				<input type="text" class="form-control" placeholder="demo" maxlength="50" id="maxdemo" value="" name=""> <span class="help-block"></span>
			</div>
		</div>
		
		<!-- Ladda button -->
		<div class="form-group">
			<label class="col-md-3 control-label">demo<span class="required" aria-required="true"> * </span></label>
			<div class="col-md-7">
				<button type="button" class="btn btn-info mt-ladda-btn ladda-button" id="savestage" data-style="zoom-in">
					<span class="ladda-label">demo</span>
				</button>
			</div>
		</div>
		
		<!-- select -->
		<div class="form-group">
			<label class="control-label col-md-3"> demo <span class="required" aria-required="true"> * </span></label>
			<div class="col-md-3">
				<select class="form-control" id="" name="">
					<option value="1" selected="">demo1</option>
					<option value="2">demo2</option>
				</select>
				<span class="help-inline"></span>
			</div>
		</div>
		
		<!-- radio -->
		<div class="form-group">
			<label class="control-label col-md-3"> demo <span class="required" aria-required="true"> * </span></label>
			<div class="col-md-4">
				<div class="mt-radio-inline">
					<label class="mt-radio">
						demo1<input type="radio" value="1" name="demo" checked="true"> <span></span>
					</label>
					<label class="mt-radio"> 
						demo2<input type="radio" value="2" name="demo" disabled=""> <span></span>
					</label>
				</div>
			</div>
		</div>
		
		<!-- select2 -->
		<div class="form-group has-info">
			<label class="control-label col-md-3">demo<span class="required" aria-required="true"> * </span></label>
			<div class="col-md-7">
				<select class="form-control select2-multiple" id="select2" multiple name="attr[]" style="display:none;">
					<option value="1" >demo1</option>
					<option value="2" >demo2</option>
					<option value="3" >demo3</option>
					<option value="4" >demo4</option>
					<option value="5" >demo5</option>
				</select>
				<span class="help-block"></span>
			</div>
		</div>
		
		<!-- 日期 -->
		<div class="form-group has-info">
			<label class="control-label col-md-3">demo</label>
			<div class="col-md-7">
				<div class="input-group input-medium date date-picker" data-date-format="yyyy-mm-dd" id="startdatepicker" >
					<input type="text" id="startdate" class="form-control" isdef="" value="" name="" readonly>
					<span class="input-group-btn">
						<button class="btn default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
				</div>
				<span class="help-block"></span>
			</div>
		</div>
		
		<!-- 日期期限 -->
		<div class="form-group">
			<label class="col-md-3 control-label">demo<span class="required" aria-required="true"> * </span></label>
			<div class="col-md-7">
				<div class="input-group input-large date-picker input-daterange" data-date="" data-date-format="yyyy-mm-dd">
					<input type="text" class="form-control" readonly value="" name="" id="term_form">
					<span class="input-group-addon"> 到 </span>
					<input type="text" class="form-control" readonly value="" name="" id="term_to">
				</div>
				<span class="help-block" id="business_term_error"></span>
			</div>
		</div>
		<!-- 日期分钟秒 -->
		<div class="form-group has-info">
			<label class="control-label col-md-2">demo</label>
			<div class="col-md-3">
				<div class="input-group date form_datetime" id="execute_time">
					<input type="text" size="16" readonly value="" class="form-control" name="Message[execute_time]" id="">
					<span class="input-group-btn">
						<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
					</span>
				</div>
				<span class="help-block"></span>
			</div>
		</div>
		
		<!-- switch -->
		<div class="form-group">
			<label class="control-label col-md-2">demo</label>
			<div class="col-md-2">
				<input type="checkbox" id="is_auto" name="Article[is_auto]" checked value="1">
				<span class="help-block"></span>
			</div>
		</div>
		
		<div class="form-actions">
			<div class="row">
				<div class="col-md-offset-3 col-md-9">
					<!-- <button type="button" data-url="" id="savebtn" class="btn green">保存</button>  -->
					<button type="button" class="btn btn-info mt-ladda-btn ladda-button" id="savebtn" data-style="zoom-in">
						<span class="ladda-label">保存</span>
					</button>
				</div>
			</div>
		</div>
		<?php echo '<?='; ?> Html::hiddenInput("id",0) ?>
	</div>
</form>
