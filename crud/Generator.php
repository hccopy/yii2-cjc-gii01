<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cjc\gii\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\TableSchema;
use yii\base\NotSupportedException;

/**
 * Generates CRUD
 *
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 *           read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property boolean|\yii\db\TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 *          
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator {

	const RELATIONS_NONE = 'none';
	const RELATIONS_ALL = 'all';
	const RELATIONS_ALL_INVERSE = 'all-inverse';
	
	const REL_TYPE = 0;
	const REL_CLASS = 1;
	const REL_IS_MULTIPLE = 2;
	const REL_TABLE = 3;
	const REL_PRIMARY_KEY = 4;
	const REL_FOREIGN_KEY = 5;
	const REL_IS_MASTER = 6;
	
	const FK_TABLE_NAME = 0;
	const FK_FIELD_NAME = 1;	
	
	public $db = 'db';
	public $tableSchema;
	public $tableName;
	public $useTablePrefix = false;
	protected $tableNames;
	protected $classNames;
		
	
    public $modelClass;
    public $controllerClass;
    public $viewPath;
    public $baseControllerClass = 'yii\web\Controller';
    public $generateRelations = self::RELATIONS_ALL;
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';
    public $JKey='';
    public $JShowattr='';
    public $JEditable='';
    public $JEdittype='';
    public $JEditrules='';
    public $JFormoptions='';
    public $JSearch='';
    public $JXedit='';
    public $JQta = '';
    public $JNavGrid = '';
    public $Titleindex = '';
    public $CDesc='';
    public $JSdateattr='';
    public $JCheckboxtxt='';
    public $JImage = '';
    public $JSelectattr='';
    public $JOpaction='';
    public $JDetailheading;
    public $JDetailheadingAttr;
    public $JDetailPanelType;
    public $JDetailAttributes;
    //public $JSearchoptions='';

    /**
     *
     * @var boolean whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = false;
    
    /**
     * @inheritdoc
     */
    public function getName() {
        return 'cjc的gii 生成CURD';
    }

    /**
     * @inheritdoc
     */
    public function getDescription() {
        return '自定义的gii CURD';
    }
    
    /**
     * @inheritdoc
     */    
    public function autoCompleteData()
    {
    	$db = $this->getDbConnection();
    	if ($db !== null) {
    		return [
    				'tableName' => function () use ($db) {
    				return $db->getSchema()->getTableNames();
    				},
    			];
    	} else {
    		return [];
    	}
    }    
    public function validateTableName()
    {
    	if (strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1)) {
    		$this->addError('tableName', 'Asterisk is only allowed as the last character.');
    
    		return;
    	}
    	$tables = $this->getTableNames();
    	if (empty($tables)) {
    		$this->addError('tableName', "Table '{$this->tableName}' does not exist.");
    	} else {
    		foreach ($tables as $table) {
    			$class = $this->generateClassName($table);
    			if ($this->isReservedKeyword($class)) {
    				$this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword.");
    				break;
    			}
    		}
    	}
    }
    public function getTablePrefix() {
    	$db = $this->getDbConnection ();
    	if ($db !== null) {
    		return $db->tablePrefix;
    	} else {
    		return '';
    	}
    }    
    /**
     * @inheritdoc
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [
                [
                    'controllerClass',
                    'modelClass',
                    'searchModelClass',
                    'baseControllerClass',
                	'Titleindex',
                	'tableName',
                ],
                'filter',
                'filter' => 'trim'
            ],
            [
                [
                    'modelClass',
                    'controllerClass',
                    'baseControllerClass',
                    'indexWidgetType',
                	'Titleindex',
                	'tableName',
                ],
                'required'
            ],
        	[
        			['tableName'],
        			'match', 'pattern' => '/^(\w+\.)?([\w\*]+)$/', 'message' => 'Only word characters, and optionally an asterisk and/or a dot are allowed.'
        	],
        	[
        			['tableName'],
        			'validateTableName'
        	], 
            [
                [
                    'searchModelClass'
                ],
                'compare',
                'compareAttribute' => 'modelClass',
                'operator' => '!==',
                'message' => 'Search Model Class must not be equal to Model Class.'
            ],
            [
                [
                    'modelClass',
                    'controllerClass',
                    'baseControllerClass',
                    'searchModelClass'
                ],
                'match',
                'pattern' => '/^[\w\\\\]*$/',
                'message' => 'Only word characters and backslashes are allowed.'
            ],
            [
                [
                    'modelClass'
                ],
                'validateClass',
                'params' => [
                    'extends' => BaseActiveRecord::className()
                ]
            ],
            [
                [
                    'baseControllerClass'
                ],
                'validateClass',
                'params' => [
                    'extends' => Controller::className()
                ]
            ],
            [
                [
                    'controllerClass'
                ],
                'match',
                'pattern' => '/Controller$/',
                'message' => 'Controller class name must be suffixed with "Controller".'
            ],
            [
                [
                    'controllerClass'
                ],
                'match',
                'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/',
                'message' => 'Controller class name must start with an uppercase letter.'
            ],
            [
                [
                    'controllerClass',
                    'searchModelClass'
                ],
                'validateNewClass'
            ],
            [
                [
                    'indexWidgetType'
                ],
                'in',
                'range' => [
                    'grid',
                    'list'
                ]
            ],
            [
                [
                    'modelClass'
                ],
                'validateModelClass'
            ],
            [
                [
                    'enableI18N',
                    'enablePjax'
                ],
                'boolean'
            ],
            [
                [
                    'messageCategory'
                ],
                'validateMessageCategory',
                'skipOnEmpty' => false
            ],
        	[
        		[
        				'generateRelations'
        		],
        		'in',
        		'range' => [
        				self::RELATIONS_NONE,
        				self::RELATIONS_ALL,
        				self::RELATIONS_ALL_INVERSE
        		]
        	],        		
            [
                [
                	'viewPath',
                	'JKey',
                	'JShowattr',
                	'JEditable',
                	'JEdittype',
                	'JEditrules',
                	'JFormoptions',
                	'JSearch',
                	'JXedit',
                	'JQta',
                	'JNavGrid',
                	'CDesc',
                	'JSdateattr',
                	'JCheckboxtxt',
                	'JImage',
                	'JSelectattr',
                	'JOpaction',
                	'JDetailheading',
                	'JDetailheadingAttr',
                	'JDetailPanelType',
                	'JDetailAttributes',
                	//'JSearchoptions',
                ],
                'safe'
            ]
                ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
            'enablePjax' => 'Enable Pjax',
        	'Titleindex'=>'页面标题',
        	'CDesc'=>'简短介绍',
        	'JSdateattr'=>'指定日期字段',
        	'JCheckboxtxt'=>'指定checkbox 是否的文字',
        	'generateRelations'=>'生成关联关系',
        	'tableName'=>'表名',
        	'JImage'=>'指定图片字段',
        	'JSelectattr'=>'指定select值为另外表',
        	'JOpaction'=>'右边单列操作',
        	'JDetailheading'=>'详情页面板标题',
        	'JDetailheadingAttr'=>'面板后字段的内容',
        	'JDetailPanelType'=>'面板样式类型',
        	'JDetailAttributes'=>'字段显示和编辑类型'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints() {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase with an uppercase first letter. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
                widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
                sorting, filtering and pagination without page refreshing.',
        	//jqgrid 配置
        	'JKey'=>'主键 jqgrid中colModel的配置,配置哪个字段作为主键,<code>colModel: [{key: true}]</code>',
        	'JShowattr'=>'jqgrid中colModel的要显示的字段，按照输入顺序显示，多个字段使用逗号分割如:{"label":"名称","name":"name"}address<code>colModel: [{{label:"名称",name: \'字段\'}]</code>',
        	'JSearch'=>'jqgrid中colModel的要显示的字段，字段:true ,不指明默认为false  json格式如下：{attr:"字段","search":true},{attr:"字段","search":true},<code>colModel: [{search: true}]</code>',
        	'JXedit'=>'Xedit 是否可以编辑',
        	'JEditable'=>'jqgrid中colModel的配置, 字段:true ,不指明默认为false  json格式如下：{attr:"字段","editable":true},{attr:"字段","editable":true},<code>colModel: [{editable: true}]</code>',
        	'JEdittype'=>'jqgrid中colModel的配置，可以编辑的类型。可选值：text, textarea, checkbox, button, select  格式json例如：<br/>
        		{"attr":"字段","edittype":"<code>text</code>","editoptions":{"size":10, "maxlength": 15}}<br/>====<br/>
        		{"attr":"字段","edittype":"<code>textarea</code>","editoptions":{"rows":2, "cols": 10}}<br/>====<br/>
        		{"attr":"字段","edittype":"<code>checkbox</code>","editoptions":{value:"Yes:No"}}<br/>====<br/>
        		{"attr":"字段","edittype":"<code>button</code>","editoptions":{value:"MyButton"}}<br/>====<br/>
        		{"attr":"字段","edittype":"<code>select</code>","editoptions":{value: “FE:FedEx; IN:InTime; TN:TNT”}}<br/>
        		',
        	'JEditrules'=>'jqgrid中colModel的配置，editable必须设置为true的情况下才有效,
        		如：<code>[<br/>{"attr":"字段","conf":{"edithidden":true,"required":true}},<br/>{"attr":"字段","conf":{"minValue":6,"maxValue":50}}<br/>]</code>,
        		属性值包括：<br/>
        		<code>edithidden</code> boolean 仅在表单编辑模块有效。默认隐藏字段无法编辑。设置为true时，隐藏字段在添加和修改方法被调用时呈现出来，可以编辑，<br/>
        		<code>required</code> boolean (true or false) 设置为true，不允许内容为空，为空将会显示一个错误信息。<br/>
        		<code>number</code> boolean (true or false) 设置为true，输入内容只能为数字，否则将会显示一个错误信息。<br/>
        		<code>integer</code> boolean (true or false)设 置为true，输入内容只能为整数，否则将会显示一个错误信息。<br/>
        		<code>minValue</code> number(integer) 最小值，如果内容小于这个配置值，将会显示错误信息。<br/>
        		<code>maxValue</code> number(integer) 最大值，如果内容大于这个配置值，将会显示错误信息。<br/>
        		<code>email</code> boolean 设置为true，输入内容格式需要满足email格式要求，否则将会显示一个错误信息。<br/>
        		<code>url</code>	boolean	设置为true，输入内容格式需要满足url格式要求，否则将会显示一个错误信息。<br/>
        		<code>date</code> boolean 设置为true，输入内容格式需要满足日期格式要求（使用ISO格式，”Y-m-d“），否则将会显示一个错误信息。<br/>
        		<code>time</code> boolean 设置为true，输入内容格式需要满足时间格式要求，否则将会显示一个错误信息。 目前仅支持”hh:mm“和后接可选的 am/pm 时间格式<br/>
        		<code>custom</code> boolean 设置为true，允许使用自定义的验证方法，如下
        		<code>custom_func</code> function custom设置为true时需要配置此函数。
        		函数参数，输入控件的值，name名称（来自colModel）。 
        		函数需要返回一个数组包含以下项目 第一项：true/false，指定验证是否成功 
        		第二项：当第一项为false有效，显示给用户的错误信息。 格式如：[false,”Please enter valid value”]
        		<code> {name:\'price\', ..., editrules:{edithidden:true, required:true....}, editable:true },</code>',
        	'JFormoptions'=>'jqgrid中colModel的配置，仅在表单编辑有效。目的是记录表中的元素并且附加一些信息在编辑元素的前面或者后面。语法如下<br/>
        		<code>
        			jQuery("#grid_id").jqGrid({<br/>
        				//...<br/>
        				colModel: [<br/>
        				//...<br/>
        				{name:\'price\', ..., formoptions:{elmprefix:\'(*)\', rowpos:1, colpos:2....}, editable:true },<br/>
        				//...<br/>
        				]<br/>
        				//...<br/>
        			});<br/>
        		</code><br/>
        		格式如下：<code>[<br/>{"attr":"字段","conf":{<br/>"elmprefix":"(*)前面显示","elmsuffix":"(*)后面显示","label":"显示文字","rowpos":1,"colpos":2<br/>}<br/>}<br/>]</code>,<br/>
        		下面为可用的选项<br/>
				<code>elmprefix</code>	string	在输入元素前显示的内容（内容可以为html格式的字符串）<br/>
				<code>elmsuffix</code>	string	在输入元素后显示的内容（内容可以为html格式的字符串）<br/>
				<code>label</code>	string	替换jqGrid配置colNames数组中定义的标签作为表单输入项的标签说明内容<br/>
				<code>rowpos</code>	number	定义元素所在行处于表单中位置，从1开始<br/>
				<code>colpos</code>	number	定义元素所在列处于表单中位置，从1开始<br/>
        		',
        		'JNavGrid'=>'导航栏配置,json格式: <br/>
	        		{<br/>
	        			"edit": true,<br/>
	                    "add": true,<br/>
	                    "del": true,<br/>
	                    "search": false,<br/>
	                    "refresh": true,<br/>
	                    "view": true,<br/>
	                    "position": "left",<br/>
	                    "cloneToTop": false<br/>
	                },<br/>
        			
        		',
        		'JQta'=>'设置其他值,json格式：<br/>
        			"viewrecords": true,<br/>
					"autowidth": true,<br/>
            		"rowNum":20,<br/>
            		"rowList":[20,30,50],<br/>
            		"rownumbers": true,<br/>
            		"rownumWidth": 35,<br/>
        		',
        		'Titleindex'=>'页面标题',
        		'JSdateattr'=>'指定日期字段,json格式为 <code>[{"attr":"字段"},{"attr":"字段"}]</code>',
        		'JCheckboxtxt'=>'指定当Jedittype中edittype的设置为checkbox时候 是和否 文字',
        		'generateRelations'=>'生成关联关系',
        		'tableName'=>'表名',
        		'JImage'=>'指定img字段，json格式如下：[{"attr":"字段"},{"attr":"字段"}]',
        		'JSelectattr'=>'当Jedittype 设置edittype为select时候，指定关联另外一张表作为select的value和label',
        		'JOpaction'=>'指定右边单列操作，显示顺序：查看，编辑，删除,json格式:<code>{"view":true,"edit":true,"del":true,"viewtxt":"查看","editxt":"编辑","deltxt":"删除"}</code>',
        		'JDetailheading'=>'面板标题',
        		'JDetailheadingAttr'=>'请填写字段名',
        		'JDetailPanelType'=>'面板样式类型',
        		'JDetailAttributes'=>'字段显示和编辑类型，如果 Jedittype 已经设置过则在此设置不会覆盖前面设置的内容。',
        		//'JSearchoptions'=>'searchoptions 搜索参数设置,事件列表，用法：<code>dataEvents: [{ type: \'click\, data: { i: 7 }, fn: function(e) { console.log(e.data.i); }},{ type: \'keypress\', fn: function(e) { console.log(\'keypress\'); } }]</code>',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates() {
        return [
            'controller.php'
        ];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes() {
        return array_merge(parent::stickyAttributes(), [
            'baseControllerClass',
            'indexWidgetType',
        ]);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * @inheritdoc
     */
    public function generate() {
    	$relations = $this->generateRelations ();
    	$db = $this->getDbConnection ();
    	//print_r($this->getTableNames());
    	
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php'))
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files [] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files [] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }

        return $files;
    }
    
    protected function getTableNames()
    {
    	if ($this->tableNames !== null) {
    		return $this->tableNames;
    	}
    	$db = $this->getDbConnection();
    	if ($db === null) {
    		return [];
    	}
    	$tableNames = [];
    	if (strpos($this->tableName, '*') !== false) {
    		if (($pos = strrpos($this->tableName, '.')) !== false) {
    			$schema = substr($this->tableName, 0, $pos);
    			$pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
    		} else {
    			$schema = '';
    			$pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
    		}
    		print_r($schema);
    		exit();
    		foreach ($db->schema->getTableNames($schema) as $table) {
    			if (preg_match($pattern, $table)) {
    				$tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
    			}
    		}
    	} elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
    		$tableNames[] = $this->tableName;
    		$this->classNames[$this->tableName] = $this->modelClass;
    	}
    
    	return $this->tableNames = $tableNames;
    }    
    
    protected function getDbConnection() {
    	return Yii::$app->get ( $this->db, false );
    }
    protected function generateRelations() {
    	if ($this->generateRelations === self::RELATIONS_NONE) {
    		return [ ];
    	}
    	$db = $this->getDbConnection ();
    
    	$relations = [ ];
    	foreach ( $this->getSchemaNames () as $schemaName ) {
    		foreach ( $db->getSchema ()->getTableSchemas ( $schemaName ) as $table ) {
    			$className = $this->generateClassName ( $table->fullName );
    			foreach ( $table->foreignKeys as $refs ) {
    				$refTable = $refs [0];
    				$refTableSchema = $db->getTableSchema ( $refTable );
    				if ($refTableSchema === null) {
    					// Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
    					continue;
    				}
    				unset ( $refs [0] );
    				$fks = array_keys ( $refs );
    				$refClassName = $this->generateClassName ( $refTable );
    					
    				// Add relation for this table
    				$link = $this->generateRelationLink ( array_flip ( $refs ) );
    				$relationName = $this->generateRelationName ( $relations, $table, $fks [0], false );
    				$relFK = key($refs);
    				$relations [$table->fullName] [$relationName] = [
    						self::REL_TYPE => "return \$this->hasOne($refClassName::className(), $link);",
    						self::REL_CLASS => $refClassName,
    						self::REL_IS_MULTIPLE => 0,
    						self::REL_TABLE => $refTable, //related table
    						self::REL_PRIMARY_KEY => $refs[$relFK], // related primary key
    						self::REL_FOREIGN_KEY => $relFK, // this foreign key
    						self::REL_IS_MASTER => in_array($relFK, $table->getColumnNames()) ? 1 : 0    						
    				];
    					
    				// Add relation for the referenced table
    				$hasMany = $this->isHasManyRelation ( $table, $fks );
    				$link = $this->generateRelationLink ( $refs );
    				$relationName = $this->generateRelationName ( $relations, $refTableSchema, $className, $hasMany );
    				$relations [$refTableSchema->fullName] [$relationName] = [
    						self::REL_TYPE => "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
    						self::REL_CLASS => $className,
    						self::REL_IS_MULTIPLE => $hasMany,
    						self::REL_TABLE => $table->fullName, // rel table
    						self::REL_PRIMARY_KEY => $refs[key($refs)], // rel primary key
    						self::REL_FOREIGN_KEY => key($refs), // this foreign key
    						self::REL_IS_MASTER => in_array($relFK, $refTableSchema->getColumnNames()) ? 1 : 0					
    				];
    			}
    
    			if (($junctionFks = $this->checkJunctionTable ( $table )) === false) {
    				continue;
    			}
    
    			$relations = $this->generateManyManyRelations ( $table, $junctionFks, $relations );
    		}
    	}
    
    	if ($this->generateRelations === self::RELATIONS_ALL_INVERSE) {
    		return $this->addInverseRelations ( $relations );
    	}
    
    	return $relations;
    }
    protected function addInverseRelations($relations) {
    	$relationNames = [ ];
    	foreach ( $this->getSchemaNames () as $schemaName ) {
    		foreach ( $this->getDbConnection ()->getSchema ()->getTableSchemas ( $schemaName ) as $table ) {
    			$className = $this->generateClassName ( $table->fullName );
    			foreach ( $table->foreignKeys as $refs ) {
    				$refTable = $refs [0];
    				$refTableSchema = $this->getDbConnection ()->getTableSchema ( $refTable );
    				unset ( $refs [0] );
    				$fks = array_keys ( $refs );
    					
    				$leftRelationName = $this->generateRelationName ( $relationNames, $table, $fks [0], false );
    				$relationNames [$table->fullName] [$leftRelationName] = true;
    				$hasMany = $this->isHasManyRelation ( $table, $fks );
    				$rightRelationName = $this->generateRelationName ( $relationNames, $refTableSchema, $className, $hasMany );
    				$relationNames [$refTableSchema->fullName] [$rightRelationName] = true;
    					
    				$relations [$table->fullName] [$leftRelationName] [0] = rtrim ( $relations [$table->fullName] [$leftRelationName] [0], ';' ) . "->inverseOf('" . lcfirst ( $rightRelationName ) . "');";
    				$relations [$refTableSchema->fullName] [$rightRelationName] [0] = rtrim ( $relations [$refTableSchema->fullName] [$rightRelationName] [0], ';' ) . "->inverseOf('" . lcfirst ( $leftRelationName ) . "');";
    			}
    		}
    	}
    	return $relations;
    }    
    protected function isHasManyRelation($table, $fks) {
    	$uniqueKeys = [
    			$table->primaryKey
    	];
    	try {
    		$uniqueKeys = array_merge ( $uniqueKeys, $this->getDbConnection ()->getSchema ()->findUniqueIndexes ( $table ) );
    	} catch ( NotSupportedException $e ) {
    		// ignore
    	}
    	foreach ( $uniqueKeys as $uniqueKey ) {
    		if (count ( array_diff ( array_merge ( $uniqueKey, $fks ), array_intersect ( $uniqueKey, $fks ) ) ) === 0) {
    			return false;
    		}
    	}
    	return true;
    }
    private function generateManyManyRelations($table, $fks, $relations) {
    	$db = $this->getDbConnection ();
    
    	foreach ( $fks as $pair ) {
    		list ( $firstKey, $secondKey ) = $pair;
    		$table0 = $firstKey [0];
    		$table1 = $secondKey [0];
    		unset ( $firstKey [0], $secondKey [0] );
    		$className0 = $this->generateClassName ( $table0 );
    		$className1 = $this->generateClassName ( $table1 );
    		$table0Schema = $db->getTableSchema ( $table0 );
    		$table1Schema = $db->getTableSchema ( $table1 );
    			
    		$link = $this->generateRelationLink ( array_flip ( $secondKey ) );
    		$viaLink = $this->generateRelationLink ( $firstKey );
    		$relationName = $this->generateRelationName ( $relations, $table0Schema, key ( $secondKey ), true );
    		$relations [$table0Schema->fullName] [$relationName] = [
    				"return \$this->hasMany($className1::className(), $link)->viaTable('" . $this->generateTableName ( $table->name ) . "', $viaLink);",
    				$className1,
    				true
    		];
    			
    		$link = $this->generateRelationLink ( array_flip ( $firstKey ) );
    		$viaLink = $this->generateRelationLink ( $secondKey );
    		$relationName = $this->generateRelationName ( $relations, $table1Schema, key ( $firstKey ), true );
    		$relations [$table1Schema->fullName] [$relationName] = [
    				"return \$this->hasMany($className0::className(), $link)->viaTable('" . $this->generateTableName ( $table->name ) . "', $viaLink);",
    				$className0,
    				true
    		];
    	}
    
    	return $relations;
    }    
    
    public function generateTableName($tableName)
    {
    	if (!$this->useTablePrefix) {
    		return $tableName;
    	}
    
    	$db = $this->getDbConnection();
    	if (preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches)) {
    		$tableName = '{{%' . $matches[1] . '}}';
    	} elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches)) {
    		$tableName = '{{' . $matches[1] . '%}}';
    	}
    	return $tableName;
    }    
    
    protected function generateRelationLink($refs) {
    	$pairs = [ ];
    	foreach ( $refs as $a => $b ) {
    		$pairs [] = "'$a' => '$b'";
    	}
    
    	return '[' . implode ( ', ', $pairs ) . ']';
    }
    protected function checkJunctionTable($table) {
    	if (count ( $table->foreignKeys ) < 2) {
    		return false;
    	}
    	$uniqueKeys = [
    			$table->primaryKey
    	];
    	try {
    		$uniqueKeys = array_merge ( $uniqueKeys, $this->getDbConnection ()->getSchema ()->findUniqueIndexes ( $table ) );
    	} catch ( NotSupportedException $e ) {
    		// ignore
    	}
    	$result = [ ];
    	// find all foreign key pairs that have all columns in an unique constraint
    	$foreignKeys = array_values ( $table->foreignKeys );
    	for($i = 0; $i < count ( $foreignKeys ); $i ++) {
    		$firstColumns = $foreignKeys [$i];
    		unset ( $firstColumns [0] );
    			
    		for($j = $i + 1; $j < count ( $foreignKeys ); $j ++) {
    			$secondColumns = $foreignKeys [$j];
    			unset ( $secondColumns [0] );
    
    			$fks = array_merge ( array_keys ( $firstColumns ), array_keys ( $secondColumns ) );
    			foreach ( $uniqueKeys as $uniqueKey ) {
    				if (count ( array_diff ( array_merge ( $uniqueKey, $fks ), array_intersect ( $uniqueKey, $fks ) ) ) === 0) {
    					// save the foreign key pair
    					$result [] = [
    							$foreignKeys [$i],
    							$foreignKeys [$j]
    					];
    					break;
    				}
    			}
    		}
    	}
    	return empty ( $result ) ? false : $result;
    }
    
    protected function generateRelationName($relations, $table, $key, $multiple) {
    	if (! empty ( $key ) && substr_compare ( $key, 'id', - 2, 2, true ) === 0 && strcasecmp ( $key, 'id' )) {
    		$key = rtrim ( substr ( $key, 0, - 2 ), '_' );
    	}
    	if ($multiple) {
    		$key = Inflector::pluralize ( $key );
    	}
    	$name = $rawName = Inflector::id2camel ( $key, '_' );
    	$i = 0;
    	while ( isset ( $table->columns [lcfirst ( $name )] ) ) {
    		$name = $rawName . ($i ++);
    	}
    	while ( isset ( $relations [$table->fullName] [$name] ) ) {
    		$name = $rawName . ($i ++);
    	}
    
    	return $name;
    }    
    
    protected function getSchemaNames() {
    	$db = $this->getDbConnection ();
    	$schema = $db->getSchema ();
    	if ($schema->hasMethod ( 'getSchemaNames' )) { // keep BC to Yii versions < 2.0.4
    		try {
    			$schemaNames = $schema->getSchemaNames ();
    		} catch ( NotSupportedException $e ) {
    			// schema names are not supported by schema
    		}
    	}
    	if (! isset ( $schemaNames )) {
    		if (($pos = strpos ( $this->tableName, '.' )) !== false) {
    			$schemaNames = [
    					substr ( $this->tableName, 0, $pos )
    			];
    		} else {
    			$schemaNames = [
    					''
    			];
    		}
    	}
    	return $schemaNames;
    }
    protected function generateClassName($tableName, $useSchemaName = null) {
    	if (isset ( $this->classNames [$tableName] )) {
    		return $this->classNames [$tableName];
    	}
    
    	$schemaName = '';
    	$fullTableName = $tableName;
    	if (($pos = strrpos ( $tableName, '.' )) !== false) {
    		if (($useSchemaName === null && $this->useSchemaName) || $useSchemaName) {
    			$schemaName = substr ( $tableName, 0, $pos ) . '_';
    		}
    		$tableName = substr ( $tableName, $pos + 1 );
    	}
    
    	$db = $this->getDbConnection ();
    	$patterns = [ ];
    	$patterns [] = "/^{$db->tablePrefix}(.*?)$/";
    	$patterns [] = "/^(.*?){$db->tablePrefix}$/";
    	if (strpos ( $this->tableName, '*' ) !== false) {
    		$pattern = $this->tableName;
    		if (($pos = strrpos ( $pattern, '.' )) !== false) {
    			$pattern = substr ( $pattern, $pos + 1 );
    		}
    		$patterns [] = '/^' . str_replace ( '*', '(\w+)', $pattern ) . '$/';
    	}
    	$className = $tableName;
    	foreach ( $patterns as $pattern ) {
    		if (preg_match ( $pattern, $tableName, $matches )) {
    			$className = $matches [1];
    			break;
    		}
    	}
    
    	return $this->classNames [$fullTableName] = Inflector::id2camel ( $schemaName . $className, '_' );
    }    

    /**
     *
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID() {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, - 10);

        return Inflector::camel2id($class);
    }

    /**
     *
     * @return string the controller view path
     */
    public function getViewPath() {
        if (empty($this->viewPath)) {
            return Yii::getAlias('@app/views/' . $this->getControllerID());
        } else {
            return Yii::getAlias($this->viewPath);
        }
    }

    public function getNameAttribute() {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();

        return $pk [0];
    }

    /**
     * Generates code for active field
     * 
     * @param string $attribute        	
     * @return string
     */
    public function generateActiveField($attribute) {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns [$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns [$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }
            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions [$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList(" . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ", ['prompt' => ''])";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
            }
        }
    }

    /**
     * Generates code for active search field
     * 
     * @param string $attribute        	
     * @return string
     */
    public function generateActiveSearchField($attribute) {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns [$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates column format
     * 
     * @param \yii\db\ColumnSchema $column        	
     * @return string
     */
    public function generateColumnFormat($column) {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        } elseif ($column->type === 'text') {
            return 'ntext';
        } elseif (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        } elseif (stripos($column->name, 'email') !== false) {
            return 'email';
        } elseif (stripos($column->name, 'url') !== false) {
            return 'url';
        } else {
            return 'text';
        }
    }

    /**
     * Generates validation rules for the search model.
     * 
     * @return array the generated validation rules
     */
    public function generateSearchRules() {
        if (($table = $this->getTableSchema()) === false) {
            return [
                "[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"
            ];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT :
                case Schema::TYPE_INTEGER :
                case Schema::TYPE_BIGINT :
                    $types ['integer'] [] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN :
                    $types ['boolean'] [] = $column->name;
                    break;
                case Schema::TYPE_FLOAT :
                case Schema::TYPE_DOUBLE :
                case Schema::TYPE_DECIMAL :
                case Schema::TYPE_MONEY :
                    $types ['number'] [] = $column->name;
                    break;
                case Schema::TYPE_DATE :
                case Schema::TYPE_TIME :
                case Schema::TYPE_DATETIME :
                case Schema::TYPE_TIMESTAMP :
                default :
                    $types ['safe'] [] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules [] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     *
     * @return array searchable attributes
     */
    public function getSearchAttributes() {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * 
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels() {
        /* @var $model \yii\base\Model */
        $model = new $this->modelClass ();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels [$name])) {
                $labels [$name] = $attributeLabels [$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels [$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', - 3, 3, true) === 0) {
                        $label = substr($label, 0, - 3) . ' ID';
                    }
                    $labels [$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * 
     * @return array
     */
    public function generateSearchConditions() {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class ();
            foreach ($model->attributes() as $attribute) {
                $columns [$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns [$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT :
                case Schema::TYPE_INTEGER :
                case Schema::TYPE_BIGINT :
                case Schema::TYPE_BOOLEAN :
                case Schema::TYPE_FLOAT :
                case Schema::TYPE_DOUBLE :
                case Schema::TYPE_DECIMAL :
                case Schema::TYPE_MONEY :
                case Schema::TYPE_DATE :
                case Schema::TYPE_TIME :
                case Schema::TYPE_DATETIME :
                case Schema::TYPE_TIMESTAMP :
                    $hashConditions [] = "'{$column}' => \$this->{$column},";
                    break;
                default :
                    $likeConditions [] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions [] = "\$query->andFilterWhere([\n" . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions) . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions [] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * 
     * @return string
     */
    public function generateUrlParams() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params [] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params [] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates action parameters
     * 
     * @return string
     */
    public function generateActionParams() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        } else {
            return '$' . implode(', $', $pks);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * 
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params [] = '@param ' . (substr(strtolower($pk), - 2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return [
                '@param ' . $table->columns [$pks [0]]->phpType . ' $id'
            ];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params [] = '@param ' . $table->columns [$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * 
     * @return boolean|\yii\db\TableSchema
     */
    public function getTableSchema() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     *
     * @return array model column names
     */
    public function getColumnNames() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            /* @var $model \yii\base\Model */
            $model = new $class ();

            return $model->attributes();
        }
    }

}
