<?php
/**
 * Very basic user group tree 
 * if you need something more than that look at a proper ACL
 * 
 * 
 * @author	Jason Medland<jason.medland@gmail.com>
 * @package	JCORE\SERVICE\AUTH
 * 
 */
 

namespace JCORE\SERVICE\AUTH;
#use JCORE\SERVICE\DAO\ORM as DAO_ORM;
use JCORE\SERVICE\DAO\ORM\DAO_ORM as DAO_ORM;

/**
 * Class CRUDE_USER_ROLE_ENTITY
 *
 * @package JCORE\SERVICE\AUTH
*/
class CRUDE_USER_ROLE_ENTITY  extends DAO_ORM{ 
	/** 
	* ACL different joins on DAO 
	*/
	protected $config = array(
		'DSN' => 'BLACKWATCH',
		'table' => 'user_role',
		'pk_field' => 'user_role_pk',
		'fk_field' => 'user_role_fk',
		'user_role' => 'role',
	);
		
	/** 
	* ACL
	*/
	protected $role = null;
	protected $parentTable = 'client';
	protected $ACL_TABLE = array();
	protected $ACL_TREE = array();
	protected $ACL_LIST = array();
	protected $indexBy = 'role';
	
	/**
	* DESCRIPTOR: 
	* 
	* @param param 
	* @return return  
	*/
	public function __construct($args =null){
		#echo __METHOD__.'@'.__LINE__.'  '.'<br>'; 
		$config = $this->config;
		$config["pk"] = $_SESSION['role_id'];
		
		parent::__construct($config);
		#echo __METHOD__.'args<pre>'.print_r($args, true).'</pre>'.PHP_EOL;
		return;
	}

	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRoleTable($args = null){
		
		if(!isset($this->config["DSN"])){
			$this->config["DSN"] = 'BLACKWATCH';
		}
		if(1 >= count($this->ACL_TABLE)){
			
			$query = '
			SELECT * 
			FROM '.$this->config["table"].'  
			ORDER BY user_role_pk ASC
			';
			$result = $GLOBALS["DATA_API"]->retrieve($this->config["DSN"], $query, $args=array('returnArray' => true));
			$parseArgs = array(
				'result' => $result
			);
			#$result = $this->parseJSONColumns($parseArgs);
			#$result = $this->parseJSONColumns($result);
			#echo __METHOD__.'result<pre>'.print_r($result, true).'</pre>'.PHP_EOL;
			$this->ACL_TABLE = $result;
			#$this->setRoleList();
		}
		
		#echo __METHOD__.'this->ACL_TABLE<pre>'.print_r($this->ACL_TABLE, true).'</pre>'.PHP_EOL;
		return $this->ACL_TABLE;
	}	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function setRoleList($args = null){
		if(1 >= count($this->ACL_TABLE)){
			$this->getRoleTable();
		}
		foreach($this->ACL_TABLE as $key => $value){
			$this->ACL_LIST[$value[$this->indexBy]] = $value;
		}
	}
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRoleList($args = null){
		if(1 >= count($this->ACL_TABLE)){
			$this->getRoleTable();
		}
			
		if(1 >= count($this->ACL_LIST)){
			$this->setRoleList();
		}
		return $this->ACL_LIST;
	}
	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRoleName($role = null){
		if(is_numeric($role)){
			$role = $this->get($this->config["table"], $this->config["user_role"]);
		}
		return $role;
	}
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRole($role = null){
		if(null != $role){
			$role = $this->getRoleName($role);
			if(isset($this->ACL_LIST[$role])){
				return $this->ACL_LIST[$role];
			}
		}
		return false;
	}
	
	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRoleTree($args = null){
		$result = $this->getRoleTable($args);
		
		$ACL_TREE = array(); 
		foreach($result AS $key => $value){
			if($value["parent_id"] == $value["user_role_pk"]){
				$ACL_TREE[$value["role"]] = $value;
			}
		}
		
		#echo 'ACL_TREE<pre>'.print_r($ACL_TREE, true).'</pre>'.PHP_EOL;
		foreach($ACL_TREE AS $key => $value){
			$args2 = array(
				'result' => $result,
				'parent_id' => $value["user_role_pk"],
				#'ACL_TREE' => $ACL_TREE,
			);
			$children =  $this->getChildren($args2);
			if(0 < count($children)){
				$ACL_TREE[$key]["children"] = $children;
			}			
		}
		
		#echo 'ACL_TREE<pre>'.print_r($ACL_TREE, true).'</pre>'.PHP_EOL;
		#echo 'this->role<pre>'.print_r($this->role, true).'</pre>'.PHP_EOL;
		$this->ACL_TREE = $ACL_TREE;
		return $this->ACL_TREE;
	}
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	*		'result' => $result,
	*		'ACL_TREE' = $ACL_TREE,
	* @return return  
	*/
	public function getChildren($args = null){

		
		$CHILD_ACL_TREE = array();
		foreach($args["result"] AS $key => $value){
			#echo 'parent_id['.$args["parent_id"].'] key['.$key.']  <pre>'.print_r($value, true).'</pre>  '.PHP_EOL;
			if(
				$args["parent_id"] == $value["parent_id"] 
				&& 
				$value["parent_id"] != $value["user_role_pk"]
			){
				echo ' TRUE '.PHP_EOL;
				$CHILD_ACL_TREE[$value["role"]] = $value;
			}else{
				#echo ' FALSE '.PHP_EOL;
				#$CHILD_ACL_TREE[$value["role"]] = $value["role"];
			}
			
		}
		return $CHILD_ACL_TREE;
	}
	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	*		'result' => $result,
	*		'ACL_TREE' = $ACL_TREE,
	* @return return  
	*/
	public function getParents($args = null){

		/**
		* traverse up the tree and return a lineage 
		* 
		
		$CHILD_ACL_TREE = array();
		foreach($args["result"] AS $key => $value){
			#echo 'parent_id['.$args["parent_id"].'] key['.$key.']  <pre>'.print_r($value, true).'</pre>  '.PHP_EOL;
			if(
				$args["parent_id"] == $value["parent_id"] 
				&& 
				$value["parent_id"] != $value["access_control_list_pk"]
			){
				echo ' TRUE '.PHP_EOL;
				$CHILD_ACL_TREE[$value["role"]] = $value;
			}else{
				#echo ' FALSE '.PHP_EOL;
				#$CHILD_ACL_TREE[$value["role"]] = $value["role"];
			}
			
		}
		return $CHILD_ACL_TREE;
		*/
	}
}

?>
