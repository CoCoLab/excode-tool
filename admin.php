<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

layout_header("Admin");

if(is_loggedin()){
	layout_menu();
	if(is_admin($coderID)) {
		?>
		<div id='admin_nav' class='section_nav'>
			<ul class='nav_list'>
				<li><a href='#coder_stats' title='Coder Stats'>Coder Stats</a></li>
				<li><a href='#unresolved_coding_flags' title='Unresolved Coding Flags'>Unresolved Coding Flags</a></li>
				<li><a href='#unresolved_question-answer_flags' title='Unresolved Question/Answer Flags'>Unresolved Question/Answer Flags</a></li>
				<li><a href='#view-export_data' title='View/Export Data'>View/Export Data</a></li>
				<li><a href='#validate_data' title='Validate Data'>Validate Data</a></li>
				<li><a href='#add_coding_cluster' title='Add Coding Cluster'>Add Coding Cluster</a></li>
				<li><a href='#add_coding_cluster_to_project' title='Add Coding Cluster To Project'>Add Coding Cluster To Project</a></li>
				<li><a href='#add_new_questions-answers' title='Add New Questions/Answers'>Add New Questions/Answers</a></li>
				<li><a href='#set_up_valid_questions-answers_for_coding' title='Set Up Valid Questions/Answers For Coding'>Set Up Valid Questions/Answers For Coding</a></li>
				<li><a href='#add-remove_admins' title='Add/Remove Admins'>Add/Remove Admins</a></li>
			</ul>
		</div>
		<?
	
		if($_GET['alert'] != "") {
			echo "<div class='submit'>".$_GET['alert']."</div>\n";
		}
		if($_GET['error'] != "") {
			echo "<div class='bad_submit'>".$_GET['error']."</div>\n";
		}
	
		$weekStartDate = date('M dS', strtotime(date('o-\\WW', time())));
		$weekEndDate = date('M dS', strtotime(date('o-\\WW', time()))+(60*60*24*7));
		
		// get the timestampe for the start of this week
		$isoWeekStartTime = strtotime(date('o-\\WW')); // {isoYear}-W{isoWeekNumber}
		$isoWeekEndTime = $isoWeekStartTime + (60*60*24*7);
		
		echo "<br />";
		//echo "<a name='coder_stats'></a>";
		echo "<span class='Qsub'>Coder Stats (week of: $weekStartDate - $weekEndDate)</span><br /><br />";
		echo "<div class='stat_indent'>";
		echo "<table><tr>";
		echo "	<th>Coder Name</th><th>Items Coded This Week</th><th>Total Coding Time</th><th>Coding Time This Week</th>";
		echo "</tr>";
		$rankedCoders = get_weekly_ranked_coders();
		$i=0;
		foreach($rankedCoders as $coder) {
			if($i%2 == 0){
				$row_class = "stripe1";
			}
			else {
				$row_class = "stripe2";
			}
			echo "<tr class='$row_class'>";
			echo "	<td>".$coder['name']."</td>";
			echo "	<td>".$coder['numItems']."</td>";
			$id = $coder['id'];
			$codingTime = get_total_coding_time($id);
			echo "	<td>";
			echo time_duration($codingTime);
			echo "</td>";
			echo "<td>";
			$weekCodingTime = get_range_coding_time($id,$isoWeekStartTime,$isoWeekEndTime);
			echo time_duration($weekCodingTime);
			echo "</td>";
			echo "</tr>";
			$i++;
		}
		
		?>
		</table>
		</div>
		<br />
		<a name='unresolved_coding_flags'></a>
		<span class='Qsub'>Unresolved Coding Flags</span><br /><br />
		<div class='stat_indent'>
			<div id='flag_scroll'>
		<?
		$flags = get_flags();
		//print_r($flags);
		
		foreach($flags as $flag) {
			echo date("M jS, Y",$flag['timestamp']);
			
			//$coding = get_coding_info($flag['coding_id']);
			//echo " by ".get_coder_name($coding['coder_id']);
			
			if($flag['discuss']) {
				echo " <strong>(dicuss)</strong>";
			}
			if($flag['unsure']) {
				echo " <strong>(unsure)</strong>";
			}
			
			echo " <a href='coding_details.php?coding_id=".$flag['coding_id']."&flag_id=".$flag['id']."'>[details]</a>";
			echo " <a href='admin_action.php?action=resolve_flag&flag_id=".$flag['id']."'>[resolve]</a>";
			echo "<br />";
		}
		
		?>
			</div>
		</div>
		<br />
		
		<a name='unresolved_question-answer_flags'></a>
		<span class='Qsub'>Unresolved Question/Answer Flags</span><br /><br />
		<div class='stat_indent'>
			<span class='alert'>Still in development!</span><br /><br />
		</div>
		<br />
		
		<a name='view-export_data'></a>
		<span class='Qsub'>View/Export Data</span><br /><br />
		<?
			$projects = get_project_info();
		?>
		<div class='stat_indent'>
		<strong>Precoding: </strong><a href='view_data.php?stage=precode'>[view]</a> <a href='view_data.php?stage=precode&export=true'>[export as .xls]</a><br />
		<strong>Coding: </strong><a href='view_data.php?stage=code'>[view]</a> <a href='view_data.php?stage=code&export=true'>[export as .xls]</a> (needs optimizing)<br />
		<strong>Everything: </strong><a href='view_data.php?stage=all'>[view]</a> <a href='view_data.php?stage=all&export=true'>[export as .xls]</a> (not ready yet)<br />
		<form name='stage1_data' method='GET' action='view_data.php'>
		<input type='hidden' name='stage' value='1' />
		<strong>Stage 1 
		<select name='project'>
			<option value='all'>all</option>
			<?
			if(count($projects) > 0){
				foreach($projects as $project) {
					echo '<option value="';
					echo $project['id'];
					echo '">';
					echo $project['name'];
					echo '</option>';
				}	
			}

			?>
		</select>
		:
		</strong>
		<a href="#" onclick="document['stage1_data'].submit()">[view]</a>
		</form>
		<form name='stage2_data' method='GET' action='view_data.php'>
		<input type='hidden' name='stage' value='2' />
		<strong>Stage 2 
		<select name='project'>
			<option value='all'>all</option>
			<?
			if(count($projects) > 0){
				foreach($projects as $project) {
					echo '<option value="';
					echo $project['id'];
					echo '">';
					echo $project['name'];
					echo '</option>';
				}	
			}

			?>
		</select>
		:
		</strong>
		<a href="#" onclick="document['stage2_data'].submit()">[view]</a>
		</form>
		<form name='stage3_data' method='GET' action='view_data.php'>
		<input type='hidden' name='stage' value='3' />
		<strong>Stage 3 
		<select name='project'>
			<option value='all'>all</option>
			<?
			if(count($projects) > 0){
				foreach($projects as $project) {
					echo '<option value="';
					echo $project['id'];
					echo '">';
					echo $project['name'];
					echo '</option>';
				}	
			}

			?>
		</select>
		:
		</strong>
		<a href="#" onclick="document['stage3_data'].submit()">[view]</a>
		</form>
		<form name='stage4_data' method='GET' action='view_data.php'>
		<input type='hidden' name='stage' value='4' />
		<strong>Stage 4 
		<select name='project'>
			<option value='all'>all</option>
			<?
			if(count($projects) > 0){
				foreach($projects as $project) {
					echo '<option value="';
					echo $project['id'];
					echo '">';
					echo $project['name'];
					echo '</option>';
				}	
			}

			?>
		</select>
		:
		</strong>
		<a href="#" onclick="document['stage4_data'].submit()">[view]</a>
		</form>
		</div><br />
		
		<a name='validate_data'></a>
		<span class='Qsub'>Validate Data</span><br /><br />
		<div class='stat_indent'>
			<span class='alert'>Still in development!</span><br /><br />
			<em>Validate data based on a user supplied validation scheme</em><br />
			<br />
			<a href="validate_coding.php?stage=1">VALIDATION TEST (stage 1)</a>
		</div>
		<br />
		
		<script language="javascript">
			var itemcount = 0;
			function addCodingItemFields() {
				itemcount++;
				var newdiv = document.createElement('div');
				newdiv.className = "coding_item";
				newdiv.title = itemcount;
				newdiv.innerHTML = "<br /><strong>Item Name: </strong><input type='text' name='itemname[]' /><br />" + "<strong>Item Description: </strong><textarea name='itemdesc[]'></textarea><br />" + "<strong>Item Display Type: </strong><select name='itemdisp[]'><option value=''></option><option value='rad'>radio buttons</option><option value='single_check'>single checkbox</option><option value='check_nota'>checkboxes w/ None of the Above option</option></select><br /><a onclick='addCodingItemValue()' />[add item value]</a><br />";
				document.getElementById('coding_items').appendChild(newdiv);
			}
			
			function addCodingItemValue() {
				var item_id = event.target.parentNode.title;
				var newdiv = document.createElement('div');
				newdiv.className = "coding_values";
				newdiv.innerHTML = "<br /><strong>Option Description: </strong><input type='text' name='item" + item_id + "valdesc[]' /><br /><strong>Option Value: </strong><input type='text' name='item" + item_id + "value[]' /><br />";
				event.target.parentNode.appendChild(newdiv);
			}
		</script>
		
		<a name='add_coding_cluster'></a>
		<span class='Qsub'>Add Coding Cluster</span><br /><br />
		<div class='stat_indent'>
		<form name='add_cluster' method='GET' action='admin_action.php'>
		<input type='hidden' name='action' value='add_cluster' />
		<strong>Question or Answer Cluster:</strong> 
		<select name='qora'>
		<option value='' selected='selected'></option>
		<option value='question' /> Question </option>
		<option value='answer' /> Answer </option>
		</select><br /><br />
		<strong>Cluster Name:</strong> 
		<input type='text' name='clustername' value=''size='25' /><br /><br />
		<strong><u>Coding Items</u></strong><br />
		<div id='coding_items'>
			<div class='coding_item' title='0'>
			<br /><strong>Item Name: </strong><input type='text' name='itemname[]' /><br />
			<strong>Item Description: </strong><textarea name='itemdesc[]'></textarea><br />
			<strong>Item Display Type: </strong><select name='itemdisp[]'><option value=''></option><option value='rad'>radio buttons</option><option value='single_check'>single checkbox</option><option value='check_nota'>checkboxes w/ None of the Above option</option></select><br />
			
			<a onclick='addCodingItemValue()' />[add item value]</a><br />
			</div>
		
		</div><br />
		<a onclick='addCodingItemFields()' />[add coding item]</a><br /><br />
		<!-- <input type='submit' value='submit' /> -->
		<a href="#" onclick="document['add_cluster'].submit()"> [add coding cluster]</a>
		</form>
		</div><br />
		
		<a name='add_coding_cluster_to_project'></a>
		<span class='Qsub'>Add Coding Cluster To Project</span><br /><br />
		<div class='stat_indent'>
			<span class='alert'>Still in development!</span><br /><br />
			<i>Select a cluster and a project.  Add coding items for that cluster to questions/answers in that project.</i><br />
			<br />
			<form name='add_cluster_to_project' method='GET' action='admin_action.php'>
				<input type='hidden' name='action' value='add_cluster_to_project' />
				Add <br />
				<strong>Question or Answers Cluster: </strong> 
				<select name='cluster'>
					<option value='' selected='selected'></option>
					<?
						$q_cats = get_category_info("q");
						$a_cats = get_category_info("a");
						foreach($q_cats as $cat){
							printf("<option value='q_%s'>QUESTION - %s</option>",$cat['cat_id'],$cat['cat_name']);
						}
						foreach($a_cats as $cat){
							printf("<option value='a_%s'>ANSWER - %s</option>",$cat['cat_id'],$cat['cat_name']);
						}
					
					?>
				</select><br />
				to
				<br />
				<strong>Project: </strong> 
				<select name='project'>
					<option value='' selected='selected'></option>
					<?
						$projects = get_project_info();
						foreach($projects as $project) {
							printf("<option value='%s'>%s</option>",$project['id'],$project['name']);
						}
					?>
				</select>	
				<br />
				<br />
				<a onclick="document['add_cluster_to_project'].submit()">[add cluster to project]</a>
			</form>
		</div><br />
		
		<a name='add_new_questions-answers'></a>
		<span class='Qsub'>Add New Questions/Answers</span><br /><br />
		<div class='stat_indent'>
			<a href="ya_pull.php">Add Questions/Answers From Yahoo! Answers</a><br />
		</div><br />
		
		<a name='set_up_valid_questions-answers_for_coding'></a>
		<span class='Qsub'>Set Up Valid Questions/Answers For Coding</span><br /><br />
		<div class='stat_indent'>
			<em>All new questions/answers must be precoded before they are added to stage 3 or 4 for coding.</em><br />
			<br />
			<?
			$unadded_2 = get_valid_unadded(2);
			$unadded_3 = get_valid_unadded(3);
			$unadded_4 = get_valid_unadded(4);
			?> 
			<strong><? echo count($unadded_2); ?></strong> valid questions have not had their answers added to stage 2. 
			<? if(count($unadded_2) > 0) { ?>
			<a href="admin_action.php?action=add_unadded_codings&stage=2">[add stage 2 codings for valid questions]</a>
			<? } ?>
			<br />
			<strong><? echo count($unadded_3); ?></strong> valid questions have not been added to stage 3. 
			<? if(count($unadded_3) > 0) { ?>
			<a href="admin_action.php?action=add_unadded_codings&stage=3">[add stage 3 codings for valid questions]</a>
			<? } ?>
			<br />
			<strong><? echo count($unadded_4); ?></strong> valid answers have not been added to stage 4. 
			<? if(count($unadded_4) > 0) { ?>
			<a href="admin_action.php?action=add_unadded_codings&stage=4">[add stage 4 codings for valid answers]</a>
			<? } ?>
			<br />
		</div><br />
		
		<a name='add-remove_admins'></a>
		<?
		echo "<span class='Qsub'>Add/Remove Admins</span><br /><br />";
		echo "<div class='stat_indent'>";
		echo "<strong><u>Current Admins</u></strong><br />";
		$admins = get_admins();
		foreach($admins as $admin) {
			$admin_id = $admin['coder_id'];
			echo $admin['coder_name'];
			if($admin_id != $coderID) {echo " <a href='admin_action.php?action=remove_admin&id=$admin_id'>[remove]</a>"; }
			echo "<br />";
		}
		echo "<br />";
		echo "<strong><u>Add Admin</u></strong><br />";
		echo "<form name='add_admin' method='GET' action='admin_action.php'>";
		echo "<input type='hidden' name='action' value='add_admin' />";
		echo "<select name='id'>";
		echo "<option value='' selected='selected'></option>";
		$non_admins = get_admins(true);
		foreach($non_admins as $non_admin) {
			echo "<option value='".$non_admin['coder_id']."'>".$non_admin['coder_name']."</option>";
		}
		echo "</select>";
		echo "<a href=\"#\" onclick=\"document['add_admin'].submit()\"> [add]</a>";
		echo "</form>";
		
		echo "</div><br />";
	
	}
	else {
		echo "<br><b>You're not an admin!  Don't try to trick me!  I'm on to you!</b>";
	}
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}
layout_footer();

?>