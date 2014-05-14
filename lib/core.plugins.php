<?

function plugins_list() {
  $ret=array();
  $q=new DB_Query("select name,displayname,directory,active from plugins order by active desc,menu_order asc");
  if($q->numRows<1)
    return $ret;
  while($r=$q->fetch())
    $ret[]=$r;
  return $ret;
}

function plugins_register_backend($plugin,$data) {
  global $sb_nav;
  $navdata=array();
  if(isset($data["icon"]))
    $navdata["icon"]=$data["icon"];
  else
    $navdata["icon"]="";
  $navdata["mod"]=$plugin["name"];
  $navdata["dname"]=$plugin["displayname"];
  if(isset($data["sub"]))
    $navdata["sub"]=$data["sub"];
  $sb_nav[]=$navdata;
}

function plugins_register_backend_handler($plugin,$sub,$action,$callback) {
  global $be_handlers;
  if(!isset($be_handlers[$plugin["name"]]) || !is_array($be_handlers[$plugin["name"]]))
    $be_handlers[$plugin["name"]]=array();
  if(!isset($be_handlers[$plugin["name"]][$sub]))
    $be_handlers[$plugin["name"]][$sub]=array();
  if(isset($be_handlers[$plugin["name"]][$sub][$action]))
    be_error(500,"be_index.php?mod=index","Plugin ".$plugin["name"]."/$sub/$action bereits registriert","Startseite");
  if(!is_callable($callback))
    be_error(500,"be_index.php?mod=index","Plugin ".$plugin["name"]."/$sub/$action besitzt keinen gültigen Handler","Startseite");
  $be_handlers[$plugin["name"]][$sub][$action]=$callback;
}

function plugins_load_backend() {
  global $config;
  $plugins=plugins_list();
  foreach($plugins as $plugin) {
    if($plugin["active"]===1)
      require($config["paths"]["root"]."/".$plugin["directory"]."backend.php");
  }
}

$sb_nav=array();
$be_handlers=array();

plugins_load_backend();
