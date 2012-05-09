<?php
// funcs.php
// Functions for use in ExCode Tool

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

/****************************
Coder Functions
****************************/

function is_loggedin(){
	global $coderID;
	if($coderID==null) {
		return false;
	}
	else {
		return true;
	}
}

function is_admin($coderID) {
	db_connect();
	
	$sql = "SELECT is_admin FROM coders WHERE coder_id='$coderID'";
	$admin_val = mysql_fetch_array(mysql_query($sql)) or die(mysql_error());
	if($admin_val['is_admin'] == 1) {
		return true;
	}
	else {
		return false;
	}
}

function is_guest() {
	global $coderID;
	if($coderID == 22){
		return true;
	}
	else {
		return false;
	}
}

function validate_login($coderName,$password) {
	db_connect();
	
	
	$result = mysql_query("SELECT * FROM coders WHERE coder_name='$coderName' AND coder_pass='$password'") or die(mysql_error());
		
	$validUnPw = mysql_num_rows($result);
	
	if($validUnPw > 0){
		$coder_array = mysql_fetch_array($result);		
		return $coder_array;
	}
	else {
		return false;
	}
}

function get_coder_name($id){
	db_connect();
	$sql = "SELECT coder_name FROM coders WHERE coder_id=$id LIMIT 1";
	$nameArr = mysql_fetch_array(mysql_query($sql));
	return $nameArr['coder_name'];
	
}

function get_all_coders() {
	db_connect();
	$sql = "SELECT coder_id, coder_name FROM coders";
	$result = mysql_query($sql);
	
	$i = 0;
	while($coder = mysql_fetch_array($result)) {
		$id = $coder['coder_id'];
		$all_coders[$id] = $coder['coder_name'];
		$i++;
	}
	
	return $all_coders;
}

// User Stats

function get_number_items_coded($id,$timeStart=0,$timeEnd=0) {
	db_connect();
	if($timeStart == 0){
		$sql = "SELECT * FROM codings WHERE coder_id=$id AND coded_value!=-1";
		$count = mysql_num_rows(mysql_query($sql));
		return $count;
	}
	else {		
		$sql = "SELECT * FROM codings WHERE coder_id=$id AND coded_value!=-1 AND timestamp > $timeStart AND timestamp < $timeEnd";
		$count = mysql_num_rows(mysql_query($sql));
		return $count;
	}
	
}

function get_total_coding_time($id=0) {
	if($id>0) {
		$sql = "SELECT SUM(coding_time) FROM codingtimes WHERE coder_id=$id";
	}
	else {
		$sql = "SELECT SUM(coding_time) FROM codingtimes";
	}
	
	$countArr = mysql_fetch_array(mysql_query($sql));
	$totalTime = $countArr['SUM(coding_time)'];
	if($totalTime > 0){
		return $totalTime;
	}
	else {return 0;}
}

function get_range_coding_time($id=0,$start,$end) {
	if($id>0) {
		$sql = "SELECT SUM(coding_time) FROM codingtimes WHERE coder_id=$id AND timestamp > $start AND timestamp < $end";
	}
	else {
		$sql = "SELECT SUM(coding_time) FROM codingtimes WHERE timestamp > $start AND timestamp < $end";
	}
	$countArr = mysql_fetch_array(mysql_query($sql));
	$totalTime = $countArr['SUM(coding_time)'];
	if($totalTime > 0){
		return $totalTime;
	}
	else {return 0;}
}

function get_coding_rate($id=0) {
	db_connect();
	if($id>0) {
		$sql = "SELECT coding_time FROM codingtimes WHERE coder_id=$id";
	}
	else {
		$sql = "SELECT coding_time FROM codingtimes";
	}
	$count = mysql_num_rows(mysql_query($sql));
	$totalTime = get_total_coding_time($id);	
	
	return ($totalTime/$count);	
}

/****************************
Admin Functions
****************************/

function get_admins($non_admin=false) {
	db_connect();
	if($non_admin) {
		$is_admin = 0;
	}
	else {
		$is_admin = 1;
	}
	$sql = "SELECT * FROM coders WHERE is_admin = '$is_admin'";
	$the_admins = mysql_query($sql) or die(mysql_error());
	$i = 0;
	while($admin = mysql_fetch_array($the_admins)){
		$admin_array[$i] = $admin;
		$i++;
	}
	return $admin_array;
}


/****************************
Coding Stats Functions
****************************/

function get_weekly_quota() {
	return 1000;  // manually set for now
}

function get_weekly_ranked_coders($offset=0) {
	db_connect();
	
	// get the timestampe for the start of this week
	$isoWeekStartTime = strtotime(date('o-\\WW')); // {isoYear}-W{isoWeekNumber}
	$isoWeekEndTime = $isoWeekStartTime + (60*60*24*7);
	
	$rankedCoders = array();
	$i = 0;
	
	$sql = "SELECT coder_name,coder_id FROM coders";
	$theCoders = mysql_query($sql);
		
	while ($coder = mysql_fetch_array($theCoders)) {
		$coderID = $coder['coder_id'];
		/* print_r($coder);
		echo "<br />"; */
		$numItems = get_number_items_coded($coderID,$isoWeekStartTime,$isoWeekEndTime);
		if($numItems > 0){
			$rankedCoders[$i]['name'] = $coder["coder_name"];
			$rankedCoders[$i]['numItems'] = $numItems;
			$rankedCoders[$i]['id'] = $coderID;
			$i++;
		}
	}
	
	usort($rankedCoders, 'sort_by_num_items');
	
	return $rankedCoders;
}

function get_ranked_coders($rangeStart=0,$rangeEnd=0) {
	db_connect();
	$rankedCoders = array();
	$i = 0;
	
	$sql = "SELECT coder_name,coder_id FROM coders";
	$theCoders = mysql_query($sql);
	
	while ($coder = mysql_fetch_array($theCoders)) {
		$coderID = $coder['coder_id'];
		/* print_r($coder);
		echo "<br />"; */
		$numItems = get_number_items_coded($coderID);
		$rankedCoders[$i]['name'] = $coder["coder_name"];
		$rankedCoders[$i]['numItems'] = $numItems;
		$rankedCoders[$i]['id'] = $coderID;
		$i++;
	}
	
	usort($rankedCoders, 'sort_by_num_items');
	
	return $rankedCoders;
}

function count_total_codings($type,$project="") {
	db_connect();
	
	if($project== "") {
		$sql = "SELECT id FROM codings WHERE item_type=$type";
	}
	else {
		$project_parameters = get_project_parameters($type,$project);
			
		$cats = "'";
		$cats .= join("', '",$project_parameters['cats']);
		$cats .= "'";
		$pulls = join(',',$project_parameters['pulls']);
		$q_or_a = $project_parameters['q_or_a'];
		$items = $project_parameters['items'];
		$q_or_a_id = $project_parameters['q_or_a_id'];
		
		$sql = "SELECT DISTINCT c.id 
			FROM codings c
			LEFT JOIN $q_or_a qa ON c.q_or_a_id = qa.$q_or_a_id
			LEFT JOIN $items i ON i.item_id = c.item_id
			WHERE ";
		if($type>2) { $sql .= "i.item_cat IN ($cats) AND "; }
		$sql .= "qa.pull_id IN ($pulls) AND
			c.item_type=$type";
	}	
	// only check coding validity until coding table can be cleaned of invalid answer precodings
	if($type == 2) {
		if($project== ""){
			$sql.= " AND valid > 0";
		}
		else {
			$sql.= " AND c.valid > 0";
		}
	}
	$count = mysql_num_rows(mysql_query($sql));
	return $count;
}

function count_uncoded($type=0,$project="") {
	db_connect();
	if($project== "") {
		$sql = "SELECT id FROM codings WHERE item_type=$type AND coded_value<0";
	}
	else {
		$project_parameters = get_project_parameters($type,$project);
			
		$cats = "'";
		$cats .= join("', '",$project_parameters['cats']);
		$cats .= "'";
		$pulls = join(',',$project_parameters['pulls']);
		$q_or_a = $project_parameters['q_or_a'];
		$items = $project_parameters['items'];
		$q_or_a_id = $project_parameters['q_or_a_id'];
		
		$sql = "SELECT c.id 
			FROM codings c
			LEFT JOIN $q_or_a qa ON c.q_or_a_id = qa.$q_or_a_id
			LEFT JOIN $items i ON i.item_id = c.item_id
			WHERE ";
		if($type>2) { $sql .= "i.item_cat IN ($cats) AND "; }
		$sql .= "qa.pull_id IN ($pulls) AND
			c.item_type=$type AND c.coded_value<0";
	
	}
	// only check coding validity until coding table can be cleaned of invalid answer precodings
	if($type == 2) {
		if($project== ""){
			$sql.= " AND valid > 0";
		}
		else {
			$sql.= " AND c.valid > 0";
		}
	}
	$count = mysql_num_rows(mysql_query($sql));
	return $count;
}

// Get valid, precoded questions/answers that have not been added to stage 2, 3, or 4 coding
function get_valid_unadded($stage) {
	if($stage == 4) {
		$table = "answers";
		$id = "answer_id";
	}
	else {
		// default to Questions (stage 2 = precoding answers for valid questions, stage 3 = coings for valid questions)
		$table = "questions";
		$id = "question_id";	
	}
	
	$sql_qa = "SELECT $id FROM $table WHERE valid='1'";
	$result_qa = mysql_query($sql_qa) or die(mysql_error());
	while($the_id = mysql_fetch_array($result_qa)){
		$valid[] = $the_id["$id"];
	} 
	
	$sql_codings = "SELECT DISTINCT q_or_a_id FROM codings WHERE item_type = '$stage'";
	$result_codings = mysql_query($sql_codings) or die(mysql_error());	
	while($id_codings = mysql_fetch_array($result_codings)){
		if($stage == 2){
			$start = strpos($id_codings['q_or_a_id'], "_");
			$the_q_or_a_id = substr($id_codings['q_or_a_id'],$start+1);
		} 
		else {
			$the_q_or_a_id = $id_codings['q_or_a_id'];
		}
		$added[] = $the_q_or_a_id;
	}
	
	if($precode){
		$added = array_unique($added);
	}
	
	$unadded = array_diff($valid,$added);
	return $unadded;
}

/****************************
Coding Functions
****************************/

function is_stage_done($type,$project="") {
	if(count_uncoded($type,$project) > 0){
		return false;
	}
	else { return true; }
}

function is_valid_project($project) {
	db_connect();
	if($project == ""){
		return false;
	}
	$sql = "SELECT name FROM projects WHERE id = '$project'";
	$result = mysql_query($sql) or die ("MySQL error: ".mysql_error());
	if(mysql_num_rows($result) > 0){
		return true;
	}
	else {
		return false;
	}
}


function get_coding_items($type,$project="") {
	if($type == "Q") {
		$table = "questionitems";
		$key_type = "q_cat";
	}
	elseif($type == "A") {
		$table = "answeritems";
		$key_type = "a_cat";
	}
	
	if($project == "" || $project == "all") {
		$sql = "SELECT item_id, item_name, item_desc, item_cat, val_disp FROM $table";
	}
	else {
		$sql = "SELECT DISTINCT i.item_id, i.item_name, i.item_desc, i.item_cat, i.val_disp FROM $table i LEFT JOIN projectkeys p ON i.item_cat = p.value WHERE p.key_type = '$key_type' AND p.proj_id = $project";
	}
	$result = mysql_query($sql) or die ("MySQL error: ".mysql_error());
	while ($item = mysql_fetch_array($result)) {
		$item['code_num'] = 1;
		$items[] = $item;
	}
	
	return $items;
}

function get_project_info() {
	db_connect();
	
	$sql = "SELECT id, name, proj_desc FROM projects";
	$result =  mysql_query($sql) or die ("MySQL error: ".mysql_error());
	while($row = mysql_fetch_array($result)){
		$projects[] = $row;
	}
	
	return $projects;
}

function get_project_parameters($type,$project) {
	if($type == '1' || $type == '3'){
		$qa_cat = "q_cat";
		$q_or_a = "questions";
		$q_or_a_id = "question_id";
		$items = "questionitems";
	}
	else {
		$qa_cat = "a_cat";
		$q_or_a = "answers";
		$q_or_a_id = "answer_id";
		$items = "answeritems";
	}

	$pull_sql = "SELECT value FROM projectkeys WHERE proj_id = '$project' AND key_type = 'pull_id'";
	$pull_result = mysql_query($pull_sql) or die ("MySQL error: ".mysql_error());
	while($pull_row = mysql_fetch_array($pull_result)){
		$project_pulls[] = $pull_row['value'];
	}
	
	$cat_sql = "SELECT value FROM projectkeys WHERE proj_id = '$project' AND key_type = '$qa_cat'";
	$cat_result = mysql_query($cat_sql) or die ("MySQL error: ".mysql_error());
	while($cat_row = mysql_fetch_array($cat_result)){
		$project_cats[] = $cat_row['value'];
	}
	
	$parameters['pulls'] = $project_pulls;
	$parameters['cats'] = $project_cats;
	$parameters['q_or_a'] = $q_or_a;
	$parameters['items'] = $items;
	$parameters['q_or_a_id'] = $q_or_a_id;
			
	return $parameters;
}

function get_coding_category($type,$codingId) {
	# select from the proper category for the type
	if($type == "1" || $type == "3") {
		$itemTable = "questionitems";
		$catTable = "questioncategories";
		if($type == "1") {
			$codingId = "-1";  // this is an override so that we use the special "check/none of the above" c2 coding cat for Question Precoding
		}
	}
	elseif($type == "2" || $type == "4") {
		$itemTable = "answeritems";
		$catTable = "answercategories";
		if($type == "2") {
			$codingId = "-1"; // // this is an override so that we use the special "check/none of the above" c2 coding cat for Answer Precoding
		}
	}
	
	$catId = mysql_fetch_array(mysql_query("SELECT item_cat FROM $itemTable WHERE item_id = '$codingId'" ));
	$theCatId = $catId ['item_cat'];
	
	$catInfo = mysql_fetch_array(mysql_query("SELECT * FROM $catTable WHERE cat_id='$theCatId'"));
	return $catInfo;
}

function get_new_coding($type,$coderID,$project="") {
	db_connect();
		
	$limit = count_uncoded($type);
	$tries = 0;
	
	do{
		// grab a random uncoded item
		if($project == "") {
			$sql = "SELECT id,item_id,q_or_a_id,item_type FROM codings WHERE coded_value=-1 AND item_type=$type order by RAND() limit 1";
		}
		else {
			$project_parameters = get_project_parameters($type,$project);
			
			$cats = "'";
			$cats .= join("', '",$project_parameters['cats']);
			$cats .= "'";
			$pulls = join(',',$project_parameters['pulls']);
			$q_or_a = $project_parameters['q_or_a'];
			$items = $project_parameters['items'];
			$q_or_a_id = $project_parameters['q_or_a_id'];
			
			$sql = "SELECT c.id,c.item_id,c.q_or_a_id,c.item_type 
				FROM codings c
				LEFT JOIN $q_or_a qa ON c.q_or_a_id = qa.$q_or_a_id
				LEFT JOIN $items i ON i.item_id = c.item_id
				WHERE ";
			if($type>2) { $sql .= "i.item_cat IN ($cats) AND "; }
			$sql .= "qa.pull_id IN ($pulls) AND
				c.coded_value=-1 AND c.item_type=$type order by RAND() limit 1";
		}
		
		$codingItem = mysql_fetch_array(mysql_query($sql)) or die ("MySQL error in get_new_coding: ".mysql_error());
		$thisQorAid = $codingItem['q_or_a_id'];
		$thisItemId = $codingItem['item_id'];
		$thisItemType = $codingItem['item_type'];
		// test to make sure it hasn't been coded by this user yet
		$dupTestSql = "SELECT id FROM codings WHERE coded_value>-1 AND q_or_a_id='$thisQorAid' AND item_type=$thisItemType AND item_id=$thisItemId AND coder_id=$coderID";
		$tries++;
		if($tries > $limit){
			break;
		}
	} while(mysql_num_rows(mysql_query($dupTestSql)) > 0);
	if($tries <= $limit){
		return $codingItem;
	}
	else {
		// this means that this user has coded all the items they can in this stage
		return false;
	}
}

function get_cat_coding_form($type,$catId,$QorAId) {
	$now = time();
	$form_str = "<input type=\"hidden\" name=\"timestamp\" value=\"$now\" />\n";

	if($type == "1" || $type == "3") {
		$catTable = "questioncategories";
		$itemsTable = "questionitems";
		$valuesTable = "qitemvalues";
	}
	elseif($type == "2" || $type == "4") {
		$catTable = "answercategories";
		$itemsTable = "answeritems";
		$valuesTable = "aitemvalues";
	}
	
	# for some categories (clusters) a description will be relivant
	$cluster_desc_sql = "SELECT cat_desc FROM $catTable WHERE cat_id = '$catId'";
	$cluster_desc_result = mysql_query($cluster_desc_sql) or die ("MySQL error: ".mysql_error());
	$cluster_desc = mysql_fetch_array($cluster_desc_result);
	if($cluster_desc['cat_desc'] != "") {
		echo "<span class='sm_desc'>";
		echo $cluster_desc['cat_desc']; 
		echo "</span><br /><br />";
	}
	
	
	# get all the info for each item in the category
	$item_info_sql = "SELECT item_id, item_name, item_desc, val_disp FROM $itemsTable WHERE item_cat='$catId'";
	$item_result = mysql_query($item_info_sql) or die ("MySQL error: ".mysql_error());
	$num_items = mysql_num_rows($item_result);
	
	$form_str .= "<input type=\"hidden\" name=\"num_items\" value=\"$num_items\" />\n";
	
	$item_num = 0;
	$nota_flag = false;
	while($item = mysql_fetch_array($item_result)) {
		#for each item in the category grab a coding
		# there should be a valid coding for every item in the category based on earlier validations
		$this_item_id = $item['item_id'];
		$this_item_disp = $item['val_disp'];
		$this_item_name = $item['item_name'];
		$this_item_desc = $item['item_desc'];
		
		if($this_item_disp == "single_check") {
			# a category with items containing a single value, marked with a checkbox.  A "none of the above" checkbox will be added automatically
			$nota_flag = true;
			$form_str .= "<input type=\"hidden\" name=\"single_check_flag\" value=\"true\" /> ";
			$form_str .= "<input type=\"hidden\" name=\"id_$item_num\" value=\"$this_item_id\" /> ";
			$form_str .= "<input type=\"checkbox\" name=\"item_$item_num\" value=\"$this_item_id\" /> ";
			
		}
		
		$form_str .= "<span class='code_lbl'>$this_item_name</span><br />";
		$form_str .= "<span class='sm_desc'>$this_item_desc</span><br /><br />";
		
		# get id for codeditem to store value for this coding item
		$coding_sql = "SELECT id FROM codings WHERE q_or_a_id='$QorAId' AND item_type='$type' AND item_id='$this_item_id' AND coded_value < 0";
		$coding_result = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error());
		$codingitem = mysql_fetch_array($coding_result);
		$this_coding_id = $codingitem['id'];
		
		# get item values for this coding item
		$value_sql = "SELECT item_val, val_desc FROM $valuesTable WHERE item_id='$this_item_id'";
		$value_result = mysql_query($value_sql) or die ("MySQL error: ".mysql_error());
		
		// if there is a "None of the Above" option, set a hidden flag
		if($this_item_disp == "check_nota") {
			$form_str .= "<input type=\"hidden\" name=\"nota_flag\" value=\"true\" /> ";
			$form_str .= "<input type=\"hidden\" name=\"nota_item_id\" value=\"$this_item_id\" /> ";
			$form_str .= "<input type=\"hidden\" name=\"nota_item_table\" value=\"$valuesTable\" /> ";
		}
				
		if($this_item_disp == "rad") {
			# radio button display of values
			while($value = mysql_fetch_array($value_result)){
				//print_r($value);
				$the_val = $value['item_val'];
				$form_str .= "<input type=\"radio\" name=\"item_$item_num\" value=\"$the_val\" /> ";
				$form_str .= $value['val_desc']."<br>\n";
						
			}
		}
		elseif($this_item_disp == "check_nota") {
			# check box display values.  This is used to map a single item's values to existing items behind the scenes
			while($value = mysql_fetch_array($value_result)){
				$the_val = $value['item_val'];
				$form_str .= "<input type=\"checkbox\" name=\"item_".$item_num."[]\" value=\"".$value['item_val']."\" /> ";
				$form_str .= $value['val_desc']."<br>\n";
				
				if($the_val > 0) {
					$coding_sql = "SELECT id FROM codings WHERE q_or_a_id='$QorAId' AND item_type='$type' AND item_id='$the_val' AND coded_value < 0";
					$coding_result = mysql_query($coding_sql) or die ("MySQL error: ".mysql_error());
					$codingitem = mysql_fetch_array($coding_result);
					$this_coding_id = $codingitem['id'];
					
					$form_str .= "<input type=\"hidden\" name=\"id_$the_val\" value=\"$this_coding_id\" /><br />\n";
				}
			}
		}
		# add flag options 
		$form_str .= "<span class='sm_text'><a href='#' class='flag_toggle_$item_num'>flag this coding</a></span>\n";
		$form_str .= "<script type='text/javascript'>";
		$form_str .= '$(document).ready(function(){$("div.flag_box_'.$item_num.'").hide(); $("a.flag_toggle_'.$item_num.'").click(function(){$("div.flag_box_'.$item_num.'").slideToggle("slow"); return false;});});';
		$form_str .= "</script>";
		$form_str .= "<div class='flag_box flag_box_$item_num'>\n";
		$form_str .= "<span class='sm_text'>(check at least one box)</span><br />";
		$form_str .= "<input type='checkbox' name='flag_discuss_$item_num' /> discuss<br />\n";
		$form_str .= "<input type='checkbox' name='flag_unsure_$item_num' /> unsure<br />\n";
		$form_str .= "<textarea name='flag_comment_$item_num' class='flag_comment' rows='2' cols='30'></textarea>\n";
		
		$form_str .= "</div><br />\n";
		
		
		if($this_item_disp != "check_nota") {
			$form_str .= "<input type=\"hidden\" name=\"id_$item_num\" value=\"$this_coding_id\" /><br />\n";
		}
		
		$item_num++;
	}
	if($nota_flag) {
		$form_str .= "<input type=\"checkbox\" name=\"item_nota\" value=\"nota\" /> ";
		$form_str .= "<span class='code_lbl'>None of the Above</span><br />";
	}
	
	return $form_str;
}

/****************************
Data Functions
****************************/

function get_coded_data($stage=0,$project="") {
	db_connect();
	
	$all_coders = get_all_coders();
	
	if($stage > 0) {
		switch($stage) {
			case 1:
				if($project == "all" || $project == "") {
					$sql = "SELECT question_id, subject, content, category FROM questions";
				}
				else {
					$project_parameters = get_project_parameters($stage,$project);
					
					$pulls = join(',',$project_parameters['pulls']);
					
					$sql = "SELECT question_id, subject, content, category FROM questions WHERE pull_id IN ($pulls)";
				}
				$result = mysql_query($sql) or die(mysql_error());
				
				$i = 0;
				while($question = mysql_fetch_array($result)) {
					$qID = $question['question_id'];
					$coded_questions[$i]['questionID'] = $qID;
					$coded_questions[$i]['q_subject'] = $question['subject'];
					$coded_questions[$i]['q_content'] = $question['content'];
					$coded_questions[$i]['category'] = $question['category'];
					
					$q_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$qID' AND item_id < 5";
					$q_result = mysql_query($q_sql) or die(mysql_error());
					
					$j_1 = 0;
					$nw_1 = 0;
					$n_1 = 0;
					$hw_1 = 0;
					while($coding = mysql_fetch_array($q_result)) {
						switch($coding['item_id']) {
							case 1:
								if($j_1 == 0) {
									$coded_questions[$i]['Joke1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Joke1_Coder'] = $all_coders[$this_coderID];
									$j_1++;
									
								}
								else {
									$coded_questions[$i]['Joke2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Joke2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 2:
								if($n_1 == 0) {
									$coded_questions[$i]['Nonsense1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Nonsense1_Coder'] = $all_coders[$this_coderID];
									$n_1++;
								}
								else {
									$coded_questions[$i]['Nonsense2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Nonsense2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 3:
								if($hw_1 == 0) {
									$coded_questions[$i]['Homework1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Homework1_Coder'] = $all_coders[$this_coderID];
									$hw_1++;
								}
								else {
									$coded_questions[$i]['Homework2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['Homework2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 4:
								if($nw_1 == 0) {
									$coded_questions[$i]['NotWhy1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['NotWhy1_Coder'] = $all_coders[$this_coderID];
									$nw_1++;
								}
								else {
									$coded_questions[$i]['NotWhy2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$coded_questions[$i]['NotWhy2_Coder'] = $all_coders[$this_coderID];
								}
								break;
						}
						
					}
					$i++;
				}
				return $coded_questions;
				
				break;
			case 2:
				/*
				// Written to deal with lots of codings, not needed yet but save for later use
				$count_sql = "SELECT count(*) FROM answers";
				$count_result = mysql_query($count_sql) or die(mysql_error());
				$count = mysql_fetch_array($count_result);
				$row_count = $count[0];
				
				$limit = 1000;
				
				$offset = 0;
				while($offset < $row_count) {
					$sql = "SELECT answer_id, question_id, content FROM answers LIMIT $limit OFFSET $offset";
					
					
					$offset += $limit;
				}
				*/
				if($project == "all" || $project == "") {
					$sql = "SELECT DISTINCT a.answer_id, a.question_id, a.content FROM answers a LEFT JOIN questions q ON q.question_id = a.question_id WHERE q.valid = '1'";
				}
				else {
					$project_parameters = get_project_parameters($stage,$project);
					
					$pulls = join(',',$project_parameters['pulls']);
					
					$sql = "SELECT DISTINCT a.answer_id, a.question_id, a.content FROM answers a LEFT JOIN questions q ON q.question_id = a.question_id WHERE q.valid = '1' AND a.pull_id IN ($pulls)";
				}
				$result = mysql_query($sql) or die(mysql_error());
				
				$i = 0;
				while($answer = mysql_fetch_array($result)) {
					$aID = $answer['answer_id'];
					$qID = $answer['question_id'];
					$coded_answers[$i]['answerID'] = $aID;
					$coded_answers[$i]['questionID'] = $qID;
					$coded_answers[$i]['a_content'] = $answer['content'];
					
					$q_sql = "SELECT subject, content, category FROM questions WHERE question_id='$qID'";
					$question = mysql_fetch_array(mysql_query($q_sql));
					
					$coded_answers[$i]['q_subject'] = $question['subject'];
					$coded_answers[$i]['q_content'] = $question['content'];
					$coded_answers[$i]['category'] = $question['category'];
					
					$a_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$aID' AND item_id < 4";
					$a_result = mysql_query($a_sql) or die(mysql_error());
					
					$j_1 = 0;
					$n_1 = 0;
					$r_1 = 0;
					while($coding = mysql_fetch_array($a_result)) {
						switch($coding['item_id']) {
							case 1:
								if($j_1 == 0) {
									$coded_answers[$i]['Joke1'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Joke1_coder'] = $all_coders[$coderID];
									$j_1++;
									
								}
								else {
									$coded_answers[$i]['Joke2'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Joke2_coder'] = $all_coders[$coderID];
								}
								break;
							case 2:
								if($n_1 == 0) {
									$coded_answers[$i]['Nonsense1'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Nonsense1_coder'] = $all_coders[$coderID];
									$n_1++;
								}
								else {
									$coded_answers[$i]['Nonsense2'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Nonsense2_coder'] = $all_coders[$coderID];
								}
								break;
							case 3:
								if($r_1 == 0) {
									$coded_answers[$i]['Redirect1'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Redirect1_coder'] = $all_coders[$coderID];
									$r_1++;
								}
								else {
									$coded_answers[$i]['Redirect2'] = $coding['coded_value'];
									$coderID = $coding['coder_id'];
									$coded_answers[$i]['Redirect2_coder'] = $all_coders[$coderID];
								}
								break;
						}
					}
					
					$i++; 
				}   
				return $coded_answers;
			
				break;
			case 3:
				$question_items = get_coding_items("Q",$project);
				//print_r($question_items);
				
				if($project == "all" || $project == "") {							
					$sql = "SELECT question_id, subject, content, category FROM questions WHERE valid = '1'";
				}
				else {
					$project_parameters = get_project_parameters($stage,$project);
					
					$pulls = join(',',$project_parameters['pulls']);
					
					$sql = "SELECT question_id, subject, content, category FROM questions WHERE valid = '1' AND pull_id IN ($pulls)";
				}
				$result = mysql_query($sql) or die(mysql_error());
				
				$i = 0;
				while($question = mysql_fetch_array($result)) {
					$qID = $question['question_id'];
					$coded_questions[$i]['questionID'] = $qID;
					$coded_questions[$i]['q_subject'] = $question['subject'];
					$coded_questions[$i]['q_content'] = $question['content'];
					$coded_questions[$i]['category'] = $question['category'];
					
					$min_item_id = 5; // stage 3 items have item_id greater than 4
					
					$q_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$qID' AND item_id >= $min_item_id";
					$q_result = mysql_query($q_sql) or die(mysql_error());
					
					
					while($coding = mysql_fetch_array($q_result)) {
						
						$the_item_key = 0;
						foreach($question_items as $key => $item) {
							if($item['item_id'] == $coding['item_id']) {
								$the_item_key = $key;
								break;
							}
						}
						
						$item_index = $question_items[$the_item_key]['item_name'].$question_items[$the_item_key]['code_num'];
						$coder_index = $item_index."_Coder";
						$coded_questions[$i]["$item_index"] = $coding['coded_value'];
						$this_coderID = $coding['coder_id'];
						$coded_questions[$i]["$coder_index"] = $all_coders[$this_coderID];
						$question_items[$the_item_key]['code_num'] = $question_items[$the_item_key]['code_num'] + 1;
						
						
					}
					$x = 0;
					$q_len = count($question_items);
					while($x < $q_len) {
						$question_items[$x]['code_num'] = 1;
						$x++;
					} 
					$i++;
				}
				//print_r($coded_questions);
				return $coded_questions;
			
				break;
			case 4:
				$answer_items = get_coding_items("A",$project);
				
				
				if($project == "all" || $project == "") {
					$sql = "SELECT answer_id, question_id, content FROM answers WHERE valid = '1'";
				}
				else {
					$project_parameters = get_project_parameters($stage,$project);
					
					$pulls = join(',',$project_parameters['pulls']);
					
					$sql = "SELECT answer_id, question_id, content FROM answers WHERE valid = '1' AND pull_id IN ($pulls)";
				}
				
				$result = mysql_query($sql) or die(mysql_error());
				
				$i = 0;
				while($answer = mysql_fetch_array($result)) {
					$aID = $answer['answer_id'];
					$qID = $answer['question_id'];
					$coded_answers[$i]['answerID'] = $aID;
					$coded_answers[$i]['questionID'] = $qID;
					$coded_answers[$i]['a_content'] = $answer['content'];
					
					$q_sql = "SELECT subject, content, category FROM questions WHERE question_id='$qID'";
					$question = mysql_fetch_array(mysql_query($q_sql));
					
					$coded_answers[$i]['q_subject'] = $question['subject'];
					$coded_answers[$i]['q_content'] = $question['content'];
					$coded_answers[$i]['category'] = $question['category'];
					
					$min_item_id = 4; // stage 4 items have item_id greater than 3
					
					$a_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$aID' AND item_id >= $min_item_id";
					$a_result = mysql_query($a_sql) or die(mysql_error());
					
					while($coding = mysql_fetch_array($a_result)) {
						$the_item_key = 0;
						foreach($answer_items as $key => $item) {
							if($item['item_id'] == $coding['item_id']) {
								$the_item_key = $key;
								break;
							}
						}
						
						$item_index = $answer_items[$the_item_key]['item_name'].$answer_items[$the_item_key]['code_num'];
						$coder_index = $item_index."_Coder";
						$coded_answers[$i]["$item_index"] = $coding['coded_value'];
						$this_coderID = $coding['coder_id'];
						$coded_answers[$i]["$coder_index"] = $all_coders[$this_coderID];
						$answer_items[$the_item_key]['code_num'] = $answer_items[$the_item_key]['code_num'] + 1;
					}
					$i++;
				}
				
				return $coded_answers;
				
				break;
		
		}
	}
	elseif($stage == "precode") {
		//$coded_questions = get_coded_data(1);	
		$coded_answers = get_coded_data(2);
    
		$total_time = 0;
		foreach($coded_answers as $answer) {
			// It's faster to pull the question information from the database, rather than search the array returned by get_coded_data(1)
			$qID = $answer['questionID'];
			$q_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$qID'";
					$q_result = mysql_query($q_sql) or die(mysql_error());
					
					$j_1 = 0;
					$nw_1 = 0;
					$n_1 = 0;
					$hw_1 = 0;
					while($coding = mysql_fetch_array($q_result)) {
						switch($coding['item_id']) {
							case 1:
								if($j_1 == 0) {
									$q['qJoke1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qJoke1_Coder'] = $all_coders[$this_coderID];
									$j_1++;
									
								}
								else {
									$q['qJoke2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qJoke2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 2:
								if($n_1 == 0) {
									$q['qNonsense1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qNonsense1_Coder'] = $all_coders[$this_coderID];
									$n_1++;
								}
								else {
									$q['qNonsense2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qNonsense2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 3:
								if($hw_1 == 0) {
									$q['qHomework1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qHomework1_Coder'] = $all_coders[$this_coderID];
									$hw_1++;
								}
								else {
									$q['qHomework2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qHomework2_Coder'] = $all_coders[$this_coderID];
								}
								break;
							case 4:
								if($nw_1 == 0) {
									$q['qNotWhy1'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qNotWhy1_Coder'] = $all_coders[$this_coderID];
									$nw_1++;
								}
								else {
									$q['qNotWhy2'] = $coding['coded_value'];
									$this_coderID = $coding['coder_id'];
									$q['qNotWhy2_Coder'] = $all_coders[$this_coderID];
								}
								break;
						}
						
					}
			$precoded[] = array_merge($answer,$q);
		}
		

		return $precoded;
		// this needs some work to merge efficiently
	}
	elseif($stage == "code") {
		$coded_answers = get_coded_data(4);
		$question_items = get_coding_items("Q");
		
		echo "Mem usage before going through coded_answers: ";
		echo memory_get_usage();
		echo "<br />";
		
		$i = 0;
		foreach($coded_answers as $answer) {
			// It's faster to pull the question information from the database, rather than search the array returned by get_coded_data(1)
			$qID = $answer['questionID'];
	
			
			$min_item_id = 5; // stage 3 items have item_id greater than 4
			
			$q_sql = "SELECT item_id, coded_value, coder_id FROM codings WHERE q_or_a_id ='$qID' AND item_id >= $min_item_id";
			$q_result = mysql_query($q_sql) or die(mysql_error());
			
			
			while($coding = mysql_fetch_array($q_result)) {
				
				$the_item_key = 0;
				foreach($question_items as $key => $item) {
					if($item['item_id'] == $coding['item_id']) {
						$the_item_key = $key;
						break;
					}
				}
				
				$item_index = $question_items[$the_item_key]['item_name'].$question_items[$the_item_key]['code_num'];
				$coder_index = $item_index."_Coder";
				$q["$item_index"] = $coding['coded_value'];
				$this_coderID = $coding['coder_id'];
				$q["$coder_index"] = $all_coders[$this_coderID];
				$question_items[$the_item_key]['code_num'] = $question_items[$the_item_key]['code_num'] + 1;
				
				
			}
			$i++;
			if($i > 500) break;
			$mem_pre = memory_get_usage();
			$coded[] = array_merge($answer,$q); 
			$mem_post = memory_get_usage();
			echo "total: ".$mem_post."<br />";
			echo ($mem_post - $mem_pre)."<br /><br />";
		}
		return $coded;
	}
}

function get_validation_variables($stage) {
	$num_coders = 2;
	
	if($stage == 1) {
		$table = "questionitems";
		$item_where = "item_cat = 'c0'";
	}
	elseif($stage == 2) {
		$table = "answeritems";
		$item_where = "item_cat = 'c0'";
	}
	elseif($stage == 3) {
		$table = "questionitems";
		$item_where = "item_cat != 'c0'";
	}
	elseif($stage == 4) {
		$table = "answeritems";
		$item_where = "item_cat != 'c0'";
	}
	
	$sql = "SELECT item_name, item_id FROM $table WHERE $item_where";
	$result = mysql_query($sql) or die(mysql_error());
	$c = 0;
	while($name = mysql_fetch_array($result)) {
		$vars[$c][] = $name['item_id']; // set element [0] of the subarray to the id number of the item for value lookup
		$vars[$c][] = $name['item_name'];  // set the name of the overall item coding to the name of the item ( ITEM )
		for($i=1;$i<=$num_coders;$i++) {
			$vars[$c][] = $name['item_name'].$i;  // give individual codings the name ITEM1, ITEM2
		}
		$c++;
	}
	
	return $vars;
}

function get_item_values($id,$q_or_a) {
	if($q_or_a == "a") {
		$table = "aitemvalues";
	}
	else {
		$table = "qitemvalues";
	}
	
	$sql = "SELECT item_val, val_desc FROM $table WHERE item_id = '$id'";
	$result = mysql_query($sql) or die(mysql_error());
	
	$the_vals = array();
	$i = 0;
	while($vals = mysql_fetch_array($result)) {
		$the_vals[$i]['value'] = $vals['item_val'];
		$the_vals[$i]['desc'] = $vals['val_desc'];
		$i++;
	}
	
	return $the_vals;
}

/****************************
Flag Functions
****************************/

function get_flags($unresolved=true,$flag_id="") {
	db_connect();
	
	$sql = "SELECT id, discuss, unsure, comment, coding_id, timestamp FROM flags";
	
	if($unresolved) {
		$sql .= " WHERE resolved = 0";
	}
	if($flag_id != ""){
		$sql .= " AND id = '$flag_id'";
	}
	
	$results = mysql_query($sql) or die(mysql_error());
	
	$flags = array();
	while($flag = mysql_fetch_array($results)) {
		$flags[] = $flag;
	}

	return $flags;
}

function get_coding_info($id) {
	db_connect();
	
	$sql = "SELECT coder_id, q_or_a_id, item_type, item_id, coded_value FROM codings WHERE id = $id";
	$result = mysql_query($sql) or die(mysql_error());
	$coding_raw = mysql_fetch_array($result);
	
	$coding = array();
	
	$coding['coder_id'] = $coding_raw['coder_id'];
	$coding['q_or_a_id'] = $coding_raw['q_or_a_id'];
	
	if($coding_raw['item_type'] == '2' || $coding_raw['item_type'] == '4') {
		// answer coding
		$a = $coding_raw['q_or_a_id'];
		$a_sql = "SELECT question_id, content FROM answers WHERE answer_id = '$a'";
		$a_result = mysql_query($a_sql) or die(mysql_error());
		$a_info = mysql_fetch_array($a_result);
		$q = $a_info['question_id'];
		$q_sql = "SELECT subject, content FROM questions WHERE question_id = '$q'";
		$q_result = mysql_query($q_sql) or die(mysql_error());
		$q_info = mysql_fetch_array($q_result);
		
		$coding['q_subject'] = $q_info['subject'];
		$coding['q_content'] = $q_info['content'];
		$coding['a_content'] = $a_info['content'];
		
		// get coding item info
		$i = $coding_raw['item_id'];
		$i_sql = "SELECT item_name, item_desc FROM answeritems WHERE item_id = '$i'";
		$i_result = mysql_query($i_sql) or die(mysql_error());
		$i_info = mysql_fetch_array($i_result);
		
		$coding['item_name'] = $i_info['item_name'];
		$coding['item_desc'] = $i_info['item_desc'];
		
		// get coded value info
		$v = $coding_raw['coded_value'];
		$v_sql = "SELECT val_desc FROM aitemvalues WHERE item_id = $i AND item_val = '$v'";
		$v_result = mysql_query($v_sql) or die(mysql_error());
		$v_info = mysql_fetch_array($v_result);
		
		$coding['coded_value_desc'] = $v_info['val_desc'];
	}
	else {
		// question coding
		$q = $coding_raw['q_or_a_id'];
		$q_sql = "SELECT subject, content FROM questions WHERE question_id = '$q'";
		$q_result = mysql_query($q_sql) or die(mysql_error());
		$q_info = mysql_fetch_array($q_result);
		
		$coding['q_subject'] = $q_info['subject'];
		$coding['q_content'] = $q_info['content'];
		
		// get coding item info
		$i = $coding_raw['item_id'];
		$i_sql = "SELECT item_name, item_desc FROM questionitems WHERE item_id = '$i'";
		$i_result = mysql_query($i_sql) or die(mysql_error());
		$i_info = mysql_fetch_array($i_result);
		
		$coding['item_name'] = $i_info['item_name'];
		$coding['item_desc'] = $i_info['item_desc'];
		
		// get coded value info
		$v = $coding_raw['coded_value'];
		$v_sql = "SELECT val_desc FROM qitemvalues WHERE item_id = $i AND item_val = '$v'";
		$v_result = mysql_query($v_sql) or die(mysql_error());
		$v_info = mysql_fetch_array($v_result);
		
		$coding['coded_value_desc'] = $v_info['val_desc'];
	}
	
	return $coding;
	
}


/****************************
Yahoo! Answers Pull Functions
****************************/

function count_returned_questions($the_QsAs) {
	return count($the_QsAs);
}

function count_returned_answers($the_QsAs) {
	$num_ans = 0;
	foreach($the_QsAs as $question) {
		$num_ans += count($question['Answers']);
	}
	return $num_ans;
}


/****************************
General Functions
****************************/

/**
 * A function for making time periods readable
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     2.0.1
 * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
 * @param       int     number of seconds elapsed
 * @param       string  which time periods to display
 * @param       bool    whether to show zero time periods
 */
function time_duration($seconds, $use = null, $zeros = false)
{
    // Define time periods
    $periods = array (
        'years'     => 31556926,
        'Months'    => 2629743,
        'weeks'     => 604800,
        'days'      => 86400,
        'hours'     => 3600,
        'minutes'   => 60,
        'seconds'   => 1
        );
  
    // Break into periods
    $seconds = (float) $seconds;
    $segments = array();
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }
  
    // Build the string
    $string = array();
    foreach ($segments as $key => $value) {
        $segment_name = substr($key, 0, -1);
        $segment = $value . ' ' . $segment_name;
        if ($value != 1) {
            $segment .= 's';
        }
        $string[] = $segment;
    }
  
    return implode(', ', $string);
}

function sort_by_num_items($a, $b) {
	return $b['numItems'] - $a['numItems'];
}

function sort_by_subarray_len($a,$b) {
	return count($b) - count($a);
}

function subarray_search($array, $key, $value)
{
    $results = array();

    if (is_array($array))
    {
        if ($array[$key] == $value)
            $results[] = $array;

        foreach ($array as $subarray)
            $results = array_merge($results, subarray_search($subarray, $key, $value));
    }

    return $results;
}

function random_greeting() {
	// Feel free to add stuff here if you get bored.
	$greetings = Array(
		"Hello",
		"Hi",
		"Hola",
		"Yo",
		"Salutations",
		"h3110",
		"Ahoy",
		"Ciao",
		"Bonjour",
		"Greetings",
		"Hey");
	
	return $greetings[array_rand($greetings)];
}

/****************************
Database Functions
****************************/

function db_connect() {
	include("config.php");
	
	$dbHostname = $db_host; 
	$dbUsername = $db_username;
	$dbPassword = $db_password;
	$dbname = $db_name;
	//$dbname = $db_testname; // THE TEST DATABASE IT'S OK TO SCREW UP THE DATA HERE!
	

	$con = mysql_connect($dbHostname, $dbUsername,$dbPassword);
	return mysql_select_db($dbname, $con);
}

/****************************
Layout Functions
****************************/

function get_coding_title($type,$project="") {
	$title = "";
	
	if($type == 1) {
		$title .= "Question Precoding";
	}
	if($type == 2) {
		$title .= "Answer Precoding";
	}
	if($type == 3) {
		$title .= "Question Coding";
	}
	if($type == 4) {
		$title .= "Answer Coding";
	}
	
	if($project != "") {
		$title .= " - ";
		$sql = "SELECT name FROM projects WHERE id = $project";
		$result = mysql_query($sql) or die(mysql_error());
		$proj_row = mysql_fetch_array($result);
		$title .= $proj_row['name'];
	}
	
	return $title;	
}

function layout_header($title, $add_script="") {
	$logoNum = rand(1,3);

	echo <<<END
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>ExCode [CoCo Lab] - $title</title>
	<link rel="stylesheet" href="excode.css" type="text/css" />
	<link rel="stylesheet" href="dropdown_nav.css" type="text/css" />
	<link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css"/>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<script src="js/languages/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.validationEngine.js" type="text/javascript"></script>
	$add_script
</head>
<body>
<div id="shell">
<div id="title"><a href="index.php" title="ExCode" class="img_link"><img src="images/excodelogo$logoNum.png" alt="ExCode, formally known as Yahoo Answers Explanation Coding" /></a></div>
END;
}

function layout_menu(){
	global $coderName, $coderID;
	$projects = get_project_info();
	$greeting = random_greeting();
	echo <<<END
<table summary="headings" border=0 cellpadding=2 cellspacing=0 width="100%"><tr><td class="menu" align="left">
	<ul id="nav">
		<li>
			<a href="code.php?type=1">Question Precoding</a>
END;
			if(count($projects) > 0){
				echo "<ul>";
				foreach($projects as $project) {
					echo '<li><a href="code.php?type=1&project=';
					echo $project['id'];
					echo '" title="';
					echo $project['proj_desc'];
					echo '">';
					echo $project['name'];
					echo '</a></li>';
					
				}
				echo "</ul>";
			}
	echo <<<END
		</li>
		<li>
			<a href="code.php?type=2">Answer Precoding</a>
END;
			if(count($projects) > 0){
				echo "<ul>";
				foreach($projects as $project) {
					echo '<li><a href="code.php?type=2&project=';
					echo $project['id'];
					echo '" title="';
					echo $project['proj_desc'];
					echo '">';
					echo $project['name'];
					echo '</a></li>';
					
				}
				echo "</ul>";
			}
	echo <<<END
		</li>
		<li>
			<a href="code.php?type=3">Question Coding</a>
END;
			if(count($projects) > 0){
				echo "<ul>";
				foreach($projects as $project) {
					echo '<li><a href="code.php?type=3&project=';
					echo $project['id'];
					echo '" title="';
					echo $project['proj_desc'];
					echo '">';
					echo $project['name'];
					echo '</a></li>';
					
				}
				echo "</ul>";
			}
	echo <<<END
		</li>
		<li>
			<a href="code.php?type=4">Answer Coding</a>
END;
			if(count($projects) > 0){
				echo "<ul>";
				foreach($projects as $project) {
					echo '<li><a href="code.php?type=4&project=';
					echo $project['id'];
					echo '" title="';
					echo $project['proj_desc'];
					echo '">';
					echo $project['name'];
					echo '</a></li>';
					
				}
				echo "</ul>";
			}
	echo <<<END
		</li>
	</ul>
	<div id="user_nav">
		&nbsp;$greeting, <a href="coder.php?id=$coderID"> $coderName</a>!
END;
	if(is_admin($coderID)) {
		echo "&nbsp; <a href='admin.php'>Admin</a>";
	}
	echo <<<END
 		&nbsp; <a href="logout.php">Logout</a>
 	</div>
</td>
</tr>
</table>
END;
//echo "<span class='alert'>CURRENTLY USING THE TEST DATABASE.  ANY CODINGS/CHANGES MADE AT THIS TIME WILL NOT AFFECT THE REAL DATA.- Dave</span><br />";
//echo "<span class='alert'>I'M DOING STUFF TO THE NAVIGATION, PLEASE STAY CALM. - Dave</span><br />";
}

function layout_footer() {
	global $coderName;
	echo <<<END
<hr size=3 noshade />
<div id="footer">
<a href="http://cognition.berkeley.edu">Concepts and Cognition Lab</a> - UC Berkeley
</div>
</div> <!-- for shell -->
</body>
</html>
END;
}