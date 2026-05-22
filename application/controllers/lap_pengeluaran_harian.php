<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_pengeluaran_harian extends OperatorController {

	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_simpanan_m');
	}	

	public function index() {
		$this->load->library("pagination");

		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'pengeluaran Harian';

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
		
		if($_GET['load']){
			$tgl_mulai = date("Y-m-d",strtotime($_GET['tgl_mulai']));
			$area = $this->db->get("mst_area")->result();
			$data_area = array();
			foreach($area AS $v){
				$data_area[$v->nama_area] = $v->nama_area;
			}
			
			$select = "
					SELECT b.id, b.nama,b.identitas,b.departement,simpanan_pokok,simpanan_wajib,simpanan_sukarela,jumlah FROM tbl_anggota AS b 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND jenis_id = '40' AND dk = 'K'
					GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND jenis_id = '41' AND dk = 'K' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk = 'K' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
					
					LEFT JOIN ((SELECT tgl_pinjam,COALESCE(SUM(jumlah),0) AS jumlah, anggota_id AS angid FROM tbl_pinjaman_h WHERE DATE(tgl_pinjam) = '".$tgl_mulai."' GROUP BY anggota_id) AS v5  ) ON v5.angid = b.id 
					
					
					WHERE simpanan_pokok > 0 OR simpanan_wajib > 0 OR simpanan_sukarela > 0  OR jumlah > 0
					ORDER BY `b`.`identitas` ASC 
				";
			
			$pasiens = $this->db->query($select)->result();
			
			$data = array();
			foreach($pasiens AS $v2){
				$total = $pokok->simpanan_pokok + $wajib->simpanan_wajib +$sukarela->simpanan_sukarela + $pinjaman->jumlah;
				if(isset($data_area[$v2->departement])){
					$data[$v2->departement][] = array(
						'nama_anggota' => $v2->nama,
						'identitas' => $v2->identitas,
						'simpanan_pokok' => $v2->simpanan_pokok,
						'simpanan_wajib' => $v2->simpanan_wajib,
						'simpanan_sukarela' => $v2->simpanan_sukarela,
						'pinjaman' => $v2->jumlah,
					);
				}				
			}
		}			

		if(isset($_GET['cetak'])){
			$this->cetak_print($_GET);
		}		
		$this->data["data_hasil"] = $data;
		
		$this->data['isi'] = $this->load->view('lap_pengeluaran_list_v', $this->data, TRUE);
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
			$pasiens = $this->db->select("b.id, b.nama,b.identitas,b.departement,(CASE 
				WHEN a.jenis_id = 40 THEN SUM(a.jumlah)
				ELSE 0
				END) 
				as simpanan_pokok,
				(CASE 
				WHEN a.jenis_id = 41 THEN SUM(a.jumlah)
				ELSE 0
				END) 
				as simpanan_wajib,
				(CASE 
				WHEN a.jenis_id = 32 THEN SUM(a.jumlah)
				ELSE 0
				END) 
				as simpanan_sukarela
			"
			)
			->join("tbl_trans_sp a","a.anggota_id=b.id","left")
			->order_by("b.identitas","ASC")
			->group_by("b.id")
			->get("tbl_anggota AS b")
			->result();
				
			$data_hasil = array();
			foreach($pasiens AS $v2){
				if(isset($data_area[$v2->departement])){
					$pinjaman = $this->db->select("SUM(jumlah) AS jumlah")
							->where('DATE(h.tgl_pinjam) >= ', ''.$tgl_dari.'')
							->where('DATE(h.tgl_pinjam) <= ', ''.$tgl_samp.'')
							->where('h.anggota_id', ''.$v2->id.'')
							->get("tbl_pinjaman_h h")	
							->row();
							
					$pokok = $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('DATE(d.tgl_transaksi) >= ', ''.$tgl_dari.'')
						->where('DATE(d.tgl_transaksi) <= ', ''.$tgl_samp.'')
						->where('d.anggota_id', ''.$v2->id.'')
						->where('d.jenis_id', '40')
						->where('d.dk', 'K')
						->get("tbl_trans_sp d")	
						->row();
					
					$wajib= $this->db->select("SUM(jumlah) AS simpanan_wajib")
						->where('DATE(d.tgl_transaksi) >= ', ''.$tgl_dari.'')
						->where('DATE(d.tgl_transaksi) <= ', ''.$tgl_samp.'')
						->where('d.anggota_id', ''.$v2->id.'')
						->where('d.dk', 'K')
						->where('d.jenis_id', '41')
						->get("tbl_trans_sp d")	
						->row();
						
					
					
					$sukarela= $this->db->select("SUM(jumlah) AS simpanan_sukarela")
						->where('DATE(d.tgl_transaksi) >= ', ''.$tgl_dari.'')
						->where('DATE(d.tgl_transaksi) <= ', ''.$tgl_samp.'')
						->where('d.anggota_id', ''.$v2->id.'')
						->where('d.jenis_id', '32')
						->where('d.dk', 'K')
						->get("tbl_trans_sp d")	
						->row();
					
					$total = $pokok->simpanan_pokok + $wajib->simpanan_wajib +$sukarela->simpanan_sukarela + $pinjaman->jumlah;
					if($total > 0){
						if(isset($data_area[$v2->departement])){
							$data_hasil[$v2->departement][] = array(
								'nama_anggota' => $v2->nama,
								'identitas' => $v2->identitas,
								'simpanan_pokok' => (isset($pokok->simpanan_pokok) ? $pokok->simpanan_pokok : 0 ),
								'simpanan_wajib' => (isset($wajib->simpanan_wajib) ? $wajib->simpanan_wajib : 0 ),
								'simpanan_sukarela' => (isset($sukarela->simpanan_sukarela) ? $sukarela->simpanan_sukarela : 0 ),
								'pinjaman' => (isset($pinjaman->jumlah) ? $pinjaman->jumlah : 0 ),
							);
						}				
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
		'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Transaksi Harian (pengeluaran)</span><p style="text-align:center;font-weight:bold"> Per Tanggal. '.$tgl_periode_txt.' </p>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		
		$total_pokok = 0;
		$total_wajib = 0;
		$total_sukarela = 0;
		$total_cicilan = 0;
		$total_jasa = 0;
		$total_pinjaman = 0;
		
		$html .= '<table  border="1" class="table table-bordered">';
		foreach($data_hasil AS $k=>$v){
				$html .= '<p style="text-align:left; font-size: 10pt; font-weight: bold;"> Area : '.ucwords(strtolower($k)).'</p>';
		
				$html .='<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center; width:30px" rowspan="2" > No. </th>
					<th style="vertical-align: middle; text-align:center;width:30px" rowspan="2">NBK</th>
					<th style="vertical-align: middle; text-align:center;width:150px" rowspan="2" >Nama Anggota </th>
					<th style="vertical-align: middle; text-align:center" colspan="3"> Simpanan  </th>
					<th style="vertical-align: middle; text-align:center" rowspan="2"> Pinjaman  </th>
				</tr>
				<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center" > Pokok</th>
					<th style="vertical-align: middle; text-align:center">Wajib </th>
					<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				</tr>';
			$no = 1;
			$subtotal_pokok = 0;
			$subtotal_wajib = 0;
			$subtotal_sukarela = 0;
			$subtotal_cicilan = 0;
			$subtotal_jasa = 0;
			$subtotal_pinjaman = 0;
			foreach($v AS $k=>$v2){
				$html .= '<tr>
					<td style="text-align:center">'.$no.'</td>
					<td style="text-align:center">'.$v2['identitas'].'</td>
					<td>&nbsp;'.$v2['nama_anggota'].'</td>
					<td style="text-align:right">'.number_format($v2['simpanan_pokok']).'</td>
					<td style="text-align:right">'.number_format($v2['simpanan_wajib']).'</td>
					<td style="text-align:right">'.number_format($v2['simpanan_sukarela']).'</td>
					<td style="text-align:right">'.number_format($v2['pinjaman']).'</td>
					
				</tr>';
				$subtotal_pokok += $v2['simpanan_pokok'];
				$subtotal_wajib += $v2['simpanan_wajib'];
				$subtotal_sukarela += $v2['simpanan_sukarela'];
				$subtotal_pinjaman += $v2['pinjaman'];
				$no++;
			}
			
			$html .='<tr style="font-weight:bold">
				<td></td>
				<td></td>
				<td>&nbsp;Sub Total</td>
				<td style="text-align:right">'.number_format($subtotal_pokok).'</td>
				<td style="text-align:right">'.number_format($subtotal_wajib).'</td>
				<td style="text-align:right">'.number_format($subtotal_sukarela).'</td>
				<td style="text-align:right">'.number_format($subtotal_pinjaman).'</td>
			
			</tr>';
			
			$total_pokok += $subtotal_pokok;
			$total_wajib += $subtotal_wajib;
			$total_sukarela += $subtotal_sukarela;
			$total_pinjaman += $subtotal_pinjaman;
		}
		
		$html .='<tr style="font-weight:bold">
			<td></td>
			<td></td>
			<td>&nbsp;Total</td>
			<td style="text-align:right">'.number_format($total_pokok).'</td>
			<td style="text-align:right">'.number_format($total_wajib).'</td>
			<td style="text-align:right">'.number_format($total_sukarela).'</td>
			<td style="text-align:right">'.number_format($total_pinjaman).'</td>
			
		</tr>';
		$html .= '</table>';
		
		$pdf->nsi_html($html);
		$pdf->Output('lap_pengeluaran_harian'.date('Ymd_His') . '.pdf', 'I');
	}
	
	function cetak_print() {
		$simpanan = $this->lap_simpanan_m->lap_jenis_simpan();
		$tgl_mulai = date("Y-m-d",strtotime($_GET['tgl_mulai']));
			
		if($simpanan == FALSE) {
			echo 'DATA KOSONG';
			//redirect('lap_simpanan');
			exit();
		}

		
			
			$area = $this->db->get("mst_area")->result();
			$data_area = array();
			foreach($area AS $v){
				$data_area[$v->nama_area] = $v->nama_area;
			}
			$select = "
					SELECT b.id, b.nama,b.identitas,b.departement,simpanan_pokok,simpanan_wajib,simpanan_sukarela,jumlah FROM tbl_anggota AS b 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND jenis_id = '40' AND dk = 'K'
					GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND jenis_id = '41' AND dk = 'K' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE DATE(tgl_transaksi) = '".$tgl_mulai."' AND dk = 'K' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
					
					LEFT JOIN ((SELECT tgl_pinjam,COALESCE(SUM(jumlah),0) AS jumlah, anggota_id AS angid FROM tbl_pinjaman_h WHERE DATE(tgl_pinjam) = '".$tgl_mulai."' GROUP BY anggota_id) AS v5  ) ON v5.angid = b.id 
					
					
					WHERE simpanan_pokok > 0 OR simpanan_wajib > 0 OR simpanan_sukarela > 0  OR jumlah > 0
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
							'pinjaman' => $v2->jumlah,
						);
					}									
				}				
			}
		$tgl_dari_txt = jin_date_ina($tgl_mulai, 'p');
		$tgl_periode_txt = $tgl_dari_txt;

	
		$html = "<style>@page { margin-top: 30px;margin-bottom: 28px; }tr,td,th{border:1px solid black;border-collapse:collapse}table{border-collapse:collapse}";
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
		
		$html .= "<center><h2>Laporan Transaksi Harian (Pengeluaran)</h2>";
		$html .= "<h4>Pertanggal ".$tgl_periode_txt ."</h4></center>";
		
		$total_pokok = 0;
		$total_wajib = 0;
		$total_sukarela = 0;
		$total_cicilan = 0;
		$total_jasa = 0;
		$total_pinjaman = 0;
		
		$html .= '<table style="font-size:10pt;width:700px;">';
		foreach($data_hasil AS $k=>$v){
				$html .= '<tr style="border:none"><td style="border:none;vertical-align:bottom" colspan="5"><br/><h5> Area :'.ucwords(strtolower($k)).' </h5></td></tr>';
		
				$html .='<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center; width:30px" rowspan="2" > No. </th>
					<th style="vertical-align: middle; text-align:center;width:30px" rowspan="2">NBK</th>
					<th style="vertical-align: middle; text-align:center;width:150px" rowspan="2" >Nama Anggota </th>
					<th style="vertical-align: middle; text-align:center" colspan="3"> Simpanan  </th>
					<th style="vertical-align: middle; text-align:center" rowspan="2"> Pinjaman  </th>
				</tr>
				<tr style="background-color:#eee">
					<th style="vertical-align: middle; text-align:center" > Pokok</th>
					<th style="vertical-align: middle; text-align:center">Wajib </th>
					<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				</tr>';
			$no = 1;
			$subtotal_pokok = 0;
			$subtotal_wajib = 0;
			$subtotal_sukarela = 0;
			$subtotal_cicilan = 0;
			$subtotal_jasa = 0;
			$subtotal_pinjaman = 0;
			foreach($v AS $k=>$v2){
				$html .= '<tr>
					<td style="text-align:center">'.$no.'</td>
					<td style="text-align:center">'.$v2['identitas'].'</td>
					<td style="padding:5px;">'.$v2['nama_anggota'].'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_pokok']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_wajib']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['simpanan_sukarela']).'</td>
					<td style="text-align:right;padding-right:7px">'.number_format($v2['pinjaman']).'</td>
					
				</tr>';
				$subtotal_pokok += $v2['simpanan_pokok'];
				$subtotal_wajib += $v2['simpanan_wajib'];
				$subtotal_sukarela += $v2['simpanan_sukarela'];
				$subtotal_pinjaman += $v2['pinjaman'];
				$no++;
			}
			
			$html .='<tr style="font-weight:bold">
				<td></td>
				<td></td>
				<td>&nbsp;Sub Total</td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_pokok).'</td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_wajib).'</td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_sukarela).'</td>
				<td style="text-align:right;padding-right:7px">'.number_format($subtotal_pinjaman).'</td>
			
			</tr>';
			
			$total_pokok += $subtotal_pokok;
			$total_wajib += $subtotal_wajib;
			$total_sukarela += $subtotal_sukarela;
			$total_pinjaman += $subtotal_pinjaman;
		}
		
		$html .='<tr style="font-weight:bold">
			<td></td>
			<td></td>
			<td>&nbsp;Total</td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_pokok).'</td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_wajib).'</td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_sukarela).'</td>
			<td style="text-align:right;padding-right:7px">'.number_format($total_pinjaman).'</td>
			
		</tr>';
		$html .= '</table>';
		
		
		echo $html;
		echo "<script>window.print();</script>";
		die();
	}
}