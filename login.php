<?
if(get_magic_quotes_gpc())
  die("magic quotes detected");
require("lib.php");

$user=$_POST["username"];
$pass=$_POST["password"];
$target=$_POST["target"];

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
