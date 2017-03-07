<?php
  require_once(LIB_PATH.DS.'database.php');

  class Comment extends DatabaseObject{

    protected static $table_name="comments";
    protected static $db_fields = array('id', 'photograph_id', 'created', 'author', 'body');

    public $id;
    public $photograph_id;
    public $created;
    public $author;
    public $body;

    //"new" is a reserved word so we use "make" (or "build")
    public static function make($photo_id, $author="Anonymous", $body=""){
      if(!empty($photo_id) && !empty($author) && !empty($body)){
        $comment=new Comment();
        $comment->photograph_id=(int)$photo_id;
        $comment->created = strftime("%Y-%m-%d %H:%M:%S", time());
        $comment->author = $author;
        $comment->body=$body;
        return $comment;
      }else{
        return false;
      }
    }

    public static function find_comments_on($photo_id=0){
      global $database;

      $sql="SELECT * FROM " . self::$table_name;
      $sql.=" WHERE photograph_id=" . $database->escape_value($photo_id);
      $sql.=" ORDER BY created ASC";
      return self::find_by_sql($sql);
    }

    public function try_to_send_notification(){
      $mail=new PHPMailer();

      $mail->IsSMTP();
      $mail->Host = "smtp.gmail.com";
      $mail->Port = 587;
      $mail->SMTPAuth = true;
      $mail->Username ="james.mchugh1988";
      $mail->Password="j4m3rs1406UK";

      $mail->FromName="Photo Gallery";
      $mail->From = "james.mchugh1988@gmail.com";
      $mail->AddAddress("james.mchugh1988@gmail.com", "Photo Gallery Admin");
      $mail->Subject="New Photo Gallery Comment";
      $created=datetime_to_text($this->created);
      $mail->Body="\n
        \n
        A new comment has been recieved in the Photo Gallery.
        \n
        At {$created}, {$this->author} wrote:
        \n
        {$this->body}
        \n
      ";

      //send the mail message
      $result = $mail->Send();

      //detect whether it fails or succeeds
      return $result;
    }

    //Common database methods
    public static function find_all(){
      global $database;
      return self::find_by_sql("SELECT * FROM ".self::$table_name);
    }

    public static function find_by_id($id=0){
     global $database;
     $result_array=self::find_by_sql("SELECT * FROM " .self::$table_name . " WHERE id={$id} LIMIT 1");
     return !empty($result_array) ? array_shift($result_array) : false;
    }

    public static function find_by_sql($sql=""){
      global $database;
      $result_set=$database->query($sql);
      $object_array=array();
      while($row=$database->fetch_array($result_set)){
        $object_array[]=self::instantiate($row);
      }
      return $object_array;
    }

    public function count_all(){
      global $database;
      $sql="SELECT COUNT(*) FROM " . self::$table_name;
      $result_set=$database->query($sql);
      $row=$database->fetch_array($result_set);
      return array_shift($row);
    }

    private static function instantiate($record){
      //Could check that $record exists and is and array
      //Simple, long-form approach

      $object = new self;
    	// $object->id          =$record['id'];
    	// $object->username    =$record['username'];
    	// $object->password    =$record['password'];
    	// $object->first_name  =$record['first_name'];
    	// $object->last_name   =$record['last_name'];

      //More dynamic, short-form approach
      foreach($record as $attribute=>$value){
        if($object->has_attribute($attribute)){
          $object->$attribute=$value;
        }
      }

      return $object;
    }

    private function has_attribute($attribute){
      //get_object_vars returns an associative array with all attributes
      //(incl private ones) as the keys and their current values as the value

      $object_vars=$this->attributes();

      //We don't care about the value, we just want to know if the key exists
      //Will return true or false

      return array_key_exists($attribute, $object_vars);
    }

    protected function attributes(){
      //return an array of attribute keys and their values
      $attributes=array();
      foreach(self::$db_fields as $field){
        if(property_exists($this, $field)){
          $attributes[$field] = $this->$field;
        }
      }
      return $attributes;
    }

    protected function sanitized_attributes(){
      global $database;
      $clean_attributes=array();
      //sanitize the values befoe submitting
      //Note:does not alter the actual value of each attribute

      foreach($this->attributes() as $key => $value){
        $clean_attributes[$key] = $database->escape_value($value);
      }

      return $clean_attributes;
    }

    public function save(){
      //A new record won't have an id yet
      return isset($this->id) ? $this->update() : $this->create();
    }

    protected function create(){
      global $database;

      //Don't forget your SQL syntax and good habits:
      //-INSERT INTO table (key, key) VALUES ('value', 'value')
      //-single-quotes around all values
      //-escape all values to prevent SQL injection

      $attributes=$this->sanitized_attributes();
      $sql="INSERT INTO " . self::$table_name . " (";
      $sql.=join(", ", array_keys($attributes));
      $sql.=") VALUES ('";
      $sql.=join("', '",array_values($attributes));
      $sql.="')";

      if($database->query($sql)){
        $this->id = $database->insert_id();
        return true;
      }else{
        return false;
      }

    }

    protected function update(){
      global $database;
      //Don't forget your SQL syntax and good habits:
      //-Update table SET key='value', key='value' WHERE condition
      //-single-quotes around all values
      //-escape all values to prevent SQL injection

      $attributes=$this->sanitized_attributes();
      $attribute_pairs=array();
      foreach($attributes as $key => $value){
        $attribute_pairs[] = "{$key}='{$value}'";
      }

      $sql="UPDATE " . self::$table_name . " SET ";
      $sql.=join(", ", $attribute_pairs);
      $sql.=" WHERE id=" . $database->escape_value($this->id);

      $database->query($sql);
      return ($database->affected_rows() == 1) ? true : false;
    }

    public function delete(){
      global $database;

      //Don't forget your SQL syntax and good habits:
      //-DELETE FROM table WHERE condition LIMIT 1
      //-escape all values to prevent SQL injection
      //- use LIMIT 1

      $sql="DELETE FROM " . self::$table_name . " ";
      $sql.="WHERE id=" . $database->escape_value($this->id);
      $sql.=" LIMIT 1";

      $database->query($sql);
      return ($database->affected_rows() == 1) ? true : false;
    }
  }
?>
