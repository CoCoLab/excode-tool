<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

if(is_loggedin()){
	if(is_admin($coderID)) {
		$ref_url = $_SERVER['HTTP_REFERER'];
		$action = $_GET['action'];
		db_connect();
		$db = pdo_connect(); // starting to upgrade to PDO database conenctions
		
		switch($action) {
			case "remove_admin":
				$remove_id = $_GET['id'];
				if($remove_id != "") {
					$db->beginTransaction();
					$st = $db->prepare("UPDATE coders SET is_admin='0' WHERE coder_id= :id");
					$ok = $st->execute(array(':id' => $remove_id));
					
					if($ok) {
						$db->commit();
						action_redirect($ref_url,'alert',"Admin removed.");
					}
					else {
						$db->rollBack();
						action_redirect($ref_url,'error',"Database error, admin not removed.");
					}
				}
				else {
					action_redirect($ref_url,'error',"Coder ID not specified, admin not removed.");
				}
				break;
			case "add_admin":
				$add_id = $_GET['id'];
				if($add_id != "") {
					$db->beginTransaction();
					$st = $db->prepare("UPDATE coders SET is_admin='1' WHERE coder_id= :id");
					$ok = $st->execute(array(':id' => $add_id));
					if($ok) {
						$db->commit();
						action_redirect($ref_url,'alert',"Admin added.");
					}
					else {
						$db->rollBack();
						action_redirect($ref_url,'error',"Database error, admin not added.");
					}
				}
				else {
					action_redirect($ref_url,'error',"Coder ID not specified, admin not added.");
				}
				break;
			case "add_cluster":
				print('<pre>');
				print_r($_GET);
				print('</pre>');
				 
				if($_GET['qora'] == ""){
					action_redirect($ref_url,'error',"Unable to submit new cluster.  Specify question or answer cluster.");
				}
				elseif($_GET['clustername'] == "") {
					action_redirect($ref_url,'error',"Unable to submit new cluster.  Cluster name must be provided.");
				}
				elseif(count($_GET['itemname']) < 1) {
					action_redirect($ref_url,'error',"Unable to submit new cluster.  Must include at least 1 coding item.");
				}
				else {
					$item_names = $_GET['itemname'];
					$item_desc = $_GET['itemdesc'];
					$item_disp = $_GET['itemdisp'];
					foreach($item_names as $key=>$item_name){
						if($item_name == "" || $item_desc[$key] == "" || $item_disp[$key] == "") {
							action_redirect($ref_url,'error',"Unable to submit new cluster.  You are missing details from one of your coding items.");	
						}
					}
				}
				// OK, if we've gotten to this point we can create our query and add this cluster and new items
				
				if($_GET['qora'] == "question") {
					$qora_table = "questions";
					$quora_col = "question_id";
					$stage = 3;
					$cat_table = "questioncategories";
					$item_table = "questionitems";
					$value_table = "qitemvalues";
				} 
				else {
					$qora_table = "answers";
					$qora_col = "answer_id";
					$stage = 4;
					$cat_table = "answercategories";
					$item_table = "answeritems";
					$value_table = "aitemvalues";	
				}
				$cat_name = strtoupper($_GET['clustername']);
				
				/*
				$db->beginTransaction();
				
				$num_sql = "SELECT COUNT(*) FROM $cat_table";
				$res = $db->query($num_sql);
				$num_clusters = $res->fetchColumn(); */
				
				$num_clusters = mysql_num_rows(mysql_query("SELECT * FROM $cat_table"));
				
				$cat_num = $num_clusters + 1;
				$cat_id = "c".$cat_num;
				
				
				
				$cluster_sql = "INSERT INTO $cat_table (cat_id, cat_name) VALUES ('$cat_id', '$cat_name')";
				
				$item_sql = array();
				$item_ids = array();
				$value_sql = array();
				
				$num_items = mysql_num_rows(mysql_query("SELECT * FROM $item_table"));
				$item_id = $num_items + 1;
				$item_num = 0;
				foreach($item_names as $key=>$item_name){
					$the_item_name = strtoupper($item_name);
					$the_item_desc = $_GET['itemdesc'][$key];
					$the_item_disp = $_GET['itemdisp'][$key];
					
					$item_sql[] = "INSERT INTO $item_table (item_id, item_name, item_desc, item_cat, val_disp) VALUES ('$item_id', '$the_item_name', '$the_item_desc', '$cat_id', '$the_item_disp')";
					
					$itemvalarr = "item".$item_num."value";
					//echo $itemvalarr."<br />";
					foreach($_GET["$itemvalarr"] as $v_key=>$value){
						$itemvaldescarr = "item".$item_num."valdesc";
						$val_desc = $_GET["$itemvaldescarr"][$v_key];
						
						$value_sql[] = "INSERT INTO $value_table (item_id, item_val, val_desc) VALUES ('$item_id','$value', '$val_desc')";
					}
					$item_ids[] = $item_id;
					
					$item_num++;
					$item_id++;	
				}
				
				
				// At this point SQL queries start getting executed.
				 				
				// add the cluster, items, and value to the database
				$cluster_result = mysql_query($cluster_sql) or die ("MySQL error: ".mysql_error());
				foreach($item_sql as $i_query) {
					$item_result = mysql_query($i_query) or die ("MySQL error: ".mysql_error());
				}
				foreach($value_sql as $v_query){
					$value_result = mysql_query($v_query) or die ("MySQL error: ".mysql_error());
				}
				
				// And now that everything worked, return to Admin page
				action_redirect($ref_url,'alert',"New cluster added.");
				break;
			case "add_cluster_to_project":
				$cluster = $_GET['cluster'];
				$project = $_GET['project'];
				
				if($cluster == "" || $project == "") {
					action_redirect($ref_url,'error',"Unable to add cluster to project.  Missing cluster or project info.");
				}
				else {
					$q_or_a = substr($cluster,0,1);
					$cluster = substr($cluster,2);
				}
				
				if(cluster_in_project($project,$cluster,$q_or_a)) {
					// check to make sure that this clusters isn't already added for these questions or answers
					action_redirect($ref_url,'error',"Cluster not added.  That cluster is already apart of that project.");
				}
				else {
					// start transaction
					mysql_query("SET AUTOCOMMIT=0");
					mysql_query("START TRANSACTION");
				
					// add cluster to projectkeys
					$key_type = $q_or_a."_cat";
					$keys_sql = "INSERT INTO projectkeys (key_type,value,proj_id) VALUES ('$key_type','$cluster','$project')";
					$keys_result = mysql_query($keys_sql) or die ("MySQL error: ".mysql_error());
					
					
					// add coding items for new cluster in project questions or answers
					if($q_or_a == "q") {
						$qora_table = "questions";
						$qora_col = "question_id";
						$stage = 3;
					}
					else {
						$qora_table = "answers";
						$qora_col = "answer_id";
						$stage = 4;
					}
					$project_parameters = get_project_parameters(null,$project);
					$pulls = join(',',$project_parameters['pulls']);
					
					// NEED TO PULL IN INFO ABOUT THE ITEMS IN THE CLUSTER TO ADD FOR $item_ids
					$item_info = get_cluster_items($cluster,$q_or_a);
					
					$qora_sql = "SELECT $qora_col FROM $qora_table WHERE valid='1' AND pull_id IN ($pulls)";
					//echo $qora_sql."<br />";
					$qora_result = mysql_query($qora_sql) or die ("MySQL error: ".mysql_error());
					
					$insert_count = 0;
					$insert_ok = true;
					while($row = mysql_fetch_array($qora_result)) {
						$qora_id = $row["$qora_col"];
						foreach($item_info as $item){
							$item_id = $item['item_id'];
							$coding_sql = "INSERT INTO codings (coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp) VALUES ('-1', '$qora_id', '$stage', '$item_id', '-1', '-1')";
							//echo $coding_sql."<br />";
							$insert_count += 2;
							$coding_result1 = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error());
							$coding_result2 = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error()); // do this twice to get 2 entries
							$insert_ok = $insert_ok && $coding_result1 && $coding_result2; 	
						}	
					}
					
					if($keys_result && $insert_ok) {
						mysql_query("COMMIT");
						action_redirect($ref_url,'alert',"$insert_count new coding items added to project.");
					}
					else {
						mysql_query("ROLLBACK");
						action_redirect($ref_url,'error',"Cluster not added.  A database error was encountered.");
					}					
					
				}
				
				break;
			case "add_unadded_codings":
				$stage = $_GET['stage'];
				
				// get coding items for questions or answers
				if($stage == 4){
					$item_table = "answeritems";
					$range_item_id = "item_id >= 4";
					//$stage = "4";
					$to_add = get_valid_unadded($stage);
				}
				elseif($stage == 3) {
					$item_table = "questionitems";
					$range_item_id = "item_id >= 5";
					//$stage = "3";
					$to_add = get_valid_unadded($stage);
				}
				elseif($stage == 2) {
					// for adding answer precodings for valid questions
					$item_table = "answeritems";
					$range_item_id = "item_id < 4 AND item_id > 0";
					//$stage = "2";
					$q_to_add = get_valid_unadded($stage);  // returns questions
					// set up $to_add with all the answers that need coding items added
					$to_add = array();
					foreach($q_to_add as $q) {
						$sql = "SELECT answer_id FROM answers WHERE question_id = '$q'";
						$result = mysql_query($sql) or die ("MySQL error: ".mysql_error());
						while($a = mysql_fetch_array($result)) {
							$to_add[] = $a['answer_id'];
						}
					}
				}
				else {
					// bad stage
					action_redirect($ref_url,'error',"Bad stage value for adding codings.");
				}
								
				$sql_items = "SELECT * FROM $item_table WHERE $range_item_id";
				$items_result = mysql_query($sql_items) or die ("MySQL error: ".mysql_error());
				
				$item_count = 0;
				while($item = mysql_fetch_array($items_result)) {
					$item_id = $item['item_id'];
					foreach($to_add as $q_or_a_id) {
						$coding_sql = "INSERT INTO codings (coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp,valid) VALUES ('-1', '$q_or_a_id', '$stage', '$item_id', '-1', '-1','1')";
						$coding_result = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error());
						//$coding_result = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error()); // do this twice to get 2 entries 
						$item_count+=1;						
					}	
				}
				
				action_redirect($ref_url,'alert',"$item_count new coding items added.");
				break;
			case "resolve_flag":
				$flag_id = $_GET['flag_id'];
				$sql = "UPDATE flags SET resolved = '1' WHERE id = $flag_id";
				$result =  mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$base_url = "/excode/admin.php";// All resolutions should redirect back to the Admin page.
				header("Location: $base_url?alert=Flag resolved.");				
				break; 
			case "validate_coding":
				$stage = $_GET['stage'];
				$scheme = $_GET['validation_scheme'];
				
				//$code = 'return $foo;'
				
				$val_func = create_function('$stage',$scheme);
				
				$val_func($stage);
				
				echo "<br /><br />".$scheme;
				
				break;
		}
	
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