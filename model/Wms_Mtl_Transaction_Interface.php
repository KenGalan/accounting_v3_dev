<?php

class Wms_Mtl_Transaction_Interface{

	/*reference*/
	/*https://www.cnblogs.com/kevinsun/archive/2012/08/30/2663080.html*/

	private $options = array();



	function __construct(){

	}

	function getLocationByClassCode($classCode){

		if($classCode=="H-Discrete"){
			$location = "HERMETICS";
		}else if($classCode=="P-Discret"){
			$location = "PLASTICS";
		}else{
			$location = "P-SOT";
		}

		return $location;
	}


	function getEoh($inventoryItemId,$lotNumber,$subinventoryCode,$locatorId){
		$sql = "SELECT SUM(TRANSACTION_QUANTITY) QTY FROM APPS.MTL_ONHAND_QUANTITIES MOQ 
		WHERE INVENTORY_ITEM_ID = '". $inventoryItemId ."' and 
		SUBINVENTORY_CODE = '". $subinventoryCode ."' and
		LOCATOR_ID = '". $locatorId ."' and
		LOT_NUMBER = '". $lotNumber . "'";
		$db = new OracleApp();
		$data = $db->query($sql)->fetchRow();
		return $data['QTY'];
	}




	function replenishSubinventoryCode($interfaceId,$inventoryItemId,$lotNo,$fromSubinventoryCode,$fromLocatorId,$qtyToIssue,$issueSubinventoryCode,$wipEntityName,$issuedBy,$receivedBy,$remarks,$transactionReference){
		
		/*getting uom*/
		$sql =  "select DESCRIPTION,PRIMARY_UOM_CODE from mtl_system_items where inventory_item_id = '". $inventoryItemId ."' and organization_id = 102";
		$db = new OracleAppMain();
		$data = $db->query($sql)->fetchRow();
		$transaction_uom = $data['PRIMARY_UOM_CODE'];
		$desc = $data['DESCRIPTION'];

		$eoh = $this->getEoh($inventoryItemId,$lotNo,$fromSubinventoryCode,$fromLocatorId);

		/*GETTING INTERFACE ID*/
		// $sql = "select mtl_material_transactions_s.nextval nxt from dual";
		// $db = new OracleAppMain();
		// $data = $db->query($sql)->fetchRow();
		// $interfaceId = $data['NXT'];

		/* START MATERIAL TRANSACTION */
		



			$db = new OracleAppMain();
			$sql = "
			INSERT INTO MTL_TRANSACTIONS_INTERFACE
			(
			transaction_uom,
			transaction_action_id,
			transaction_date,
			source_code,
			source_line_id,
			source_header_id,
			process_flag ,
			transaction_mode ,
			lock_flag,
			locator_id ,
			last_update_date ,
			last_updated_by ,
			creation_date ,
			created_by ,
			inventory_item_id ,
			subinventory_code,
			organization_id,
			transaction_quantity ,
			primary_quantity ,
			transaction_type_id ,
			transfer_subinventory,
			transfer_locator,
			transaction_interface_id,
			attribute11,--mo
			attribute12,--issued by
			attribute13, --remarks
			attribute14, -- received by
			TRANSACTION_REFERENCE -- transactions
			) VALUES (
			'". $transaction_uom ."' , 
			2,
			sysdate, 
			'Replenish supply subinventory', 
			100,
			100, 
			1, 
			3 , 
			2 ,
			'".$fromLocatorId."' , --FROM LOCATOR_ID
			sysdate, 
			0 , 
			sysdate, 
			0 ,
			'". $inventoryItemId ."' , 
			'". $fromSubinventoryCode ."', --FROM SUBINVENTORY_CODE
			102,
			'".$qtyToIssue * -1 ."' , 
			'".$eoh."' ,
			51,
			'". $issueSubinventoryCode ."',
			null, 
			'".$interfaceId."',
			'". $wipEntityName ."',
			'". $issuedBy ."',
			'". $remarks ."',
			'". strtoupper($receivedBy) ."',
			'". strtoupper($transactionReference) ."'
			)
			";
			// pre($sql);
			$db->query($sql);


			if($lotNo){
				$sql = "INSERT INTO mtl_transaction_lots_interface (transaction_interface_id,
				source_code,
				source_line_id,
				lot_number,
				transaction_quantity,
				process_flag,
				created_by,
				creation_date,
				last_updated_by,
				last_update_date)
				VALUES (
				'". $interfaceId ."',
				'Replenish supply subinventory',      
				100,                               
				'". $lotNo ."',
				'". -1 * $qtyToIssue ."',
				1,                               
				fnd_global.user_id,
				SYSDATE,
				fnd_global.user_id,
				SYSDATE)";
				// pre($sql);
				$db->query($sql);

			}
	}/*end replenish subinventory*/

	function miscIssuance($interfaceId,$inventoryItemId,$lotNo,$fromSubinventoryCode,$fromLocatorId,$qtyToIssue,$deptCode,$account,$accountCode,$issuedBy,$receivedBy,$remarks,$transactionReference){

		/*getting uom*/
		$sql =  "select DESCRIPTION,PRIMARY_UOM_CODE from mtl_system_items where inventory_item_id = '". $inventoryItemId ."' and organization_id = 102";
		$db = new OracleAppMain();
		$data = $db->query($sql)->fetchRow();
		$transaction_uom = $data['PRIMARY_UOM_CODE'];
		$desc = $data['DESCRIPTION'];

		$sql  = "
		INSERT into mtl_transactions_interface(
		transaction_uom,
		transaction_date,
		source_code,
		source_line_id,
		source_header_id,
		process_flag ,
		transaction_mode ,
		lock_flag ,
		locator_id ,
		last_update_date ,
		last_updated_by ,
		creation_date ,
		created_by ,
		inventory_item_id ,
		subinventory_code,
		organization_id,
		transaction_quantity ,
		primary_quantity ,
		transaction_type_id ,
		dst_segment1,
		dst_segment2,
		dst_segment3,
		transaction_interface_id,
		distribution_account_id,
		attribute12,--issued by
		attribute13, --remarks
		attribute14, -- received by
		TRANSACTION_REFERENCE
		) VALUES (
		'". $transaction_uom ."', --transaction uom
		SYSDATE, --transaction date
		'Miscellaneous Issue', --source code
		99, --source line id
		99, --source header id
		1, --process flag
		3 , --transaction mode
		2 , --lock flag
		'". $fromLocatorId ."' , --locator id
		SYSDATE , --last update date
		0, --last updated by
		SYSDATE , --creation date
		0, --created by
		'". $inventoryItemId .  "', --inventory item id
		'". $fromSubinventoryCode ."', --From subinventory code
		102, --organization id
		'".  -1 * $qtyToIssue ."',--transaction quantity
		'". $qtyToIssue ."', --Primary quantity
		32, --transaction type id
		'02', --segment1 account combination
		'". $deptCode ."', --segment2 account combination
		'". $account ."', --segment3 account combination
		'".  $interfaceId . "', --transaction interface id,
		'". $accountCode ."', --distribution_account_id
		'". strtoupper($issuedBy) ."',
		'". $remarks ."',
		'". strtoupper($receivedBy) ."',
		'". strtoupper($transactionReference) ."'
	)";
	$db = new OracleAppMain();
		// pre($sql);
	$db->query($sql);

	if($lotNo){
		$db = new OracleAppMain();
		$sql = "INSERT INTO INV.mtl_transaction_lots_interface (
		transaction_interface_id,
		source_code,
		source_line_id,
		lot_number,
		transaction_quantity,
		process_flag,
		created_by,
		creation_date,
		last_updated_by,
		last_update_date
		)VALUES(
		'" . $interfaceId ."',
		'Miscellaneous Issue',          
		100,                               
		'". $lotNo. "',
		'". -1 * $qtyToIssue."',
		1,                               
		fnd_global.user_id,
		SYSDATE,
		fnd_global.user_id,
		SYSDATE)";
		$db->query($sql);
		// pre($sql);
	}
} /*end material issuance*/


function subInventoryTransfer($interfaceId,$inventoryItemId,$lotNo,$fromSubinventoryCode,$fromLocatorId,$toSubinventoryCode,$toLocatorId,$qtyToIssue,$wipEntityName,$issuedBy,$receivedBy,$remarks,$transactionReference){


	/*getting uom*/
	$sql =  "select DESCRIPTION,PRIMARY_UOM_CODE from mtl_system_items where inventory_item_id = '". $inventoryItemId ."' and organization_id = 102";
	$db = new OracleAppMain();
	$data = $db->query($sql)->fetchRow();
	$transaction_uom = $data['PRIMARY_UOM_CODE'];
	$desc = $data['DESCRIPTION'];


	$sql = "Select * from apps.mtl_onhand_quantities
	where inventory_item_id = '". $inventoryItemId ."' and lot_number = '". $lotNo ."'
	and subinventory_code = '". $fromSubinventoryCode ."'";
	if($fromLocatorId){
		$sql .= " AND LOCATOR_ID IS NULL";
	}
	$db = new OracleApp();
	$data = $db->query($sql)->fetchRow();
	$eoh = $data['TRANSACTION_QUANTITY'];


	/*check EOH*/

	$sql = "
	INSERT INTO MTL_TRANSACTIONS_INTERFACE
	(
	transaction_uom,
	transaction_date,
	source_code,
	source_line_id,
	source_header_id,
	process_flag ,
	transaction_mode ,
	lock_flag,
	locator_id ,
	last_update_date ,
	last_updated_by ,
	creation_date ,
	created_by ,
	inventory_item_id ,
	subinventory_code,
	organization_id,
	transaction_quantity ,
	primary_quantity ,
	transaction_type_id ,
	transfer_subinventory,
	transfer_locator,
	transaction_interface_id,
	attribute11,--mo
	attribute12,--issued by
	attribute13, --remarks
	attribute14, -- received by
	TRANSACTION_REFERENCE -- transactions
	)
	VALUES (
	'". $transaction_uom ."', --transaction uom
	sysdate, --transaction date
	'Subinventory Transfer', --source code
	99, --source line id
	99, --source header id
	1, --process flag
	3 , --transaction mode
	2 , --lock flag
	'". $fromLocatorId ."' , --locator id
	sysdate, --last update date
	0 , --last updated by
	sysdate, --created date
	0 , --created by
	'". $inventoryItemId ."' , --inventory item id
	'". $fromSubinventoryCode ."', --subinventory code
	'102', --organization id
	'". $qtyToIssue ."' , --transaction quantity
	'". $eoh ."' , --primary quantity check EOH
	2, --transaction type id
	'". $toSubinventoryCode ."', -- from subinventory
	'". $toLocatorId ."', -- from locator id
	'". $interfaceId ."',
	'". $wipEntityName ."',
	'". $issuedBy ."',
	'". $remarks ."',
	'". strtoupper($receivedBy) ."',
	'". strtoupper($transactionReference) ."'
)";
$db = new OracleAppMain();
$data = $db->query($sql);



if($lotNo){
	$db = new OracleAppMain();
	$sql = "INSERT INTO INV.mtl_transaction_lots_interface (
	transaction_interface_id,
	source_code,
	source_line_id,
	lot_number,
	transaction_quantity,
	process_flag,
	created_by,
	creation_date,
	last_updated_by,
	last_update_date
	)VALUES(
	'" . $interfaceId ."',
	'Subinventory Transfer',          
	100,                               
	'". $lotNo. "',
	'". -1 * $qtyToIssue."',
	1,                               
	fnd_global.user_id,
	SYSDATE,
	fnd_global.user_id,
	SYSDATE)";
	$db->query($sql);
		// pre($sql);
}
} /*END OF SUBINVENTORY TRANSFER*/

/*START OF MATERIAL RECEIPT*/
function materialReceipt($interfaceId,$inventoryItemId,$lotNo,$fromSubinventoryCode,$fromLocatorId,$receiptQty,$wipEntityName,$issuedBy,$remarks,$transactionReference){


	if($receiptQty <= 0 ){
		return false;
	}

	/*getting uom*/
	$sql =  "select DESCRIPTION,PRIMARY_UOM_CODE from mtl_system_items where inventory_item_id = '". $inventoryItemId ."' and organization_id = 102";
	$db = new OracleAppMain();
	$data = $db->query($sql)->fetchRow();
	$transaction_uom = $data['PRIMARY_UOM_CODE'];
	$desc = $data['DESCRIPTION'];

	$sql = "INSERT INTO INV.MTL_TRANSACTIONS_INTERFACE 
	( 
	process_flag                   , 
	validation_required         ,   
	transaction_mode           ,  
	lock_flag                        ,      
	last_update_date            ,      
	last_updated_by             ,   
	creation_date                 ,         
	created_by                     , 
	inventory_item_id           ,     
	organization_id               ,   
	transaction_quantity        ,  
	primary_quantity             ,            
	transaction_uom             ,  
	transaction_date             ,      
	subinventory_code         ,
	locator_id , 
	transaction_action_id      , 
	transaction_type_id        ,   
	transaction_interface_id  ,
	source_code                  ,
	source_line_id                ,
	source_header_id           ,
	distribution_account_id   ,
	transaction_cost,
	attribute11, --mo
	attribute12, -- issued by
	attribute13, -- remarks
	TRANSACTION_REFERENCE --transaction reference
	)VALUES(
	1                          , --process_flag
	1                          , --validation_required
	3                          , --transaction_mode
	2                          , --lock_flag
	sysdate               , --last_update_date
	'1114'                    , --last_updated_by
	sysdate               , --creation_date
	'1114'                    , -- created_by
	'". $inventoryItemId ."',--inventory_item_id
	102     , --organization_id
	'". $receiptQty ."'                , --transaction_quantity
	'". $receiptQty ."'                , --primary_quantity
	'". $transaction_uom ."'                   ,--uom 
	sysdate               , --transaction_date
	'". $fromSubinventoryCode ."'         ,  -- subinventory_code
	'". $fromLocatorId ."',--locator_id
	27                        , --transaction_action_id
	42                        , --transaction_type_id
	'". $interfaceId ."' , --transaction_interface_id auto gen
	'Material Receipt', --source_code
	'99'                  ,--source_line_id
	'99'                       , -- source_header_id
	'9319'                    , -- default account code for warehouse
	null, -- transaction cost : check tpcost
	'', --mo
	'". $issuedBy ."',
	'". $remarks ."',
	'". $transactionReference ."')";	
	$db = new OracleAppMain();
	$db->query($sql);


	if($lotNo){
		if($this->getExpirationDate($inventoryItemId,$lotNo) == null){
			$sql = "
			INSERT INTO INV.mtl_transaction_lots_interface (
			LOT_EXPIRATION_DATE,
			transaction_interface_id,
			source_code,
			source_line_id,
			lot_number,
			transaction_quantity,
			process_flag,
			created_by,
			creation_date,
			last_updated_by,
			last_update_date
			)
			VALUES (
			sysdate + 730,
			'". $interfaceId ."',
			'Material Receipt',          
			100,                               
			'". $lotNo ."',
			'".  $receiptQty ."',
			1,                               
			fnd_global.user_id,
			SYSDATE,
			fnd_global.user_id,
			SYSDATE	)";
			$db = new OracleAppMain();
			$db->query($sql);
		}else{
			$sql = "
			INSERT INTO INV.mtl_transaction_lots_interface (
			transaction_interface_id,
			source_code,
			source_line_id,
			lot_number,
			transaction_quantity,
			process_flag,
			created_by,
			creation_date,
			last_updated_by,
			last_update_date
			)
			VALUES (
			'". $interfaceId ."',
			'Material Receipt',          
			100,                               
			'". $lotNo ."',
			'".  $receiptQty ."',
			1,                               
			fnd_global.user_id,
			SYSDATE,
			fnd_global.user_id,
			SYSDATE
		)";
		$db = new OracleAppMain();
		$db->query($sql);
	}

}









}
function getExpirationDate($inventoryItemId,$lotNumber){
	$sql = "select EXPIRATION_DATE from mtl_lot_numbers where inventory_item_id = 8824 and lot_number='". $lotNumber ."'";
	$db = new OracleAppMain();
	$data = $db->query($sql)->fetchRow();
	return $data['EXPIRATION_DATE'];
}


}