<?php
include_once("funcs.php");

session_start();

layout_header("Coder Stats");

if(is_loggedin()){
	layout_menu();
	
	if($_GET["id"]) {
		$thisID = $_GET["id"];
		$thisName = get_coder_name($thisID);
		if($thisName != "") {
			echo "<br />";
			echo "<span class='Qsub'>Coding Stats For $thisName</span><br /><br />";
			echo "<div class='stat_indent'>";
			$itemsCoded = get_number_items_coded($thisID);
			echo "<strong># Items Coded:</strong> $itemsCoded <br />";
			$codingTime = get_total_coding_time($thisID);
			echo "<strong>Time Spent Coding: </strong>";
			echo time_duration($codingTime); 
			echo "<br />";
			if(!is_guest()){
				$codingRate = get_coding_rate($thisID);
				echo "<strong><span title='since September 12th, 2011'>Coding Rate:</span></strong> $codingRate sec/item <br />";
			}
			echo "</div>";
		}
		else {
			echo "<span class='error'>Invalid Coder ID</span>";
		}
	}
	
	echo "<br />";
	
	if(!is_guest()) {
		$weekStartDate = date('M dS', strtotime(date('o-\\WW', time())));
		$weekEndDate = date('M dS', strtotime(date('o-\\WW', time()))+(60*60*24*7));
		echo "<span class='Qsub'>Contributions For This Week ($weekStartDate - $weekEndDate)</span><br /><br />";
		echo "<div class='stat_indent'>";
		$weeklyItemsCoded = 0;
		$rankedCoders = get_weekly_ranked_coders();
		$rank = 1;
		foreach($rankedCoders as $coder) {
			 echo "<strong>$rank )</strong> ";
			 if($coder["name"] == $thisName){
				echo "<strong>";
			 }
			 echo $coder["name"];
			 echo " - ";
			 echo $coder["numItems"];
			 if($coder["name"] == $thisName){
				echo "</strong>";
			 }
			 $weeklyItemsCoded += $coder["numItems"];
			 echo "<br />"; 
			 $rank++;
		}
	
		echo "<br />";
		echo "<strong>Total Items Coded This Week:</strong> $weeklyItemsCoded<br />";
		echo "</div>";	
	}
	
	echo "<br />";
	echo "<span class='Qsub'>Total Progress</span><br /><br />";
	echo "<div class='stat_indent'>";
	
	/*$firstStageTotal = countCodings(1);
	$firstStageUncoded = countUncoded(1);
	$firstStageCoded = $firstStageTotal - $firstStageUncoded;
	$percentDone = $firstStageCoded/$firstStageTotal;
	$barSize = round(400*$percentDone);
	echo "<div id='qCodeBar'><div id='qCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo $percentDone*100;
	echo "% of Question Coding Completed<br /><br />";
	echo "<strong>Question Codings Left To Be Done:</strong> ";
	echo $firstStageUncoded."<br />";	*/
	
	/* $thirdStageTotal = count_total_codings(4);
	echo "4th stage total: ".$thirdStageTotal."<br />";
	$thirdStageUncoded = count_uncoded(4);
	echo "4th stage uncoded: ".$thirdStageUncoded."<br />";
	$thirdStageCoded = $thirdStageTotal - $thirdStageUncoded;
	$percentDone = $thirdStageCoded/$thirdStageTotal;
	$barSize = round(400*$percentDone);
	echo "<div id='qCodeBar'><div id='qCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo $percentDone*100;
	echo "% Coding Completed<br /><br />";
	echo "<strong>Codings Left To Be Done:</strong> ";
	echo $thirdStageUncoded; */
	
	/*
	$firstStageTotal = countCodings(1);
	$firstStageUncoded = countUncoded(1);
	$firstStageCoded = $firstStageTotal - $firstStageUncoded;
	echo "<strong>Question Codings Left To Be Done:</strong> ";
	echo $firstStageUncoded;
	echo " <span class='alert'>(Fixing Old Duplicates)</span><br />";
	*/
	
	echo "<br />";
	$totalCodingRate = get_coding_rate();
	echo "<strong><span title='since September 12th, 2011'>Average Coding Rate:</span></strong> $totalCodingRate sec/item <br />";
	$totalTime = get_total_coding_time();
	echo "<strong><span title='since September 12th, 2011'>Total Coding Time:</span></strong> ";
	echo time_duration($totalTime);
	echo "<br />";

	
	
	echo "</div>";
	
	echo "<br />";
	
	if(!is_guest()) {
		echo "<span class='Qsub'>Individual Contributions</span><br /><br />";
		echo "<div class='stat_indent'>";
		$rankedCoders = get_ranked_coders();
		$rank = 1;
		foreach($rankedCoders as $coder) {
			 echo "<strong>$rank )</strong> ";
			 if($coder["name"] == $thisName){
				echo "<strong>";
			 }
			 echo $coder["name"];
			 echo " - ";
			 echo $coder["numItems"];
			 if($coder["name"] == $thisName){
				echo "</strong>";
			 }
			 echo "<br />"; 
			 $rank++;
		}
		echo "<br />";
		echo "</div><br />";
	}
	
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}
layout_footer();

?>