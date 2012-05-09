<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

layout_header("Home");

if(is_loggedin()){
	layout_menu();
	
	echo "<br /><span class='page_title'>Your Weekly Quota</span><br />";
	$totalTypeCodings = get_weekly_quota();
	// get the timestampe for the start of this week
	$isoWeekStartTime = strtotime(date('o-\\WW')); // {isoYear}-W{isoWeekNumber}
	$isoWeekEndTime = $isoWeekStartTime + (60*60*24*7);
	$typeCoded = get_number_items_coded($coderID,$isoWeekStartTime,$isoWeekEndTime);
	$percentDone = $typeCoded/$totalTypeCodings;
	$barSize = round(400*$percentDone);
	echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo "$typeCoded of $totalTypeCodings coded.</span><br />";
	
	echo "<br />";
		
	echo "<br /><span class='page_title'><a href='code.php?type=1'>Question Precoding</a></span><br />";
	$totalTypeCodings = count_total_codings(1);
	$typeUncoded = count_uncoded(1);
	$typeCoded = $totalTypeCodings - $typeUncoded;
	$percentDone = $typeCoded/$totalTypeCodings;
	$barSize = round(400*$percentDone);
	echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo "$typeCoded of $totalTypeCodings coded.</span><br />";
	
	echo "<br /><span class='page_title'><a href='code.php?type=2'>Answer Precoding</a></span><br />";
	$totalTypeCodings = count_total_codings(2);
	$typeUncoded = count_uncoded(2);
	$typeCoded = $totalTypeCodings - $typeUncoded;
	$percentDone = $typeCoded/$totalTypeCodings;
	$barSize = round(400*$percentDone);
	echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo "$typeCoded of $totalTypeCodings coded.</span><br />";
	
	echo "<br /><span class='page_title'><a href='code.php?type=3'>Question Coding</a></span><br />";
	$totalTypeCodings = count_total_codings(3);
	$typeUncoded = count_uncoded(3);
	$typeCoded = $totalTypeCodings - $typeUncoded;
	$percentDone = $typeCoded/$totalTypeCodings;
	$barSize = round(400*$percentDone);
	echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo "$typeCoded of $totalTypeCodings coded.</span><br />";
	
	echo "<br /><span class='page_title'><a href='code.php?type=4'>Answer Coding</a></span><br />";
	$totalTypeCodings = count_total_codings(4);
	$typeUncoded = count_uncoded(4);
	$typeCoded = $totalTypeCodings - $typeUncoded;
	$percentDone = $typeCoded/$totalTypeCodings;
	$barSize = round(400*$percentDone);
	echo "<span class='QorAid'><div id='smCodeBar'><div id='smCodeProg' style='width: ".$barSize."px;'></div></div>";
	echo "$typeCoded of $totalTypeCodings coded.</span><br />";
	
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}
layout_footer();


?>