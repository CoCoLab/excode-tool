<?php

include_once("funcs.php");

/*
yahooAnswers class

*/

class yahooAnswers {	
	var $appID = null;
	var $searchQuestionURL = "http://answers.yahooapis.com/AnswersService/V1/questionSearch";
	var $getQuestionURL = "http://answers.yahooapis.com/AnswersService/V1/getQuestion";
	
	var $output = "php"; // get results as a PHP array
	var $search_strings = null; // search for questions with "why"
	
	var $categories = null; // this will be an array of catagory names
	var $questions_per_cat = ""; // max 50
	var $date_range = ""; // all or this format: "7" = 7 days ago, "7-30" = 7 to 30 days ago
	
	
	var $the_questions = array();
	var $the_QsAs = array();
	
	public function __construct($categories,$questions_per_cat,$search_string,$date_range="all") {
		$this->categories = $categories;
		$this->questions_per_cat = $questions_per_cat;	
		$this->date_range = $date_range;
		$this->search_string = urlencode($search_string);
		
		include("config.php");  // get app id from config.php
		$this->appID = $ya_appid;
	}
	
	public function getQandAs() {
		$this->searchQuestions();
		$this->getQuestions();	
	}
	
	// $db_scheme = "old" or "new"
	// returns pull_id : a unique identifier for this Yahoo! Answers pull (or false if everything blows up)
	public function addQandAsToDB() {
		db_connect();
		
		$sql_pull = "SELECT id FROM pullinfo ORDER BY id DESC LIMIT 1";
		$result_pull = mysql_query($sql_pull) or die ("MySQL error: ".mysql_error());
		$pull_array = mysql_fetch_array($result_pull);
		$pull_id = $pull_array[0] + 1;
		
		$timestamp = time();
		
		// add questions
		foreach($this->the_QsAs as $q) {			
			$subject = mysql_real_escape_string($q[Subject]);
			$content = mysql_real_escape_string($q[Content]);
			$category = $q[Category][content];

			$sql_q = "INSERT INTO questions (question_id, subject, content, timestamp, category, user_id, num_answers, num_comments, pull_id, valid) VALUES ('$q[id]','$subject','$content','$q[Timestamp]','$category','$q[UserId]','$q[NumAnswers]','$q[NumComments]', '$pull_id', '0')";
			$result_q = mysql_query($sql_q) or die ("MySQL error: ".mysql_error());

			
			// add answers
			$a_id = 0;
			foreach($q[Answers] as $a) {
				$answer_id = "ans".$a_id."_".$q[id];
				$a_content = mysql_real_escape_string($a[Content]);
				$a_ref = mysql_real_escape_string($a[Reference]);
				
				$sql_a = "INSERT INTO answers (answer_id, question_id, user_id, content, reference, best, timestamp, pull_id, valid) VALUES ('$answer_id','$q[id]','$a[UserId]','$a_content','$a_ref','$a[Best]','$a[Timestamp]', '$pull_id', '0')";
				$result_a = mysql_query($sql_a) or die ("MySQL error: ".mysql_error());
				
				$a_id++;
			}
		}
		
		// pull from questions and answers tables and insert into codeditems table
		
		$sql_q_code = "SELECT question_id FROM questions WHERE pull_id = '$pull_id'";
		$result_q_code = mysql_query($sql_q_code) or die ("MySQL error: ".mysql_error());
		$i=0;
		while($Qid = mysql_fetch_array($result_q_code)){
			$the_Qid = $Qid['question_id'];
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Qid','1','1','-1','-1','0','0')";
			$result1 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Qid','1','1','-1','-1','0','0')";
			$result1 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Qid','1','2','-1','-1','0','0')";
			$result2 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Qid','1','2','-1','-1','0','0')";
			$result2 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Qid','1','3','-1','-1','0','0')";
			$result3 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Qid','1','3','-1','-1','0','0')";
			$result3 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Qid','1','4','-1','-1','0','0')";
			$result4 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Qid','1','4','-1','-1','0','0')";
			$result4 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
			// Don't add answer codings to the DB yet.  Only add answer codings for valid questions.
			/* 			
			$sql_a_code = "SELECT answer_id FROM answers WHERE question_id = '$the_Qid'";
			$result_a_code = mysql_query($sql_a_code) or die ("MySQL error: ".mysql_error());
			
			while($Aid = mysql_fetch_array($result_a_code)){
				$the_Aid = $Aid['answer_id'];
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Aid','2','1','-1','-1','0','0')";
				$result5 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Aid','2','1','-1','-1','0','0')";
				$result5 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Aid','2','2','-1','-1','0','0')";
				$result6 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Aid','2','2','-1','-1','0','0')";
				$result6 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('1','-1','$the_Aid','2','3','-1','-1','0','0')";
				$result7 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
				
				$sql = "INSERT INTO codings (rank, coder_id, q_or_a_id, item_type, item_id, coded_value, timestamp, toReview, valid) VALUES ('2','-1','$the_Aid','2','3','-1','-1','0','0')";
				$result7 = mysql_query($sql) or die ("MySQL error: ".mysql_error());
			
				$i+=6; // 3 types * 2 coders
			}*/
			
			$i+=8; // 4 types * 2 coders
		}
		echo "<em>$i coding items added to the database</em><br />";
		
		
		// add pull_id to pullinfo table
		$sql_pullid = "INSERT INTO pullinfo (id,timestamp,pull_desc) VALUES ('$pull_id', '$timestamp', '')";
		$result_pullid = mysql_query($sql_pullid) or die ("MySQL error: ".mysql_error());
		
		return $pull_id;
	}
	
	private function searchQuestions() {		
		$requestURL = $this->searchQuestionURL."?appid=".$this->appID;
		
		foreach($this->categories as $cat) {
			$requestURL .= "&query=".$this->search_string;
			
			
			/* $cat = urlencode($cat); // clean up $cat name for url encoding
			$requestURL .= "&category_name=".$cat; */
			$requestURL .= "&category_id=".$cat;
			$requestURL .= "&date_range=".$this->date_range;
			$requestURL .= "&results=".$this->questions_per_cat;
			$requestURL .= "&output=".$this->output;
			
			echo "<strong>".$requestURL."</strong><br />";
			//echo "<strong>".$cat."</strong><br />";
			$results = $this->makeCURLCall($requestURL);
			echo "<strong>****pre unserialize(): ".$results."</strong><br />";
			$results = unserialize($results);
			echo "<pre>";
			print_r($results);
			echo "</pre>";
			
			echo "<br />";
			echo "<strong>In searchQuestions(): ".count($results['Questions'])."</strong><br />";
			
			// merge all questions into master $the_questions array
			foreach($results['Questions'] as $q) {
				$this->the_questions[] = $q;
			}
			
			$requestURL = $this->searchQuestionURL."?appid=".$this->appID;
		}
			
		
		
		
		/* echo "<strong>mergered araray</strong><br />";
		echo "<pre>";
		print_r($this->the_questions);
		echo "</pre>"; */
 
	
	}
	
	private function getQuestions() {
		$requestURL = $this->getQuestionURL."?appid=".$this->appID;
		
		foreach($this->the_questions as $q) {
			$requestURL .= "&question_id=".$q['id'];
			$requestURL .= "&output=".$this->output;
			
			$results = $this->makeCURLCall($requestURL);
			//echo "<strong>****pre unserialize(): ".$results."</strong><br /><br />";
			$results = unserialize($results);
			
			echo "<strong>In getQuestions(): ".count($results['Questions'])."</strong><br />";

			// merge all questions into the all important $the_QsAs array
			foreach($results['Questions'] as $q) {
				$this->the_QsAs[] = $q;
			}
			
			$requestURL = $this->getQuestionURL."?appid=".$this->appID;
		}
		
		/* echo "<strong>mergered QA araray</strong><br />";
		echo "<pre>";
		print_r($this->the_QsAs);
		echo "</pre>"; */
		
	}
	
	function makeCURLCall($url) {
		$url = urlencode($url);
		$cURL_handle = curl_init($url);
		if ($cURL_handle) {
			// set curl settings
			curl_setopt($cURL_handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURL_handle, CURLOPT_FOLLOWLOCATION,true);
			curl_setopt($cURL_handle, CURLOPT_TIMEOUT,60);
	
			$result = curl_exec($cURL_handle); // with $this->results set to "php" this should be a PHP array
			
			curl_close($cURL_handle);
			
			return $result;
		}
		else {
			throw new Exception("Unable to make call to Yahoo! Answers servers.");
		}
	}	
	

}

?>