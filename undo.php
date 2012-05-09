<?php
include_once("funcs.php");

session_start();

if(is_loggedin()){
	$code_type = $_GET['code_type'];
	
	$ref_url = $_SERVER['HTTP_REFERER'];
	$parse_url =  parse_url($ref_url);
	//$url_params = explode("&",$parse_url['query']);  // [0] = code_type  [1] = sure
	
	$num_submitted = $_GET['num_submitted'];
	
	$undo_list = "&num_submitted=$num_submitted";
	
	$i=0;
	
	while($i<$num_submitted) {
		$undo_list .= "&undo_$i=".$_GET["undo_$i"];
		$i++;
	}
	
	// NEED TO PULL OUT ALL CODING IDS TO UNDO

	if($_GET['sure'] == "yep") {
		db_connect();
		$i = 0;
		while($i<$num_submitted) {
			$this_id = $_GET["undo_$i"];
			$sql = "UPDATE codings SET coder_id='-1', coded_value='-1', timestamp='-1' WHERE id='$this_id'";
			//echo $sql;
			//echo "<br />";
			$result = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			$i++;
		}
		$head_url = "Location: code.php?type=$code_type&submit=undo";
		//echo $head_url;
		header($head_url);		
	}
	else {
		layout_header("Undo Last Coding");
		layout_menu();
	
		echo "<br />";
		echo "<div class='align_center'>";
		echo "Are you sure that you want to undo your last set of codings?<br /><br />";
		$undo_url = "undo.php?code_type=$code_type&sure=yep$undo_list";
		echo "<a href='$undo_url' class='page_title '>YES</a><br /><br />";
		echo "<a href='$ref_url' class='page_title '>NO</a>";
		echo "</div><br />";
		
		layout_footer();
	}
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}

?>