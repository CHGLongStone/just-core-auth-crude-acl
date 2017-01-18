<?php 
/**
* This is basically deprecated by CRUDE_ACL and the access_control_list table
*
*/

return array(
    'ACL_SCHEMES' => array(
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
);

?>