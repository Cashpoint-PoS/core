<?php
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
//src http://www.dasprids.de/blog/2008/08/22/getting-a-password-hidden-from-stdin-with-php-cli
/**
 * Get a password from the shell.
 *
 * This function works on *nix systems only and requires shell_exec and stty.
 *
 * @param  boolean $stars Wether or not to output stars for given characters
 * @return string
 */
function getPassword($stars = false)
{
		//windows can't do this
		if(strtolower(substr(PHP_OS,0,3))=="win")
			return trim(fgets(STDIN), "\n");

    // Get current style
    $oldStyle = shell_exec('stty -g');

    if ($stars === false) {
        shell_exec('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
    } else {
        shell_exec('stty -icanon -echo min 1 time 0');

        $password = '';
        while (true) {
            $char = fgetc(STDIN);

            if ($char === "\n") {
                break;
            } else if (ord($char) === 127) {
                if (strlen($password) > 0) {
                    fwrite(STDOUT, "\x08 \x08");
                    $password = substr($password, 0, -1);
                }
            } else {
                fwrite(STDOUT, "*");
                $password .= $char;
            }
        }
    }

    // Reset old style
    shell_exec('stty ' . $oldStyle);

    // Return the password
    return $password;
}
