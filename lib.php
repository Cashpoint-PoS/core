<?
//library

//Config
require("config.php");

//DB
require("DB.php");
require("DB_Query.php");
//Backend theme functions
require("be_template/lib.php");

require("lib/core.errorhandling.php");
require("lib/core.DBObj.php");
require("lib/core.notify.php");
require("lib/core.DBObj.Interface_HTML.php");
require("lib/core.DBObj.Interface_JSON.php");
require("lib/core.acl.php");
//this loads and inits the plugins
require("lib/core.plugins.php");

//only after all the classes were defined!
//see http://www.macuser.de/forum/f57/problem-_session-__php_incomplete_class-431681/#post4921533
session_start();
/**
 * This function generates a password salt as a string of x (default = 15) characters
 * in the a-zA-Z0-9!@#$%&*? range.
 * @param $max integer The number of characters in the string
 * @return string
 * @author AfroSoft <info@afrosoft.tk>
 */
function generateSalt($max = 15) {
  $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $i = 0;
  $salt = "";
  while ($i < $max) {
    $salt .= $characterList{mt_rand(0, (strlen($characterList) - 1))};
    $i++;
  }
  return $salt;
}

//escale shorthand
function esc($s) {
  $flags=ENT_QUOTES;
  if(defined("ENT_HTML5"))
    $flags|=ENT_HTML5;
  return htmlspecialchars($s,$flags,"UTF-8");
}

//get node array
function getNodes($startId) {
  $q=new DB_Query("select * from nodes where id=?;",$startId);
  $r=array();
  $e=$q->fetch();
  $e["price"]=floatval($e["price"]);
  
  $q2=new DB_Query("select id from nodes where parent_id=? order by id asc",$startId);
  $opts=array();
  if($q2->numRows>0) {
    while($r2=$q2->fetch()) {
      $opts[]=getNodes($r2["id"]);
    }
  }
  if($e["price"]>0)
    $r["price"]=$e["price"];
  if(sizeof($opts)>0)
    $r["options"]=$opts;
  if($e["is_leaf"]==1)
    $r["desc"]=$e["desc"];
  else
    $r["desc"]=$e["sel_lbl"];
  if($e["is_leaf"]==1)
    $r["leaf"]=true;
  else
    $r["leaf"]=false;
  if($e["sel_str"]!="")
    $r["key"]=$e["sel_str"];
  $r["dbid"]=$startId;
  $r["rawdb"]=$e;
  return $r;
}


function redir($target,$code=303) {
  ob_end_clean();
  header("Location: $target",true,$code);
  exit;
}

//check if we are logged in, redirect to login page or return JSON error-object
function check_login($json=false) {
  if(isset($_SESSION["user"]))
    return;
  if($json) {
    exit;
  }
  be_error(403,"index.php","Anmeldung erforderlich","Zur Anmeldung");
}

//get all groups the user is a member of
function get_user_groups($user_id) {
  $ret=array();
  $q=new DB_Query("select * from link_users_groups where users_id=?;",$user_id);
  if($q->numRows<1)
    return $ret;
  while($r=$q->fetch())
    $ret[]=$r["groups_id"];
  return $ret;
}

//get all members of a group
function get_group_users($group_id) {
  $ret=array();
  $q=new DB_Query("select * from link_users_groups where groups_id=?;",$group_id);
  if($q->numRows<1)
    return $ret;
  while($r=$q->fetch())
    $ret[]=$r["users_id"];
  return $ret;
}
