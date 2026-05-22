<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pendidikan extends AdminController {

	public function __construct() {
		parent::__construct();	
	}	
	
	public function index() {
		$this->data['judul_browser'] = 'Pendidikan';
		$this->data['judul_utama'] = 'Pendidikan';
		$this->data['judul_sub'] = 'Master Pendidikan';

		$this->output->set_template('gc');

		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		//$crud->set_theme('datatables');
		$crud->set_table('mst_pendidikan');
		$crud->set_subject('Master Pendidikan');

		$crud->columns('no_ordered', 'nama_pendidikan');
		$crud->fields('no_ordered', 'nama_pendidikan');
	
		$crud->display_as('no_ordered','No Urut');
		$crud->display_as('nama_pendidikan','Nama Pendidikan');
		$crud->required_fields('no_ordered','nama_pendidikan');

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
