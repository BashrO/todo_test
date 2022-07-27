<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

class DB {
//   protected $connection;
  protected $query;
  protected $show_errors = true;
  protected $query_closed = TRUE;
  private $db=[
      'server'=>'localhost', 
      'user'=>'root', 
      'password'=>'', 
      'name'=>'todo'
  ];

  public $query_count = 0;
  protected $charset = 'utf8';
  public function __construct() {
    $this->connection = new mysqli($this->db['server'], $this->db['user'], $this->db['password']);
    //,$this->db['name']
    if ($this->connection->connect_error) {
      $this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
    }
    mysqli_select_db($this->connection, $this->db['name']);
    $this->connection->set_charset($this->charset);
  }
  public function setQuery($q) {
    return $this->query($q);
  }
  public function query($query) {
    if (!$this->query_closed) {
      $this->query->close();
    }
    if ($this->query = $this->connection->prepare($query)) {
      if (func_num_args() > 1) {
        $x = func_get_args();
        $args = array_slice($x, 1);
        $types = '';
        $args_ref = array();
        foreach ($args as $k => &$arg) {
          if (is_array($args[$k])) {
            foreach ($args[$k] as $j => &$a) {
              $types .= $this->_gettype($args[$k][$j]);
              $args_ref[] = &$a;
            }
          } else {
            $types .= $this->_gettype($args[$k]);
            $args_ref[] = &$arg;
          }
        }
        array_unshift($args_ref, $types);
        call_user_func_array(array($this->query, 'bind_param'), $args_ref);
      }
      $this->query->execute();
      if ($this->query->errno) {
        $this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
      }
      $this->query_closed = FALSE;
      $this->query_count++;
    } else {
      $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
    }
    return $this;
  }

    public function initiateDB($tableName,$vars){
        $q = "CREATE DATABASE IF NOT EXISTS ".$this->db['name']." CHARACTER SET utf8 COLLATE utf8_general_ci";
        $dbRes=$this->query($q);
        $prime='';
        $tableQuery=' CREATE  TABLE IF NOT EXISTS `'.$this->db['name'].'`.`'.$tableName.'` (';
        $varsToAdd='';
        foreach ($vars as $i=>$v){
            $varsToAdd.=($varsToAdd!=''?',':'').' `'.$i.'` '.$v['type'].' ('.$v['length'].')  '.(isset($v['default']) ? 'DEFAULT \''.$v['default'].'\''  :'').' ';  
            if(isset($v['isPrime'])){
                $prime=', PRIMARY KEY (`'.$i.'`) ';
            }
        }
        $tableQuery.=$varsToAdd.$prime.') ENGINE=InnoDB';
        // die($tableQuery);
        $tableRes = $this->query($tableQuery);
        return [
            'db'=>$dbRes,
            'table'=>$tableRes,
        ];
    }
  public function fetchAll($callback = null) {
    $params = array();
    $row = array();
    $meta = $this->query->result_metadata();
    while ($field = $meta->fetch_field()) {
      $params[] = &$row[$field->name];
    }
    call_user_func_array(array($this->query, 'bind_result'), $params);
    $result = array();
    while ($this->query->fetch()) {
      $r = array();
      foreach ($row as $key => $val) {
        $r[$key] = $val;
      }
      if ($callback != null && is_callable($callback)) {
        $value = call_user_func($callback, $r);
        if ($value == 'break') break;
      } else {
        $result[] = $r;
      }
    }
    $this->query->close();
    $this->query_closed = TRUE;
    return $result;
  }

  public function fetchArray() {
    $params = array();
    $row = array();
    $meta = $this->query->result_metadata();
    while ($field = $meta->fetch_field()) {
      $params[] = &$row[$field->name];
    }
    call_user_func_array(array($this->query, 'bind_result'), $params);
    $result = array();
    while ($this->query->fetch()) {
      foreach ($row as $key => $val) {
        $result[$key] = $val;
      }
    }
    $this->query->close();
    $this->query_closed = TRUE;
    return $result;
  }

  public function close() {
    return $this->connection->close();
  }

  public function numRows() {
    $this->query->store_result();
    return $this->query->num_rows;
  }

  public function affectedRows() {
    return $this->query->affected_rows;
  }

  public function lastInsertID() {
    return $this->connection->insert_id;
  }

  public function error($error) {
    if ($this->show_errors) {
      exit($error);
    }
  }

  private function _gettype($var) {
    if (is_string($var)) return 's';
    if (is_float($var)) return 'd';
    if (is_int($var)) return 'i';
    return 'b';
  }

}
?>
