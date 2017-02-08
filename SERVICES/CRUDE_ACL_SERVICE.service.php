<?php
/**
 * Very basic RBAC-ACL mechanism (Role Based Access Control-Access Control List) 
 *	uses a rudimentary "tree" (parent ID reference) to give 
 *  VERY BASIC role/group based access control mimicing Apache mod_access
 *  with role/group replacing "host"
 * 
 *	https://httpd.apache.org/docs/current/mod/mod_access_compat.html
 *	
 *	The following table structure results in this "tree"
 *	with the group 'super' having 2 subgroups 'legal_adviser' & 'industry_adviser'
 *  and all other groups being undifferentiated
 *  subgroups having the option of permission "extension" or "contraction"
 * 
 *	$ACL = array (
 *	  'admin',
 *	  'super' => 
 *			array ( 'legal_adviser', 'industry_adviser'	),
 *	  'client',
 *	  'guest'
 *	);
 *	
 *  PK	Role		Parent_PK
 *	1	admin		1
 *	2	super_user	2
 *	3	client		3
 *	4	CSR			2
 *	5	Tech		2
 *	6	guest		6
 *
 *  see AUTOLOAD/acl.global.php for $perms = array() structures
 *  applied to menu item permissions in /_UI/MAIN_UI/menu.php
 *  
 *  
 * @author	Jason Medland<jason.medland@gmail.com>
 * @package	JCORE\SERVICE\AUTH
 * 
 */

namespace JCORE\SERVICE\AUTH;
/**
* we DGAF about exposing this as a public API class 
* so we don't need to extend from SOA_BASE
* use JCORE\TRANSPORT\SOA\SOA_BASE as SOA_BASE;
*/
use JCORE\SERVICE\AUTH\USER_ROLE_ENTITY as USER_ROLE_ENTITY;
use JCORE\SERVICE\AUTH\CRUDE_ACL_ENTITY as CRUDE_ACL_ENTITY;

/**
* just-core/foundation/CORE/AUTH/AUTH.interface.php
* https://chglongstone.github.io/just-core/api/classes/JCORE.AUTH.AUTH_INTERFACE.html
*/
use JCORE\AUTH\AUTH_INTERFACE as AUTH_INTERFACE;

/**
 * Class CRUDE_ACL
 * ALL PERMISSION FAILURES EVALUATE TO FALSE
 *
 * ALLOW === TRUE
 * DENY === FALSE
 * 
 * @package JCORE\SERVICE\AUTH 
*/
class CRUDE_ACL  implements AUTH_INTERFACE{ 
	/**
	* @access public 
	* @var string
	*/
	public $ROLE_LIST = null;
	/**
	* @access public 
	* @var string
	*/
	public $WORKING_RULE = null;
	/**
	* @access public 
	* @var string
	*/
	public $WORKING_ROLE = null;
	/**
	* @access public 
	* @var string
	*/
	public $ERROR_MESSAGE = null;
	/** 
	* STRICT to return FLASE by default from authorizeDeny
	* or any other value to return TRUE by default
	* this could be pulled from the config
	* 
	* @access public 
	* @var string
	*/
	public $PERMISSIVE = 'STRICT';
	
	
	/**
	* DESCRIPTOR: an empty constructor, the service MUST be called with 
	* the service name and the service method name specified in the 
	* in the method property of the JSONRPC request in this format
	* 		""method":"AJAX_STUB.aServiceMethod"
	* 
	* @param param 
	* @return return  
	*/
	public function __construct(){
		return;
	}
	/**
	* initialize 
	* @param array args 
	* @return return  
	*/
	public function init($args){
		#echo __METHOD__.'@'.__LINE__.'  '.'<br>'; 
		/**
		* acl.global.php
		*/
		#$this->config = $GLOBALS["CONFIG_MANAGER"]->getSetting('AUTH','ACL_ENTITY_CONTAINER','RULE');	
		#$this->config = $GLOBALS["CONFIG_MANAGER"]->getSetting('AUTH','ACL_ENTITY_CONTAINER','ROLE');	
		$this->CRUDE_USER_ROLE_ENTITY = new CRUDE_USER_ROLE_ENTITY();
		#echo __METHOD__.'@'.__LINE__.'  '.'<br>'; 
		#$this->ROLE_TABLE = $this->CRUDE_USER_ROLE_ENTITY->getRoleTable();
		#$this->ROLE_TREE = $this->CRUDE_USER_ROLE_ENTITY->getRoleTree();
		$this->ROLE_LIST = $this->CRUDE_USER_ROLE_ENTITY->getRoleList();
		#echo __METHOD__.'@'.__LINE__.'$this->ROLE_LIST<pre>['.var_export($this->ROLE_LIST, true).']</pre>'.'<br>'; 
		/***/
		$this->CRUDE_ACL_ENTITY = new CRUDE_ACL_ENTITY();
		#$this->RULE_TABLE = $this->CRUDE_ACL_ENTITY->getRuleTable();
		#$this->RULE_TREE = $this->CRUDE_ACL_ENTITY->getRuleTree();
		$this->RULE_LIST = $this->CRUDE_ACL_ENTITY->getRuleList();
		#echo __METHOD__.'@'.__LINE__.'$this->RULE_LIST<pre>['.var_export($this->RULE_LIST, true).']</pre>'.'<br>'; 
		/***/
		$this->ACL_SCHEMES = $GLOBALS["CONFIG_MANAGER"]->getSetting('AUTH','CRUDE_ACL_SCHEMES');
		
		
		return;
	}
	
	/**
	* authenticate
	* 
	* @params array 
	* @return bool  
	*/
	public function authenticate($params = null){
		if(isset($_SESSION['role_id'])){
			
			return true;
		}
		return false;
		
	}
	
	
	/**
	* DESCRIPTOR: authorize 
	* https://httpd.apache.org/docs/current/mod/mod_access_compat.html#order
	* Allow,Deny
	*	First, all Allow directives are evaluated; at least one must match, 
	*	or the request is rejected. Next, all Deny directives are evaluated. 
	*	If any matches, the request is rejected. Last, any requests which do 
	*	not match an Allow or a Deny directive are denied by default.
	* 
	* Deny,Allow
	*	First, all Deny directives are evaluated; if any match, the request is 
	*	denied unless it also matches an Allow directive. Any requests which do 
	*	not match any Allow or Deny directives are permitted.
	* 
	* @params array 
	* @return this->serviceResponse  
	*/
	public function authorize($params = null){
		#echo __METHOD__.'@'.__LINE__.'$args<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		$this->init($params);
		$_SESSION['role_id'];
		if(!isset($params["role"])){
			return false;
		}
		
		
		if(!isset($params["rule"])){
			return false;
		}
		$this->WORKING_ROLE = $this->CRUDE_USER_ROLE_ENTITY->getRole($params["role"]);
		if(is_numeric($params["role"])){
			#echo __METHOD__.'@'.__LINE__.'$args<pre>['.var_export($params, true).']</pre>'.'<br>'; 
			$params["role"] = $this->CRUDE_USER_ROLE_ENTITY->getRoleName($params["role"]);
		}
		#echo __METHOD__.'@'.__LINE__.'$args<pre>['.var_export($params, true).']</pre>'.'<br>'.PHP_EOL; 
		#$params["rule"] = 
		#echo __METHOD__.'@'.__LINE__.'$workingRole<pre>['.var_export($workingRole, true).']</pre>'.'<br>'; 

		$this->WORKING_RULE = $this->CRUDE_ACL_ENTITY->getRule($params["rule"]);
		#echo __METHOD__.'@'.__LINE__.'$this->WORKING_ROLE<pre>['.var_export($this->WORKING_ROLE, true).']</pre>'.'<br>'.PHP_EOL; 
		#echo __METHOD__.'@'.__LINE__.'$this->WORKING_RULE<pre>['.var_export($this->WORKING_RULE, true).']</pre>'.'<br>'.PHP_EOL; 
		$authtest = false;
		
		switch(strtoupper($params["order"])){//authType
			case "ALLOW":
			case "ALLOW,DENY":
				$authtest = $this->authorizeAllowDeny($params);
				break;
			case "DENY":
			case "DENY,ALLOW":
				$authtest = $this->authorizeDenyAllow($params);
				break;
			default:
				return false;
				break;
		}
		#echo __METHOD__.'@'.__LINE__.'$authtest<pre>['.var_export($authtest, true).']</pre>'.'<br>'.PHP_EOL; 
		return $authtest;
	}
	/**
	* DESCRIPTOR: authorizeAllowDeny 
	*  https://httpd.apache.org/docs/current/mod/mod_access_compat.html#order
	*  First, all Allow directives are evaluated; at least one must match, 
	*  or the request is rejected. Next, all Deny directives are evaluated. 
	*  If any matches, the request is rejected. Last, any requests which do not 
	*  match an Allow or a Deny directive are denied by default.
	* 
	* @args array 
	* @return this->serviceResponse  
	*/
	public function authorizeAllowDeny($params = null){
		#echo __METHOD__.'@'.__LINE__.'$args<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		
		if(TRUE === $this->authorizeAllow($params)){
			#echo __METHOD__.'@'.__LINE__.'$params<pre>['.var_export($params, true).']</pre>'.'<br>'; 
			return TRUE;
		}
		
		if(TRUE === $this->authorizeDeny($params)){
			return TRUE;
		}
		$this->getErrorMessage($params);
		return FALSE;
		
	}
	/**
	* DESCRIPTOR: an example namespace call 
	*  https://httpd.apache.org/docs/current/mod/mod_access_compat.html#order
	*  First, all Deny directives are evaluated; if any match, the request is denied 
	*  unless it also matches an Allow directive. Any requests which do not match 
	*  any Allow or Deny directives are permitted.
	* 
	* @args array 
	* @return this->serviceResponse  
	*/
	public function authorizeDenyAllow($params = null){
		#echo __METHOD__.'@'.__LINE__.'$args<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		
		if(FALSE === $this->authorizeDeny($params)){
			if(TRUE === $this->authorizeAllow($params)){
				return TRUE;
			}
			
			$this->getErrorMessage($params);
			return FALSE;
		}
	
		return TRUE;
	}
	/**
	* DESCRIPTOR:  authorizeAllow
	*  https://httpd.apache.org/docs/current/mod/mod_access_compat.html#order
	* 
	* Look for the role in the allow list
	* if the role exists in the allow list return true
	* 
	* DEFAULT IS FALSE
	* 
	* @params array 
	* @return bool  
	*/
	public function authorizeAllow($params = null){
		#echo __METHOD__.'@'.__LINE__.'$params<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		#echo __METHOD__.'@'.__LINE__.'$this->WORKING_RULE<pre>['.var_export($this->WORKING_RULE, true).']</pre>'.'<br>'; 
		#$this->WORKING_ROLE; $this->WORKING_RULE;
		
		if('allow' == $params["order"]){
			if(true === in_array($params["role"], $this->WORKING_RULE[$params["order"]]) ){
				return true;
			}
			if(true === in_array('all', $this->WORKING_RULE[$params["order"]]) ){
				return true;
			}
			/**
			* just for clairity
			*/
			if(true === in_array('none', $this->WORKING_RULE[$params["order"]]) ){
				return false;
			}
			
		}
		#echo __METHOD__.'@'.__LINE__.'$params<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		return FALSE;
	}
	/**
	* DESCRIPTOR: authorizeDeny
	*  https://httpd.apache.org/docs/current/mod/mod_access_compat.html#order
	* 
	* Look for the role in the deny list
	* if the role exists in the deny list return deny
	* 
	* DEFAULT IS TRUE
	* 
	* @args array 
	* @return bool 
	*/
	public function authorizeDeny($params = null){
		#echo __METHOD__.'@'.__LINE__.'$params<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		$this->WORKING_ROLE; $this->WORKING_RULE;
		
		if('deny' == $params["order"]){
			if(true === in_array($params["role"], $this->WORKING_RULE[$params["order"]]) ){
				return false;
			}
			if(true === in_array('all', $this->WORKING_RULE[$params["order"]]) ){
				return false;
			}
			/**
			* just for clairity
			*/
			if(true === in_array('none', $this->WORKING_RULE[$params["order"]]) ){
				return true;
			}
		}
		if('STRICT' == $this->PERMISSIVE){
			return false;
		}
		return true;
	}
	/**
	* getErrorMessage
	* @args array 
	* @return bool 
	*/
	public function getErrorMessage($params = null){
		if(isset($this->WORKING_RULE[$params["order"]]["rule_fail_msg"])){
			$this->ERROR_MESSAGE = $this->WORKING_RULE[$params["order"]]["rule_fail_msg"];
			return;
		}
		$this->ERROR_MESSAGE = 'failed authorization';
		return;
		
	}
	
}



?>