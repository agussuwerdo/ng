<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store_model extends Ng_Model {

	function __construct() 
	{
        parent::__construct();
		$this->set_schema('public');
		$this->set_table('msStore');
		$this->set_pk(array('storecode','name'));
    }	
	
}