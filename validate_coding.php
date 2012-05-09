<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

layout_header("Validate Codings");

if(is_loggedin()){
	layout_menu();
	if(is_admin($coderID)) {
	$stage = $_GET['stage'];
	$vars = get_validation_variables($stage);
	?>
	<br />
	<span class='Qsub'>Validate Coding For Stage: <? echo $stage; ?></span><br /><br />
	<strong>available variables/values</strong><br />
	<table>
	<?
	foreach($vars as $v) {
		echo "<tr><td>";
		foreach($v as $k=>$i) {
			if($k != 0) {
				echo $i."<br />";
			}
		}
		echo "</td><td>";
		$vals = get_item_values($v[0],$stage);
		foreach($vals as $val){
			echo $val['value']." : ";
			echo $val['desc']."<br />";
		}
		echo "</td></tr>";
	}
	?>
	</table>
	<br />
	<form action="admin_action.php" method="GET">
	<input type="hidden" name="action" value="validate_coding" />
	<input type="hidden" name="stage" value="<? echo $stage; ?>" />
	
	<strong>validation scheme</strong><br />
	<textarea rows="10" cols="50" name="validation_scheme" id="validation_scheme"></textarea>
	<br />
	write/paste validation code (in psudo-PHP)<br />
	<br />
	
	<input type="submit" value="Validate" />
	</form>
	<?
	
	
	}
	else {
		echo "<br><b>You're not an admin!  Don't try to trick me!  I'm on to you!</b>";
	}
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}
?>