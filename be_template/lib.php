<?
function be_error($code,$back,$str,$link_title="Zurück") {
  global $outformat;
  if($outformat=="json") {
    $e=new Exception();
    $trace=$e->getTraceAsString();
    $e=new Exception("be_error $code $str\n$trace");
    throw $e;
  }
  if(func_num_args()>4) { //str is a printf-string
    $args=func_get_args();
    array_shift($args); //remove code
    array_shift($args); //remove back
    $str=call_user_func_array("sprintf",$args);
  }
  ob_end_clean();
  $tpl_styles=array("errors.css");
  $tpl_title="Fehler";
  header(':', true, $code);
  require("header.php");
  echo "<body class=\"error-page\">";
  require("errors/$code.php");
  require("footer.php");
  flush();
  exit();
}

function be_start($tpl_title="Seite",$tpl_styles=array()) {
  ob_start();
  $GLOBALS["tpl_title"]=$tpl_title;
  $GLOBALS["tpl_styles"]=$tpl_styles;
}
function be_stop() {
  $tpl_title=$GLOBALS["tpl_title"];
  $tpl_styles=$GLOBALS["tpl_styles"];
  $content=ob_get_clean();
  require("header.php");
  //do not put body in header - it's needed with a specific class in error
  echo "<body>";
  require("wrapper_begin.php");
  require("sb_begin.php");
  require("nav.php");
  require("sb_end.php");
  echo $content;
  require("wrapper_end.php");
  require("footer.php");
}

function be_set_title($tpl_title) {
  $GLOBALS["tpl_title"]=$tpl_title;
}
