<?php

require_once('config.php');

class SQLiteHandler
{
  private $db;

  private $default_key_length;
  private $burn_key_tolerance;
  private $callid_target;
  private $callid_usr;
  private $callid_pwd;

  function __construct($database){
    $this->db = new SQLite3($database);
    $this->default_key_length = $GLOBALS["CONFIG"]['default_key_length'];
    $this->burn_key_tolerance = $GLOBALS["CONFIG"]['burn_key_tolerance'] * 60;
    $this->callid_target = $GLOBALS["CONFIG"]['callid_target'];
    $this->callid_usr = $GLOBALS["CONFIG"]['callid_usr'];
    $this->callid_pwd = $GLOBALS["CONFIG"]['callid_pwd'];
  }

  function createTable_logins(){
    return $this->db->query("CREATE TABLE logins (id INTEGER PRIMARY KEY AUTOINCREMENT, userid INTEGER, key VARCHAR(50), timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, valid INTEGER, ip VARCHAR(50))");
  }

  function createTable_users(){
    return $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(50), email VARCHAR(50), callid VARCHAR(50), gmc_regid VARCHAR(255), key VARCHAR(20) NOT NULL UNIQUE, expires DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, last_modified DATETIME DEFAULT CURRENT_TIMESTAMP)");
  }

  function createTable_burn_keys(){
    return $this->db->query("CREATE TABLE burn_keys (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(50), key VARCHAR(20) NOT NULL UNIQUE, callid VARCHAR(50), expires DATETIME DEFAULT NULL, used DATETIME DEFAULT NULL, ip VARCHAR(50))");
  }

  function createTable_admin_settings(){
    return $this->db->query("CREATE TABLE admin_settings (enabled INTEGER DEFAULT 1, force_permission INTEGER DEFAULT 0, temporary_mute DATETIME DEFAULT NULL)");
  }

  function dropTable($table){
    return $this->db->query("DROP TABLE IF EXISTS ".$table);
  }

  function validate($auth, $auth_call=false){
    $auth_name = "key";
    if ($auth_call)
    {
      if ( strcmp($auth_call, $this->callid_target) != 0)
      {
        $return["valid"] = 0;
        return $return;
      }
      $auth_name = "callid";
    }

    $result = $this->db->query("SELECT id, name, expires FROM users WHERE ".$auth_name."='".$auth."'");
    $return = $result->fetchArray();

    if (!$return)
    {
      $result = $this->db->query("SELECT id, name, expires, used FROM burn_keys WHERE ".$auth_name."='".$auth."'"); //AND (expires IS NULL OR datetime('now', 'localtime') <= expires)");
      $return = $result->fetchArray();
      if ($return)
      {
        if ($return['used'])
        {
          $tolerance = (time() - strtotime($return['used']));
          if ($tolerance > $burn_key_tolerance)
          {
            $this->deleteBurnKey($return['id']); 
          }
        }
        else
        {
          $query = "UPDATE burn_keys SET used='".date('Y-m-d H:i:s', time())."' WHERE id=".$return['id'];
        }
      }
    }

    if ($return)
    {
      if (!$return["expires"] || (strtotime('now') <= strtotime($return["expires"])))                                                                 
        $return["valid"] = 1;                                                                                      
      else                                                                                                         
        $return["valid"] = 0; 
    }
    else
      $return = array('valid' => 0, 'id' => NULL, 'name' => NULL, 'expires' => NULL);

    return $return;
  }

  function addBurnKey($user=false, $key=false, $callid=false, $expires=false){
    if(!$user)
      $user = "anonymous";
    if(!$key)                                                                                                                                                                                     
      $key = $this->generateKey($this->default_key_length);                                                                                                                                              
    if(!$callid)                                                                                                                                                                          
      $callid = NULL;
    if(!$expires)                                                                                                                                                                                 
      $query = "INSERT INTO burn_keys ('name', 'key', 'callid', 'expires') VALUES ('".$user."', '".$key."', '".$callid."', '".date('Y-m-d H:i:s', strtotime('+1 day'))."')";                                                                                                             
    else                                                                                                                                                                                          
      $query = "INSERT INTO burn_keys ('name', 'key', 'callid', 'expires') VALUES ('".$user."', '".$key."', '".$callid."', '".$expires."')";                                                                                   
                                                                                                                                                                                                  
    $this->db->query($query);                                                                                                                                                                     
                                                                                                                                                                                                  
    return $key;  
  }

  function deleteBurnKey($id){
    $this->db->query("DELETE FROM burn_keys WHERE id=".$id);
  }

  function addUser($user, $key=false, $callid=false, $expires=false){
    if(!$key)
      $key = $this->generateKey($this->default_key_length);
    if(!$callid)                                                                                                                                                                        
      $callid = NULL;
    if(!$expires)                         
      $query = "INSERT INTO users ('name', 'key', 'callid') VALUES ('".$user."', '".$key."', '".$callid."')";                                 
    else
      $query = "INSERT INTO users ('name', 'key', 'callid', 'expires') VALUES ('".$user."', '".$key."', '".$callid."', '".$expires."')";

    $this->db->query($query);

    return $key;
  }

  function removeUser($id){
    $this->db->exec("DELETE FROM users WHERE id=".$id);
  }

  function updateUserKey($id, $key=false){
    if(!$key)
      $key = $this->generateKey($this->default_key_length);
    $this->db->query("UPDATE users SET key = '".$key."' WHERE id=".$id);
  }

  function generateKey($digits){
    $key = '';                                                                                                                                                                                  
    for($i = 0; $i < $digits; $i++) {                                                                                                                                        
      $key .= mt_rand(0, 9);                                                                                                                                                                    
    }                                                                                                                                                                                           
    return $key;
  }
  
  function updateUserKeyExpires($id, $expires=false){
    if(!$expires)
      $expires = "NULL";
    else
      $expires = "'" . $expires . "'";

    $this->db->query("UPDATE users SET expires = ".$expires." WHERE id=".$id);
  }

 function whichUser($key){
    $result = $this->db->query("SELECT id, name FROM users WHERE key='".$key."'");
    $user = $result->fetchArray();
    return $user;
  }

 function getGMCDevice($id){                                                                                                                                        
    $result = $this->db->query("SELECT gmc_regid FROM users WHERE id=".$id);                                                                                 
    $user = $result->fetchArray(SQLITE3_NUM);                                                                                                                 
    return $user;                                                                                                                                                  
 } 

 function listUsers(){
    $users = [];
    $result = $this->db->query("SELECT * FROM users");
    while ($row = $result->fetchArray()) {
      $users[$row["name"]] = $row["key"];
    }
    return $users;
  }

  function log($userid,$key,$valid,$ip){
    if($userid)
    {
      $this->db->query("INSERT INTO logins ('userid', 'key', 'timestamp', 'valid', 'ip') VALUES (".$userid.", '****', CURRENT_TIMESTAMP , ".$valid.", '".$ip."')");
      $this->db->query("UPDATE users SET last_login=CURRENT_TIMESTAMP WHERE id=".$userid);
    }
    else
      $this->db->query("INSERT INTO logins ('key', 'timestamp', 'valid', 'ip') VALUES ('".$key."', CURRENT_TIMESTAMP , ".$valid.", '".$ip."')");
  }

  function dumpLogins(){
    $result = $this->db->query("SELECT * FROM logins");
    while ($row = $result->fetchArray()) {
      print ($row["key"]."\t".$row["userid"]."\t".$row["timestamp"]."\t".$row["valid"]."\t".$row["ip"]."\n");
    }
  }

  function getLoginCounts($userid=false){
    if(!$userid)
    {
      $condition = " ";
    } else {
      $condition = " WHERE id=".$userid." ";
    }
    $logins = null;
    $result = $this->db->query("SELECT count(*) AS logincount, userid FROM logins".$condition."GROUP BY userid ORDER BY logincount DESC");
    while ($row = $result->fetchArray()){
      $logins[$row["userid"]] = $row["logincount"];
    }
    return $logins;
  }
}
?>
