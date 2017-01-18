<?php
/**
 * Very basic rule tree 
 * if you need something more than that look at a proper ACL
 * 
 * 
 * @author	Jason Medland<jason.medland@gmail.com>
 * @package	JCORE\SERVICE\AUTH
 * 
 */
 

namespace JCORE\SERVICE\AUTH;
use JCORE\SERVICE\DAO\ORM\DAO_ORM as DAO_ORM;



/**
 * Class CRUDE_ACL_ENTITY
 * Very basic rule tree not even MPTT
 * if you need something more than that look at a proper ACL
 * 
 * @package JCORE\SERVICE\AUTH
*/
class CRUDE_ACL_ENTITY extends DAO_ORM{ 
	/** 
	* ACL different joins on DAO_ORM 
	*/
	protected $config = array(
		'DSN' => 'JCORE',
		'SELECT' => '*, rule_name AS rule',
		'table' => 'access_control_list',
		'pk_field' => 'access_control_list_pk',
		#'fk_field' => 'access_control_list_fk',
		'user_rule' => 'rule_name',
	);
	/** 
	* ACL different joins on DAO_ORM 
	*/
	protected $JSONColumns = array(
		'allow',
		'deny',
	);
	
		
	/** 
	* ACL
	*/
	protected $rule = null;
	protected $parentTable = 'client';
	protected $ACL_TABLE = array();
	protected $ACL_TREE = array();
	protected $ACL_LIST = array();
	protected $indexBy = 'rule';
	
	/**
	* DESCRIPTOR: 
	* 
	* @param param 
	* @return return  
	*/
	public function __construct($args = null){
		if(isset($args["DSN"])){
			$this->config["DSN"] = $args["DSN"];
		}
		parent::__construct($args);
		return;
	}

	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRuleTable($args = null){
		#echo __METHOD__.'@'.__LINE__.''.'<br>'.PHP_EOL; 
		
		
		if(isset($args["DSN"])){
			$this->config["DSN"] = $args["DSN"];
		}
		if(1 >= count($this->ACL_TABLE)){
			$query = '
			SELECT '.$this->config["SELECT"].' 
			FROM '.$this->config["table"].'  
			ORDER BY access_control_list_pk ASC
			';
			$result = $GLOBALS["DATA_API"]->retrieve($this->config["DSN"], $query, $args=array('returnArray' => true));
			#echo __METHOD__.'result<pre>'.print_r($result, true).'</pre>'.PHP_EOL;
			
			$result = $this->parseJSONColumns($result);
			#echo __METHOD__.'result<pre>'.print_r($result, true).'</pre>'.PHP_EOL;
			
			$this->ACL_TABLE = $result;
			#$this->setRuleList();
		}
			
		return $this->ACL_TABLE;
	}	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function setRuleList($args = null){
		#echo __METHOD__.'@'.__LINE__.''.'<br>'.PHP_EOL; 
		if(1 >= count($this->ACL_TABLE)){
			$this->getRuleTable();
		}
		foreach($this->ACL_TABLE as $key => $value){
			#echo __METHOD__.'$value['.$this->indexBy.'] ['.$value[$this->indexBy].'] key['.$key.']<pre>'.print_r($value, true).'</pre>'.PHP_EOL;
			$this->ACL_LIST[$value[$this->indexBy]] = $value;
		}
	}
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  getRoleList
	*/
	public function getRuleList($args = null){
		#echo __METHOD__.'@'.__LINE__.''.'<br>'.PHP_EOL; 
		if(1 >= count($this->ACL_TABLE)){
			$this->getRuleTable();
		}
			
		if(1 >= count($this->ACL_LIST)){
			$this->setRuleList();
		}
		return $this->ACL_LIST;
	}
	
	/**
	* DESCRIPTOR: 
	* 
	* @param args 
	* @return return  
	*/
	public function getRule($rule = null){
		#echo __METHOD__.'@'.__LINE__.' count($this->ACL_LIST) ['.count($this->ACL_LIST).']$rule<pre>['.var_export($rule, true).']</pre>'.'<br>'.PHP_EOL; 
		if(1 >= count($this->ACL_LIST)){
			$this->getRuleList();
			#echo __METHOD__.'@'.__LINE__.'$this->getRuleList()<pre>['.var_export($this->getRuleList(), true).']</pre>'.'<br>'.PHP_EOL; 
		}
		if(null != $rule){
			if(isset($this->ACL_LIST[$rule])){
				return $this->ACL_LIST[$rule];
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
	public function getRuleTree($args = null){
		$result = $this->getRuleTable($args);
		
		$ACL_TREE = array(); 
		foreach($result AS $key => $value){
			if($value["parent_id"] == $value["access_control_list_pk"]){
				$ACL_TREE[$value["rule"]] = $value;
			}
		}
		
		#echo 'ACL_TREE<pre>'.print_r($ACL_TREE, true).'</pre>'.PHP_EOL;
		foreach($ACL_TREE AS $key => $value){
			$args2 = array(
				'result' => $result,
				'parent_id' => $value["access_control_list_pk"],
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
				$value["parent_id"] != $value["access_control_list_pk"]
			){
				echo ' TRUE '.PHP_EOL;
				$CHILD_ACL_TREE[$value["rule"]] = $value;
			}else{
				#echo ' FALSE '.PHP_EOL;
				#$CHILD_ACL_TREE[$value["rule"]] = $value["rule"];
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
				$CHILD_ACL_TREE[$value["rule"]] = $value;
			}else{
				#echo ' FALSE '.PHP_EOL;
				#$CHILD_ACL_TREE[$value["rule"]] = $value["rule"];
			}
			
		}
		return $CHILD_ACL_TREE;
		*/
	}
	

}

?>
