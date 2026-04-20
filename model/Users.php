<?php

class Users{
	
	
	private $db;
	private $table = "users";
	
	
	function __construct(){
		
	}
	
	
	function getAllUsers(){
		$db = new OracleApp();
		$sql = "select * from " . $this->table ;
		$result = $db->query($sql)->fetchAll();
		return $result;
	}
	
	function save($value,$value2){
		$db = new OracleApp();
		$sql = "INSERT INTO USERS(FIELD1,FIELD2) VALUES('". $value ."','". $value2 ."')";
		$db->query($sql);
	}
	
	
	
	
	
	
	
	
	
	
}