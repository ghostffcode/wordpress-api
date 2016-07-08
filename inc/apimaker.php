<?php

require_once '../wp-config.php';


/**
 * This is the class that gets data from database and transforms to API data
 */
class apimaker {

  // Edit to add database host, database name, username and password
  private $host = DB_HOST,
          $db = DB_NAME,
          $db_user = DB_USER,
          $db_pass = DB_PASSWORD,
          $prefix = '';  // edit this if you set a different table prefix for wordpress

  // Do not edit lines below unless you know what you're doing
  private $postTable = 'posts',
          $userTable = 'users';

  public $conn;

  function __construct() {
    global $table_prefix;
    $this->prefix = $table_prefix;
    try {
    $this->db = new PDO("mysql:host=$this->host;dbname=$this->db", $this->db_user, $this->db_pass);
    // set the PDO error mode to exception
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->setTables();
    } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    }
  }

  public function getPosts () {
    $myArray = array();
    //select dataset from database Table
    $query = $this->db->prepare("SELECT id, post_title, post_content, post_date, post_date_gmt, post_modified, post_modified_gmt, post_author  FROM $this->postTable WHERE post_status=:status AND post_type=:type");
    $query->bindValue(':status', 'publish');
    $query->bindValue(':type', 'post');
    $query->execute();
    $query->setFetchMode(PDO::FETCH_ASSOC);
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $row['author'] = $this->getUser($row['post_author']);
        unset($row['post_author']);
        $myArray[] = $row;
      }
      echo json_encode($myArray);
      //echo json_encode(array("data" => $myArray));
    }

  }

  // get the user display name using ID
  private function getUser($id = '') {
    // get the Author profile name
    $myArray = array();
    //select dataset from database Table
    $query = $this->db->prepare("SELECT display_name, user_url FROM $this->userTable WHERE ID=:user");
    $query->bindParam(':user', $id);
    $query->execute();
    $query->setFetchMode(PDO::FETCH_ASSOC);
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $myArray[] = array_merge($row, $this->getReal($id,'first_name'), $this->getReal($id,'last_name'));
      }
      return $myArray;
    }
  }

  // get the user display name using ID
  private function getReal($id = '', $field) {
    // get the Author profile name
    $myArray = array();
    //select dataset from database Table
    $query = $this->db->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id=:user AND meta_key=:name");
    $query->bindParam(':user', $id);
    $query->bindParam(':name', $field);
    $query->execute();
    $query->setFetchMode(PDO::FETCH_ASSOC);
    if($query->rowCount() > 0){
      while($row = $query->fetch()) {
        $myArray[$field] = $row['meta_value'];
      }
      return $myArray;
  }
}

private function setTables() {
  $this->postTable = $this->prefix .$this->postTable;
  $this->userTable = $this->prefix .$this->userTable;
}

  function __destruct() {
    // Close mysqli connection
    $this->conn = null;
  }

}

?>
