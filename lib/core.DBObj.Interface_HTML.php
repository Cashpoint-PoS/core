<?
class DBObj_Interface_HTML {	
  public static function listView($class,$q=null) {
    $mod=$class::$mod;
    $sub=$class::$sub;
?>
  <div class="widget widget-table bms-view-<?=$class?>-list">
  <div class="widget-content">
    <table class="table table-striped table-bordered data-table">
      <thead><tr>
        <th>ID</th>
<?
    foreach($class::$list_elements as $e) {
?>
        <th><?=esc($class::$elements[$e]["title"])?></th>
<?
    }
?>
        <th>Erstellt von</th>
        <th>Erstellt</th>
        <th>Letzter Bearbeiter</th>
        <th>Letzte Bearbeitung</th>
        <th>Aktion</th>
      </tr></thead>
      <tbody>
<?
    if($q===null)
      $q=$class::getAll();
    $q=$class::getAll();
    $total=sizeof($q);
    $discarded=0;
    if($total>0) {
      foreach($q as $obj) {
        //discard entries which we are not allowed to read
        if(!acl_check("$mod/$sub",$obj->id,"r")) {
          $discarded++;
          continue;
        }
?>
        <tr class="gradeA">
          <td><?= $obj->id ?></td>
<?
        foreach($class::$list_elements as $e) {
?>
          <td><?= str_replace("\n",", ",$obj->getProperty($e)); ?></td>
<?
        }
?>
          <td><?= esc($obj->creator["name"]) ?></td>
          <td><?= esc($obj->create_time) ?></td>
          <td><?= esc($obj->last_editor["name"]) ?></td>
          <td><?= esc($obj->modify_time) ?></td>
          <td>
            <a title="Details" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=view&amp;id=<?= $obj->id ?>"><span class="icon-eye"></span></a>
            <a title="Bearbeiten" class="<?= (!acl_check("$mod/$sub",$obj->id,"w"))? "disabled":"";?>" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=edit&amp;id=<?= $obj->id ?>"><span class="icon-pen"></span></a>
            <a title="Löschen" class="<?= (!acl_check("$mod/$sub",$obj->id,"d"))? "disabled":"";?>" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=delete&amp;id=<?= $obj->id ?>"><span class="icon-trash-stroke"></span></a>
          </td>
        </tr>
<?
    }
  }
?>
        </tbody>
    </table>
    <? if($discarded>0) { ?>
    <div class="details">
      <?=$total?> Einträge insgesamt, davon <?=$discarded?> wegen ACL-Beschränkungen nicht angezeigt.
    </div>
    <? } ?>
  </div> <!-- widget-content -->
  </div> <!-- widget -->
  <div class="box plain">
    <a class="btn btn-primary <?= (!acl_check("$mod/$sub",0,"c"))? "disabled":"";?>" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=edit&amp;id=0"><span class="icon-document-alt-stroke"></span>Neuen Datensatz erstellen</a>
  </div> <!-- box -->
<?
  }
  
  //selector view
  //url must end with id= and expect a POST
  //selector is an array of objects of type $class
  public static function selectView($class,$selector,$multiple=false,$action_url="be_index.php?mod=INVALID&amp;sub=INVALID&amp;id=") {
    $mod=$class::$mod;
    $sub=$class::$sub;
?>
  <div class="widget widget-table bms-view-<?=$class?>-list">
  <div class="widget-content">
     <form action="<?=$action_url?>" method="post">
    <table class="table table-striped table-bordered data-table">
      <thead><tr>
        <th>ID</th>
<?
    foreach($class::$list_elements as $e) {
?>
        <th><?=esc($class::$elements[$e]["title"])?></th>
<?
    }
?>
        <th>Erstellt von</th>
        <th>Erstellt</th>
        <th>Letzter Bearbeiter</th>
        <th>Letzte Bearbeitung</th>
        <th>Aktion</th>
      </tr></thead>
      <tbody>
<?
    $q=$selector;
    $total=sizeof($q);
    $discarded=0;
    if($total>0) {
      foreach($q as $obj) {
        //discard entries which we are not allowed to read
        if(!acl_check("$mod/$sub",$obj->id,"r")) {
          $discarded++;
          continue;
        }
?>
        <tr class="gradeA">
          <td><?= $obj->id ?></td>
<?
        foreach($class::$list_elements as $e) {
?>
          <td><?= str_replace("\n",", ",$obj->getProperty($e)); ?></td>
<?
        }
?>
          <td><?= esc($obj->creator["name"]) ?></td>
          <td><?= esc($obj->create_time) ?></td>
          <td><?= esc($obj->last_editor["name"]) ?></td>
          <td><?= esc($obj->modify_time) ?></td>
          <td>
<?
        if($multiple) {
?>
              <input type="checkbox" name="id[]" value="<?= $obj->id ?>" /><span class="icon-check"></span>
<?
        } else {
?>
               <button class="btn btn-primary" name="id" value="<?= $obj->id ?>"><span class="icon-check"></span></button>
<?
        }
?>
          </td>
        </tr>
<?
    }
  }
?>
      </tbody>
    </table>
<?
    if($multiple) {
?>
    <button class="btn btn-primary"><span class="icon-check"></span>Absenden</button>
<?
    }
?>
    </form>
    <? if($discarded>0) { ?>
    <div class="details">
      <?=$total?> Einträge insgesamt, davon <?=$discarded?> wegen ACL-Beschränkungen nicht angezeigt.
    </div>
    <? } ?>
  </div> <!-- widget-content -->
  </div> <!-- widget -->
<?
  }
  public static function detailView(DBObj $obj) {
    $class=get_class($obj);
    $mod=$class::$mod;
    $sub=$class::$sub;
?>
    <div class="widget widget-tabs bms-view-<?=$class?>-detail-<?= $obj->id ?>">
      <div class="widget-header">
        <h3>Zeige Objekt <?=$class?>/<?= $obj->id ?></h3>
        <ul class="tabs right">
          <li class="active"><a href="#tab-<?=$class?>-<?= $obj->id ?>-details">Objekt</a></li>
<?
    foreach($class::$links as $b=>$data) {
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-link-<?=$b?>"><?=$data["title"]?></a></li>
<?
    }
    foreach($class::$one2many as $b=>$data) {
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-o2m-<?=$b?>"><?=$data["title"]?></a></li>
<?
    }
    foreach($class::$detail_views as $title=>$function) {
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-view-<?=$function?>"><?=$title?></a></li>
<?
    }
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-acls">ACLs</a></li>
        </ul>
      </div> <!-- widget-header -->
      <div id="tab-<?=$class?>-<?= $obj->id ?>-details" class="widget-content">
        <h4>Details</h4>
        <table class="table table-bordered table-striped">
          <tbody>
            <tr><th>ID</th><td><?= $obj->id ?></td></tr>
<?
    foreach($class::$detail_elements as $e) {
?>
            <tr>
              <th><?=esc($class::$elements[$e]["title"])?></th>
              <td><?=str_replace("\n","<br />",$obj->getProperty($e))?></td>
            </tr>
<?
    }
?>
            <tr><th>Erstellt von</th><td><?= esc($obj->creator["name"]) ?></td></tr>
            <tr><th>Erstellt</th><td><?= esc($obj->create_time) ?></td></tr>
            <tr><th>Letzter Bearbeiter</th><td><?= esc($obj->last_editor["name"]) ?></td></tr>
            <tr><th>Letzte Bearbeitung</th><td><?= esc($obj->modify_time) ?></td></tr>
          </tbody>
        </table>
        <a class="btn btn-primary <?= (!acl_check("$mod/$sub",$obj->id,"w"))? "disabled":"";?>" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=edit&amp;id=<?= $obj->id ?>"><span class="icon-pen"></span>Bearbeiten</a>
      </div> <!-- tab -->
<?
      foreach($class::$links as $b=>$data) {
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-link-<?=$b?>" class="widget-content">
        <h4><?=$data["title"]?></h4>
<?
        static::relationshipView($obj, $b, $data["table"]);
?>
      </div> <!-- tab -->
<?
      }
      foreach($class::$one2many as $b=>$data) {
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-o2m-<?=$b?>" class="widget-content">
        <h4><?=$data["title"]?></h4>
<?
        static::o2mView($obj, $b);
?>
      </div> <!-- tab -->
<?
      }
      foreach($class::$detail_views as $title=>$function) {
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-view-<?=$function?>" class="widget-content">
        <h4><?=$title?></h4>
<?
        $obj->$function("html");
?>
      </div> <!-- tab -->
<?
      }
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-acls" class="widget-content">
        <h4>ACLs für Objekt <?=$mod?>/<?=$sub?>/<?= $obj->id ?></h4>
<?
    static::aclView($obj);
?>
      </div> <!-- tab -->
    </div> <!-- widget -->
    <div class="box plain">
      <a class="btn btn-primary" onclick="return history.back();"><span class="icon-arrow-left"></span>Zurück</a>
      
    </div> <!-- box -->
<?
  }
  
  //display the objects of class $b related to object $a in a tab
  public static function relationshipView(DBObj $a, $b, $table) {
?>
  <div class="box plain bms-view-<?=get_class($a)?>-link-<?=$b?>">
    <table class="table table-bordered table-striped">
      <thead><tr>
        <th>Link-ID</th>
        <th>Objekt-ID</th>
<?
    foreach($b::$link_elements as $e) {
      $ed=$b::$elements[$e];
?>
        <th><?=$ed["title"]?></th>
<?
    }
?>
        <th>Verknüpft von</th>
        <th>Verknüpft</th>
      </tr></thead>
      <tbody>
<?
    $g=$a->getLinkedObjects($b,$table);
    foreach($g as $r) {
?>
        <tr>
          <td><?= $r->id ?></td>
          <td><?= $r->obj->id ?></td>
<?
      foreach($b::$link_elements as $e) {
?>
          <td><?=str_replace("\n",", ",$r->obj->getProperty($e))?></td>
<?
      }
?>
          <td><?= esc($r->creator["name"]) ?></td>
          <td><?= esc($r->create_time) ?></td>
        </tr>
<?
     }
?>
      </tbody>
    </table>
  </div> <!-- box -->
<?
  }

  //display the elements of type $b owned by $a
  public static function o2mView(DBObj $a, $b) {
?>
  <div class="box plain bms-view-<?=get_class($a)?>-o2m-<?=$b?>">
    <table class="table table-bordered table-striped">
      <thead><tr>
        <th>Objekt-ID</th>
<?
    foreach($b::$link_elements as $e) {
      $ed=$b::$elements[$e];
?>
        <th><?=$ed["title"]?></th>
<?
    }
?>
        <th>Erstellt von</th>
        <th>Erstellt</th>
        <th>Letzter Bearbeiter</th>
        <th>Letzte Bearbeitung</th>
      </tr></thead>
      <tbody>
<?
    $g=$b::getByOwner($a);;
    foreach($g as $r) {
?>
        <tr>
          <td><?= $r->id ?></td>
<?
      foreach($b::$link_elements as $e) {
?>
          <td><?=str_replace("\n",", ",$r->getProperty($e))?></td>
<?
      }
?>
          <td><?= esc($r->creator["name"]) ?></td>
          <td><?= esc($r->create_time) ?></td>
          <td><?= esc($r->last_editor["name"]) ?></td>
          <td><?= esc($r->modify_time) ?></td>
        </tr>
<?
     }
?>
      </tbody>
    </table>
  </div> <!-- box -->
<?
  }
  
  
  //display the ACLs applying to object $obj in a tab
  public static function aclView(DBObj $obj) {
    $list=ACL::getAll();
    $class=get_class($obj);
    $obj_type=$class::$mod."/".$class::$sub;
?>
    <div class="box plain">
      <table class="table table-striped table-bordered">
        <thead><tr>
          <th>ID</th>
          <th>Gültig für Objekt(e)</th>
          <th>Betrifft Benutzer</th>
          <th>Rechte</th>
          <th>Erstellt von</th>
          <th>Erstellt</th>
          <th>Letzter Bearbeiter</th>
          <th>Letzte Bearbeitung</th>
        </tr></thead>
        <tbody>
  <?
    foreach($list as $acl) {
      //skip ACLs with other object type except the "all objects" one
      if($acl->object_type!==$obj_type && $acl->object_type!=="")
        continue;
      //skip ACLs with same object type, but different targets (except 0, which is for all objects)
      if($acl->object_type===$obj_type && ($acl->object_id!==0 && $acl->object_id!=$obj->id))
        continue;
      
      $subject="";
      if($acl->object_type==="")
        $subject="Alle Objekte";
      else {
        if($acl->object_id===0)
          $subject="Alle dieses Typs";
        else
          $subject="Nur dieses Objekt";
      }
      
      $target="";
      if($acl->target_type==="")
        $target="Alle Benutzer und Gruppen";
      elseif($acl->target_type==="user")
        $target="Benutzer ".$acl->target_id." (".(User::getById($acl->target_id,false)->displayname).")";
      elseif($acl->target_type==="group")
        $target="Alle Benutzer der Gruppe ".$acl->target_id." (".(Group::getById($acl->target_id,false)->name).")";
      
      $fields=str_split($acl->acl);
      $aclstr="";
      foreach($fields as $field) {
        switch($field) {
          case "r": $aclstr.="<span class=\"icon-eye\" title=\"Zeige das Objekt an\"></span>"; break;
          case "w": $aclstr.="<span class=\"icon-pen\" title=\"Bearbeite das Objekt\"></span>"; break;
          case "c": $aclstr.="<span class=\"icon-document-alt-stroke\" title=\"Erstelle ein neues Objekt dieses Typs\"></span>"; break;
          case "d": $aclstr.="<span class=\"icon-trash-stroke\" title=\"Lösche das Objekt\"></span>"; break;
          case "": break;
          default: $aclstr.="<span style=\"color:red\" title=\"Unbekanntes Flag\">$field</span>"; break;
        }
      }
  ?>
          <tr>
            <td><?= $acl->id ?></td>
            <td><?= $subject ?></td>
            <td><?= $target ?></td>
            <td><?= ($acl->negate===1)? "Entferne: " : "" ?><?= $aclstr ?></td>
            <td><?= esc($acl->creator["name"]) ?></td>
            <td><?= esc($acl->create_time) ?></td>
            <td><?= esc($acl->last_editor["name"]) ?></td>
            <td><?= esc($acl->modify_time) ?></td>
          </tr>
  <?
    }
  ?>
        </tbody>
      </table>
    </div> <!-- box -->
  <?  
  }
  public static function editView(DBObj $obj,$invalidFields=array(),$target="") {
    $class=get_class($obj);
    $mod=$class::$mod;
    $sub=$class::$sub;
    if($target=="")
      $target="be_index.php?mod=$mod&amp;sub=$sub&amp;action=submit";
?>
    <form action="<?=$target?>" id="bms-form-<?=$class?>-<?= $obj->id ?>" method="post">
    <button style="position:absolute;left:-100%" class="btn btn-primary"><span class="icon-check"></span>Speichern</button>
    <input type="hidden" name="bms-<?=$class?>-ids[]" value="<?= $obj->id ?>" />
    <input type="hidden" name="bms-objects[]" value="<?=$class?>" />
    <div class="widget widget-tabs bms-view-<?=$class?>-edit-<?= $obj->id ?>">
      <div class="widget-header">
        <h3>Editiere Objekt <?=$class?>/<?= $obj->id ?></h3>
        <ul class="tabs right">
          <li class="active"><a href="#tab-<?=$class?>-<?= $obj->id ?>-details">Objekt</a></li>
<?
    foreach($class::$links as $b=>$data) {
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-link-<?=$b?>"><?=$data["title"]?></a></li>
<?
    }
    foreach($class::$detail_views as $title=>$function) {
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-view-<?=$function?>"><?=$title?></a></li>
<?
    }
?>
          <li><a href="#tab-<?=$class?>-<?= $obj->id ?>-acls">ACLs</a></li>
        </ul>
      </div> <!-- widget-header -->
      <div id="tab-<?=$class?>-<?= $obj->id ?>-details" class="widget-content">
        <h4>Details</h4>
        <table class="table table-bordered table-striped">
          <tbody>
            <tr><th>ID</th><td><?= $obj->id ?></td></tr>
<?
    foreach($class::$edit_elements as $e) {
      $ed=$class::$elements[$e];
      $inputClass=(in_array($e,$invalidFields))? "ui-state-error":"";
?>
            <tr>
              <th><?=esc($ed["title"])?></th>
              <td>
<?
      switch($ed["mode"]) {
        case "select":
          $k=$ed["dbkey"];
          $sel=$obj->$k;
?>
                <select name="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" id="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" class="<?=$inputClass?>">
<?
          foreach($ed["data"] as $k=>$v) {
?>
                  <option value="<?=esc($k)?>" <?= ($k==$sel)? 'selected="selected"' : '' ?>><?=$v?></option>
<?
          }
?>
                </select>
<?
        break;
        case "one2many":
          $k=$ed["dbkey"];
          $v=$obj->$k;
          $t="number";
?>
            <button class="btn btn-primary" name="bms-choose" value="<?=$ed["dbkey"]?>"><span class="icon-box"></span>Objekt suchen</button> (ausgewählt: <input type="<?=$t?>" name="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" id="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" value="<?=esc($v)?>" class="<?=$inputClass?>" />)
<?
        break;
        case "string":
          $k=$ed["dbkey"];
          $v=$obj->$k;
          $t=isset($ed["data"])? $ed["data"] : "text";
?>
                <input type="<?=$t?>" name="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" id="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" value="<?=esc($v)?>" class="<?=$inputClass?>" />
<?
        break;
        case "text":
          $k=$ed["dbkey"];
          $v=$obj->$k;
?>
                <textarea name="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" id="bms-<?=$class?>[<?= $obj->id ?>][<?=$e?>]" class="<?=$inputClass?>"><?=esc($v);?></textarea>
<?
        break;
        default:
          be_error(500,"be_index.php?mod=index","Unbekannter Typ ".$ed["mode"]." für Key ".$ed["title"]." auf Objekt $class angefragt");
      }
?>
              </td>
            </tr>
<?
    }
?>
            <tr><th>Erstellt von</th><td><?= esc($obj->creator["name"]) ?></td></tr>
            <tr><th>Erstellt</th><td><?= esc($obj->create_time) ?></td></tr>
            <tr><th>Letzter Bearbeiter</th><td><?= esc($obj->last_editor["name"]) ?></td></tr>
            <tr><th>Letzte Bearbeitung</th><td><?= esc($obj->modify_time) ?></td></tr>
          </tbody>
        </table>
      </div> <!-- tab -->
<?
      foreach($class::$links as $b=>$data) {
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-link-<?=$b?>" class="widget-content">
        <h4><?=$data["title"]?></h4>
<?
        static::relationshipView($obj, $b, $data["table"]);
?>
      </div> <!-- tab -->
<?
      }
      foreach($class::$detail_views as $title=>$function) {
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-view-<?=$function?>" class="widget-content">
        <h4><?=$title?></h4>
<?
        $obj->$function("html");
?>
      </div> <!-- tab -->
<?
      }
?>
      <div id="tab-<?=$class?>-<?= $obj->id ?>-acls" class="widget-content">
        <h4>ACLs für Objekt <?=$mod?>/<?=$sub?>/<?= $obj->id ?></h4>
<?
    static::aclView($obj);
?>
      </div> <!-- tab -->
    </div> <!-- widget -->
    <div class="box plain">
      <a class="btn btn-primary" onclick="return history.back();"><span class="icon-arrow-left"></span>Zurück</a>
      <a class="btn btn-primary <?= (!acl_check("$mod/$sub",$obj->id,"r"))? "disabled":"";?>" href="be_index.php?mod=<?=$mod?>&amp;sub=<?=$sub?>&amp;action=view&amp;id=<?= $obj->id ?>"><span class="icon-eye"></span>Details</a>
      <button class="btn btn-primary"><span class="icon-check"></span>Speichern</button>
      <a class="btn btn-primary" onclick="return false;"><span class="icon-undo"></span>Zurücksetzen</a>
    </div> <!-- box -->
    </form>
<?
  }
}
