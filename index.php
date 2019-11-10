<?php 
$method = $_SERVER['REQUEST_METHOD'];
date_default_timezone_set("Asia/Kolkata");

$servername = "localhost";
$username = "root";
$password = "";
$dataBaseName="kitscollege";
// Create connection
$conn = mysqli_connect($servername, $username, $password,$dataBaseName);

// Check connec
// tion
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connection successful<br/>";

if($method == 'POST'){
	$requestBody = file_get_contents('php://input');
	$json = json_decode($requestBody);

	$text = $json->result->metadata->intentName;

	switch ($text) {
		case 'Welcome':
			$speech="Hi , I am KITS college enquiry chatbot.";
		break;
		case 'About':
			$speech = "I am chatbot of kits collage.";
			break;

		case 'location':
			$speech = "Kakatiya Institute of Technology & Science (KITS),\nOpp: Yerragattu Hillock,  Bheemaram (V),\nHasanparthy (M), Warangal (Dist.),Telangana (State.) 506 015.\nmaps link : https://goo.gl/maps/iDLu2HKftNL2";
			break;

		case 'Holidays':
			$today=strtotime((new DateTime())->format("d-m-Y"));
			$count=0;
			$jsonf = file_get_contents('holidays.json');
			$json_data = json_decode($jsonf, true);
			$speech="up coming 5 general holidays are    ";
			foreach ($json_data as $value) {
				if ($today<=strtotime($value['ondate'])){
				if ($count<5) {
					$speech =$speech."******".$value['ondate']."       ".$value['occasion']."";
					$count++;
				}
			}
			}
			break;
		case 'isholiday':
			$today1=(new DateTime($json->result->parameters->date))->format("d-m-Y");
			$day=strtolower((new DateTime())->format("l"));
			if ($day=='sunday') {
				$reason="Sunday";
			}
			$today=strtotime($today1);
			$jsonf = file_get_contents('holidays.json');
			$json_data = json_decode($jsonf,true);
			$flag=0;
			if($reason!="Sunday")
			foreach ($json_data as $value) {
				if(strtotime($value['ondate'])==$today){
					$reason=$value['occasion'];
					$flag=1;
					break;
				}
			}
			$speech=($flag==0)?"No.".$today1." is not a general holiday":"Yes. ".$today1." is a holiday becouse it is ".$reason.".";
			break;
		case 'nextholiday':
			$today=strtotime((new DateTime())->format("d-m-Y"));
			$jsonf = file_get_contents('holidays.json');
			$json_data = json_decode($jsonf, true);
			foreach ($json_data as $value) {
				if ($today<strtotime($value['ondate'])){
					$speech ="You have next general holiday on ".$value['ondate']." becouse it is".$value['occasion'].".";
					break;
				}
			}
			break;
		case 'whenisH':
			$name=$json->result->parameters->name;
			$jsonf = file_get_contents('holidays.json');
			$json_data = json_decode($jsonf, true);
			$flag=0;
			$count=0;
			foreach ($json_data as $value) {
				$sim = similar_text($name,$value['occasion'], $perc);
				if(strtolower($name)==strtolower($value['occasion'])){
					$mid =$value['occasion']." general holiday is on ".$value['ondate'].".";
					$flag=1;$count=1;
					break;
				}
				if ($perc>60){
					$flag=1;$count++;
					$mid =$mid.$value['occasion']." general holiday is on ".$value['ondate'].".";

				}
			}
			$speech=($flag==0)?"I didnt get that try by date":($count==1)?$mid:"we have ".$count." response matching your requast they are :".$mid;
			
			break;
		case 'seats':
			$Dept=$json->result->parameters->Dept;
			$degree=$json->result->parameters->degree;
			if(!$degree){$degree="BTech";}
			$jsonf = file_get_contents('seats.json');
			$json_data = json_decode($jsonf,true);
			foreach ($json_data as $value) {
				if($value['Dept']==$Dept){
 					$speech= "we have ".(string)$value[$degree]." seats in ".$Dept." for ".$degree;
				}
			}
			if (!$speech) {
				$speech="that department or degree does not exist in over collage.";
			}
			break;
		case 'semsubject':
			$Dept=$json->result->parameters->Dept;
			$sem=$json->result->parameters->sem;
			$jsonf = file_get_contents('semdata.json');
			$json_data = json_decode($jsonf,true);
			$jsonf1 = file_get_contents('subjects.json');
			$json_data1 = json_decode($jsonf1,true);
			$subjects=$json_data[$Dept][$sem];
			$speech="you have these fallowing subjects in ".$sem;
			foreach ($subjects as $value) {
				$speech=$speech."____".$value."-----".$json_data1[$value]['name'].".";
			}
			if(!$speech){
				$speech="that department does not exist in over collage.";
			}
			break;
		case 'datetimetable':
			$day=strtolower((new DateTime($json->result->parameters->ddate))->format("l"));
			$classid=strtoupper($json->result->parameters->dclass);
			$jsonf = file_get_contents('timetable.json');
			$speech=$day.' Time Table' ;
			$json_data = json_decode($jsonf,true);
			foreach ( $json_data[$classid][$day]as $value) {
				$start=$value['start'];
				$left = substr($start, 0, (strlen($start))-2);
				$right = substr($start, (strlen($start))-2);
				$start=$left.":".$right;
				$end=$value['end'];
				$left = substr($end, 0, (strlen($end))-2);
				$right = substr($end, (strlen($end))-2);
				$end=$left.":".$right;
				$speech=$speech."________________________________________ ".$start."--".$end." ".$value['title'];
			}
			break;
		case 'nextwhat':
			$day=strtolower((new DateTime())->format("l"));
			$time=(new DateTime())->format("H:i");
			$jsonf = file_get_contents('timetable.json');
			$json_data = json_decode($jsonf,true);
			$time=(int)str_replace(":", "", $time);
			$classid=strtoupper($json->result->parameters->dclass);
			$value=$json_data[$classid][$day];
			for ($x = 0; $x <= 6; $x++) {
				$start=(int)$value[$x]['start'];
				if($start>=$time){
					$start1=(string)$start;
					$left = substr($start1, 0, (strlen($start1))-2);
					$right = substr($start1, (strlen($start1))-2);
					$start1=$left.":".$right;
					$speech= "the next class is ".$value[$x]['title'].". it is going to start by ".$start1." O'clock.";
					break;
				}
			}
			if ($time>=1560) {
				$speech= "there is no next class today seeyou tomorrow ";
			}
			break;

		case 'Teacherinfo':
			$name=strtolower($json->result->parameters->name);
			$jsonf = file_get_contents('faculty.json');
			$json_data = json_decode($jsonf);
			$count=0;
			$mid="";
			foreach ($json_data as $value) {
				if($value->Smallname==$name||strtolower($value->Name)==$name){
					$mid=$mid.$value->Name."(".$value->Qualification.") is a ".$value->Designation." working on ".$value->Research.". Contact info: Email: ".$value->Email." Mobile no: ".$value->Mobile."  Phone no: ".$value->Phone. "  Room no: ".$value->Room;
					$count++;
				}
			}
			if($count>1){
				$speech="We have ".$count." with same name they are as fallowing.".$mid;
			}
			else
				$speech=$mid;
			break;
		case 'Hod':
			$dept=$json->result->parameters->Dept;
			$jsonf = file_get_contents('Hods.json');
			$json_data = json_decode($jsonf,true);
			foreach ($json_data as $value) {
				if($value["Dept"]==$dept){
					$speech="the Head of department of ".$dept." is ".$value['HOD']." .";
				}
			}
			if (!$speech) {
				$speech="that department does not exist in over collage.";
			}
			break;
		case 'examtimetable':
			$dept=$json->result->parameters->Dept;
			$type=$json->result->parameters->exam;
			$year=$json->result->parameters->year;
			if(!$type){$type="sem";}
			$jsonf = file_get_contents('examtt.json');
			$json_data = json_decode($jsonf,true);
			if($type=="mid1"){
				$speech="that exam is over we dont have that info.try to see about mid2 or semester .";
				break;
			}
			$array=array();
			$speech="Exam timetable of ".$dept." Dept ".$year." year";
			foreach ($json_data as $value)
				if($value["type"]==$type)
					if($value["year"]==$year)
						$array[$value["Date"]]=" __________________________________________________ ".$value["Date"]."   ".$value[$dept];
			$keys = array_keys($array);
			$arraySize = count($array);
			$flag=1;
			for($i=0;$i<$arraySize;$i++){
				for($j=0;$j<$arraySize;$j++){
					if($array[$keys[$j]]!="done"){
						if($flag==1){
							$small=$j;
							$flag=0;
							continue;
						}
						if(strtotime($keys[$small])>strtotime($keys[$j]))
							$small=$j;
					}
				}
				if($array[$keys[$small]]!="--"){
				$speech=$speech.$array[$keys[$small]];
				}
				$array[$keys[$small]]="done";
				$flag=1;
			}
			break;
		case 'placement':
			$year=$json->result->parameters->year;
			$company=$json->result->parameters->company;
			$dept=$json->result->parameters->Dept;
			if(!$year){$year="2017";}
			if(!$company){$company="Total";}
			if(!$dept){$dept="Total";}
			$jsonf = file_get_contents('placement.json');
			$json_data = json_decode($jsonf,true);
			
			$sql_query = "select * from placements";

		$result=mysqli_query($conn, $sql_query);
 
	
 	if(mysqli_num_rows($result)>0)
 	{
     		while ($row = mysqli_fetch_assoc($result)) 
     		{     
         
         		foreach($row as $k=>$value)
 	        	{
        	     		$speech.=$value." ";
         		}
 
     		}
 	}	
			echo $speech;

/*			foreach ($json_data as $value) {
				if($value["Year"]==$year)
					if($value["Company"]==$company){
						if($company=="Total"){
							if ($dept!="Total") {
								$speech=(string)$value[$dept]." students got placed from ".$dept." department in ".$value['Year'];
							}else{
								$speech=$value["Total"]." students got placed in ".$value['Year'];
							}
						}else{
							if($dept=="Total"){
								$speech=$value["Total"]." students got placed in ".$company." in ".$value['Year'];
							}else{
								$speech=$value[$dept]." students got placed from ".$dept." in ".$company." in ".$value['Year'];
							}
						}
					}
			}
*/
			if (!$speech) {
				$speech="that department does not exist in our college.";
			}
		break;
		case "tellmoreaboutfests":
			$fests=$json->result->parameters->fests;
			if($fests==1){
				$speech="Sanskriti is annual cultural fest it is condected on 12th and 13th April ";
			}else if($fests==2){
				$speech="Sumshodhini is a tech fest";
			}
			
		break;
		case "listeventsinfest":
			$fests=$json->result->parameters->fests;
			if($fests==1){
				$speech="1)CSE events 2)IT Events";
			} else if($fests==2){
				$speech="Sumshodhini event allready completed.";
			}
		break;
		default:
			$speech = "Sorry, I didnt get that. Please ask me something else.";
			break;
	}

	$response = new \stdClass();
	$response->speech = $speech;
	$response->displayText = $speech;
	$response->source = "webhook";
	//echo json_encode($response);
	echo $speech;
}
else
{
	echo "Method not allowed Connected successfully";
}

?>
