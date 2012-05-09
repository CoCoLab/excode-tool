<?php
include_once("funcs.php");

session_start();
$coderName = $_SESSION['coder_name'];
$coderID = $_SESSION['coder_id'];

$script_add = "<script>
		jQuery(document).ready(function(){
			jQuery(\"#qa_pull\").validationEngine();
		});
	</script>";

layout_header("Yahoo! Answers Pull",$script_add);

if(is_loggedin()){
	layout_menu();
	if(is_admin($coderID)) {
		?>
		<br />
		<span class='Qsub'>Add Questions/Answers From Yahoo! Answers</span><br /><br />
		<?
		if($_POST["review"] == "true") {
			?>
			Review pull results<br /><br />
			<div class="stat_indent">
			<?
			include_once("yahooAnswers.php");
			
			$categories = $_POST['categories'];
			$questions_per_cat = $_POST['q_per_c'];
			$search_string = $_POST['search_string'];
			$date_range = $_POST['date_range'];
			
			$yA = new yahooAnswers($categories,$questions_per_cat,$search_string,$date_range);
			
			$yA->getQandAs();
		
			/*
			echo "<pre>";
			echo print_r($yA);
			echo "</pre>"; */
			
			echo "<strong>";
			echo count_returned_questions($yA->the_QsAs);
			echo "</strong> questions returned <br />";
			echo "<strong>";
			echo count_returned_answers($yA->the_QsAs);
			echo "</strong> answers returned <br />";
			
			$serialized_yA = urlencode(serialize($yA)); // make the $yA object a passable string
			
			?>
				<br />
				<em>Do you want to add these questions and answers and set them up for stage 1 coding?</em><br />
				<form action="ya_pull.php" id="add_qa" method="POST">
					<input type="hidden" name="ya_obj" value="<? echo $serialized_yA; ?>" />
					<input type="hidden" name="add" value="true" />
					<input type="submit" value="Add These Questions and Answers" />
				</form>

			</div>
			<br />
			<?	
		}
		elseif ($_POST["add"] == "true") {
			?>
			Adding pull results<br /><br />
			<div class="stat_indent">
			<?
			include_once("yahooAnswers.php");
			
			$yA_str = urldecode($_POST['ya_obj']);
			$yA = unserialize($yA_str); // turn that string back into a yahooAnswers object
			
			$pull_id = $yA->addQandAsToDB();
			
			if($pull_id) {
				echo "<div class='submit'>The new questions and answers have been added</div><br /><br />";
			
				echo "<strong>";
				echo count_returned_questions($yA->the_QsAs);
				echo "</strong> questions and ";
				echo "<strong>";
				echo count_returned_answers($yA->the_QsAs);
				echo "</strong> answers have been added to the databse under pull_id <strong>$pull_id</strong> and set up for stage 1 coding.<br /><br />";
				echo "A record for pull_id <strong>$pull_id</strong> has been created.<br /><br />";
			}
			else {
				echo "<span class='error'>Something when wrong.  Have someone who knows what they're doing look at the database.</span><br /><br />";
			}
			
			echo "Return to the <a href='admin.php'>admin page</a>.";
			?>
			</div>
			<br />
			<?
		}
		else {
			$categories = array(			
				"Books &amp; Authors" => 396545299,
				"Dancing" => 396545374,
				"Genealogy" => 396546034,
				"History" => 396545298,
				"Other - Arts &amp; Humanities" => 396545310,
				"Performing Arts" => 396545300,
				"Philosophy" => 396545231,
				"Poetry" => 2115500137,
				"Theater &amp; Acting" => 396546419,
				"Visual Arts" => 396545309,
				"Fashion &amp; Accessories" => 396545392,
				"Hair" => 396546058,
				"Makeup" => 396546059,
				"Other - Beauty &amp; Style" => 396546061,
				"Skin &amp; Body" => 396546060,
				"Advertising &amp; Marketing" => 396545190,
				"Careers &amp; Employment" => 396545318,
				"Corporations" => 396545319,
				"Credit" => 396545320,
				"Insurance" => 396545321,
				"Investing" => 396545322,
				"Other - Business &amp; Finance" => 396545191,
				"Personal Finance" => 396545323,
				"Renting &amp; Real Estate" => 396545324,
				"Small Business" => 396545129,
				"Taxes" => 396545140,
				"Aircraft" => 396546088,
				"Boats &amp; Boating" => 396546385,
				"Buying &amp; Selling" => 396545312,
				"Car Audio" => 396546177,
				"Car Makes" => 396545608,
				"Commuting" => 396545313,
				"Insurance &amp; Registration" => 396545314,
				"Maintenance &amp; Repairs" => 396545315,
				"Motorcycles" => 396546040,
				"Other - Cars &amp; Transportation" => 396545317,
				"Rail" => 396546440,
				"Safety" => 396545316,
				"Computer Networking" => 396545676,
				"Hardware" => 396545661,
				"Internet" => 396545662,
				"Other - Computers" => 396545665,
				"Programming &amp; Design" => 396545663,
				"Security" => 396546062,
				"Software" => 396545664,
				"Camcorders" => 396545666,
				"Cameras" => 396545631,
				"Cell Phones &amp; Plans" => 396545653,
				"Games &amp; Gear" => 396545667,
				"Home Theater" => 396545678,
				"Land Phones" => 396545655,
				"Music &amp; Music Players" => 396545650,
				"Other - Electronics" => 396545326,
				"PDAs &amp; Handhelds" => 396545657,
				"TiVO &amp; DVRs" => 396545677,
				"TVs" => 396545648,
				"Argentina" => 396546446,
				"Australia" => 396546178,
				"Austria" => 396546447,
				"Brazil" => 396546448,
				"Canada" => 396546193,
				"Fast Food" => 396546441,
				"France" => 396546449,
				"Germany" => 396546539,
				"India" => 396546314,
				"Indonesia" => 2115500635,
				"Ireland" => 396546224,
				"Italy" => 396546450,
				"Malaysia" => 396547077,
				"Mexico" => 396546451,
				"New Zealand" => 2115500435,
				"Other - Dining Out" => 396545358,
				"Philippines" => 396547078,
				"Singapore" => 396546452,
				"Spain" => 396546454,
				"Switzerland" => 396546453,
				"Thailand" => 2115500492,
				"United Kingdom" => 396546207,
				"United States" => 396546169,
				"Vietnam" => 2115500634,
				"Financial Aid" => 396545360,
				"Higher Education (University +)" => 396545359,
				"Home Schooling" => 396546443,
				"Homework Help" => 396545134,
				"Other - Education" => 396545364,
				"Preschool" => 396546442,
				"Primary &amp; Secondary Education" => 396545136,
				"Quotations" => 396545214,
				"Special Education" => 396545361,
				"Standards &amp; Testing" => 396547121,
				"Studying Abroad" => 396546332,
				"Teaching" => 396545363,
				"Trivia" => 396545223,
				"Words &amp; Wordplay" => 396545196,
				"Celebrities" => 396545137,
				"Comics &amp; Animation" => 396545198,
				"Horoscopes" => 396546042,
				"Jokes &amp; Riddles" => 396546041,
				"Magazines" => 396545365,
				"Movies" => 396545138,
				"Music" => 396545139,
				"Other - Entertainment" => 396545366,
				"Polls &amp; Surveys" => 396546444,
				"Radio" => 2115500179,
				"Television" => 396545199,
				"Alternative Fuel Vehicles" => 2115500148,
				"Conservation" => 2115500305,
				"Global Warming" => 2115500306,
				"Green Living" => 2115500307,
				"Other - Environment" => 2115500308,
				"Family" => 396546416,
				"Friends" => 396545435,
				"Marriage &amp; Divorce" => 396545437,
				"Other - Family &amp; Relationships" => 396545438,
				"Singles &amp; Dating" => 396545604,
				"Weddings" => 396546044,
				"Beer, Wine &amp; Spirits" => 396545371,
				"Cooking &amp; Recipes" => 396545368,
				"Entertaining" => 396546026,
				"Ethnic Cuisine" => 396545370,
				"Non-Alcoholic Drinks" => 396545369,
				"Other - Food &amp; Drink" => 396545372,
				"Vegetarian &amp; Vegan" => 396546874,
				"Amusement Parks" => 396545373,
				"Board Games" => 396545377,
				"Card Games" => 396545378,
				"Gambling" => 396545375,
				"Hobbies &amp; Crafts" => 396545183,
				"Other - Games &amp; Recreation" => 396545212,
				"Toys" => 396545380,
				"Video &amp; Online Games" => 396545187,
				"Alternative Medicine" => 396545175,
				"Dental" => 396545381,
				"Diet &amp; Fitness" => 396545382,
				"Diseases &amp; Conditions" => 396545115,
				"General Health Care" => 396545143,
				"Men's Health" => 396545224,
				"Mental Health" => 396546043,
				"Optical" => 2115500199,
				"Other - Health" => 396545393,
				"Women's Health" => 396545203,
				"Cleaning &amp; Laundry" => 396545395,
				"Decorating &amp; Remodeling" => 396545396,
				"Do It Yourself (DIY)" => 396546406,
				"Garden &amp; Landscape" => 396545397,
				"Maintenance &amp; Repairs" => 396545398,
				"Other - Home &amp; Garden" => 396545400,
				"Argentina" => 396546673,
				"Australia" => 396546269,
				"Austria" => 396546674,
				"Brazil" => 396546675,
				"Canada" => 396546231,
				"France" => 396546676,
				"Germany" => 396546677,
				"India" => 396546333,
				"Indonesia" => 2115500536,
				"Ireland" => 396546262,
				"Italy" => 396546678,
				"Mexico" => 396546679,
				"New Zealand" => 2115500439,
				"Other - Local Businesses" => 396545432,
				"Singapore" => 396546680,
				"Spain" => 396546681,
				"Switzerland" => 396546682,
				"Thailand" => 2115500557,
				"United Kingdom" => 396546245,
				"United States" => 396546170,
				"Vietnam" => 2115500584,
				"Current Events" => 396545440,
				"Media &amp; Journalism" => 396545441,
				"Other - News &amp; Events" => 396545442,
				"Birds" => 396546023,
				"Cats" => 396546020,
				"Dogs" => 396546021,
				"Fish" => 396546024,
				"Horses" => 2115500432,
				"Other - Pets" => 396546025,
				"Reptiles" => 396546022,
				"Rodents" => 2115500150,
				"Civic Participation" => 396545445,
				"Elections" => 396547134,
				"Embassies &amp; Consulates" => 396545446,
				"Government" => 396545447,
				"Immigration" => 396546045,
				"International Organizations" => 2115500309,
				"Law &amp; Ethics" => 396545448,
				"Law Enforcement &amp; Police" => 396546501,
				"Military" => 396545449,
				"Other - Politics &amp; Government" => 396545599,
				"Politics" => 396545450,
				"Adolescent" => 396546048,
				"Adoption" => 2115500138,
				"Baby Names" => 396547166,
				"Grade-Schooler" => 396546047,
				"Newborn &amp; Baby" => 396546049,
				"Other - Pregnancy &amp; Parenting" => 396546054,
				"Parenting" => 396546050,
				"Pregnancy" => 396546051,
				"Toddler &amp; Preschooler" => 396546052,
				"Trying to Conceive" => 396546053,
				"Agriculture" => 2115500149,
				"Alternative" => 396547171,
				"Astronomy &amp; Space" => 396545160,
				"Biology" => 396545209,
				"Botany" => 396546086,
				"Chemistry" => 396545227,
				"Earth Sciences &amp; Geology" => 396545162,
				"Engineering" => 396545219,
				"Geography" => 396545210,
				"Mathematics" => 396545161,
				"Medicine" => 396545452,
				"Other - Science" => 396545453,
				"Physics" => 396545211,
				"Weather" => 396546055,
				"Zoology" => 396545607,
				"Anthropology" => 396545302,
				"Dream Interpretation" => 2115500160,
				"Economics" => 396545303,
				"Gender Studies" => 396545307,
				"Other - Social Science" => 396545308,
				"Psychology" => 396545305,
				"Sociology" => 396545306,
				"Community Service" => 396545455,
				"Cultures &amp; Groups" => 396545456,
				"Etiquette" => 396545165,
				"Holidays" => 396545182,
				"Languages" => 396545217,
				"Mythology &amp; Folklore" => 396545457,
				"Other - Society &amp; Culture" => 396545458,
				"Religion &amp; Spirituality" => 396545163,
				"Royalty" => 396546108,
				"Auto Racing" => 396545601,
				"Baseball" => 396545232,
				"Basketball" => 396545233,
				"Boxing" => 396546410,
				"Cricket" => 396545459,
				"Cycling" => 396545460,
				"Fantasy Sports" => 396545236,
				"Football (American)" => 396545234,
				"Football (Australian)" => 396546111,
				"Football (Canadian)" => 396546112,
				"Football (Soccer)" => 396545464,
				"Golf" => 396545461,
				"Handball" => 396546520,
				"Hockey" => 396545462,
				"Horse Racing" => 396546113,
				"Martial Arts" => 396546519,
				"Motorcycle Racing" => 2115500156,
				"Olympics" => 396546114,
				"Other - Sports" => 396545468,
				"Outdoor Recreation" => 396545186,
				"Rugby" => 396545463,
				"Running" => 396547165,
				"Snooker &amp; Pool" => 396546115,
				"Surfing" => 2115500159,
				"Swimming &amp; Diving" => 396545465,
				"Tennis" => 396545235,
				"Volleyball" => 396545466,
				"Water Sports" => 396546085,
				"Winter Sports" => 396545467,
				"Wrestling" => 396546411,
				"Africa &amp; Middle East" => 396545470,
				"Air Travel" => 396546521,
				"Argentina" => 396545540,
				"Asia Pacific" => 396545484,
				"Australia" => 396545485,
				"Austria" => 396545523,
				"Brazil" => 396545541,
				"Canada" => 396545499,
				"Caribbean" => 396545508,
				"Cruise Travel" => 396546522,
				"Europe (Continental)" => 396545522,
				"France" => 396545526,
				"Germany" => 396545527,
				"India" => 396545487,
				"Ireland" => 396545529,
				"Italy" => 396545530,
				"Latin America" => 396545539,
				"Mexico" => 396545552,
				"Nepal" => 396545492,
				"New Zealand" => 2115500437,
				"Other - Destinations" => 396545598,
				"Spain" => 396545535,
				"Switzerland" => 396545536,
				"Travel (General)" => 396545562,
				"United Kingdom" => 396545537,
				"United States" => 396545566,
				"Vietnam" => 2115500603,
				"My Yahoo!" => 396546407,
				"Other - Yahoo! Products" => 396546093,
				"Yahoo! Answers" => 396546090,
				"Yahoo! Autos" => 396546354,
				"Yahoo! Bookmarks" => 396547185,
				"Yahoo! Finance" => 396547133,
				"Yahoo! Groups" => 396546285,
				"Yahoo! Local" => 396546357,
				"Yahoo! Mail" => 396546091,
				"Yahoo! Message Boards" => 396546384,
				"Yahoo! Messenger" => 396546095,
				"Yahoo! Mobile" => 396547064,
				"Yahoo! Music" => 2115500217,
				"Yahoo! Pulse" => 396546092,
				"Yahoo! Real Estate" => 396546358,
				"Yahoo! Search" => 396546352,
				"Yahoo! Shopping" => 396546356,
				"Yahoo! Small Business" => 396547179,
				"Yahoo! Toolbar" => 396546353,
				"Yahoo! Travel" => 396546355,
				"Yahoo! Widgets" => 396546351
			);
			
			?>
			Build query for question/answer pull<br /><br />
			<div class="stat_indent">
				<form id="qa_pull" method="POST" action="ya_pull.php">
					<u><strong>Categories</strong></u><br /><br />
					<?
					foreach($categories as $name => $id){
						echo "<input type='checkbox' name='categories[]' value='$id' id='$id' class='validate[required] checkbox'/>$name <br />";
					}
					?>
					<br />
					<u><strong>Questions Per Category</strong></u><br />
					<input type="text" name="q_per_c" id="q_per_c" class="validate[required,max[50]]" /> (max 50)<br />
					<br />
					<u><strong>Date Range</strong></u><br />
					<select name="date_range" id="date_range" class="validate[required]">
						<option value="" selected="selected"></option>
						<option value="all">Anytime</option>
						<option value="7">Within the last 7 days</option>
						<option value="7-30">Within the last 7-30 days</option>
						<option value="30-60">Within the last 30-60 days</option>
						<option value="60-90">Within the last 60-90 days</option>
						<option value="more90">More than 90 days ago</option>
					</select><br />
					<br />
					<u><strong>Search Strings</strong></u><br />
					<br />
					<textarea name="search_string" id="search_string" class="validate[required]">why</textarea><br />
					(use spaces seperated strings e.g.: "why dinosaurs 'kung fu' Alaska")
					<br />			
					<br />
					<input type="hidden" name="review" value="true" />
					<input type="submit" value="Submit" />
				</form>
			</div>
			<?
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
layout_footer();

?>