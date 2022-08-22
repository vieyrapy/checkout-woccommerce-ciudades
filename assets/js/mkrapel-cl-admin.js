var mkrapel_cl_settings = (function($, window, document) {
	'use strict';

	var MSG_INVALID_NAME = 'NAME/ID must begin with a lowercase letter ([a-z]) and may be followed by any number of lowercase letters, digits ([0-9]) and underscores ("_")';
	var OPTION_ROW_HTML  = '<tr>';
        OPTION_ROW_HTML += '<td style="width:150px;"><input type="text" name="i_options_key[]" placeholder="Option Value" style="width:140px;"/></td>';
		OPTION_ROW_HTML += '<td style="width:190px;"><input type="text" name="i_options_text[]" placeholder="Option Text" style="width:180px;"/></td>';
		OPTION_ROW_HTML += '<td class="action-cell"><a href="javascript:void(0)" onclick="mkrapelclAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a></td>';
		OPTION_ROW_HTML += '<td class="action-cell"><a href="javascript:void(0)" onclick="mkrapelclRemoveOptionRow(this)" class="btn btn-red" title="Remove option">x</a></td>';
		OPTION_ROW_HTML += '<td class="action-cell sort ui-sortable-handle"></td>';
		OPTION_ROW_HTML += '</tr>';

	$(function() {
		$( "#mkrapel_cl_new_field_form_pp" ).dialog({
		  	modal: true,
			width: 600,
			resizable: false,
			autoOpen: false,
			buttons: [{
				text: "Save",
				click: function() {
					var form = $("#mkrapel_cl_new_field_form");
					var valid = validate_field_form(form);
					if(valid){ 
						prepare_field_form(form);
						form.submit(); 
					}
				}
			}]
		});
		
		$( "#mkrapel_cl_edit_field_form_pp" ).dialog({
		  	modal: true,
			width: 600,
			resizable: false,
			autoOpen: false,
			buttons: [{
				text: "Save",
				click: function() {
					var form = $("#mkrapel_cl_edit_field_form");
					var valid = validate_field_form(form);
					if(valid){ 
						prepare_field_form(form);
						form.submit(); 
					}
				}
			}]
		});

		$('select.mkrapel-cl-enhanced-multi-select').select2({
			minimumResultsForSearch: 10,
			allowClear : true,
			placeholder: $(this).data('placeholder')
		}).addClass('enhanced');

		$('#mkrapel_cl_checkout_fields tbody').sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: 'td.sort',
			scrollSensitivity:40,
			helper:function(e,ui){
				ui.children().each(function(){
					$(this).width($(this).width());
				});
				ui.css('left', '0');
				return ui;
			}
		});

		$("#mkrapel_cl_checkout_fields tbody").on("sortstart", function( event, ui ){
			ui.item.css('background-color','#f6f6f6');										
		});
		$("#mkrapel_cl_checkout_fields tbody").on("sortstop", function( event, ui ){
			ui.item.removeAttr('style');
			prepare_field_order_indexes();
		});
	});

	function isHtmlIdValid(id) {
		var re = /^[a-z\_]+[a-z0-9\_]*$/;
		return re.test(id.trim());
	}

	function decodeHtml(str) {
		if($.type(str)=== "string"){
			var map = {
	        	'&amp;': '&',
	        	'&lt;': '<',
	        	'&gt;': '>',
	        	'&quot;': '"',
	        	'&#039;': "'"
	    	};
	    	return str.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/g, function(m) {return map[m];});
		}
	   	return str;
	}

	function get_property_field_value(form, type, name){
		var value = '';
		
		switch(type) {
			case 'select':
				value = form.find("select[name=i_"+name+"]").val();
				value = value == null ? '' : value;
				break;
				
			case 'checkbox':
				value = form.find("input[name=i_"+name+"]").prop('checked');
				value = value ? 1 : 0;
				break;

			case 'textarea':
				value = form.find("textarea[name=i_"+name+"]").val();
				value = value == null ? '' : value;
				
			default:
				value = form.find("input[name=i_"+name+"]").val();
				value = value == null ? '' : value;
		}	
		
		return value;
	}
	
	function set_property_field_value(form, type, name, value, multiple){
		switch(type) {
			case 'select':
				if(multiple == 1){
					value = typeof(value) === 'string' ? value.split(",") : value;
					name = name+"[]";
					form.find('select[name="i_'+name+'"]').val(value).trigger("change");
				}else{
					form.find('select[name="i_'+name+'"]').val(value);
				}
				break;
				
			case 'checkbox':
				value = value == 1 ? true : false;
				form.find("input[name=i_"+name+"]").prop('checked', value);
				break;

			case 'textarea':
				value = value ? decodeHtml(value) : value;
				form.find("textarea[name=i_"+name+"]").val(value);
				break;
				
			default:
				value = value ? decodeHtml(value) : value;
				form.find("input[name=i_"+name+"]").val(value);
		}	
	}

	function openNewFieldForm(sname){
		if(sname == 'billing' || sname == 'shipping' || sname == 'additional'){
			sname = sname+'_';	
		}
		
		var form = $("#mkrapel_cl_new_field_form");
		clear_field_form(form);
		form.find("select[name=i_type]").change();

		set_property_field_value(form, 'text', 'name', sname, 0);
		set_property_field_value(form, 'text', 'class', 'form-row-wide', 0);

	  	$("#mkrapel_cl_new_field_form_pp").dialog("open");
	}

	function openEditFieldForm(elm, rowId){
		var row = $(elm).closest('tr');
		var form = $("#mkrapel_cl_edit_field_form");

		var props_json = row.find(".f_props").val();
		//props_json = decodeHtml(props_json);
		var props = JSON.parse(props_json);
		//var type = props.type;

		populate_field_form_general(form, props)
		form.find("select[name=i_type]").change();
		populate_field_form(row, form, props);

	  	$("#mkrapel_cl_edit_field_form_pp").dialog("open");
	}

	function clear_field_form( form ){
		form.find('.err_msgs').html('');

		set_property_field_value(form, 'hidden', 'autocomplete', '', 0);
		set_property_field_value(form, 'hidden', 'priority', '', 0);
		set_property_field_value(form, 'hidden', 'custom', '', 0);
		set_property_field_value(form, 'hidden', 'oname', '', 0);
		set_property_field_value(form, 'hidden', 'otype', '', 0);

		set_property_field_value(form, 'select', 'type', 'text', 0);
		set_property_field_value(form, 'text', 'name', '', 0);
		set_property_field_value(form, 'text', 'label', '', 0);
		set_property_field_value(form, 'text', 'placeholder', '', 0);
		set_property_field_value(form, 'text', 'default', '', 0);
		set_property_field_value(form, 'text', 'class', '', 0);
		set_property_field_value(form, 'select', 'validate', '', 1);

		set_property_field_value(form, 'checkbox', 'required', 1, 0);
		set_property_field_value(form, 'checkbox', 'enabled', 1, 0);
		set_property_field_value(form, 'checkbox', 'show_in_email', 1, 0);
		set_property_field_value(form, 'checkbox', 'show_in_order', 1, 0);

		populate_options_list(form, false);
	}

	function populate_field_form_general(form, props){
		var autocomplete = props['autocomplete'] ? props['autocomplete'] : '';
		var priority = props['priority'] ? props['priority'] : '';
		var custom = props['custom'] ? props['custom'] : '';

		var type = props['type'] ? props['type'] : 'text';
		var name = props['name'] ? props['name'] : '';

		set_property_field_value(form, 'hidden', 'autocomplete', autocomplete, 0);
		set_property_field_value(form, 'hidden', 'priority', priority, 0);
		set_property_field_value(form, 'hidden', 'custom', custom, 0);
		set_property_field_value(form, 'hidden', 'oname', name, 0);
		set_property_field_value(form, 'hidden', 'otype', type, 0);
		set_property_field_value(form, 'select', 'type', type, 0);
		set_property_field_value(form, 'text', 'name', name, 0);
	}

	function populate_field_form(row, form, props, custom){
		var custom = props['custom'] ? props['custom'] : '';

		var label = props['label'] ? props['label'] : '';
		var placeholder = props['placeholder'] ? props['placeholder'] : '';
		var default_val = props['default'] ? props['default'] : '';
		var cssclass = props['class'] ? props['class'] : '';
		var validate = props['validate'] ? props['validate'] : '';
		var required = props['required'] && (props['required'] || props['required'] === 'yes') ? 1 : 0;
		var enabled = props['enabled'] && (props['enabled'] || props['enabled'] === 'yes') ? 1 : 0;
		var show_in_email = props['show_in_email'] && (props['show_in_email'] || props['show_in_email'] === 'yes') ? 1 : 0;
		var show_in_order = props['show_in_order'] && (props['show_in_order'] || props['show_in_order'] === 'yes') ? 1 : 0;

		show_in_email = custom == 1 ? show_in_email : true;
		show_in_order = custom == 1 ? show_in_order : true;

		set_property_field_value(form, 'text', 'label', label, 0);
		set_property_field_value(form, 'text', 'placeholder', placeholder, 0);
		set_property_field_value(form, 'text', 'default', default_val, 0);
		set_property_field_value(form, 'text', 'class', cssclass, 0);
		set_property_field_value(form, 'select', 'validate', validate, 1);
		set_property_field_value(form, 'checkbox', 'required', required, 0);
		set_property_field_value(form, 'checkbox', 'enabled', enabled, 0);
		set_property_field_value(form, 'checkbox', 'show_in_email', show_in_email, 0);
		set_property_field_value(form, 'checkbox', 'show_in_order', show_in_order, 0);

		var optionsJson = row.find(".f_options").val();
		populate_options_list(form, optionsJson);

		if(custom == 1){	
			form.find("input[name=i_name]").prop('disabled', false);
			form.find("select[name=i_type]").prop('disabled', false);
			form.find("input[name=i_show_in_email]").prop('disabled', false);
			form.find("input[name=i_show_in_order]").prop('disabled', false);
		}else{
			form.find("input[name=i_name]").prop('disabled', true);
			form.find("select[name=i_type]").prop('disabled', true);
			form.find("input[name=i_show_in_email]").prop('disabled', true);
			form.find("input[name=i_show_in_order]").prop('disabled', true);
			form.find("input[name=i_label]").focus();	
		}
	}

	function prepare_field_form(form){
		var options_json = get_options(form);
		set_property_field_value(form, 'hidden', 'options_json', options_json, 0);
	}

	function validate_field_form(form){
		var err_msgs = '';
		var name = get_property_field_value(form, 'text', 'name');
		var type = get_property_field_value(form, 'select', 'type');
		var otype = get_property_field_value(form, 'select', 'otype');

		if(type == '' && otype != 'country' && otype == 'state'){
			err_msgs = 'Type is required';
		}else if(name == ''){
			err_msgs = 'Name is required';
		}else if(!isHtmlIdValid(name)){
			err_msgs = MSG_INVALID_NAME;
		}	
		
		if(err_msgs != ''){
			form.find('.err_msgs').html(err_msgs);
			return false;
		}
		return true;
	}

	function fieldTypeChangeListner(elm){
		var type = $(elm).val();
		var form = $(elm).closest('form');
		
		showAllFields(form);

		if(type === 'select'){			
			form.find('.row-validate').hide();

		}else if(type === 'radio'){			
			form.find('.row-validate').hide();
			form.find('.row-placeholder').hide();

		}else{			
			form.find('.row-options').hide();
		}			
	}
	
	function showAllFields(form){
		form.find('.row-options').show();
		form.find('.row-placeholder').show();
		form.find('.row-validate').show();
	}

	/*------------------------------------
	*---- OPTIONS FUNCTIONS - SATRT ------
	*------------------------------------*/
	function get_options(form){
		var optionsKey  = form.find("input[name='i_options_key[]']").map(function(){ return $(this).val(); }).get();
		var optionsText = form.find("input[name='i_options_text[]']").map(function(){ return $(this).val(); }).get();
		
		var optionsSize = optionsText.length;
		var optionsArr = [];
		
		for(var i=0; i<optionsSize; i++){
			var optionDetails = {};
			optionDetails["key"] = optionsKey[i];
			optionDetails["text"] = optionsText[i];
			
			optionsArr.push(optionDetails);
		}
		
		var optionsJson = optionsArr.length > 0 ? JSON.stringify(optionsArr) : '';
		optionsJson = encodeURIComponent(optionsJson);
		return optionsJson;
	}

	function populate_options_list(form, optionsJson){
		var optionsHtml = "";
		
		if(optionsJson){
			try{
				optionsJson = decodeURIComponent(optionsJson);
				var optionsList = $.parseJSON(optionsJson);
				if(optionsList){
					jQuery.each(optionsList, function() {
						var html  = '<tr>';
						html += '<td style="width:150px;"><input type="text" name="i_options_key[]" value="'+this.key+'" placeholder="Option Value" style="width:140px;"/></td>';
						html += '<td style="width:190px;"><input type="text" name="i_options_text[]" value="'+this.text+'" placeholder="Option Text" style="width:180px;"/></td>';
						html += '<td class="action-cell"><a href="javascript:void(0)" onclick="mkrapelclAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a></td>';
						html += '<td class="action-cell"><a href="javascript:void(0)" onclick="mkrapelclRemoveOptionRow(this)" class="btn btn-red" title="Remove option">x</a></td>';
						html += '<td class="action-cell sort ui-sortable-handle"></td>';
						html += '</tr>';
						
						optionsHtml += html;
					});
				}
			}catch(err) {
				console.log(err);
			}
		}
		
		var optionsTable = form.find(".mkrapel-cl-option-list tbody");
		if(optionsHtml){
			optionsTable.html(optionsHtml);
		}else{
			optionsTable.html(OPTION_ROW_HTML);
		}
	}

	function add_new_option_row(elm){
		var ptable = $(elm).closest('table');
		var optionsSize = ptable.find('tbody tr').size();
			
		if(optionsSize > 0){
			ptable.find('tbody tr:last').after(OPTION_ROW_HTML);
		}else{
			ptable.find('tbody').append(OPTION_ROW_HTML);
		}
	}
	
	function remove_option_row(elm){
		var ptable = $(elm).closest('table');
		$(elm).closest('tr').remove();
		var optionsSize = ptable.find('tbody tr').size();
			
		if(optionsSize == 0){
			ptable.find('tbody').append(OPTION_ROW_HTML);
		}
	}
	/*------------------------------------
	*---- OPTIONS FUNCTIONS - END --------
	*------------------------------------*/
	
	function prepare_field_order_indexes() {
		$('#mkrapel_cl_checkout_fields tbody tr').each(function(index, el){
			$('input.f_order', el).val( parseInt( $(el).index('#mkrapel_cl_checkout_fields tbody tr') ) );
		});
	};
	
	function selectAllCheckoutFields(elm){
		var checkAll = $(elm).prop('checked');
		$('#mkrapel_cl_checkout_fields tbody input:checkbox[name=select_field]').prop('checked', checkAll);
	}

	function removeSelectedFields(){
		$('#mkrapel_cl_checkout_fields tbody tr').removeClass('thpladmin-strikeout');
		$('#mkrapel_cl_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');

			if(!row.hasClass("thpladmin-strikeout")){
				row.addClass("thpladmin-strikeout");
			}

			row.find(".f_deleted").val(1);
			row.find(".f_edit_btn").prop('disabled', true);
	  	});	
	}

	function enableDisableSelectedFields(enabled){
		$('#mkrapel_cl_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			row.find(".f_enabled").val(enabled);

			if(enabled == 0){
				if(!row.hasClass("thpladmin-disabled")){
					row.addClass("thpladmin-disabled");
				}

				row.find(".f_edit_btn").prop('disabled', true);
				row.find(".td_enabled").html('-');
			}else{
				row.removeClass("thpladmin-disabled");	

				row.find(".f_edit_btn").prop('disabled', false);
				row.find(".td_enabled").html('<span class="dashicons dashicons-yes"></span>');			
			}
	  	});	
	}
	
	return {
		openNewFieldForm : openNewFieldForm,
		openEditFieldForm : openEditFieldForm,
		selectAllCheckoutFields : selectAllCheckoutFields,
		removeSelectedFields : removeSelectedFields,
		enableDisableSelectedFields : enableDisableSelectedFields,
		fieldTypeChangeListner : fieldTypeChangeListner,
		addNewOptionRow : add_new_option_row,
		removeOptionRow : remove_option_row,
   	};
}(window.jQuery, window, document));	

function mkrapelclOpenNewFieldForm(tabName){
	mkrapel_cl_settings.openNewFieldForm(tabName);		
}

function mkrapelclOpenEditFieldForm(elm, rowId){
	mkrapel_cl_settings.openEditFieldForm(elm, rowId);		
}
	
function mkrapelclRemoveSelectedFields(){
	mkrapel_cl_settings.removeSelectedFields();
}

function mkrapelclEnableSelectedFields(){
	mkrapel_cl_settings.enableDisableSelectedFields(1);
}

function mkrapelclDisableSelectedFields(){
	mkrapel_cl_settings.enableDisableSelectedFields(0);
}

function mkrapelclFieldTypeChangeListner(elm){	
	mkrapel_cl_settings.fieldTypeChangeListner(elm);
}
	
function mkrapelclSelectAllCheckoutFields(elm){
	mkrapel_cl_settings.selectAllCheckoutFields(elm);
}

function mkrapelclAddNewOptionRow(elm){
	mkrapel_cl_settings.addNewOptionRow(elm);
}
function mkrapelclRemoveOptionRow(elm){
	mkrapel_cl_settings.removeOptionRow(elm);
}