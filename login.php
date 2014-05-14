<?
if(get_magic_quotes_gpc())
  die("magic quotes detected");
require("lib.php");

$user=$_POST["username"];
$pass=$_POST["password"];
$target=$_POST["target"];

if(isset($_POST["rfid"]) && $_POST["rfid"]=="on") {
  $url="http://msbs.selfhost.eu/ks_services/api.php?action=rfid_auth";
  $content=@file_get_contents($url);
  if($content===false)
    throw new Exception("could not contact the terminal");
  $content=json_decode($content);
  if($content===null)
    throw new Exception("terminal returned garbage content");

  if($content->status=="error")
    throw new Exception("terminal returned error: ".$content->message);
  
  $uid=$content->data->card_uid;
  $data=$content->data->card;
  $key=GPG_Key::getById(1);
  $res=openssl_verify($uid,base64_decode($data->sig),$key->processProperty("pubkey_handle"),OPENSSL_ALGO_SHA1);
  if($res!=1)
    be_error(403,"index.php","Signatur ungültig");
  
  $tokens=UserToken::getByFilter("where serial=? and active=1",$uid);
  if(sizeof($tokens)!=1)
    be_error(403,"index.php","Karte ungültig");
  $token=$tokens[0];
  
  $q=new DB_Query("select * from users where id=?",$token->users_id);
  if($q->numRows!=1)
    be_error(403,"index.php","Benutzername existiert nicht");
  $row=$q->fetch();
  //check if account is active
  if($row["is_active"]!=1)
    be_error(403,"index.php","Account nicht aktiv");

  $_SESSION["user"]=$row;  
  switch($target) {
    case "app":
      redir("kasse/index.php");
    case "backend":
      redir("be_index.php");
    default:
      be_error(404,"index.php","Ungültiges Backend");
  }

  exit;
}

$q=new DB_Query("select * from users where name=?",$user);
if($q->numRows!=1)
  be_error(403,"index.php","Benutzername existiert nicht");

$row=$q->fetch();
if(strpos($row["password"],":")===FALSE)
  $p="0:1:md5:".$row["password"].":";
else
  $p=$row["password"];

list($version,$iterations,$alg,$hash,$salt)=explode(":",$p);
//check password
if($hash!=hash($alg,$pass.$salt))
  be_error(403,"index.php","Passwort falsch");
//check if account is active
if($row["is_active"]!=1)
  be_error(403,"index.php","Account nicht aktiv");

$_SESSION["user"]=$row;

//check if the password is a md5-only password (reset by directly editing the DB)
if($p!=$row["password"]) {
  $u=User::getById($row["id"]);
  $u->password=$pass;
  $u->commit();
}

switch($target) {
  case "app":
    redir("kasse/index.php");
  case "backend":
    redir("be_index.php");
  default:
    be_error(404,"index.php","Ungültiges Backend");
}
