<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];


if($_GET['export'] != "true") {
	layout_header("View Data");
}

if(is_loggedin()){
	if($_GET['export'] != "true") {
		layout_menu();
		
		echo "<br />";
	}
	
	$view_stage = $_GET["stage"];
	$view_project = $_GET["project"];
	
	if($_GET['export'] == "true") {
		$the_date = date("Ymd");
		header("Content-type: application/vnd.ms-excel");
		$filename = "excodeStage".$view_stage;
		if($view_project != "all" && $view_project != "") {
			$filename .= "Project".$view_project;
		}
		$filename .= "_".$the_date.".xls";
		header("Content-Disposition: attachment; filename=$filename");
	}
	
	
	if($view_stage != "") {
		$then = microtime(true);
	
		if(is_numeric($view_stage)) {
			$coded_data = get_coded_data($view_stage,$view_project);
		}
		else {
			$coded_data = get_coded_data($view_stage,$view_project);
		}
		
		if(count($coded_data) > 0) {

			usort($coded_data, "sort_by_subarray_len");
		
			if($_GET['export'] != "true") {
				if($view_project == "all" or $view_project == "") {
					echo "<span class='Qsub'>Data For Stage $view_stage</span><br /><br />";
				}
				else {
					echo "<span class='Qsub'>Data For Stage $view_stage, Project $view_project</span><br /><br />";
				}
				
				$num_rows = count($coded_data);
				echo "<strong>rows returned: </strong> $num_rows <br />";
				if($view_project == "all" or $view_project == "") {
					echo "<a href='view_data.php?stage=$view_stage&export=true'>[export as .xls]</a><br />";
				}
				else {
					echo "<a href='view_data.php?stage=$view_stage&project=$view_project&export=true'>[export as .xls]</a><br />";
				}
				
				echo "<br />";	
			}
		
			
			
			$keys = array_keys($coded_data[0]);
			
			echo "<table class='data_table'>\n";
			echo "	<tr>\n";
			
			foreach($keys as $key){
				echo "<th>$key</th>\n";
			}
			
			echo "	</tr>\n";
			
			$i=0;
			foreach($coded_data as $data_row){
				if($i%2 == 0){
					$row_class = "stripe1";
				}
				else {
					$row_class = "stripe2";
				}
				echo "	<tr class='$row_class'>\n";
				foreach($data_row as $cell) {
					echo "		<td>";
					echo htmlspecialchars($cell);
					echo "		</td>\n";
				}
				echo "	</tr>\n";
				$i++;
			}
			
			echo "</table>";
		
		}
		else {
			if($view_project == "all" or $view_project == "") {
				echo "<span class='Qsub'>No Data Available For Stage $view_stage</span><br /><br />";
			}
			else {
				echo "<span class='Qsub'>No Data Available For Stage $view_stage, Project $view_project</span><br /><br />";
			}	
		}
		$now = microtime(true);
		if($_GET['export'] != "true") {
			echo "<br />";
			echo sprintf("<span class='sm_text'>Data pull time: %f seconds</span><br />", $now-$then);
		}
	}
}
else {
	echo "<br><b>Could not find coder Id. Try logging in again.</b>";
	echo "<script> self.location='login.php'; </script>";
}

if($_GET['export'] != "true") {
	layout_footer();
}

?>
	