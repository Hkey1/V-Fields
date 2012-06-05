$(document).ready(function(){
    $("#draggable").draggable();
	//TEXT
	$(".text").dblclick(function()
	{
		var attr = $(this).attr('id');
		var backup = $("."+attr+ " .backup_value").val();
		$("."+attr).dialog({
			buttons : [ {
				text : "Ok",
				click : function()
				{
					$.ajax({ 
						 type: "POST",
						 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
						 data: {operation: "update_field",fieldtype: "Text",fieldid: attr,data: $("."+attr+" textarea").val()},
						 beforeSend: function()
						 {

						 },
						 success :function(data)
						 {
							$("."+attr+" .backup_value").val($("."+attr+" textarea").val()); 
					        $("#"+attr).html(data);
						 }
						 });
					$(this).dialog("close");
				}
			},{
				text: "Cancel",
				click : function(){
					$("."+attr+" textarea").val(backup);
					$(this).dialog("close");
				}}
			]
		}, {
			modal : false
		}, {
			resizable : false
		});
	});
	
	//DIGIT
	$(".digit").dblclick(function()
	{
		var attr = $(this).attr('id');
		$("."+attr).dialog({
			buttons : [ {
				text : "Ok",
				click : function()
				{
					$.ajax({ 
						 type: "POST",
						 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
						 data: {operation: "update_field",fieldtype: "Digit",fieldid: attr,data: $("."+attr+" .edit_digit").val(),params: $("."+attr+" .backup_params").val()},
						 beforeSend: function()
						 {

						 },
						 success :function(data)
						 {
					        $("#"+attr).html(data);
						 }
						 });
					$(this).dialog("close");
				}
			},{
				text: "Cancel",
				click : function(){
					//$("."+attr+" input").val($("#"+attr).html());
					$(this).dialog("close");
				}}
			]
		}, {
			modal : false
		}, {
			resizable : false
		});
	});
	
	//При нажатии на Enter закрываем диалог редактирования онлайн
    $(".edit_digit,.edit_text,.edit_string,.edit_bool,.edit_select").keypress(function(e)
            {
                code= (e.keyCode ? e.keyCode : e.which);
                if (code == 13)
                	{
                		classname = $(e.target).parent().attr('class').split(' ')[0];
                		buttons = $("."+classname).dialog('option', 'buttons');
                		buttons[0].click();
                		$("."+classname).dialog("close");
                	}


            });

	//String
	$(".string").dblclick(function()
	{
		var attr = $(this).attr('id');
		$("."+attr).dialog({
			buttons : [ {
				text : "Ok",
				click : function()
				{
					$.ajax({ 
						 type: "POST",
						 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
						 data: {operation: "update_field",fieldtype: "String",fieldid: attr,data: $("."+attr+" input").val()},
						 beforeSend: function()
						 {

						 },
						 success :function(data)
						 {
					        $("#"+attr).html(data);
						 }
						 });
					$(this).dialog("close");
				}
			},{
				text: "Cancel",
				click : function(){
					$("."+attr+" input").val($("#"+attr).html());
					$(this).dialog("close");
				}}
			]
		}, {
			modal : false
		}, {
			resizable : false
		});
	});
	//Select
	$(".select").dblclick(function()
	{
		var attr = $(this).attr('id');
		$("."+attr).dialog({
			buttons : [ {
				text : "Ok",
				click : function()
				{
					$.ajax({ 
						 type: "POST",
						 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
						 data: {operation: "update_field",fieldtype: "Select",fieldid: attr,data: $("."+attr+" select option:selected").val()},
						 beforeSend: function()
						 {

						 },
						 success :function(data)
						 {
					        $("#"+attr).html(data);
						 }
						 });
					$(this).dialog("close");
				}
			},{
				text: "Cancel",
				click : function(){
					var data =$("#"+attr).html();
					//var selected = parseInt($("."+attr+" input").val())+1;
					$("."+attr+" select :contains("+data+")").attr("selected","selected");

					$(this).dialog("close");
				}}
			]
		}, {
			modal : false
		}, {
			resizable : false
		});
	});
	//Bool
	$(".bool").click(function()
	{
		var attr = $(this).attr('id');
		if($(this).val() == 1)
			$(this).val(0);
		else
			$(this).val(1);
		$.ajax({ 
			 type: "POST",
			 url: "/wp-content/plugins/v-custom-fields/v-ajax.php",
			 data: {operation: "update_field",fieldtype: "Bool",fieldid: attr,data: $("#"+attr).val()},
			 beforeSend: function()
			 {

			 },
			 success :function(data)
			 {
		        
			 }
			 });
		
	});

	//Edit fields all
	$("#draggable a").click(function()
	{
		$("#edit_fields").dialog({
			buttons : [ {
				text : "Ok",
				click : function()
				{
					//var fields = new Array();
					$(".postgroup").each(function(i)
							{
								post_id = $(this).attr('id');
								//fields[post_id] = "hello";
								fields = $("#"+post_id.toString()+" *").serialize();
								alert(fields);
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
		}, {
			open: function(){
                $("#accordion").accordion({ autoHeight: false },{ collapsible: true});
            }
		},
		{ maxHeight: 500 },{ minWidth: 500 }
		);
		return false;
	});
	//
});