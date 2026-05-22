<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pekerjaan extends AdminController {

	public function __construct() {
		parent::__construct();	
	}	
	
	public function index() {
		$this->data['judul_browser'] = 'Pekerjaan';
		$this->data['judul_utama'] = 'Pekerjaan';
		$this->data['judul_sub'] = 'Master Pekerjaan';

		$this->output->set_template('gc');

		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->set_table('pekerjaan');
		$crud->set_subject('Master Pekerjaan');

		$crud->columns('id_kerja', 'jenis_kerja');
		$crud->fields('id_kerja', 'jenis_kerja');
	
		$crud->display_as('id_kerja','No');
		$crud->display_as('jenis_kerja','Nama Pekerjaan');
		$crud->required_fields('jenis_kerja');

		$crud->unset_read();
		$output = $crud->render();

		$out['output'] = $this->data['judul_browser'];
		$this->load->section('judul_browser', 'default_v', $out);
		$out['output'] = $this->data['judul_utama'];
		$this->load->section('judul_utama', 'default_v', $out);
		$out['output'] = $this->data['judul_sub'];
		$this->load->section('judul_sub', 'default_v', $out);
		$out['output'] = $this->data['u_name'];
		$this->load->section('u_name', 'default_v', $out);

		$this->load->view('default_v', $output);
		

	}

}
