<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

layout_header("Coding Details");

if(is_loggedin()){
	layout_menu();
	if(is_admin($coderID)) {
		$coding_id = $_GET['coding_id'];
		$flag_id = $_GET['flag_id'];
		$coding = get_coding_info($coding_id);
		if(array_key_exists("a_content",$coding)) {
			$questionDivClass = "unfocused";
			$answerDivClass = "focused";
		}
		else {
			$questionDivClass = "focused";
			$answerDivClass = "unfocused";
		}
		?>
		<br />
		<span class='Qsub'>Coding Details For Coding Id: <? echo $coding_id; ?></span><br /><br />
		<br />
		<span class="QAlabel">Flag Issues:</span><br />
		<br />
		<div class="instructions">
			<?
			$flag = get_flags(true,$flag_id);
			if($flag[0]['discuss']) {
				echo " <span class='code_lbl'>Discuss</span>";
			}
			if($flag[0]['unsure']) {
				echo " <span class='code_lbl'>Unsure</span>";
			}
			?>
			<br /><br />
			<strong>Comments: </strong><br />
			<? echo $flag[0]['comment']; ?>
		</div>
		<br />
		<span class="QAlabel">Question:</span>
		<br />
		<br />
		<div class="<?php echo $questionDivClass; ?>">
			<span class="Qsub"><?php echo $coding['q_subject']; ?></span>
			<br/>
			<?php echo $coding['q_content']; ?>
		</div>
		<br />
		<? if(array_key_exists("a_content",$coding)) { ?>				
		<span class="QAlabel">Answer:</span>
		<br />
		<br />
		<div class="<?php echo $answerDivClass; ?>">
			<?php echo $coding['a_content']; ?>
		</div>
		<? } ?>
		<br />
		<span class="QAlabel">Coding Instructions:</span>
		<br />
		<br />
		<div class="instructions">
			<span class='code_lbl'><? echo $coding['item_name']; ?></span><br />
			<span class='sm_desc'><? echo $coding['item_desc']; ?></span><br />
			<br />
			Coded value: <strong><? echo $coding['coded_value_desc']; ?></strong><br />
			<br />
			Coded by: <? echo get_coder_name($coding['coder_id']); ?>
		</div>
		<br />
		<a href="admin_action.php?action=resolve_flag&flag_id=<? echo $flag_id; ?>">[resolve flag]</a>
		<br />
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
layout_footer();

?>