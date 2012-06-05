<?php
include_once ($_SERVER['DOCUMENT_ROOT']."/wp-content/plugins/v-custom-fields/v-functions.php");
?>
<h2>Custom fields settings</h2>
<div id="tabs">
    <ul>
        <li><a href="#tab-fields"><span>Fields</span></a></li>
        <li><a href="#tab-settings"><span>Settings</span></a></li>
    </ul>
    <div id="tab-fields">
<div id="accordion">
<?php 
	echo show_fields_options();
?>
</div>
<button id="add_field_button" class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all">
   <span class="ui-button-text">Add field</span>
</button>

<button id="save_all" <?php if(!field_count()) echo "style=\"display:none;\"";?> class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all">
   <span class="ui-button-text">Save</span>
</button>
        <span id="preview" style="position: relative; float: right;font-size: 16px; border-bottom: 1px dashed black; cursor: help;"><b>Preview</b></span>
<div class="ui-state-highlight ui-corner-all" id="saved" style="margin-top: 20px; padding: 0 .7em;"> 
	<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
	<strong>SAVED!</strong></p>
</div>
<div class="ui-state-error ui-corner-all" id="error" style="padding: 0 .7em; margin-top: 20px;"> 
	<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
	</p>
</div> 
<div id="add_field_dialog" title="Select type">
<select class="select">
	<?php
	    $fieldtypes = get_field_names();
	    foreach($fieldtypes as $row)
	    {
	    	echo "<option value=\"$row\">".$row."</option>";
	    }
	?>
</select>
</div>

<div id="preview_fields" title="preview"></div>

<div id="delete_field_dialog" title="Confirmation">Are you sure you want to delete this field</div>

    </div>
    <div id="tab-settings">
		<p>Settings</p></br>

	<p>Custom fields for page:</p>
	<div id="radio">
		<input type="radio" id="radio1" name="radio" checked="checked" value="0"/><label for="radio1">None</label>
		<input type="radio" id="radio2" name="radio" value="1"/><label for="radio2">As posts</label>
	</div>			

    </div>
</div>

<div id="options_dialog" title="Fields options code"></div>
