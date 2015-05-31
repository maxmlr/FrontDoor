<?php
class SQLiteHandler
{
  private $db;

  private $default_key_length = 4;

  function __construct($database){
    $this->db = new SQLite3($database);
  }

  function createTable_logins(){
    return $this->db->query("CREATE TABLE logins (id INTEGER PRIMARY KEY AUTOINCREMENT, user varchar(50), date DATETIME DEFAULT CURRENT_TIMESTAMP, ip varchar(50))");
  }

  function createTable_users(){
    return $this->db->query("CREATE TABLE users (user varchar(50) PRIMARY KEY, key varchar(20), valid DATETIME DEFAULT NULL)");
  }

  function dropTable($table){
    return $this->db->query("DROP TABLE IF EXISTS ".$table);
  }

  function validate($key){
    $result = $this->db->query("SELECT EXISTS(SELECT 1 FROM users WHERE key='".$key."' and (valid is null or datetime('now', 'localtime') <= valid))");
    return $result->fetchArray()[0];
  }

  function addUser($user, $key=false, $valid=false){
    if(!$key)
    {
      $key = '';
      for($i = 0; $i < $this->default_key_length; $i++) {
        $key .= mt_rand(0, 9);
      }
    }
    if(!$valid)                         
      $query = "INSERT INTO users ('user', 'key') VALUES ('".$user."', '".$key."')";                                 
    else
      $query = "INSERT INTO users ('user', 'key', 'valid') VALUES ('".$user."', '".$key."', '".$valid."')";
    $result = $this->db->query($query);
    return $key;
  }

  function removeUser($user){
    $this->db->exec("DELETE FROM users WHERE user='".$user."'");
  }

  function updateUserKey($user, $key=false){
    if(!$key)
    {
      $key = '';
      for($i = 0; $i < $this->default_key_length; $i++) {
        $key .= mt_rand(0, 9);
      }
    }

    $result = $this->db->query("UPDATE users SET key = '".$key."' WHERE user='".$user."'");
  }

  function updateUserValid($user, $datetime=false){
    if(!$datetime)
    {
      $valid = "NULL";
    }
    $valid = "'" . $valid . "'";

    $result = $this->db->query("UPDATE users SET valid = ".$valid." WHERE user='".$user."'");
  }

 function whichUser($key){
    $result = $this->db->query("SELECT user FROM users WHERE key='".$key."'");
    $row = $result->fetchArray();
    return $row["user"];
  }

 function listUsers(){
    $users = [];
    $result = $this->db->query("SELECT * FROM users");
    while ($row = $result->fetchArray()) {
      $users[$row["user"]] = $row["key"];
    }
    return $users;
  }

  function addLogin($user,$date,$ip){
    $result = $this->db->query("INSERT INTO logins ('user', 'date', 'ip') VALUES ('".$user."', '".$date."', '".$ip."')");
  }

  function dumpLogins(){
    $result = $this->db->query("SELECT * FROM logins");
    while ($row = $result->fetchArray()) {
      print ($row["user"]."\t".$row["date"]."\t".$row["ip"]."\n");
    }
  }

  function getLoginCounts($user=false){
    $condition = null;
    if(!$user)
    {
      $condition = ' ';
    } else {
      $condition = " WHERE user='".$user."' ";
    }
    $logins = null;
    $result = $this->db->query("SELECT count(*) AS logincount, user FROM logins".$condition."GROUP BY user ORDER BY logincount DESC");
    while ($row = $result->fetchArray()){
      $logins[$row["user"]] = $row["logincount"];
    }
    return $logins;
  }
}
?>
