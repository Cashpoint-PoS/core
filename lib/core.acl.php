<?
//get all ACLs for a object
function acl_get_acls($obj_type="",$obj_id=0) {
  //Cache ACL queries for one request => when changing ACLs, just unset the cache after the write
  global $acl_cache;
  if(!is_array($acl_cache))
    $acl_cache=array();
  $acl_key=sprintf("%s\0%d",$obj_type,$obj_id);
  //Check if the query has been cached
  if(in_array($acl_key,$acl_cache))
    return $acl_cache[$acl_key];
  $ret=array();
  $q=new DB_Query("select * from acl where object_type=? and object_id=?;",$obj_type,$obj_id);
  if($q->numRows<1)
    return $ret;
  while($r=$q->fetch())
    $ret[]=$r;
  $acl_cache[$acl_key]=$ret;
  return $ret;
}

//get all ACLs for a object, including these of parent objects
function acl_get_all_applicable_acls($obj_type="",$obj_id=0) {
  $ret=array();
  //ACLs for all objects
  $ret=array_merge($ret,acl_get_acls());
  //ACLs for all objects of the same type as the requested object
  if($obj_type!=="")
    $ret=array_merge($ret,acl_get_acls($obj_type));
  //ACLs for this object
  if($obj_type!=="" && $obj_id!==0)
    $ret=array_merge($ret,acl_get_acls($obj_type,$obj_id));
  
  return $ret;
}

//get the ACLs applicable to a object for a user
function acl_get_applicable_acls($obj_type="",$obj_id=0,$user_id=0) {
  $myacls=array();
  $mydenys=array();
  $acls=acl_get_all_applicable_acls($obj_type,$obj_id);
  $groups=get_user_groups($user_id);
//  printf("User %d is member of groups %s\n",$user_id,implode(",",$groups));
  //Walk all ACLs for the object and check if the user is affected by them
  foreach($acls as $acl) {
    if($acl["target_type"]==="user" && $acl["target_id"]!==$user_id) { //ACL is for other user
//      printf("Discarding ACL object %d because user %d is not %d\n",$acl["id"],$acl["target_id"],$user_id);
      continue;
    } elseif($acl["target_type"]==="group" && !in_array($acl["target_id"],$groups)) { //ACL is for a group the user is not a member of
//      printf("Discarding ACL object %d because its group %d is not in the groups (%s) of user %d\n",$acl["id"],$acl["target_id"],implode(",",$groups),$user_id);
      continue;
    }
    $acl_elements=str_split($acl["acl"]);
    foreach($acl_elements as $key) {
      if($key==="")
        continue;
      if($acl["negate"]==1)
        $target=&$mydenys;
      else
        $target=&$myacls;
      if(!in_array($key,$target))
        $target[]=$key;
    }
//    printf("Walking ACL object %s\n",print_r($acl_elements,true));
  }
//  printf("ACLs for %s/%d, user %d: %s, denied %s\n",$obj_type,$obj_id,$user_id,implode(",",$myacls),implode(",",$mydenys));
  $ret=array();
  //keep only the ACLs which have not been denied somewhere
  foreach($myacls as $acl) {
    $pos=array_search($acl,$mydenys);
    if($pos===false)
      $ret[]=$acl;
  }
//  printf("Final ACLs for %s/%d, user %d: %s\n",$obj_type,$obj_id,$user_id,implode(",",$ret));
  return $ret;
}

//check if a user has a specific ACL key on a specific object
function acl_check($obj_type="",$obj_id=0,$acl_key,$user_id=-1) {
  if($user_id===-1)
    $user_id=$_SESSION["user"]["id"];
//  printf("Checking if user %d has the ACL key %s on %s/%d\n",$user_id,$acl_key,$obj_type,$obj_id);
  $acls=acl_get_applicable_acls($obj_type,$obj_id,$user_id);
//  printf("ACLs of user %d: %s\n",$user_id,implode(",",$acls));
  $wanted=str_split($acl_key);
  foreach($wanted as $acl_key) {
    if(!in_array($acl_key,$acls))
      return false;
  }
  return true;
}
