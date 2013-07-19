
<h2 class="sectionTitle">Event Settings</h3>

<div id="errordiv" class="noDisplay alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span>An error occurred.</span>
</div>
<div id="successdiv" class="noDisplay alert alert-success">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span>Settings saved.</span>
</div>

<form id="settings" enctype="multipart/form-data" action="/events/manage/php/post_settings.php" method="post">
    <table>
		<?php

			$settings = $db->get_results($db->prepare("SELECT * FROM `settings` ORDER BY `sort_id` ASC"));
			foreach ($settings as $setting) {
				$name = $setting->display_name;
				$desc = $setting->description;
				$prop = $setting->property;
				$value = $setting->value;
				echo "
					<tr>
						<td class='displayName' style=\"width:200px\">{$name}</td>
						<td class='value'><input name='{$prop}' type='text' value='{$value}' class='inputfield short' /></td>
						<td class='description tiny' style='padding-left:15px;'>{$desc}</td>
					</tr>";
			}
		
		?>
    </table>
	<div align='center'>
		<input type="hidden" value="settings" name="action" />
		<input type="submit" class='btn' value="Save Settings" />
	</div>
</form>


<script type="text/javascript">

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>