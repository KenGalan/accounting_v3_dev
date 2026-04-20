<?php 
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

class MysqlApp{

private $sql;
private $query;
private $limit;
private $conn;
private $result = false;

	function __construct(){
		$this->conn = $conn = mysql_connect("localhost","root","");
		$this->db = mysql_select_db("lms");
	}
	
	function select($sql){
	 $this->sql = $sql;
	return $this;
	}
	function query($sql){
	try {
		$this->query = $query = mysql_query($sql) or die (mysql_error());
		return $this;	
	} catch (Exception $e) {
 echo 'Caught exception: ',  $e->getMessage(), "\n";
} 
	}
	
	function result(){
	try {
		// $this->sql;
		 // echo $this->sql;
		$this->query = $query = mysql_query($this->conn,$this->sql) or die (mysql_error());
		return $this;	
} catch (Exception $e) {
 echo 'Caught exception: ',  $e->getMessage(), "\n";
} 

	}
	function limit($limit){
		$this->limit = $limit;
		return $this;
	}
	
	
	function fetchAll(){
	$counter=0;
		while($result = mysql_fetch_array($this->query)){
		$counter++;
			$this->result[] = $result;
			if(isset($this->limit)){
				if($this->limit == $counter){
					break;		
				}
			}
		}
		if(!empty($this->result)){
			return $this->result; 		
		}
		
	}
	function fetchRow(){
		$result = mysql_fetch_array($this->query);
		$this->result = $result;
		return $this->result;
	}
	
	
	

}

?>