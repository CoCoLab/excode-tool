<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

$type = $_GET['type'];
$project = $_GET['project'];
if(is_valid_project($project)) {
	$codingTitle = get_coding_title($type,$project);
}
else {
	$codingTitle = get_coding_title($type);
}

layout_header($codingTitle);

if(is_loggedin()){
	layout_menu();
		
	if($_GET["submit"]=="ok"){
		echo "<br />";
		echo "<div class='submit'>Coding successfully submitted.<br />\n";
		
		$num_submitted = $_GET['num_submitted'];
		$i=0;
		$undo_list = "?code_type=$type&sure=no&num_submitted=$num_submitted";
		while($i<$num_submitted) {
			$undo_list .= "&undo_$i=".$_GET["coded_$i"];
			$i++;
		}
		
		echo "<span class='sm_text'><a href='undo.php$undo_list' title='Undo Last Coding'>[Undo Last Coding]</a></span></div>";
	}
	elseif($_GET["submit"]=="bad"){
		echo "<br />";
		echo "<div class='bad_submit'>There was an error in your submission.  Your last coding cluster was not recorded.</div>";
	}
	elseif($_GET["submit"]=="undo"){
		echo "<br />";
		echo "<div class='submit'>Your last set of codings has been undone.<br /></div>";
	}
	echo "<br /><span class='page_title'>$codingTitle</span><br />";
	
	if($type != "" && (!isset($_GET['project']) || is_valid_project($project))){
		# do type based coding.  This would include Q or A precoding or full scale Q or A coding
		
		#display completed status
		 // need to introduct project specific counts for this
		$totalTypeCodings = count_total_codings($type,$project);
		$typeUncoded = count_uncoded($type,$project);
		$typeCoded = $totalTypeCodings - $typeUncoded;
		if($totalTypeCodings > 0) {
			$percentDone = $typeCoded/$totalTypeCodings;
			$barSize = round(400*$percentDone);
			echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
			echo "$typeCoded of $totalTypeCodings in";
			if(is_valid_project($project)){
				echo " project $project, ";
			}
			echo " stage $type coded.</span><br />";
		}
		else {
			echo "<br />There are no codings of this type/project<br />";
		}
		
		# display weekly quota status
		$totalTypeCodings = get_weekly_quota();
		// get the timestampe for the start of this week
		$isoWeekStartTime = strtotime(date('o-\\WW')); // {isoYear}-W{isoWeekNumber}
		$isoWeekEndTime = $isoWeekStartTime + (60*60*24*7);
		$typeCoded = get_number_items_coded($coderID,$isoWeekStartTime,$isoWeekEndTime);
		$percentDone = $typeCoded/$totalTypeCodings;
		$barSize = round(400*$percentDone);
		echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
		echo "$typeCoded of $totalTypeCodings coded for your weekly quota.</span><br />";
		
		
		if(!is_stage_done($type,$project)) {
			if(is_valid_project($project)) {
				$validCoding = get_new_coding($type,$coderID,$project);
			}
			else {
				$validCoding = get_new_coding($type,$coderID);
			}
			if($validCoding){
				$codingId = $validCoding['item_id'];
				$codingQorAId = $validCoding['q_or_a_id'];
				$codingCategory = get_coding_category($type,$codingId);
				$thisCatName = $codingCategory['cat_name'];
				$thisCatDesc = $codingCategory['cat_desc'];
				$thisCatId = $codingCategory['cat_id'];
				
				if($type == "1" || $type == "3") {
					# question types
					$questionId = $codingQorAId;
					$displayQuestion = $codingQorAId;
					
					# get an answer to display
					$aSql = "SELECT content,answer_id FROM answers WHERE question_id='$questionId' limit 1";
					$aResult = mysql_fetch_array(mysql_query($aSql));
					$aContent = $aResult['content'];
					$aId = $aResult['answer_id'];
					
					# set CSS for question and answer displays
					$answerDivClass = "unfocused";
					$questionDivClass = "focused";
				}
				if($type == "2" || $type == "4") {
					# answer types
					$aId = $codingQorAId;
					
					$aSql = "SELECT question_id,content FROM answers WHERE answer_id='$codingQorAId' limit 1";
					$aResult = mysql_fetch_array(mysql_query($aSql));
					$aContent = $aResult['content'];
					
					# get the question to display
					$displayQuestion = $aResult['question_id'];
					
					# set CSS for question and answer displays
					$answerDivClass = "focused";
					$questionDivClass = "unfocused";
				}
				
				# display the question and answer
				$qSql = "SELECT question_id,subject,content FROM questions WHERE question_id='$displayQuestion' limit 1";
				$qResult = mysql_fetch_array(mysql_query($qSql));
				$qId = $qResult["question_id"];
				$qSubj = $qResult["subject"];
				$qContent = $qResult["content"];
				
				?>
				<br />
				<span class="QAlabel">Question:</span>
				<br />
				<br />
				<div class="<?php echo $questionDivClass; ?>">
					<span class="Qsub"><?php echo $qSubj; ?></span>
					<br/>
					<?php echo $qContent; ?>
				</div>
				<div class="QorAid">
					Question ID: <? echo $qId; ?>
				</div>
				<br />
				<span class="QAlabel">Answer:</span>
				<br />
				<br />
				<div class="<?php echo $answerDivClass; ?>">
					<?php echo $aContent; ?>
				</div>
				<div class="QorAid">
					Answer ID: <? echo $aId; ?>
				</div>
				<br />
				
				<span class="QAlabel">Coding Instructions:</span>
				<br />
				<br />
				<div class="instructions">
					<form action="submit.php" method="post">
						<span class="code_lbl"><u><? echo $thisCatName; ?> Cluster</u></span><br /><br />
						<?
						$codingForm = get_cat_coding_form($type,$thisCatId,$codingQorAId);
						echo $codingForm;						
						?>
						<br>
						<input type="submit" value="submit" />

					</form>
				</div>
				
				<?
				
			}
		}
		else {
			echo "<br /><strong>It looks like you've done all the unique codings you can for this stage.  Nice job.</strong>";
		}
	
	}

}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}
layout_footer();


?>