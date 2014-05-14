<?
//JSON prettifier
function pretty_json($json) {
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;
    for ($i=0; $i<=$strLen; $i++) {
        // Grab the next character in the string.
        $char = substr($json, $i, 1);
        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        // Add the character to the result string.
        $result .= $char;
        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }
    return $result;
}
$ts_start=microtime(true);
ini_set("zlib.output_compression","on");
header("Content-Type:application/json; charset=utf-8");

require("lib.php");
$ts_apistart=microtime(true);
$ret=array();
$log="";
try {
  if(!isset($_GET["action"]) || $_GET["action"]=="")
    throw new APIMissingParameterException("Keine Aktion angegeben");
  $action=$_GET["action"];
  
  switch($action) {
    case "getsessiondata": //not a bms action
      $ret["data"]=$_SESSION["user"];
    break;
    default: //bms actions
      //this is fixed!
      $outformat="json";
      $informat="get_json";
      $sub=(isset($_GET["sub"])) ? $_GET["sub"] : "";
      $mod=$_GET["mod"];
      $id=(isset($_REQUEST["id"])) ? (int)$_REQUEST["id"] : 0;
      if(!acl_check($mod,0,"r") || ($sub!=="" && !acl_check($mod."/".$sub,0,"r")))
        throw new PermissionDeniedException();
      if(!isset($be_handlers[$mod]))
        throw new APIUnknownModuleException();
      if(!isset($be_handlers[$mod][$sub]))
        throw new APIUnknownSubException();
      if(!isset($be_handlers[$mod][$sub][$action]))
        throw new APIUnknownHandlerException();
      $log="";
      call_user_func($be_handlers[$mod][$sub][$action]);
  }
  $ret["status"]="ok";
  $ret["message"]=$log;
} catch(Exception $e) {
  $ret["status"]="error";
  $ret["message"]=$e->getMessage();
  $ret["type"]=get_class($e);
}
$ts_end=microtime(true);
$ret["rt"]=$ts_end-$ts_start;
$ret["api_rt"]=$ts_end-$ts_apistart;
if(isset($_GET["_mysqlprofile"])) {
  $ret["queries"]=DB::$queries;
  $ret["allqueries"]=DB::$allqueries;
}
echo pretty_json(json_encode($ret,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP));
