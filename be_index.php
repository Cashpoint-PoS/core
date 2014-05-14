<?
set_time_limit(10);
require("lib.php");

$outformat="html";
$informat="post"; //may, later on, also be xml, json etc

//check if we're logged in
check_login();

if(!isset($_GET["mod"]))
	redir("be_index.php?mod=index");

$sub=(isset($_GET["sub"])) ? $_GET["sub"] : "";
$mod=$_GET["mod"];
$id=(isset($_REQUEST["id"])) ? (int)$_REQUEST["id"] : 0;
$action=(isset($_GET["action"])) ? $_GET["action"] : "";
$outformat=(isset($_GET["outformat"])) ? $_GET["outformat"] : "html";
$informat=(isset($_GET["informat"])) ? $_GET["informat"] : "post";


//Check if the user is actually allowed to do this op
//This also protects from invalid values for $mod/$sub, as an invalid value
//will just cause a 403 error
if(!acl_check($mod,0,"r") || ($sub!=="" && !acl_check($mod."/".$sub,0,"r")))
  be_error(403,"be_index.php?mod=index","Diese Aktion ist nicht erlaubt","Startseite");

//Check if we actually have a handler for the op, ACL checking *MUST* be done in handler!
//print_r($be_handlers);exit;
if(!isset($be_handlers[$mod]))
  be_error(404,"be_index.php?mod=index","Modul unbekannt".print_r($be_handlers,true),"Startseite");
if(!isset($be_handlers[$mod][$sub]))
  be_error(404,"be_index.php?mod=index","Submodul unbekannt","Startseite");
if(!isset($be_handlers[$mod][$sub][$action]))
  be_error(404,"be_index.php?mod=index","Diese Aktion besitzt keinen zugehörigen Handler","Startseite");
try {
  be_start("Index",array("all.css"));
?>
  <div class="grid-24">
<?
  if(isset($_SESSION["notify"]) && isset($_GET["notifies"])) {
    $notifies=explode(",",$_GET["notifies"]);
    foreach($notifies as $notify) {
      if(!isset($_SESSION["notify"][$notify]))
        continue;
      $data=$_SESSION["notify"][$notify];
?>
    <div class="notify <?=$data["class"]?>">
      <a href="javascript:;" class="close">×</a>
      <h3><?=$data["title"]?></h3>
      <p><?=$data["message"]?></p>
    </div> <!-- notify -->
<?
    }
  }
  call_user_func($be_handlers[$mod][$sub][$action]);
?>
  </div> <!-- grid -->
<?
  be_stop();
} catch(Exception $e) {
  $detail=sprintf("<div class=\"ex-detail\">%s</div><div class=\"ex-trace\"><pre>%s</pre></div>",$e->getMessage(),$e->getTraceAsString());
  try {
    $l=DBLog::fromScratch();
    $l->subject="program";
    $l->type="exception";
    $l->data=serialize(array("message"=>$e->getMessage(),"trace"=>$e->getTrace(),"get"=>$_GET,"post"=>$_POST,"request"=>$_REQUEST,"server"=>$_SERVER));
    $l->commit();
  } catch(Exception $e) {
    $detail.="<div class=\"ex-add\">Zusätzlich schlug das Einfügen eines Eintrags in die Datenbank fehl.</div>";
    $detail.=sprintf("<div class=\"ex-detail\">%s</div><div class=\"ex-trace\"><pre>%s</pre></div>",$e->getMessage(),$e->getTraceAsString());
  }

  be_error(500,"be_index.php?mod=index","Bei dieser Aktion ist ein Fehler aufgetreten. $detail","Startseite");
}
