<?php
include_once ($_SERVER['DOCUMENT_ROOT']."/wp-content/plugins/v-custom-fields/v-functions.php");
?>
<h2>Snippets</h2>
		<div id="accordion">
			<?php 
				echo load_snippets();
			?>
		</div>
		<button id="add_field_button" class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all">
		   <span class="ui-button-text">Add snippet</span>
		</button>

<button id="save_all" class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all">
   <span class="ui-button-text">Save</span>
</button>

				<div class="ui-state-highlight ui-corner-all" id="saved" style="margin-top: 20px; padding: 0 .7em;"> 
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<strong>SAVED!</strong></p>
				</div>
				<div class="ui-state-error ui-corner-all" id="error" style="padding: 0 .7em; margin-top: 20px;"> 
					<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
					</p>
				</div> 
<div id="add_field_dialog" title="Are you sure you want to add snippet">
	
</div>

<div id="delete_field_dialog" title="Confirmation">Are you sure you want to delete this snippet</div>