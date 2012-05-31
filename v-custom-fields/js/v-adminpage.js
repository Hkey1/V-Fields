$(document).ready(function() {
	pic = new Image();
	pic.src = "/wp-content/plugins/v-custom-fields/img/wait.gif";
    /////Tabs/////////////////////////////
	//////////////////////////////////////
    $("#tabs").tabs();
	//////////////////////////////////////
    //////////////////////////////////////
	//Hover fo buttons
	$('.ui-button').hover(
			function() { $(this).addClass('ui-state-hover'); }, 
			function() { $(this).removeClass('ui-state-hover'); }
	);
	//////////////////////////////////////
	////Ajax Form
	$("#fields").ajaxForm(function(data){
		alert(data);
	});
	//Bind an action to open close dialog
	$(".ui-icon-closethick").live("click", function(){
		var element_to_delete = $(this).parents(".section");
		$("#delete_field_dialog").dialog({
			buttons : [ {
				text : "Ok",
				click : function() {
					element_to_delete.fadeOut('slow',function(){
						$( "#accordion" ).accordion("destroy");
						element_to_delete.remove();
						$("#accordion").accordion({
							header : "> div > h3"
						}).sortable({
							axis : "y",
							handle : "h3",
							stop : function() {
								stop = true;
							}
						});
					});
					$(this).dialog("close");
				}
			},{
				text: "Cancel",
				click : function(){
					$(this).dialog("close");
				}}
			]
		}, {
			modal : true
		}, {
			resizable : false
		}
		);
	});
	
	
    /////////////////////////////////////////
	////Dialog and adding new field
	$("#add_field_button").click(function(){
		// Modal dialog to add a type of form
		$("#add_field_dialog").dialog({
			buttons : [ {
				text : "Ok",
				click : function() {
					choice = $("#add_field_dialog .select").val();
					$('#accordion').append(proper_html(choice))				
					.accordion('destroy').accordion({
						header : "> div > h3",
						autoHeight: false
					}).sortable({
						axis : "y",
						handle : "h3",
						stop : function() {
							stop = true;
						}
					});
					$(this).dialog("close");
					$("#save_all").show();

				}
			},{
				text: "Cancel",
				click : function(){
					$(this).dialog("close");
				}}
			]
		}, {
			modal : true
		}, {
			resizable : false
		});
	});
    ///////////////////////////////////////////////////
	//Function of proper html to add in
	function proper_html(choice)
	{ 
		str = "";
		$.ajax({
			  type: "POST",
			  url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
			  async: false,
			  data: {operation: "new_options",classname: choice},
			  success: function(data){
			    str = data;
			  }
			});
		return str;
	}
	
    //////////////////////////////////////////////////
	// Accordeon
	$(function() {
		var stop = false;
		$("#accordion h3").live("click",(function(event) {
			//alert(event.target.className);
			if(event.target.className == "name" || event.target.className == "select")
				{
				stop = true;
				
				event.target.focus();
				}

			if (stop) {
				event.stopImmediatePropagation();
				event.preventDefault();
				stop = false;
			}
		}));
		$("#accordion").accordion({
			header : "> div > h3",
			autoHeight: false
		}).sortable({
			axis : "y",
			handle : "h3",
			stop : function() {
				stop = true;
			}
		});
	});
   /////Save//////////////////////////////////////////////////
   ///////////////////////////////////////////////////////////
   //Array with field optons(name,default value,size, e.t.c)//
	var fields = new Array();
   ///////////////////////////////////////////////////////////
	
	$("#save_all").click(function(){

	    fields = $(".section *").serialize();
		$.post("/wp-content/plugins/v-custom-fields/v-ajax.php",{operation: "save_fields", data: fields}, function(data)
				{
			        $("#error,#saved").hide();
					if(data == "SAVED")
						$("#saved").show("slow");
					else
						{
							$("#error p").html(data);
							$("#error").show("slow");
						}
					
				});
	});
	$('#save_all').keydown(function(event) {
		  if (event.ctrlKey && event.shiftKey) { 
			  fields = $(".section *").serialize();
			  	$("#options_dialog").html(fields);
				$("#options_dialog").dialog({
					buttons : [ {
						text : "Ok",
						click : function() {
							$(this).dialog("close");
						}
					}
					]
				}, {
					modal : true
				}, {
					resizable : false
				});
		  }
		});
	
	/////////////При смене типа поля выводим соответствующие настройки
	$(".select").live("change",(function(event)
			{
				 current_parent = $(this).parents(".section");
				 current_div = $(current_parent).children(".field_settings");
				 $.ajax({ 
						 type: "POST",
						 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
						 data: {operation: "change_type",data: $(event.target).val()},
						 beforeSend: function()
						 {
							 current_div.html("<img src=\"/wp-content/plugins/v-custom-fields/img/wait.gif\">");
						 },
						 success :function(data)
						 {
					        current_div.html(data);

						 }
						 });
			}));
	
	$("#error,#saved").hide();
	$("#preview_fields").hide();
	$("#preview").click(function()
	{		
		jQuery.ajax({
		    url:'/wp-admin/post-new.php',
		    type:'get',
		    dataType:'html',
		    success:function(data)
		   { 
		       var _html= jQuery(data);
		       var custom_fields_preview = $(_html).find("#myplugin_sectionid");
		       //do some thing with html eg: _html.find('div').addClass('red')
		       jQuery('#preview_fields').html(custom_fields_preview);
				$("#preview_fields").dialog({
					buttons : [ {
						text : "Ok",
						click : function() {
							$("#preview_fields").html("");
							$("#preview_fields").hide();
							$(this).dialog("close");

						}
					}
					]
				}, {
					modal : true
				},
				{
					minWidth: 450 
					},
				{
					resizable : true
				});
		   }
		});




	});
	
});
