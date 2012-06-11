
<div id="errordiv" style="margin-top:8px;" class="noDisplay error">An error occurred.</div>
<div id="successdiv" style="margin-top:8px;" class="noDisplay success">Settings saved.</div>

<form id="settings" enctype="multipart/form-data" action="/php/post_settings.php" method="post">
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
						<td class='displayName'>{$name}</td>
						<td class='value'><input name='{$prop}' type='text' value='{$value}' class='inputfield short' /></td>
						<td class='description' style='padding-left:5px;'>{$desc}</td>
					</tr>";
			}
		
		?>
    </table>
	<div align='center'>
		<input type="hidden" value="settings" name="action" />
		<input type="submit" class='button' value="Save Settings" />
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