<?

//thrown by getById() when there is no corresponding object found
//catch this exception if you expect this may happen
class DBObj_NotFoundException extends BMS_Exception {
}

//thrown by commit() when the MySQL statement changes 0 or more than 1 rows
class DBObj_NoChangeException extends BMS_Exception {
}

//thrown by validate() when some constraint fails
class DBObj_ValidateFailException extends BMS_Exception {
  public $fields=array();
  public function __construct($fields) {
    $this->fields=$fields;
    parent::__construct("Feldvalidation fehlgeschlagen");
  }
}

abstract class DBObj {
  protected static $__table="";
  public static $list_elements=array(); //these elements are the columns of the action=list view
  public static $detail_elements=array(); //these elements are the rows in the action=view view
  public static $one2many=array(); //one-to-x relationships (this is in the "dominant" object)
  public static $links=array(); //x-to-x relationships
  public static $detail_views=array();
  public static $edit_elements=array();
  public static $mod="INVALID";
  public static $sub="INVALID";
  protected $__invalidFields=array(); //used by validate(), each entry is one of the elements(!) keys
  
  protected function loadFrom($id,$recurse=true) {
    global $dbobj_usercache;
    $q=new DB_Query("select * from ".static::$__table." where id=?",$id);
    if($q->numRows!=1)
      throw new DBObj_NotFoundException("Konnte Objekt %d nicht finden",$id);
    $r=$q->fetch();

    if(isset($r["creator"]) && $recurse) {
      try {
        if(isset($dbobj_usercache[$r["creator"]])) {
          $u=$dbobj_usercache[$r["creator"]];
        } else {
          $u=User::getById($r["creator"],false);
          $dbobj_usercache[$r["creator"]]=$u;
        }
        $r["creator"]=array("id"=>$u->id,"name"=>$u->name);
      } catch(DBObj_NotFoundException $e) {
        $r["creator"]=array("id"=>0,"name"=>"Unbekannt");
      }
    }
    if(isset($r["last_editor"]) && $recurse) {
      try {
        if(isset($dbobj_usercache[$r["last_editor"]])) {
          $u=$dbobj_usercache[$r["last_editor"]];
        } else {
          $u=User::getById($r["last_editor"],false);
          $dbobj_usercache[$r["last_editor"]]=$u;
        }
        $u=User::getById($r["last_editor"],false);
        $r["last_editor"]=array("id"=>$u->id,"name"=>$u->name);
      } catch(DBObj_NotFoundException $e) {
        $r["last_editor"]=array("id"=>0,"name"=>"Unbekannt");
      }
    }
    if(isset($r["create_time"]))
      $r["create_time"]=date("d.m.Y H:i:s",$r["create_time"]);
    if(isset($r["modify_time"]))
      $r["modify_time"]=date("d.m.Y H:i:s",$r["modify_time"]);
    foreach($r as $k=>$v)
      $this->$k=$v;    
  }
  
  public static function getById($id,$recurse=true) {
    global $dbobj_objcache;
    $key=get_called_class()."-".$id."-".$recurse;
    if(isset($dbobj_objcache[$key]))
      return $dbobj_objcache[$key];
    $obj=new static();
    $obj->loadFrom($id,$recurse);
    $dbobj_objcache[$key]=$obj;
//    print_r($dbobj_objcache);
    return $obj;
  }
  public static function getAll() {
    $ret=array();
    $q=new DB_Query("select id from ".static::$__table);
    if($q->numRows<1)
      return $ret;
    while($r=$q->fetch())
      $ret[]=static::getById($r["id"]);
    return $ret;
  }
  
  //get all objects where a specific filter ("WHERE x=y") matches; supports prepared stmt
  //can also be used for pagination (LIMIT x,y) or sorting (ORDER BY)
  //$fetch: also fetch the objects (set to false if you just need a count with empty rows)
  public static function getByFilter($filter) {
    $ret=array();
    
    $queryargs=func_get_args();
    $queryargs[0]="select id from ".static::$__table." ".$queryargs[0];

    //ugly hack, call_user_func_array does not support ctors
    $ref=new ReflectionClass("DB_Query");
    $q=$ref->newInstanceArgs($queryargs);
    if($q->numRows<1)
      return $ret;
    while($r=$q->fetch()) {
      $ret[]=static::getById($r["id"]);
    }
    return $ret;
  }
  
  //fetches the objects with a specific one-to-many owner (like, CRM_Address::getByOwner("Customer",2) will
  //fetch all addresses which have customers_id = 2
  public static function getByOwner(DBObj $otherObj) {
    $other_col=$otherObj::$__table."_id";
    return static::getByFilter("where $other_col=?",$otherObj->id);
  }
  //get an array with a list of $otherObj objects which have a link-relationship
  public function getLinkedObjects($otherObj,$table) {
    $ret=array();
    $mytable=static::$__table;
    $othertable=$otherObj::$__table;
    
    $q=new DB_Query("select * from $table where {$mytable}_id=?",$this->id);
    if($q->numRows<1)
      return $ret;
    
    while($r=$q->fetch()) {
      if(isset($r["creator"])) {
        try {
          $u=User::getById($r["creator"],false);
          $r["creator"]=array("id"=>$u->id,"name"=>$u->name);
        } catch(DBObj_NotFoundException $e) {
          $r["creator"]=array("id"=>0,"name"=>"Unbekannt");
        }
      }
      if(isset($r["create_time"]))
        $r["create_time"]=date("d.m.Y H:i:s",$r["create_time"]);
      
      $r["obj"]=$otherObj::getById($r[$othertable."_id"]);
      
      $o=new stdClass();
      foreach($r as $k=>$v)
        $o->$k=$v;
      $ret[]=$o;
    }
    
    return $ret;
  }
  public static function fromScratch() {
    $q=new DB_Query("describe ".static::$__table);
    if($q->numRows<1)
      return;
    $o=new static();
    while($r=$q->fetch())
      $o->$r["Field"]=$r["Default"];
    $o->id=0;
    if(property_exists($o,"create_time"))
      $o->create_time=date("d.m.Y H:i:s");
    if(property_exists($o,"modify_time"))
      $o->modify_time=date("d.m.Y H:i:s");
    if(property_exists($o,"creator"))
      $o->creator=array("id"=>$_SESSION["user"]["id"],"name"=>$_SESSION["user"]["name"]);
    if(property_exists($o,"last_editor"))
      $o->last_editor=array("id"=>$_SESSION["user"]["id"],"name"=>$_SESSION["user"]["name"]);
    return $o;
  }
  public function delete() {
    //commit invalidates all caches
    global $dbobj_objcache;
    global $dbobj_usercache;
    $dbobj_objcache=array();
    $dbobj_usercache=array();
    
    if($this->id===0)
      throw new Exception("cannot delete uncommitted object");
    
    $q=new DB_Query("delete from ".static::$__table." where id=?",$this->id);
    if($q->affectedRows!=1)
      throw new Exception("delete failed");
  }
  public function commit() {
    //commit invalidates all caches
    global $dbobj_objcache;
    global $dbobj_usercache;
    $dbobj_objcache=array();
    $dbobj_usercache=array();
    
    $fields=array();
    $changes=array(); //this is used for the log data
    
    $q=new DB_Query("describe ".static::$__table);
    if($q->numRows<1)
      return;
    
    $queryargs=array();
    if($this->id===0) {
      $query="INSERT INTO ";
      $o=static::fromScratch();
    } else {
      $query="UPDATE ";
      $o=static::getById($this->id);
    }
    $query.=static::$__table." SET ";
    while($r=$q->fetch()) {
      //don't commit not-existing fields (these will be set to default)
      if(!property_exists($this,$r["Field"]))
        continue;
      //don't commit changes to the id field
      if($r["Field"]==="id")
        continue;
      //don't commit changes in the creator-field if the object already exists
      if($r["Field"]==="creator") {
        if($this->id!==0) //skip when object exists
          continue;
        $this->creator=$_SESSION["user"]["id"];
      } elseif($r["Field"]==="create_time") {
        if($this->id!==0)
          continue;
        $query.="create_time=UNIX_TIMESTAMP(NOW()),";
        continue;
      } elseif($r["Field"]==="last_editor") {
        $this->last_editor=$_SESSION["user"]["id"];
      } elseif($r["Field"]==="modify_time") {
        $query.="modify_time=UNIX_TIMESTAMP(NOW()),";
        continue;
      }
      
      //don't commit equal values
      if($o->$r["Field"]==$this->$r["Field"])
        continue;
      $query.="`".$r["Field"]."`=?,";
      $queryargs[]=$this->$r["Field"];
      
      //skip adding creator and last_editor to the diff, this messes up the logs
      if(!in_array($r["Field"],array("creator","last_editor")))
        $changes[$r["Field"]]=array("to"=>$this->$r["Field"],"from"=>$o->$r["Field"]);
    }
    //No change => we do not support touch()-like functionality
    if(sizeof($changes)==0)
      return;
    $query=substr($query,0,-1); //trim last comma
    if($this->id!==0) {
      $query.=" WHERE id=?";
      $queryargs[]=$this->id;
    }
    array_unshift($queryargs,$query);
    
    //ugly hack, call_user_func_array does not support ctors
    $ref=new ReflectionClass("DB_Query");
    $q=$ref->newInstanceArgs($queryargs);
    
    if($q->affectedRows!=1)
      throw new DBObj_NoChangeException();
    
    if($this->id===0) {
      $this->id=$q->insertId;
      $type="dbcreate";
    } else
      $type="dbchange";
    
    $this->loadFrom($this->id);
    
    if(class_exists("DBLog") && get_class($this)!=="DBLog") {
      $l=DBLog::fromScratch();
      $l->subject=get_class($this)."/".$this->id;
      $l->type=$type;
      $l->data=serialize($changes);
      $l->commit();
    }
  }
  
  public static function detailView($id=NULL) {
    global $outformat,$mod,$sub;
    if($id===NULL)
      global $id;
    
		if($id==0) {
			$obj=static::fromScratch();
		} else {
      try {
        $obj=static::getById($id);
      } catch(DBObj_NotFoundException $e) {
        be_error(404,"be_index.php?mod=index","Objekt $mod/$sub/$id nicht gefunden!","Startseite");
      }
    }

    if(!acl_check("$mod/$sub",$obj->id,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/".$obj->id." für diesen Benutzer nicht gesetzt!","Startseite");    

    switch($outformat) {
      case "html": DBObj_Interface_HTML::detailView($obj); break;
      case "json": DBObj_Interface_JSON::detailView($obj); break;
      default:
        be_error(500,"be_index.php?mod=index","Format $outformat unbekannt");
    }
  }
  
  public static function listView() {
    global $outformat,$mod,$sub;

    if(!acl_check("$mod/$sub",0,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/0 für diesen Benutzer nicht gesetzt!","Startseite");

    switch($outformat) {
      case "html": DBObj_Interface_HTML::listView(get_called_class()); break;
      case "json": DBObj_Interface_JSON::listView(get_called_class()); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  
  public static function linkView() {
    global $outformat,$mod,$sub,$id;

    if(!acl_check("$mod/$sub",0,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/0 für diesen Benutzer nicht gesetzt!","Startseite");
		
		if(!isset($_GET["target"]))
			be_error(500,"be_index.php?mod=index","Parameter target missing");
		
		$target=$_GET["target"];
		$obj=static::getById($id);
		if(!isset(static::$links[$target]))
			be_error(500,"be_index.php?mod=index","Parameter target unknown");
		
		$list=$obj->getLinkedObjects($target,static::$links[$target]["table"]);
		$objList=array();
		foreach($list as $linkObj)
			$objList[]=$linkObj->obj;
		
    switch($outformat) {
      case "json": DBObj_Interface_JSON::listView($target,$objList); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  public function addLink($targetObj) {
  	$targetClass=get_class($targetObj);
  	$linkdata=static::$links[$targetClass];
  	$q=new DB_Query("select * from ".$linkdata["table"]." where ".(static::$__table)."_id=? and ".($targetClass::$__table)."_id=?",$this->id,$targetObj->id);
  	if($q->numRows==1) {
  		return;
  	}
  	$q=new DB_Query("insert into ".$linkdata["table"]." set ".(static::$__table)."_id=?,".($targetClass::$__table)."_id=?",$this->id,$targetObj->id);
  	if($q->affectedRows!=1)
  		throw new Exception("addLink insert failed");
  }
  public function removeLink($targetObj) {
  	$targetClass=get_class($targetObj);
  	$linkdata=static::$links[$targetClass];
  	$q=new DB_Query("select * from ".$linkdata["table"]." where ".(static::$__table)."_id=? and ".($targetClass::$__table)."_id=?",$this->id,$targetObj->id);
  	if($q->numRows==0) {
  		return;
  	}
  	$q=new DB_Query("delete from ".$linkdata["table"]." where ".(static::$__table)."_id=? and ".($targetClass::$__table)."_id=?",$this->id,$targetObj->id);
  	if($q->affectedRows!=1)
  		throw new Exception("removeLink delete failed");
  }
  
	public static function processSpecialAction() {
	  global $outformat,$mod,$sub,$id;

    if(!acl_check("$mod/$sub",0,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/0 für diesen Benutzer nicht gesetzt!","Startseite");
		
		if(!isset($_GET["target"]))
			be_error(500,"be_index.php?mod=index","Parameter target missing");
		
		$target=$_GET["target"];
		$obj=static::getById($id);
		
		$ret=$obj->specialAction($target);
    switch($outformat) {
      case "json": DBObj_Interface_JSON::logView($ret); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
	}
  public static function processAddLink() {
    global $outformat,$mod,$sub,$id;

    if(!acl_check("$mod/$sub",0,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/0 für diesen Benutzer nicht gesetzt!","Startseite");
		
		if(!isset($_GET["target"]))
			be_error(500,"be_index.php?mod=index","Parameter target missing");
		if(!isset($_GET["targetId"]))
			be_error(500,"be_index.php?mod=index","Parameter targetId missing");
		
		$target=$_GET["target"];
		$targetId=$_GET["targetId"];
		$obj=static::getById($id);
		$targetObj=$target::getById($targetId);
		
		if(!isset(static::$links[$target]))
			be_error(500,"be_index.php?mod=index","Parameter target unknown");
		
		$obj->addLink($targetObj);
		
    switch($outformat) {
      case "json": DBObj_Interface_JSON::detailView($obj); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  public static function processRemoveLink() {
    global $outformat,$mod,$sub,$id;

    if(!acl_check("$mod/$sub",0,"r"))
      be_error(403,"be_index.php?mod=index","Flag r auf $mod/$sub/0 für diesen Benutzer nicht gesetzt!","Startseite");
		
		if(!isset($_GET["target"]))
			be_error(500,"be_index.php?mod=index","Parameter target missing");
		if(!isset($_GET["targetId"]))
			be_error(500,"be_index.php?mod=index","Parameter targetId missing");
		
		$target=$_GET["target"];
		$targetId=$_GET["targetId"];
		$obj=static::getById($id);
		$targetObj=$target::getById($targetId);
		
		if(!isset(static::$links[$target]))
			be_error(500,"be_index.php?mod=index","Parameter target unknown");
		
		$obj->removeLink($targetObj);
		
    switch($outformat) {
      case "json": DBObj_Interface_JSON::detailView($obj); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  
  //target is the http form action
  public static function editView($id=NULL, $target="") {
    global $outformat,$mod,$sub;

    if($id===NULL)
      global $id;
    
    $acl_flag=($id==0)? "cr" : "wr";
    
    if(!acl_check("$mod/$sub",$id,$acl_flag))
      be_error(403,"be_index.php?mod=index","Flag $acl_flag auf $mod/$sub/$id für diesen Benutzer nicht gesetzt!","Startseite");
    
    $fields=array();
    
    if(isset($_GET["storage"]) && isset($_SESSION["storage"]) && is_array($_SESSION["storage"]) && isset($_SESSION["storage"][$_GET["storage"]])) {
      $storage=$_SESSION["storage"][$_GET["storage"]][0]; //todo make this multiple-object-aware
      $storedObj=$storage["object"];
      $fields=$storage["fields"];
      if(get_called_class()!==get_class($storedObj))
        be_error(500,"be_index.php?mod=index","Storage-Klasse ist ".get_class($storedObj).", angefragt ist ".get_called_class());
      if($storedObj->id!==$id)
        be_error(500,"be_index.php?mod=index","Storage-Objekt-ID ".$storedObj->id." stimmt nicht mit angefragter ID $id überein");
      $obj=$storedObj;
    } else
      $obj=($id==0)? static::fromScratch() : static::getById($id);
    
    //create_time/creator will either already be set in the db or by fromScratch()
    if(property_exists($obj,"modify_time"))
      $obj->modify_time=date("d.m.Y H:i:s");
    if(property_exists($obj,"last_editor"))
      $obj->last_editor=array("id"=>$_SESSION["user"]["id"],"name"=>$_SESSION["user"]["name"]);

    switch($outformat) {
      case "html": DBObj_Interface_HTML::editView($obj,$fields,$target); break;
      case "json": DBObj_Interface_JSON::editView($obj); break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  
  //todo support passing multiple kinds of objects and find a valid usecase for this
  //the edit-views already pass properly formatted input variable names, so the only thing missing is support in here
  public static function processSubmit($doCommit=true) {
    global $outformat,$informat,$mod,$sub;
    $class=get_called_class();
    $invars=array();
    $inids=array();
    
    //extract the object data from the user-supplied input
    switch($informat) {
      case "post":
        if(!isset($_POST["bms-objects"]) || !is_array($_POST["bms-objects"]) ||
           !isset($_POST["bms-$class"]) || !is_array($_POST["bms-$class"]) ||
           !in_array($class,$_POST["bms-objects"])
        )
          be_error(500,"be_index.php?mod=index","Keine Objekte gegeben bzw. kein passendes Objekt übergeben");
        if(!isset($_POST["bms-$class-ids"]))
          be_error(500,"be_index.php?mod=index","Keine IDs übergeben");
        $inids=is_array($_POST["bms-$class-ids"])? $_POST["bms-$class-ids"] : array($_POST["bms-$class-ids"]);
        foreach($inids as $id) {
          $id=(int)$id;
          if(!isset($_POST["bms-$class"][$id]))
            be_error(500,"be_index.php?mod=index","Objekt $class/$id angekündigt, aber nicht übergeben");
          $invars[$id]=$_POST["bms-$class"][$id];
        }
      break;
      case "get_json":
        if(!isset($_GET["json_input"]))
          be_error(500,"be_index.php?mod=index","Keine Objekte gegeben bzw. kein passendes Objekt übergeben 1");
        $indata=json_decode($_GET["json_input"]);
        if($indata===false)
          be_error(500,"be_index.php?mod=index","Keine Objekte gegeben bzw. kein passendes Objekt übergeben 2");
        $inids=$indata->ids;
        foreach($inids as $id)
          $invars[$id]=(array)$indata->data->$id;
//        print_r($indata);
      break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
    
    $warn=array();
    $objects=array();
    
    //check all supplied objects for permission and validity
    foreach($inids as $id){
      $indata=$invars[$id];
      
      $acl_flag=($id==0)? "cr" : "wr";
    
      if(!acl_check("$mod/$sub",$id,$acl_flag))
        be_error(403,"be_index.php?mod=index","Flag $acl_flag auf $mod/$sub/$id für diesen Benutzer nicht gesetzt!","Startseite");
      
      $obj=($id==0)? $class::fromScratch() : $class::getById($id);
      
      foreach($class::$edit_elements as $e) {
        $ed=$class::$elements[$e];
        //print_r($ed);
        if(!isset($indata[$e]))
          continue;
        $v=$indata[$e];
        //validation
        switch($ed["mode"]) {
          case "string": //email, number etc validation
            if(!isset($ed["data"]))
              $ed["data"]="text";
            switch($ed["data"]) {
            }
          break;
          case "radio":
          case "select":
//            if(!in_array(
          break;
          case "text":
          case "one2many": //todo, o2m needs existence check
          break;
          default:
            be_error(500,"be_index.php?mod=index","Unbekannter Modus für Key $e");
        }
        $obj->$ed["dbkey"]=$v;
//        echo "Key $e Value $v\n";
      }
      
      //check if the final object is valid (e.g. constraints checks)
      $obj->validate();
      if(sizeof($obj->__invalidFields)>0)
        $warn[]=array("object"=>$obj,"fields"=>$obj->__invalidFields);
      $objects[]=$obj;
    }
    if(sizeof($warn)==0) {
//      $doCommit=false;
//      print_r($obj);return;
      if($doCommit) {
        foreach($objects as $obj)
          $obj->commit();
      }
      switch($outformat) {
        case "html":
          addNotify("Objekt gespeichert","Objekt $class/".$obj->id." erfolgreich gespeichert");
          //redirect to display if it went ok
          redir("be_index.php?mod=$mod&sub=$sub&action=view&id=".$obj->id."&".getNotifyParam());
        break;
        case "int":
          return array("result"=>true,"obj"=>$obj);
        case "json":
           DBObj_Interface_JSON::submitView($obj,true,array()); break;
        break;
        default:
          be_error(500,"be_index.php?mod=index","Format unbekannt");
      }
    } else {
      switch($outformat) {
        case "html":
          $key=addNotify("Fehler","Objekt $class/".$obj->id." konnte nicht gespeichert werden, bitte die markierten Felder prüfen.","notify-error");
          if(!isset($_SESSION["storage"]) || !is_array($_SESSION["storage"]))
            $_SESSION["storage"]=array();
          $_SESSION["storage"][$key]=$warn;
          redir("be_index.php?mod=$mod&sub=$sub&action=edit&id=".$obj->id."&".getNotifyParam()."&storage=$key");
        break;
        case "int":
          $key=addNotify("Fehler","Objekt $class/".$obj->id." konnte nicht gespeichert werden, bitte die markierten Felder prüfen.","notify-error");
          if(!isset($_SESSION["storage"]) || !is_array($_SESSION["storage"]))
            $_SESSION["storage"]=array();
          $_SESSION["storage"][$key]=$warn;
          return array("result"=>false,"key"=>$key);
        case "json":
          DBObj_Interface_JSON::submitView($obj,false,$warn); break;
        break;
        default:
          be_error(500,"be_index.php?mod=index","Format unbekannt");
      }
    }
  }

  public static function processDelete() {
    global $outformat,$informat,$mod,$sub;
    $class=get_called_class();
    
    //extract the object data from the user-supplied input
    switch($informat) {
      case "get_json":
        $id=(int)$_GET["id"];
        if($id===0)
          throw new Exception("id 0 invalid");
      break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
    if(!acl_check("$mod/$sub",$id,"d"))
      be_error(403,"be_index.php?mod=index","Flag d auf $mod/$sub/$id für diesen Benutzer nicht gesetzt!","Startseite");
      
    $obj=$class::getById($id);
    $obj->delete();
    switch($outformat) {
      case "html":
        addNotify("Objekt gelöscht","Objekt $class/".$obj->id." erfolgreich gelöscht");
        //redirect to display if it went ok
        redir("be_index.php?mod=$mod&sub=$sub&action=list&".getNotifyParam());
      break;
      case "int":
        return array("result"=>true);
      case "json":
         DBObj_Interface_JSON::deleteView($obj);
      break;
      default:
        be_error(500,"be_index.php?mod=index","Format unbekannt");
    }
  }
  
  //check if non-database constraints are met (double group memberships, whatever)
  //add the fields (keys of static::$elements!) that are wrong to $this->__invalidFields
  public function validate() {
    //override this in subclasses, if there's a need
    //but do not forget to call back here
  }
  
  //get a properly formatted property of an object
  public function getProperty($key,$escapeHTML=true) {
    //notice that we don't use any checking - php will throw an error, which will be converted to exception by us
    $elements=static::$elements;
    $element=$elements[$key];
    switch($element["mode"]) {
      case "select":
      case "radio":
        $vals=$element["data"];
        $vkey=$this->$element["dbkey"];
        if(!isset($vals[$vkey]))
          be_error(500,"be_index.php?mod=index","Unbekannter Wert für Key $vkey auf Property $key auf Objekt ".get_called_class()."/".$this->id);
        $val=$vals[$vkey];
        break;
      case "string":
      case "text":
        $val=$this->$element["dbkey"];
        break;
      case "process":
        $val=$this->processProperty($key);
        break;
      case "one2many":
        $dsid=$this->$element["dbkey"];
        if($dsid==0) { //0 = no "child" object set
          $val="(unbekannt)";
          break;
        } else
          $val=$element["data"]::getById($dsid)->toString();
      break;
      default:
        be_error(500,"be_index.php?mod=index","Unbekannter Modus ".$element["mode"]." für Key $key auf Objekt ".get_called_class()." angefragt");
    }
    if($escapeHTML)
      $val=esc($val);
    return $val;
  }
}
