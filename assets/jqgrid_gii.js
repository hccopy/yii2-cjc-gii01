(function($){
	jQuery(document).ready(function() {
		$.fn.datetimepicker.dates['zh'] = {
		        days:       ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六","星期日"],
		        daysShort:  ["日", "一", "二", "三", "四", "五", "六","日"],
		        daysMin:    ["日", "一", "二", "三", "四", "五", "六","日"],
		        months:     ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月","十二月"],
		        monthsShort:  ["一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二"],
		        meridiem:    ["上午", "下午"],
		        today:       "今天"  
		};
		var yes='是',no='否',attrcheckbox=[];
		_opts = $.parseJSON(_opts);
		var navgrid = _opts.navgrid,qta = _opts.qta,checkboxtxt=_opts.checkboxtxt,sedittype=_opts.edittype,imagevals=_opts.image,opaction=_opts.opaction,showattrs=_opts.showattr,xedit=_opts.xedit;
		window.colkey=_opts.key;
		navgrid==null?navgrid={}:navgrid;
		xedit==null?xedit={}:xedit;
		qta==null?qta={}:qta;
		checkboxtxt==null?checkboxtxt={}:checkboxtxt;
		sedittype==null?sedittype={}:sedittype;
		imagevals==null?imagevals={}:imagevals;
		selectdata = $.parseJSON(selectdata);
		//selectvals = $.parseJSON(selectvals);
		opaction==null?opaction={}:opaction;
		showattrs==null?showattrs={}:showattrs;
		
		opaction.view = opaction.view?opaction.view:false;
		opaction.edit = opaction.edit?opaction.edit:false;
		opaction.del = opaction.del?opaction.del:false;
		opaction.viewtxt = opaction.viewtxt?opaction.viewtxt:'查看';
		opaction.deltxt = opaction.deltxt?opaction.deltxt:'删除';
		try{
			if(diag){
				opaction.edit=false;
				opaction.view=false;
				opaction.del=false;
			}
		}catch(e){}
		var curjqdiag = false;
    	try{
    		curjqdiag = jqdiag;
    	}catch(e){
    	}
    	window.operationfun = '';
    	window.operationfungrid = '';
		
		$.jgrid.styleUI.Bootstrap.base.rowTable = "table table-bordered table-striped";
		//$.jgrid.defaults.responsive = true;

		function editvalues(){
			var txt = arguments[0] ? arguments[0] : '全部';
	        var result = ':['+txt+']';
	        $.each(_opts.menus, function () {
	            var menu = this;
                result+=';'+menu['name']+':'+menu['name'];
	        });
			return result;
		}
		function editformvalues(){
			var txt = arguments[0] ? arguments[0] : '';
	        var result = ':['+txt+']';
	        $.each(_opts.menus, function () {
	            var menu = this;
                result+=';'+menu['id']+':'+menu['name'];
	        });
			return result;
		}
		//下拉框值
		function editselvals(name){
			var result = ':[全部]',val = '1:0';
			$.each(checkboxtxt,function(){
				var checkb = this;
				if(checkb.attr==name){
					yes = checkb.yes?checkb.yes:yes;
					no = checkb.no?checkb.no:no;
				}
			});
			$.each(sedittype,function(){
				var edittype = this;
				if(edittype.attr==name && edittype.editoptions.value){
					val = edittype.editoptions.value;
					val = val.split(":");
					val[0] = val[0]?val[0]:1;
					val[1] = val[1]?val[1]:0;
				}
			});			
			result+=';'+val[0]+':'+yes;
			result+=';'+val[1]+':'+no;
			return result;
		}
		
		var colModel = [];
		$.each(_opts.showattr,function(){
			var othis = this,code={};
			code = othis;
			code.formatter = defaultformatter;
			code.unformat = undefaultformatter;
			if(code.name==colkey){
				code.key = true;
			}
			if(parseInt(window[code.name])){
				code.width=parseInt(window[code.name]);
			}
			//editable
			code.editable=false;
			$.each(_opts.editable,function(){
				var editable = this;
				if(editable.attr==code.name){
					code.editable=editable.editable;
				}
			});
			//search
			code.search=false;
			$.each(_opts.search,function(){
				var search = this;
				if(search.attr==code.name){
					code.search=search.search;
				}
			});
			
			//edittype
			//selectvalsqq = $.parseJSON(selectvalsqq);
			$.each(_opts.edittype,function(){
				var edittype = this;
				if(edittype.attr==code.name && code.editable){
					var scop={value:editselvals(code.name)}
					code.edittype=edittype.edittype;
					if(edittype.editoptions){
						code.editoptions = edittype.editoptions;
					}
					if(code.edittype=='checkbox'){
						code.stype='select';
	                	code.searchoptions = scop;
						code.formatter = formatcheckbox;
						code.unformat = unformatcheckbox;
					}else if(code.edittype=='select'){
						code.stype='select';
						if(selectdata[code.name]){
							var curseld = selectdata[code.name],v=':[请选择]',s=[];
							for(var a in curseld){
								var atemp = curseld[a];
								s.push(a+':'+curseld[a]);
							}
							if(s.length>0){
								v += ';'+s.join(';');
							}
							code.editoptions = {
									value:v
							};
							code.searchoptions = {
									value:v
							};
						}else{
							code.searchoptions = {
									value:edittype.editoptions.value
							};
						}
						code.formatter = formatselect;
					}else if(code.edittype=='button'){
						code.search=false;
					}
				}
			});
			//editrules
			$.each(_opts.editrules,function(){
				var editrules = this;
				if(editrules.attr==code.name && code.editable){
					if(editrules.conf){
						code.editrules = editrules.conf;
					}
				}
			});
			//formoptions
			$.each(_opts.formoptions,function(){
				var form = this;
				if(form.attr==code.name && code.editable){
					if(form.conf){
						code.formoptions = form.conf;
					}
				}
			});
			
			//date attr
			$.each(_opts.dateattr,function(){
				var dateattr = this;
				if(dateattr.attr==code.name && code.search){
					var sop = {
							dataInit:dateinit
					};
					var foroptions = {
							srcformat:'Y-m-d H:i:s',
							newformat:'Y-m-d H:i:s'
					};
					code.editable=false;
					code.sorttype='date';
					code.formatter='date';
					code.formatoptions = foroptions;
					code.searchoptions = sop;
				}
			});
			//img attr
			$.each(_opts.image,function(){
				var image = this;
				if(image.attr==code.name){
					code.search = false;
					code.formatter = forimage;
					code.unformat = unforimage;
					code.edittype = 'text';
				}
			});
			colModel.push(code);
		});
		var opcode = {};
		if(opaction.view || opaction.edit || opaction.del){
			opcode.label = '操作';
			opcode.name = colkey;
			opcode.search = false;
			opcode.editable = false;
			opcode.sortable = false;
			opcode.width = 125;
			opcode.formatter = foropaction;
			colModel.push(opcode);
		}
		
		//opaction
		
        $("#jqGrid").jqGrid({
            url: url,
            mtype: "post",
			styleUI : 'Bootstrap',
            datatype: "json",
            colModel: colModel,
			viewrecords: qta.viewrecords?qta.viewrecords:true,
			autowidth: qta.autowidth?qta.autowidth:true,
            rowNum:qta.rowNum?qta.rowNum:20,
            rowList:qta.rowList?qta.rowList:[15,30,50],
            rownumbers: qta.rownumbers?qta.rownumbers:true,
            rownumWidth: qta.rownumWidth?qta.rownumWidth:35,
			jsonReader:{
				userdata:'userdata'
			},
			onSelectRow:function(id){
				if(id && id!==Jsd.jqselid){
					Jsd.jqselid=id;
				}
			},
            gridComplete:function() {
            	var table = this;
            	try{
            		if(jqdiag){
            			$('#jqGrid').jqGrid('setGridHeight',300,true);
            		}
            	}catch(e){
            		var gheight = window.gridheight?window.gridheight:document.documentElement.clientHeight-435;
                	$('#jqGrid').jqGrid('setGridWidth',$('div.portlet').outerWidth(true)-40,true);
                	$('#jqGrid').jqGrid('setGridHeight',gheight,false);
            	}
            	$('a#opactionview').each(function(){
            		var othis = this;
            		$(othis).click(function(){
            			Jsd.opviewrow($(this).attr('data-id'));
            		});
            	});
            	$('a#opactiondel').each(function(){
            		var othis = this;
            		$(othis).click(function(){
            			Jsd.opdelrow($(this).attr('data-id'));
            		});
            	});
            	if(typeof operationfungrid === 'function'){
            		operationfungrid();
            	}
            	//$('div.ui-jqgrid-bdiv').css('overflow','visible');
            	//$('div.ui-jqgrid-view').css('overflow-x','visible');
            	$.each(showattrs,function(){
            		var curattr = this,isshow = false;
            		for(var a in xedit){
            			var atemp = xedit[a];
            			if(atemp['attr']==curattr.name){
            				isshow = atemp['xedit'];
            			}
            		}
            		if(!curattr.key && isshow){
        			    $('span[id='+curattr.name+']').each(function(){
        			    	var xetype = 'text',source=[];
        			    	if($(this).attr('data-xetype')=='select1'){
        			    		xetype='select2';
        			    		source = attrcheckbox[curattr.name];
        			    	}
        			    	//
        			    	if($(this).attr('data-xetype')=='local'){
        			    		xetype='select2';
        			    		if(sedittype){
        			    			for(var a in sedittype){
        			    				var atemp = sedittype[a];
        			    				if(atemp.attr==curattr.name){
        			    					if(atemp.editoptions.value){
        			    						var v = atemp.editoptions.value,vt=v.split(';');
        			    						for(var b in vt){
        			    							var btemp = vt[b].split(':');
        			    							if(btemp[0] && btemp[1]){
        			    								source.push({
        			    									id:btemp[0],
        			    									text:btemp[1]
        			    								});	
        			    							}
        			    						}
        			    					}
        			    				}
        			    			}
        			    		}
        			    	}else if($(this).attr('data-xetype')=='model'){
        			    		xetype='select2';
        			    		var seldata = selectdata[curattr.name];
        			    		if(seldata){
        			    			for(var a in seldata){
        			    				var atemp = seldata[a];
        			    				var ct={
        			    						id:a,
        			    						text:atemp
        			    				};
        			    				source.push(ct);
        			    			}
        			    		}
        			    	}else if($(this).attr('data-xetype')=='textarea'){
        			    		xetype = $(this).attr('data-xetype');
        			    	}
        			    	//textarea
        			    	$(this).editable({
        							type: xetype,
        							placement:'bottom',
        							emptytext:Jsd.emptytext,
        							source:source,
        							pk:$(this).attr('data-id'),
        			    			url: function(params){
        				    			   var d = new $.Deferred;
        				    			   Jsd.XEditableSave(d,params,xesaveurl?xesaveurl:'');
        			    			       return d.promise();
        				    		},
        				    		value:$(this).attr('data-value'),
        				    		success:Jsd.XEditsuccess,
        				    	});
        			    });
            		}
            	});
			    //overflow: visible;
            },
            loadComplete:function(){
            	var table = this,result='<option role="option" value="">[全部]</option>';
            	_opts = $(this).getGridParam('userData');
    			$('select[role="select"]').each(function(){
    				$(this).css({'height':'34px'});
    			});
    			
            	$(table).setColProp('parent', {
            		editoptions: {
            			value: editformvalues()
            		}
            	});
    	        $.each(_opts.menus, function () {
    	            var menu = this;
    	            result+='<option role="option" value="'+menu['name']+'">'+menu['name']+'</option>';
    	        });
            	$('select#gs_parent').html(result);
            	Jsd.imgerror();
            },          
            pager: "#jqGridPager",
            editurl:delurl,
            subGrid:Jsd.subgrid,
            subGridRowExpanded:Jsd.showChildGrid
        });

		jQuery("#jqGrid").jqGrid('filterToolbar',{
			autosearch:true,
		});  
    	
        $('#jqGrid').navGrid('#jqGridPager',
        		{
        			edit: navgrid.edit?navgrid.edit:false,
                    add: navgrid.add?navgrid.add:false,
                    del: navgrid.del?navgrid.del:false,
                    search: navgrid.search?navgrid.search:false,
                    refresh: navgrid.refresh?navgrid.refresh:false,
                    view: navgrid.view?navgrid.view:false,
                    position: navgrid.position?navgrid.position:'left',
                    cloneToTop: navgrid.cloneToTop?navgrid.cloneToTop:false
                },
                {
                	//edit
        			top : !curjqdiag?($(window).height()/2)-400:80,
        			left: !curjqdiag?($(window).width()/2)-450:150,
					url:upurl,
					mtype:'post',
					jqModal:false,
					modal:false,
					closeAfterEdit: true,
					closeOnEscape:false,
					recreateForm: true,
					viewPagerButtons: true,
					onclickPgButtons:onpgfun,
					beforeShowForm : beforeShowForm,
					beforeSubmit:beforeSubmit,
					afterSubmit:aftersubmitfun
                },{
                	//add
        			top : !curjqdiag?($(window).height()/2)-400:80,
                	left: !curjqdiag?($(window).width()/2)-450:150,
					url:addurl,
					mtype:'post',
					jqModal:false,
					modal:false,
					closeAfterAdd: true,
					closeOnEscape:false,
					recreateForm: true,
					beforeShowForm : beforeShowForm,
					beforeSubmit:beforeSubmit,
					afterSubmit:aftersubmitfun				
                },
                {
                	//delete
        			top : !curjqdiag?($(window).height()/2):200,
                	left: !curjqdiag?($(window).width()/2):250,
					url:delurl,
					mtype:'post',
					modal:false,
					closeOnEscape:true,
					recreateForm: true,
                },
                {
                	multipleSearch: true,
                	multipleGroup: true,
                	showQuery: true
                },{},{});
        $('select[role="listbox"]').css('width','auto');
		$(window).resize(function(e){
			//$('#jqGrid').jqGrid('setGridWidth',$('div.portlet').outerWidth(true)-40,true);
			//$('#jqGrid').jqGrid('setGridHeight',$('div.page-container').outerHeight(true)-430,true);
		});

		function aftersubmitfun(response, postdata){
			var m = true,msg = '';
			try{
				var data = $.parseJSON(response.responseText);
				if(data.error>0){
					m=false;
					for(var a in data.msg){
						if(!msg){
							msg = data.msg[a];
						}
					}
				}
			}catch(e){
				m=false;
				msg= '非法请求';
			}
			return [m,msg];
		}
		function parent_format(cellvalue, options, cell){
			var r = '(未设置)';
			var t = $("#jqGrid").getGridParam('userData');
	        $.each(t.menus, function () {
	            var menu = this;
	            if(cellvalue==menu['id']){
	            	r = menu['name'];
	            }
	        });
	        return r;
		}
		function beforeSubmit(postdata, formid){
			$.each(colModel,function(){
				var cthis = this;
				if(cthis.editable){
					//postdata[""+cthis.index+""] = postdata[cthis.name];
				}
			});
			return [true,''];
		}
		function beforeShowFormedit(e){
			Jsd.jqgridbeforeshow();
			var form = $(e[0]);
			$.each(imagevals,function(){
				var img = this;
				$(form).find('#'+img.attr).each(function(){
					//$(this).hide();
					$(this).before($(this).val());
					$(this).val($(this).prev().attr('src'));
				});
			});
			Jsd.imgerror();
		}
		function beforeShowForm(e){
			Jsd.jqgridbeforeshow();
			var form = $(e[0]);
			$.each(imagevals,function(){
				var img = this;
				$(form).find('#'+img.attr).each(function(){
					$(this).hide();
					var h = '<img src="'+$(this).val()+'" style="width:40px;height:40px;cursor:pointer;" onclick="Jsd.imagedialog(this);"/>';
					$(this).before(h);
				});
			});
			Jsd.imgerror();
		}
		//日期
		function dateinit(element){
            $(element).datetimepicker({
				autoclose: true,
				language:  'zh',
				format: "yyyy-m-dd hh:ii:ss",
				pickerPosition : 'bottom'
            });
		}
		//checkbox
		function formatcheckbox(cellvalue, options, cell){
			var name=options.colModel.name,yes='是',no='否',ruleval=cellvalue,newcellvalue=yes;
			$.each(sedittype,function(){
				var edittype = this,val = '1:0';
				if(edittype.attr==name && edittype.editoptions.value){
					val = edittype.editoptions.value;
				}
				val = val.split(":");
				val[0] = val[0]?val[0]:1;
				val[1] = val[1]?val[1]:0;
				$.each(checkboxtxt,function(){
					var checkb = this;
					if(checkb.attr==name){
						yes = checkb.yes?checkb.yes:yes;
						no = checkb.no?checkb.no:no;
						newcellvalue = yes;
						if(cellvalue==val[1]){
							newcellvalue = no;
						}
						attrcheckbox[name]=[];
						attrcheckbox[name].push({"id":val[0],'text':yes});
						attrcheckbox[name].push({"id":val[1],'text':no});
					}
				});
			});
			cellvalue = newcellvalue;
			//attrcheckbox
			return '<span data-value="'+ruleval+'" id="'+options.colModel.name+'" data-id="'+cell.id+'" style="cursor:pointer;" data-xetype="select1">'+cellvalue+'</span>';
		}
		//un checkbox
		function unformatcheckbox(cellvalue, options, cell){
			return $('span', cell).attr('data-value');
		}
		
		function defaultformatter(cellvalue, options, cell){
				if(typeof window[options.colModel.name+'Formatter'] === 'function'){
					return window[options.colModel.name+'Formatter'](cellvalue, options, cell);
				}else{
					var xtype='text';
					$.each(sedittype,function(){
						var vet = this;
						if(vet.attr==options.colModel.name){
							if(vet.edittype=='textarea'){
								xtype = vet.edittype;
							}
						}
					});
					return '<span class="editable" id="'+options.colModel.name+'" data-id="'+cell.id+'" data-value="'+cellvalue+'" style="cursor:pointer;" data-xetype="'+xtype+'">'+cellvalue+'</span>';
				}
		}
		function undefaultformatter(cellvalue, options, cell){
			return $('span', cell).attr('data-value');
		}
		//select
		function formatselect(cellvalue, options, cell){
			var selvus = selectdata[options.colModel.name],curtype='local',curcellvalue=cellvalue;
			if(selvus){
				for(var a in selvus){
					var atemp = selvus[a];
					if(cellvalue==a){
						cellvalue = atemp;
					}
				}
			}else{
				selvus = '';
				$.each(sedittype,function(){
					var vtrs = this;
					if(vtrs.attr==options.colModel.name && vtrs.editoptions.value!=''){
						selvus += ''+vtrs.editoptions.value;
					}
				});
				var b = selvus.split(';');
				for(var c in b){
					var ctemp = b[c].split(':');
					if(ctemp[0] && cellvalue==ctemp[0]){
						cellvalue = ctemp[1];
					}
				}
			}
			if(selectdata[options.colModel.name]){
				curtype = 'model';
			}
			return '<span class="editable" id="'+options.colModel.name+'" data-id="'+cell.id+'" data-value="'+curcellvalue+'" data-xetype="'+curtype+'" style="cursor:pointer;">'+cellvalue+'</span>';
		}
		//图片过滤
		function forimage(cellvalue, options, cell){
			var h = '<img src="'+cellvalue+'" onerror="" width="40" height="40" alt="40x40" data-imgurl="'+cellvalue+'"/>';
			return h;
		}		
		//操作过滤
		function foropaction(cellvalue, options, cell){
			if(typeof operationfun === 'function'){
				return operationfun(cellvalue, options, cell);
			}else{
				var h = '';
				if(cell[colkey]){
					if(opaction.view){
						h += '<a href="javascript:void(0);" data-id="'+cell[colkey]+'" id="opactionview">'+opaction.viewtxt+'</a>';
					}
					h += (opaction.view?' | ':'');
					if(opaction.del){
						h += '<a href="javascript:void(0);" data-id="'+cell[colkey]+'" id="opactiondel">'+opaction.deltxt+'</a>';
					}
				}
				return h;
			}
		}	
		//修改数据时反过滤
		function unforimage(cellvalue, options, cell){
			return $('img', cell).attr('data-imgurl');
		}
		//编辑对话框上一个下一个按钮事件
		function onpgfun(whichbutton, formid, rowid){
			var form = $(formid[0]);
			var ids = $("#jqGrid").jqGrid('getDataIDs'),curid;
			for(var a in ids){
				if(ids[a]==rowid){
					curid = a;
				}
			}
			curid = parseInt(curid);
			if(whichbutton=='prev'){
				curid--;
			}else if(whichbutton=='next'){
				curid++;
			}
			rowid = ids[curid];
			var rowdata = $("#jqGrid").jqGrid('getRowData',rowid);
			$('img',form).attr('src',rowdata.address);
		}
	});
	
})(jQuery);