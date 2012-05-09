<?php
include_once("funcs.php");

session_start();

layout_header("Login");

if($_GET["error"]=="true"){
	echo "<br />";
	echo "<div class='error'>There was an error with your username or password. Please try again.</div>";
}

if(isset($_SESSION['coder_name'])){
	$coderName = $_SESSION['coder_name'];  
	// Member is logged in so we have to display welcome message with userid and one logout link
	echo "<br />";
	echo "<strong>Welcome {$coderName}</strong>
	<br/>
	<a href='index.php'>Go To Main Page</a><br/>
	<a href='logout.php'>Logout</a>";
} else {  
	// Member has not logged in so we can display the login form allowing member to login with user id and password
	echo "<br />";
	echo "<div class='align_center'>";
	echo "<form name='login' method='post' action='validate.php'>
	<table style='margin-left: auto; margin-right: auto;'>
		<tr>
			<td style='text-align: right'>username:</td>
			<td><input name='coder_name' type='text' class='field' id='coder_name' size='20'></td>
		</tr>
		<tr>
			<td style='text-align: right'>password:</td>
			<td><input name='password' type='password' class='field' id='password' size='20'></td>
		</tr>
			<td></td>
			<td style='text-align: right'><input name='submit' type='submit' value='Login' width='48' height='18'></td>
		</tr>
	</table>
	<br />
	</form>";
	echo "</div>";
}


layout_footer();

?>