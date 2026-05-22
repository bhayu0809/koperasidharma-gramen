<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_pemasukan_harian extends OperatorController {

	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_simpanan_m');
	}	

	public function index() {
		// $data_pinjaman = $this->db->get('v_hitung_pinjaman')->result();
		
		// foreach($data_pinjaman AS $v){
			// $this->db->where('id', $v->id);
			// $this->db->update('tbl_pinjaman_d',array('jml_pokok_angsuran' => $v->pokok_angsuran));
		// }
		// die();
		error_reporting(0);
		$this->load->library("pagination");

		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Pemasukan Harian';

		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/default/easyui.css';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/icon.css';
		$this->data['js_files'][] = base_url() . 'assets/easyui/jquery.easyui.min.js';

		#include tanggal
		$this->data['css_files'][] = base_url() . 'assets/extra/bootstrap_date_time/css/bootstrap-datetimepicker.min.css';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/bootstrap-datetimepicker.min.js';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/locales/bootstrap-datetimepicker.id.js';

			#include seach
		$this->data['css_files'][] = base_url() . 'assets/theme_admin/css/daterangepicker/daterangepicker-bs3.css';
		$this->data['js_files'][] = base_url() . 'assets/theme_admin/js/plugins/daterangepicker/daterangepicker.js';

		$config = array();
		$config["base_url"] = base_url() . "lap_simpanan/index/halaman";
		$config["total_rows"] = $this->lap_simpanan_m->get_jml_data_simpan(); // banyak data
		$config["per_page"] = 10;
		$config["uri_segment"] = 4;
		$config['use_page_numbers'] = TRUE;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['first_link'] = '&laquo; First';
		$config['first_tag_open'] = '<li class="prev page">';
		$config['first_tag_close'] = '</li>';

		$config['last_link'] = 'Last &raquo;';
		$config['last_tag_open'] = '<li class="next page">';
		$config['last_tag_close'] = '</li>';

		$config['next_link'] = 'Next &rarr;';
		$config['next_tag_open'] = '<li class="next page">';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = '&larr; Previous';
		$config['prev_tag_open'] = '<li class="prev page">';
		$config['prev_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['num_tag_open'] = '<li class="page">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		if($offset > 0) {
			$offset = ($offset * $config['per_page']) - $config['per_page'];
		}
		$this->data["data_jns_simpanan"] = $this->lap_simpanan_m->get_data_jenis_simpan($config["per_page"], $offset); // panggil seluruh data aanggota
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;
		
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}		
		
		$data_area = array();
		$data = array();
		
		if($_GET['load']){
			$tgl_mulai = date("Y-m-d",strtotime($_GET['tgl_mulai']));
			$area = $this->db->get("mst_area")->result();
			
			foreach($area AS $v){
				$data_area[$v->nama_area] = $v->nama_area;
			}
			$select = "
				SELECT b.id, b.nama,b.identitas,b.departement,simpanan_pokok,simpanan_wajib,simpanan_sukarela,v2.tgl_transaksi,jumlah_bayar,jasa FROM tbl_anggota AS b 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '40' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '41' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
				
				LEFT JOIN ((SELECT COALESCE(SUM(jml_pokok_angsuran),0) AS jumlah_bayar,COALESCE(SUM(jml_jasa_angsuran),0) AS jasa, v_hitung_pinjaman.anggota_id AS angid FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE DATE(tgl_bayar) = '".$tgl_mulai."' GROUP BY v_hitung_pinjaman.anggota_id) AS v5  ) ON v5.angid = b.id 

				WHERE simpanan_pokok > 0 OR simpanan_wajib > 0 OR simpanan_sukarela > 0 OR jumlah_bayar > 0 OR jasa > 0
				ORDER BY `b`.`identitas` ASC 
			";
				
			$pasiens = $this->db->query($select)->result();
			//var_dump($select);
			//var_dump($data_area);die();
			// $pasiens = $this->db->select("b.id, b.nama,b.identitas,b.departement")
			// ->order_by("b.identitas",'ASC')			
			// ->group_by("id")
			// ->get("tbl_anggota AS b")
			// ->result();
			
			foreach($pasiens AS $v2){
				$total = $pokok->simpanan_pokok + $wajib->simpanan_wajib +$sukarela->simpanan_sukarela + $jasa->jasa + $cicilan->jumlah_bayar;
				//if($total > 0){
					
					if(isset($data_area[$v2->departement])){
						$data[$v2->departement][] = array(
							'nama_anggota' => $v2->nama,
							'identitas' => $v2->identitas,
							'simpanan_pokok' => $v2->simpanan_pokok,
							'simpanan_wajib' => $v2->simpanan_wajib,
							'simpanan_sukarela' => $v2->simpanan_sukarela,
							'cicilan' => $v2->jumlah_bayar,
							'jasa' => $v2->jasa,
						);
					}				
				//}				
			}
		}
		
		if(isset($_GET['cetak'])){
			$this->cetak_print($_GET);
		}
		$this->data["data_hasil"] = $data;
		 
		$this->data['isi'] = $this->load->view('lap_pemasukan_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$simpanan = $this->lap_simpanan_m->lap_jenis_simpan();
		if($simpanan == FALSE) {
			echo 'DATA KOSONG';
			//redirect('lap_simpanan');
			exit();
		}

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}		
		
			$area = $this->db->get("mst_area")->result();
			$data_area = array();
			foreach($area AS $v){
				$data_area[$v->nama_area] = $v->nama_area;
			}
			
			
			$select = "
				SELECT b.id, b.nama,b.identitas,b.departement,simpanan_pokok,simpanan_wajib,simpanan_sukarela,v2.tgl_transaksi,jumlah_bayar,jasa FROM tbl_anggota AS b 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '40' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '41' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
				
				LEFT JOIN ((SELECT COALESCE(SUM(jml_pokok_angsuran),0) AS jumlah_bayar,COALESCE(SUM(jml_jasa_angsuran),0) AS jasa, v_hitung_pinjaman.anggota_id AS angid FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE DATE(tgl_bayar) = '".$tgl_mulai."' GROUP BY v_hitung_pinjaman.anggota_id) AS v5  ) ON v5.angid = b.id 

				WHERE simpanan_pokok > 0 OR simpanan_wajib > 0 OR simpanan_sukarela > 0 OR jumlah_bayar > 0 OR jasa > 0
				ORDER BY `b`.`identitas` ASC 
			";
			$pasiens = $this->db->query($select)->result();
			
			$data_hasil = array();
			foreach($pasiens AS $v2){
				if(isset($data_area[$v2->departement])){
					
					$total = $pokok->simpanan_pokok + $wajib->simpanan_wajib +$sukarela->simpanan_sukarela + $jasa->jasa + $cicilan->jumlah_bayar;
					if(isset($data_area[$v2->departement])){
						$data_hasil[$v2->departement][] = array(
							'nama_anggota' => $v2->nama,
							'identitas' => $v2->identitas,
							'simpanan_pokok' => $v2->simpanan_pokok,
							'simpanan_wajib' => $v2->simpanan_wajib,
							'simpanan_sukarela' => $v2->simpanan_sukarela,
							'cicilan' => $v2->jumlah_bayar,
							'jasa' => $v2->jasa,
						);
					}						
				}				
			}
		 
		$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
		$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
		$tgl_periode_txt = $tgl_dari_txt . ' - ' . $tgl_samp_txt;

		$this->load->library('Pdf');

		$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->set_nsi_header(TRUE);
		$pdf->AddPage('P');
		$html = '
		<style>
			.h_tengah {text-align: center;}
			.h_kiri {text-align: left;}
			.h_kanan {text-align: right;}
			.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
			.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
			th{background-color:#eee}
		</style>
		'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Transaksi Harian (Pemasukan)</span><p style="text-align:center;font-weight:bold"> Per Tanggal. '.$tgl_periode_txt.' </p>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		$total_pokok = 0;
		$total_wajib = 0;
		$total_sukarela = 0;
		$total_cicilan = 0;
		$total_jasa = 0;
		
		$html .= '<table  border="1" class="table table-bordered">';
		foreach($data_hasil AS $k=>$v){
				$html .= '<p style="text-align:left; font-size: 10pt; font-weight: bold;"> Area : '.ucwords(strtolower($k)).'</p>';
		
				$html .='<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center; width:30px" rowspan="2" > No. </th>
					<th style="vertical-align: middle; text-align:center;width:30px" rowspan="2">NBK</th>
					<th style="vertical-align: middle; text-align:center;width:150px" rowspan="2" >Nama Anggota </th>
					<th style="vertical-align: middle; text-align:center" colspan="3"> Simpanan  </th>
					<th style="vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				</tr>
				<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center" > Pokok</th>
					<th style="vertical-align: middle; text-align:center">Wajib </th>
					<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
					<th style="vertical-align: middle; text-align:center"> Cicilan  </th>
					<th style="vertical-align: middle; text-align:center"> Jasa  </th>
				</tr>';
			$no = 1;
			$subtotal_pokok = 0;
			$subtotal_wajib = 0;
			$subtotal_sukarela = 0;
			$subtotal_cicilan = 0;
			$subtotal_jasa = 0;
			foreach($v AS $k=>$v2){
				$html .= '<tr>
					<td style="text-align:center">'.$no.'</td>
					<td style="text-align:center">&nbsp;'.$v2['identitas'].'</td>
					<td style="padding:10px;">&nbsp;'.$v2['nama_anggota'].'</td>
					<td style="text-align:right;padding:10">'.number_format($v2['simpanan_pokok']).'<span style="color:white;"></span></td>
					<td style="text-align:right">'.number_format($v2['simpanan_wajib']).'<span style="color:white;"></span></td>
					<td style="text-align:right">'.number_format($v2['simpanan_sukarela']).'<span style="color:white;"></span></td>
					<td style="text-align:right">'.number_format($v2['cicilan']).'<span style="color:white;"></span></td>
					<td style="text-align:right">'.number_format($v2['jasa']).'<span style="color:white;"></span></td>
					
				</tr>';
				$v2['jasa'] = str_replace(',','',number_format($v2['jasa']));
				$subtotal_pokok += $v2['simpanan_pokok'];
				$subtotal_wajib += $v2['simpanan_wajib'];
				$subtotal_sukarela += $v2['simpanan_sukarela'];
				$subtotal_cicilan += $v2['cicilan'];
				$subtotal_jasa += $v2['jasa'];
				$no++;
			}
			
			$html .='<tr style="font-weight:bold">
				<td></td>
				<td></td>
				<td>Sub Total</td>
				<td style="text-align:right">'.number_format($subtotal_pokok).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($subtotal_wajib).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($subtotal_sukarela).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($subtotal_cicilan).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($subtotal_jasa).'<span style="color:white;"></span></td>			
			</tr>';
			
			$total_pokok += $subtotal_pokok;
			$total_wajib += $subtotal_wajib;
			$total_sukarela += $subtotal_sukarela;
			$total_cicilan += $subtotal_cicilan;
			$total_jasa += $subtotal_jasa;
		}
		
		$html .='<tr style="font-weight:bold">
			<td></td>
			<td></td>
			<td>Total</td>
			<td style="text-align:right">'.number_format($total_pokok).'<span style="color:white;"></span></td>
			<td style="text-align:right">'.number_format($total_wajib).'<span style="color:white;"></span></td>
			<td style="text-align:right">'.number_format($total_sukarela).'<span style="color:white;"></span></td>
			<td style="text-align:right">'.number_format($total_cicilan).'<span style="color:white;"></span></td>
			<td style="text-align:right">'.number_format($total_jasa).'<span style="color:white;"></span></td>
		</tr>';
		$html .= '</table>';

		$pdf->nsi_html($html);
		$pdf->Output('lap_pemasukan_harian'.date('Ymd_His') . '.pdf', 'I');
	}
	
	function cetak_print() {
		$simpanan = $this->lap_simpanan_m->lap_jenis_simpan();
		if($simpanan == FALSE) {
			echo 'DATA KOSONG';
			//redirect('lap_simpanan');
			exit();
		}
		
			$tgl_mulai = date("Y-m-d",strtotime($_GET['tgl_mulai']));
		
			$area = $this->db->get("mst_area")->result();
			$data_area = array();
			foreach($area AS $v){
				$data_area[$v->nama_area] = $v->nama_area;
			}
			
			
			$select = "
				SELECT b.id, b.nama,b.identitas,b.departement,simpanan_pokok,simpanan_wajib,simpanan_sukarela,v2.tgl_transaksi,jumlah_bayar,jasa FROM tbl_anggota AS b 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '40' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '41' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


				LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk='D' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
				
				LEFT JOIN ((SELECT COALESCE(SUM(jml_pokok_angsuran),0) AS jumlah_bayar,COALESCE(SUM(jml_jasa_angsuran),0) AS jasa, v_hitung_pinjaman.anggota_id AS angid FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE DATE(tgl_bayar) = '".$tgl_mulai."' GROUP BY v_hitung_pinjaman.anggota_id) AS v5  ) ON v5.angid = b.id 

				WHERE simpanan_pokok > 0 OR simpanan_wajib > 0 OR simpanan_sukarela > 0 OR jumlah_bayar > 0 OR jasa > 0
				ORDER BY `b`.`identitas` ASC 
			";
			$pasiens = $this->db->query($select)->result();
			
			$data_hasil = array();
			foreach($pasiens AS $v2){
				if(isset($data_area[$v2->departement])){				
					if(isset($data_area[$v2->departement])){
						$data_hasil[$v2->departement][] = array(
							'nama_anggota' => $v2->nama,
							'identitas' => $v2->identitas,
							'simpanan_pokok' => $v2->simpanan_pokok,
							'simpanan_wajib' => $v2->simpanan_wajib,
							'simpanan_sukarela' => $v2->simpanan_sukarela,
							'cicilan' => $v2->jumlah_bayar,
							'jasa' => $v2->jasa,
						);
					}				
				}				
			}
		
		
		$tgl_dari_txt = jin_date_ina($tgl_mulai, 'p');
		$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
		$tgl_periode_txt = $tgl_dari_txt;

		$html = " <style>@page { width:1200px; margin-top: 30px;margin-bottom: 28px; }tr,td,th{border:1px solid black;border-collapse:collapse}table{border-collapse:collapse}";
		$html .= "
			table { page-break-after:auto }
			tr    { page-break-inside:avoid; page-break-after:auto }
			td    { page-break-inside:avoid; page-break-after:auto }
			thead { display:table-header-group }
			tfoot { display:table-footer-group }";
		$html .= "</style>";
		$html .= "<div class='header' style='float:left'>";
		$html .= "<img style='float:left'src='".base_url()."assets/theme_admin/img/logo.png' width='100' height='100' alt='logo' />";
		$html .= "<div style='font-size:16px'><br/>KOPERASI DWP BINA SEJAHTERA</div>";
		$html .= "<div>JL. KISAMAUN NO.1 KEL.SUKARASA</div>";
		$html .= "<div>Tel.021-55795402 <br/>Email :</div>";
		$html .= "<div/>";
		$html .= "<div style='clear:both'></div>";
		$html .= "<hr/>";
		
		$html .= "<center><h2>Laporan Transaksi Harian (Pemasukan)</h2>";
		$html .= "<h4>Pertanggal ".$tgl_periode_txt ."</h4></center>";
		
		$total_pokok = 0;
		$total_wajib = 0;
		$total_sukarela = 0;
		$total_cicilan = 0;
		$total_jasa = 0;
		
		$html .= '<table  style="font-size:10pt;width:700px;" >';
		foreach($data_hasil AS $k=>$v){
				$html .= '<tr style="border:none"><td style="border:none;vertical-align:bottom" colspan="5"><br/><h5> Area :'.ucwords(strtolower($k)).' </h5></td></tr>';
		
				$html .='<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center; width:30px" rowspan="2" > No. </th>
					<th style="vertical-align: middle; text-align:center;width:30px" rowspan="2">NBK</th>
					<th style="vertical-align: middle; text-align:center;width:170px" rowspan="2" >Nama Anggota </th>
					<th style="vertical-align: middle; text-align:center" colspan="3"> Simpanan  </th>
					<th style="vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				</tr>
				<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center" > Pokok</th>
					<th style="vertical-align: middle; text-align:center">Wajib </th>
					<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
					<th style="vertical-align: middle; text-align:center"> Cicilan  </th>
					<th style="vertical-align: middle; text-align:center"> Jasa  </th>
				</tr>';
			$no = 1;
			$subtotal_pokok = 0;
			$subtotal_wajib = 0;
			$subtotal_sukarela = 0;
			$subtotal_cicilan = 0;
			$subtotal_jasa = 0;
			foreach($v AS $k=>$v2){
				$html .= '<tr>
					<td style="text-align:center">'.$no.'</td>
					<td style="text-align:center;">'.$v2['identitas'].'</td>
					<td style="padding:5px;">&nbsp;'.$v2['nama_anggota'].'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_pokok']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_wajib']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_sukarela']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['cicilan']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['jasa']).'</td>
					
				</tr>';
				$v2['jasa'] = str_replace(',','',number_format($v2['jasa']));
				$subtotal_pokok += $v2['simpanan_pokok'];
				$subtotal_wajib += $v2['simpanan_wajib'];
				$subtotal_sukarela += $v2['simpanan_sukarela'];
				$subtotal_cicilan += $v2['cicilan'];
				$subtotal_jasa += $v2['jasa'];
				$no++;
			}
			
			$html .='<tr style="font-weight:bold;">
				<td></td>
				<td></td>
				<td>Sub Total</td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_pokok).'<span style="color:white;"></span></td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_wajib).'<span style="color:white;"></span></td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_sukarela).'<span style="color:white;"></span></td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_cicilan).'<span style="color:white;"></span></td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_jasa).'<span style="color:white;"></span></td>			
			</tr>';
			
			$total_pokok += $subtotal_pokok;
			$total_wajib += $subtotal_wajib;
			$total_sukarela += $subtotal_sukarela;
			$total_cicilan += $subtotal_cicilan;
			$total_jasa += $subtotal_jasa;
		}
		
		$html .='<tr style="font-weight:bold">
			<td></td>
			<td></td>
			<td>Total</td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_pokok).'<span style="color:white;"></span></td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_wajib).'<span style="color:white;"></span></td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_sukarela).'<span style="color:white;"></span></td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_cicilan).'<span style="color:white;"></span></td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_jasa).'<span style="color:white;"></span></td>
		</tr>';
		$html .= '</table>';

		echo $html;
		echo "<script>window.print();</script>";
		die();
	}
}