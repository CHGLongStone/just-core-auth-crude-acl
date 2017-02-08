<?php 
/**
* This is basically deprecated by CRUDE_ACL and the access_control_list table
* pretty much following the apache example with various levels of access 
* from un-permissive to very permissive
*  - processing order
*  - deny role(s)
*  - allow role(s)
* 
* see the package https://github.com/CHGLongStone/just-core-stub/
* and this file https://github.com/CHGLongStone/just-core-stub/blob/master/data/schema/schema.sql
* for the example data structures 
* 
* 
* 
* container -> user 
*/

return array(
	'AUTH' => array(
		'CRUDE_ACL_SCHEMES' => array(
			'VERY_STRICT' => array(
				'Order' => 'Deny,Allow',
				'Deny' => 'All',
				'Allow' => 'admin',
			),
			'MORE_STRICT' => array(
				'Order' => 'Deny,Allow',
				'Deny' => 'All',
				'Allow' => 'admin,super',
			),
			'STRICT' => array(
				'Order' => 'Deny,Allow',
				'Deny' => 'All',
				'Allow' => 'admin,super,legal_adviser,industry_adviser',
			),
			'PERMISSIVE' => array(
				'Order' => 'Allow,Deny',
				'Deny' => 'guest',
				'Allow' => 'All',
			),
			'VERY_PERMISSIVE' => array(
				'Order' => 'Allow,Deny',
				'Deny' => 'All',
				'Allow' => 'All',
			),
			
		),
		/**
		* see the package just-core/stub
		*/
		'ACL_ENTITY_CONTAINER' => array(
			'ROLE' => array(
				'DSN' => 'JCORE',
				'table' => 'user_role',
				'pk_field' => 'user_role_pk',
				'fk_field' => 'user_role_fk',
				'user_role' => 'role',
			);
			'RULE' => array(
				'DSN' => 'JCORE',
				'SELECT' => '*, rule_name AS rule',
				'table' => 'access_control_list',
				'pk_field' => 'access_control_list_pk',
				#'fk_field' => 'access_control_list_fk',
				'user_rule' => 'rule_name',
			);
		),
	),
);

?>