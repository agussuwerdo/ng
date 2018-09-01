<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MX_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->model('my_models');
		$this->load->model('store_model');
		$this->load->view('welcome_message');
		
		$user = $this->my_models->get_user(1);
		
		// print_r($user);
		// $str = $this->store_model->get(array('storecode'=>'1X','name'=>'TRIAL1'));
		$whr = array();
		// $whr['storecode'] = '1X';
		// $whr['name'] = 'TRIAL1';
		$whr['description'] = 'gass';
		$this->store_model->set_where($whr);
		// $str = $this->store_model->get($whr);
		$str = $this->store_model->get_list();
		print_r($str);
	}
}
