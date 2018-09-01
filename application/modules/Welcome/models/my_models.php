<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_models extends Ng_model 
{
	
	function get_user($userid)
	{
		$this->db->select('*');

		$qry = $this->db->get('user');
		
			return $qry;
		
	}
}