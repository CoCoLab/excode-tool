<?php
include_once("funcs.php");

session_start();
db_connect();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

if(is_loggedin()){
	
	$timestamp = time();
	
	$ref_url = $_SERVER['HTTP_REFERER']; 
	$parse_url =  parse_url($ref_url);
	$ref_page = $parse_url['path'];
	
	$url_params = explode("&",$parse_url['query']); // take $url_params[0] to be the type variable for code.php
	
	$ref_page .= "?".$url_params[0]; // this makes the $ref_url something like "code.php?type=1"
	
	// need to check for project id
	if(isset($url_params[1]) && substr_count($url_params[1],"project")){
		$ref_page .= "&".$url_params[1];
	}
	
	// This is here so that the guest account can "submit" without really submitting a coding.
	if(!is_guest()){
	
		$items_in_cluster = $_POST['num_items'];
		$codingStartTime = $_POST['timestamp'];
		
		$codeTime = $timestamp - $codingStartTime;
		
		$cluster_flag = true;
		
		
		// setup for 1 question clusters with "None of the Above" option
		if(isset($_POST["nota_flag"])) {
			$coding_children = $_POST['item_0'];
			$count = count($coding_children);
			$items_in_cluster = $count - 1;
			
			// test to make sure that at least one box was checked
			if($count < 1) {
				$the_error = "must_check_at_least_one_box";
				$cluster_flag = false;
			}
			
			// $submitted_param is a string of url parameters to be used for passing the ids of successfully submitted codings (for undo)
			$submitted_param = "&num_submitted=$items_in_cluster";
			
			$i = 0;
			// check if a flag needs to be made
			if(isset($_POST["flag_discuss_$i"]) OR isset($_POST["flag_unsure_$i"])) {
				if(isset($_POST["flag_discuss_$i"])){
					$discuss = 1;
				}
				else {
					$discuss = 0;
				}
				if(isset($_POST["flag_unsure_$i"])) {
					$unsure = 1;
				}
				else {
					$unsure = 0;
				}
				$comment = mysql_real_escape_string($_POST["flag_comment_$i"]);
				
				
				$flag_sql[$i] = "INSERT INTO flags (discuss,unsure,comment,coding_id,timestamp) VALUES ('$discuss', '$unsure', '$comment', '-1', '$timestamp')";
			}
			
			$this_item_id = $_POST['nota_item_id'];
			$value_table = $_POST['nota_item_table'];
			
			
			$sql = "SELECT item_val FROM $value_table WHERE item_id = '$this_item_id' AND item_val != '-1'";
			$result = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			while($value = mysql_fetch_array($result)){
				$real_coding_id = $value['item_val'];
				if(in_array($real_coding_id,$coding_children)) {
					$item_value = 1;
				}
				else {
					$item_value = 0;
				}
				
				$item_id = $_POST["id_$real_coding_id"];
				
				$item_sql[$i] = "UPDATE codings SET coded_value='$item_value', timestamp='$timestamp', coder_id='$coderID' WHERE id='$item_id'";
				$codeTime = $timestamp - $codingStartTime;
				$codetime_sql[$i] = "INSERT INTO codingtimes (q_or_a_id,coder_id,item_type,item_id,coding_time,timestamp,coding_id) VALUES ('','$coderID','','','$codeTime','$timestamp','$item_id')";
				
				$submitted_param .= "&coded_$i=$item_id";
				
				$i++;
			}
			
			
		}
		
		else {
			// $submitted_param is a string of url parameters to be used for passing the ids of successfully submitted codings (for undo)
			$submitted_param = "&num_submitted=$items_in_cluster";
			
			if(isset($_POST["single_check_flag"])){
				// create boolean for OR check of all checkboxes in single_check_flag cluster, starting with the nota checkbox
				$must_check_at_least_one_flag = isset($_POST['item_nota']);
			}
			
			$i=0;
			while($i<$items_in_cluster) {
				
				if(isset($_POST["single_check_flag"])){
					$must_check_at_least_one_flag = $must_check_at_least_one_flag || isset($_POST["item_$i"]);
					
					$item_id = $_POST["id_$i"];
					if(isset($_POST["item_$i"])) {
						$item_value = "1";
					}
					else {
						$item_value = "0";
					}
				}
				else {
					$item_value = $_POST["item_$i"];
					$item_id = $_POST["id_$i"];
				
					// cluster_flag tests
					if($item_value == "") {
						$the_error = "item_value_blank_for_item_$i";
						$cluster_flag = false;
					}
					if($item_value == "-1") {
						$the_error = "item_value_-1_for_item_$i";
						$cluster_flag = false;
					}
					if($item_id == "") {
						$the_error = "item_id_blank_for_item_$i";
						$cluster_flag = false;
					}
				}
				
				// check if a flag needs to be made
				if(isset($_POST["flag_discuss_$i"]) OR isset($_POST["flag_unsure_$i"])) {
					if(isset($_POST["flag_discuss_$i"])){
						$discuss = 1;
					}
					else {
						$discuss = 0;
					}
					if(isset($_POST["flag_unsure_$i"])) {
						$unsure = 1;
					}
					else {
						$unsure = 0;
					}
					$comment = mysql_real_escape_string($_POST["flag_comment_$i"]);
					
					
					
					$flag_sql[$i] = "INSERT INTO flags (discuss,unsure,comment,coding_id,timestamp) VALUES ('$discuss', '$unsure', '$comment', '$item_id', '$timestamp')";
				}
				
				
				$item_sql[$i] = "UPDATE codings SET coded_value='$item_value', timestamp='$timestamp', coder_id='$coderID' WHERE id='$item_id'";
				$codeTime = $timestamp - $codingStartTime;
				$codetime_sql[$i] = "INSERT INTO codingtimes (q_or_a_id,coder_id,item_type,item_id,coding_time,timestamp,coding_id) VALUES ('','$coderID','','','$codeTime','$timestamp','$item_id')";
				
				$submitted_param .= "&coded_$i=$item_id";
				
				$i++;
			}
		
		}
		if(isset($_POST["single_check_flag"])){
			if(!$must_check_at_least_one_flag) {
				$cluster_flag = false;
				$the_error = "must_check_at_least_one_box";
			} 
		}
		
		
		if($cluster_flag){
			/* 
			print_r($item_sql);
			echo "<br />";
			print_r($codetime_sql);
			echo "<br />";
			print_r($flag_sql); */
			
			
			
			foreach($item_sql as $item) {
				$result = mysql_query($item) or die ("MySQL error: ".mysql_error());
			}
			foreach($codetime_sql as $codingtime) {
				$result = mysql_query($codingtime) or die ("MySQL error: ".mysql_error());
			}
			foreach($flag_sql as $flag) {
				$result = mysql_query($flag) or die ("MySQL error: ".mysql_error());
			}
			$head_url = "Location: ".$ref_page."&submit=ok".$submitted_param;
			header($head_url);
			
			
		}
		else {
			$head_url = "Location: ".$ref_page."&submit=bad&error=".$the_error;
			header($head_url);
		}
	}
	else {
		$head_url = "Location: ".$ref_page."&submit=ok";
		header($head_url);
	}

}
else {
	header("Location: http://cognition.berkeley.edu/");
}


?>