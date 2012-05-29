<?php

	//Time Constants	
	$START_TIME = "10 AM";
	$END_TIME = "5 PM";
	$INTERVAL = "15"; //minutes
	
?>

<div id="errordiv" style="margin-top:8px;" class="noDisplay error">An error occurred.</div>


<h2>Create Weekly Event</h2>
<form id="addEvent" enctype="multipart/form-data" action="/php/post_events.php" method="post">
    <table>
		<thead>
			<th style="width:110px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: </td>
	            <td><input type="text" name="name" id="name" class="inputfield" /></td>
	        </tr>
	        <tr>
	            <td>Description: </td>
	            <td><textarea name="description" id="description"></textarea></td>
	        </tr>
			<tr>
	            <td>Day: </td>
	            <td><select name="day_of_week" style="width: 120px">
	            	<option selected="selected"></option>
					<?php
						$timestamp = strtotime('next Monday');
						for ($x = 0; $x < 7; $x++) {
							$name = strftime('%A', $timestamp);
							echo "<option name='day_{$count}' value='{$name}'/>{$name}</option>";
							$timestamp = strtotime('+1 day', $timestamp);
						}
					?>
	             </select></td>
	        </tr>
			<tr>
	            <td>Time: </td>
	            <td><select name="time" style="width: 120px">
	            	<option selected="selected"></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							echo "<option value='". date('H:i', $i) . "'>" . date('g:i A', $i) . "</option>";
						}
		            ?>
	        </select></td>
	        </tr>
	        <tr>
	            <td>Icon: </td>
	            <td><input type="file" name="thumbnail" /></td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center"><input type="submit" class="button" value="Create Event" />
				<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a></td></td>
			</tr> 
		</tbody>
    </table>
</form>

<script type="text/javascript">

	function cancel() {
		var ref = "<?= $_SERVER['HTTP_REFERER']; ?>" || "/";
		window.setTimeout("window.location.href = '" + ref + "';", 500);
	}

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}

</script>
