<?php

class Wms_Issuance{

	/*reference*/
	/*https://www.cnblogs.com/kevinsun/archive/2012/08/30/2663080.html*/

	private $options = array();



	function __construct(){

	}


	function saveItemMovement($transactionId,$mainId,$fromSubinventoryCode,$fromLocatorId,$toSubinventoryCode,$toLocatorId){
		$sql = "INSERT INTO WH_WMS_ITEM_MOVEMENTS (
		TRANSACTION_ID,
		PL_ITEM_ID,
		FROM_SUBINVENTORY_CODE,
		FROM_LOCATOR_ID,
		TO_SUBINVENTORY_CODE,
		TO_LOCATOR_ID, 
		ADDED_ON, 
		ADDED_BY,
		ACTIVE ) VALUES ( 
		$transactionId,
		$mainId,
		'". $fromSubinventoryCode ."',
		'". $fromLocatorId ."',
		'". $toSubinventoryCode ."',
		'". $toLocatorId ."',
		SYSDATE,
		'username-change',
		1)";
		$db = new OracleApp();
		$db->query($sql);
	}
	

}