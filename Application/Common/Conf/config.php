<?php
if(DEV){
	return array(
		'LOAD_EXT_CONFIG'		=>	'db,oauth_dev,partner_dev,signin_dev,live_dev'
	);
}else{
	return array(
		'LOAD_EXT_CONFIG'		=>	'db,oauth,partner,signin,live'
	);
}