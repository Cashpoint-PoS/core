<?
//call getNotifyParam for a url-appendable list of the notifies generated in this request
$notifies=array();

//add a notify to the storage
function addNotify($title,$message,$class="notify-success") {
  global $notifies;
  $key=md5(rand().time().$title.$message); //basic unique key, no need for true collision-freeness here
  if(!isset($_SESSION["notify"]) || !is_array($_SESSION["notify"]))
    $_SESSION["notify"]=array();
  $_SESSION["notify"][$key]=array("title"=>$title,"message"=>$message,"class"=>$class);
  $notifies[]=$key;
  return $key;
}

function getNotifyParam() {	
  global $notifies;
  return "notifies=".implode(",",$notifies);
}
