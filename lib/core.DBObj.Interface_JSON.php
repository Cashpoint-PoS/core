<?
class DBObj_Interface_JSON {	
  //list view. if $q is an array with (filtered) objs, use it instead of querying
  public static function listView($class,$q=null) {
    $mod=$class::$mod;
    $sub=$class::$sub;
    $ret=array();

    if($q===null)
      $q=$class::getAll();
    $total=sizeof($q);
    $discarded=0;
    $current=-1;
    $start=0;
    $length=-1;
    if(isset($_GET["rangeStart"]))
    	$start=(int)$_GET["rangeStart"];
    if(isset($_GET["rangeLength"]))
    	$length=(int)$_GET["rangeLength"];
    $trimExport=false; //only export ids for o2m/link relationships
    if(isset($_GET["trimExport"]))
    	$trimExport=true;
    $plainExport=false; //omit relationships entirely (faster search)
    if(isset($_GET["plainExport"]))
    	$plainExport=true;
    if($total>0) {
      foreach($q as $obj) {
        //discard entries which we are not allowed to read
        if(!acl_check("$mod/$sub",$obj->id,"r")) {
          $discarded++;
          continue;
        }
        $current++;
        if($current<$start)
        	continue;
        if($length>0 && $current>=$start+$length)
        	break;
        $row=array();
        if(!$plainExport) {
          $row["_links"]=array();
          foreach($class::$links as $b=>$data) {
            $row["_links"][$b]=array();
            $g=$obj->getLinkedObjects($b,$data["table"]);
            foreach($g as $r) {
              if($trimExport)
                $row["_links"][$b][]=$r->obj->id;
              else
                $row["_links"][$b][]=$r->obj;
            }
          }
          $row["_o2m"]=array();
          foreach($class::$one2many as $b=>$data) {
            $row["_o2m"][$b]=array("title"=>$data["title"],"elements"=>array());
            $g=$b::getByOwner($obj);
            foreach($g as $r) {
              if($trimExport) {
                $row["_o2m"][$b]["elements"][]=$r->id;
              } else {
                $row2=array();
                $class2=get_class($r);
                $row2["_class"]=$class2;
                $row2["_raw"]=$r;
                $row2["_all"]=$class2::$elements;
                $row2["_elements"]=array();
                foreach($class2::$list_elements as $e)
                  $row2["_elements"][$e]=$r->getProperty($e,false);
                $row["_o2m"][$b]["elements"][]=$row2;
              }
            }
          }
        }
        $row["_class"]=$class;
        $row["_raw"]=$obj;
        $row["_all"]=$class::$elements;
        $row["_elements"]=array();
        $row["_keys"]=array(
        	"link"=>$class::$link_elements,
        	"list"=>$class::$list_elements,
        	"detail"=>$class::$detail_elements,
        	"edit"=>$class::$edit_elements,
        );
        foreach($class::$list_elements as $e)
          $row["_elements"][$e]=$obj->getProperty($e,false);
        $ret[]=$row;
      }
    }
    $range=array();
    $range["start"]=$start;
    $range["length"]=$length;
    $range["total"]=$total;
    $GLOBALS["ret"]["data"]=$ret;
    $GLOBALS["ret"]["range"]=$range;
  }
  public static function detailView(DBObj $obj) {
    $class=get_class($obj);
    $mod=$class::$mod;
    $sub=$class::$sub;
    $ret["_class"]=$class;
    $ret["_raw"]=$obj;
    $ret["_all"]=$class::$elements;
    $ret["_links"]=array();
    foreach($class::$links as $b=>$data) {
      $ret["_links"][$b]=array();
      $g=$obj->getLinkedObjects($b,$data["table"]);
      foreach($g as $r) {
        $ret["_links"][$b][]=$r->obj;
      }
    }
    $ret["_o2m"]=array();
    foreach($class::$one2many as $b=>$data) {
      $ret["_o2m"][$b]=array("title"=>$data["title"],"elements"=>array());
      $g=$b::getByOwner($obj);
      foreach($g as $r) {
        $row2=array();
        $class2=get_class($r);
        $row2["_class"]=$class2;
        $row2["_raw"]=$r;
        $row2["_all"]=$class2::$elements;
        $row2["_elements"]=array();
        foreach($class2::$list_elements as $e)
          $row2["_elements"][$e]=$r->getProperty($e,false);
        $ret["_o2m"][$b]["elements"][]=$row2;
      }
    }
    $ret["_elements"]=array();
    foreach($class::$detail_elements as $e)
      $ret["_elements"][$e]=$obj->getProperty($e,false);
    $GLOBALS["ret"]["data"]=$ret;
  }
  public static function editView(DBObj $obj) {
    $class=get_class($obj);
    $mod=$class::$mod;
    $sub=$class::$sub;
    $ret["_class"]=$class;
    $ret["_raw"]=$obj;
    $ret["_all"]=$class::$elements;
    $ret["_elements"]=array();
    foreach($class::$edit_elements as $e) {
      $ret["_elements"][]=$e;
    }
    $GLOBALS["ret"]["data"]=$ret;
  }
  public static function submitView(DBObj $obj,$have_warn,$warn) {
    if($warn)
      throw new Exception("unsatisfied validation".print_r($warn,true));
    static::detailView($obj);
  }
  public static function deleteView(DBObj $obj) {
    
  }
  public static function logView($log) {
  	$GLOBALS["ret"]["data"]=$log;
  }
}
