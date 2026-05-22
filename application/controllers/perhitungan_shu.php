<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Perhitungan_shu extends OperatorController {

public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_kas_anggota_m');
	}	

	public function index() {
		error_reporting(0);
		$this->load->library("pagination");

		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Sisa Hasil Usaha';

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

		//$this->data['data_pasiva'] = $this->lap_shu_m->get_data_akun_pasiva();
		
		$area = $this->db->get("mst_area")->result();
		$data_area = array();
		foreach($area AS $v){
			$data_area[$v->nama_area] = $v->nama_area;
		}
		
		$this->data['simpanan_total']= '';
			
		$this->data['pinjaman_total'] = '';
		$tahun = $_GET['tahun'];
		
			
		$subtotal_pokok = 0;
		$subtotal_wajib = 0;
		$subtotal_jml_simpanan = 0;
		$subtotal_sukarela = 0;
		$data = array();
		if(isset($_GET['hitung'])){
			$select_anggota = "
					SELECT b.id, b.nama,b.identitas,b.departement,tarikan_simpanan,jml_pinjaman,simpanan,v2.tgl_transaksi FROM tbl_anggota AS b 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) = '".$tahun."' AND dk='D' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) = '".$tahun."' AND dk='K' GROUP BY anggota_id) AS p4  ) ON p4.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' GROUP BY anggota_id) AS pinj  ) ON pinj.angid = b.id 
			
					ORDER BY `b`.`identitas`,`b`.departement ASC 
				";
			$query_anggota = $this->db->query($select_anggota)->result();
			$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
		
			$this->data['simpanan_total'] = $simpanan_total->simpanan;
			$this->data['pinjaman_total'] = $pinjaman_total->pinjaman_1tahun;
			
			foreach($query_anggota AS $v2){				
				if(isset($data_area[$v2->departement])){
					$data[$v2->departement][] = array(
						'nama_anggota' => $v2->nama,
						'identitas' => $v2->identitas,
						'simpanan' => ($v2->simpanan - $v2->tarikan_simpanan),
						'simpanan_wajib' => 0,
						'simpanan_sukarela' => 0,
						'jumlah_simpanan' => 0,
						'pinjaman' => intval($v2->jml_pinjaman),
						'pokok' => 0,
						'sisa' => 0,
					);
				}
			}
		}
		
		if(isset($_GET['cetak'])){
			$this->cetak_print();
		}
		$this->data["data_hasil"] = $data;
	
		$this->data['isi'] = $this->load->view('perhitungan_shu_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);

	}
	
	function cetak() {
		
		$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
		
		$simpanan_total= $this->db->select("SUM(jumlah) AS simpanan")
			->where('d.dk', 'D')
			->get("tbl_trans_sp d")	
			->row();
			
		$pinjaman_total = $this->db->select("SUM(jumlah) AS pinjaman_1tahun")
			->where('YEAR(h.tgl_pinjam) >= ', ''.$tahun.'')
			->get("tbl_pinjaman_h h")	
			->row();
		
		$simpanan_total = $simpanan_total->simpanan;
		$pinjaman_total = $pinjaman_total->pinjaman_1tahun;
		
		$area = $this->db->get("mst_area")->result();
		$data_area = array();
		foreach($area AS $v){
			$data_area[$v->nama_area] = $v->nama_area;
		}
		
		$simpanan_total = '';
			
		$pinjaman_total = '';
		
		$data_anggota  = $this->lap_kas_anggota_m->lap_data_anggota(); // panggil seluruh data aanggota
			
		$data = array();
		foreach($data_anggota AS $v2){
			if(isset($data_area[$v2->departement])){
				$simpanan= $this->db->select("SUM(jumlah) AS simpanan")
				->where('d.anggota_id', ''.$v2->id.'')
				->where('d.dk', 'D')
				->get("tbl_trans_sp d")	
				->row();
				
				$pinjaman = $this->db->select("SUM(jumlah) AS pinjaman_1tahun")
					->where('YEAR(h.tgl_pinjam) = ', ''.$tahun.'')
					->where('h.anggota_id', ''.$v2->id.'')
					->get("tbl_pinjaman_h h")	
					->row();
				if($simpanan->simpanan > 0 || $pinjaman->pinjaman_1tahun > 0){
					if(isset($data_area[$v2->departement])){
						$data[$v2->departement][] = array(
							'nama_anggota' => $v2->nama,
							'identitas' => $v2->identitas,
							'simpanan' => ($simpanan->simpanan ? $simpanan->simpanan : 0),
							'simpanan_wajib' => 0,
							'simpanan_sukarela' => 0,
							'jumlah_simpanan' => 0,
							'pinjaman' => ($pinjaman->pinjaman_1tahun ? $pinjaman->pinjaman_1tahun : 0),
							'pokok' => 0,
							'sisa' => 0,
						);
					}
				}
			}				
		}
		$tgl_periode_txt = jin_date_ina($_GET['tgl_cetak'], 'p');

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
		'.$pdf->nsi_box($text = '<span class="txt_judul"> KEADAAN SIMPANAN, PINJAMAN DAN SHU</span><p style="text-align:center;font-weight:bold"> Tgl Cetak. '.$tgl_periode_txt.' </p>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		$total_pokok = 0;
		$total_wajib = 0;
		$total_sukarela = 0;
		$total_cicilan = 0;
		$total_jasa = 0;
		$total_shu_simpanan =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
		$total_shu_simpanan_tampil =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
		$total_shu_pinjaman =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
		$total_shu_pinjaman_tampil =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
		$index_simpanan = ((int)$_GET['deviden_simpanan'] > 0 ? $total_shu_simpanan * 1000 / $_GET['deviden_simpanan'] : 0);
		$index_pinjaman = ((int)$_GET['deviden_pinjaman'] > 0 ? $total_shu_pinjaman * 1000 / $_GET['deviden_pinjaman'] : 0);
		
		$html .= '<h6>Index	Simpanan : '.number_format($index_simpanan).'</h6>';
		$html .= '<h6>Index	Pinjaman : '.number_format($index_pinjaman).'</h6>';
		
		$html .= '<table  border="1" class="table table-bordered">';
		

		foreach($data AS $k=>$v){
				$html .= '<tr style="border:none"><td style="border:none;vertical-align:bottom" colspan="5"><br/><h5> Area :'.ucwords(strtolower($k)).' </h5></td></tr>';
				$html .='<tr style="background-color:#eee">
						<th style="width:5%; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
						<th style="width:20%; vertical-align: middle; text-align:center" rowspan="2">No Anggota </th>
						<th style="width:30%; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
						<th style="width:15%; vertical-align: middle; text-align:center" colspan="2"> Simpanan  </th>
						<th style="width:15%; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
						<th style="width:15%; vertical-align: middle; text-align:center" rowspan="2" > Total SHU  </th></tr>';
				$html .= '<tr style="background-color:#eee"><th>1</th><th>2</th><th>1</th><th>2</th></tr>';
					
					
					
					
			$no = 1;
			$subtotal_pokok = 0;
			$subtotal_wajib = 0;
			$subtotal_sukarela = 0;
			$subtotal_cicilan = 0;
			$subtotal_jasa = 0;
			foreach($v AS $k=>$v2){
				$shu_simpanan = number_format((float)$index_simpanan, 2, '.', '') * $v2['simpanan'] / 1000;
				$shu_pinjaman = number_format((float)$index_pinjaman, 2, '.', '') * $v2['pinjaman'] / 1000;
				$html .='<tr>
					<td>'.$no.'</td>
				
					<td>'.$v2['identitas'].'</td>
					<td>'.$v2['nama_anggota'].'</td>
					<td>'.$v2['simpanan'].'</td>
					<td>'.$shu_simpanan.'</td>
					<td>'.$v2['pinjaman'].'</td>
					<td>'.$shu_pinjaman.'</td>
					<td>0</td>
				</tr>';
				$subtotal_pokok += $v2['simpanan'];
				$subtotal_wajib += $v2['simpanan_wajib'];
				$subtotal_sukarela += $v2['simpanan_sukarela'];
				$subtotal_cicilan += $v2['pokok'];
				$subtotal_jasa += $v2['sisa'];
				$subtotal_jml_simpanan += $v2['jumlah_simpanan'];
				$no++;
			}
			
			$html .='<tr>
				<th></th>
				<th></th>
				<th>Sub Total</th>
				<th>'.number_format($subtotal_pokok).'</th>
				<th>'.number_format($subtotal_wajib).'</th>
				<th>'.number_format($subtotal_sukarela).'</th>
				<th>'.number_format($subtotal_jml_simpanan).'</th>
				<th>'.number_format($subtotal_jml_simpanan).'</th>
			</tr>';
			
			$total_pokok += $subtotal_pokok;
			$total_wajib += $subtotal_wajib;
			$total_sukarela += $subtotal_sukarela;
			$total_jml_simpanan += $subtotal_jml_simpanan;
			$total_cicilan += $subtotal_cicilan;
			$total_jasa += $subtotal_jasa;
		}
		
		$html .='<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($subtotal_pokok).'</th>
			<th>'.number_format($subtotal_wajib).'</th>
			<th>'.number_format($subtotal_sukarela).'</th>
			<th>'.number_format($subtotal_jml_simpanan).'</th>
			<th>'.number_format($subtotal_jml_simpanan).'</th>
		</tr>';
		$html .= '</table>';
 
		
		$pdf->nsi_html($html);
		$pdf->Output('perhitungan_shu'.date('Ymd_His') . '.pdf', 'I');
	}
	
	function cetak_print() {
		$tgl_mulai = date("Y-m-d",strtotime($_GET['tgl_cetak']));
		
		$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;
		
		$simpanan_total= $this->db->select("SUM(jumlah) AS simpanan")
			->where('d.dk', 'D')
			->get("tbl_trans_sp d")	
			->row();
			
		$pinjaman_total = $this->db->select("SUM(jumlah) AS pinjaman_1tahun")
			->where('YEAR(h.tgl_pinjam) >= ', ''.$tahun.'')
			->get("tbl_pinjaman_h h")	
			->row();
		
		
		$area = $this->db->get("mst_area")->result();
		$data_area = array();
		foreach($area AS $v){
			$data_area[$v->nama_area] = $v->nama_area;
		}
		
		$select_anggota = "
			SELECT b.id, b.nama,b.identitas,b.departement,tarikan_simpanan,jml_pinjaman,simpanan,v2.tgl_transaksi FROM tbl_anggota AS b 

			LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) = '".$tahun."' AND dk='D' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

			LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) = '".$tahun."' AND dk='K' GROUP BY anggota_id) AS p4  ) ON p4.angid = b.id 
			
			LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' GROUP BY anggota_id) AS pinj  ) ON pinj.angid = b.id 
			
			ORDER BY `b`.`identitas`,`b`.departement ASC 
		";
		$query_anggota = $this->db->query($select_anggota)->result();

		foreach($query_anggota AS $v2){
			if(isset($data_area[$v2->departement])){
				$data[$v2->departement][] = array(
					'nama_anggota' => $v2->nama,
					'identitas' => $v2->identitas,
					'simpanan' => ($v2->simpanan - $v2->tarikan_simpanan),
					'simpanan_wajib' => 0,
					'simpanan_sukarela' => 0,
					'jumlah_simpanan' => 0,
					'pinjaman' => intval($v2->jml_pinjaman),
					'pokok' => 0,
					'sisa' => 0,
				);
			}										
		}
		
		$tgl_periode_txt = jin_date_ina($tgl_mulai, 'p');

		$html = "<style>@page { margin-top: 30px;margin-bottom: 28px; }tr,td,th{border:1px solid black;border-collapse:collapse}table{border-collapse:collapse}";
		$html .= "</style>";
		$html .= "<div class='header' style='float:left'>";
		$html .= "<img style='float:left'src='http://localhost/koperasi/assets/theme_admin/img/logo.png' width='100' height='100' alt='logo' />";
		$html .= "<div style='font-size:16px'><br/>KOPERASI DWP BINA SEJAHTERA</div>";
		$html .= "<div>JL. KISAMAUN NO.1 KEL.SUKARASA</div>";
		$html .= "<div>Tel.021-55795402 <br/>Email :</div>";
		$html .= "<div/>";
		$html .= "<div style='clear:both'></div>";
		$html .= "<hr/>";
		
		$html .= "<center><h2>KEADAAN SIMPANAN, PINJAMAN DAN SHU</h2>";
		$html .= "<h4>Pertanggal ".$tgl_periode_txt ."</h4></center>";
		
		$total_simpanan = 0;
		$total_shu_simpanan = 0;
		$total_pinjaman = 0;
		$total_shu_pinjaman = 0;
		$total_total_shu = 0;
		$total_shu_simpanan =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
		$total_shu_simpanan_tampil =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
		$total_shu_pinjaman =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
		$total_shu_pinjaman_tampil =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
		$index_simpanan = ((int)$_GET['deviden_simpanan'] > 0 ? $total_shu_simpanan * 1000 / $_GET['deviden_simpanan'] : 0);
		$index_pinjaman = ((int)$_GET['deviden_pinjaman'] > 0 ? $total_shu_pinjaman * 1000 / $_GET['deviden_pinjaman'] : 0);
		
		$html .= '<span>Index	Simpanan : '.number_format($index_simpanan).'</span><br/>';
		$html .= '<span>Index	Pinjaman : '.number_format($index_pinjaman).'</span><br/><br/>';
		
		$html .= '<table  border="1" class="table table-bordered">';
		

		foreach($data AS $k=>$v){
				$html .= '<tr style="border:none"><td style="border:none;vertical-align:bottom" colspan="5"><br/><h5> Area :'.ucwords(strtolower($k)).' </h5></td></tr>';
				$html .='<tr style="background-color:#eee">
						<th style="width:5%; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
						<th style="width:20%; vertical-align: middle; text-align:center" rowspan="2">No Anggota </th>
						<th style="width:30%; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
						<th style="width:15%; vertical-align: middle; text-align:center" colspan="2"> Simpanan  </th>
						<th style="width:15%; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
						<th style="width:15%; vertical-align: middle; text-align:center" rowspan="2" > Total SHU  </th></tr>';
				$html .= '<tr style="background-color:#eee">
				<th style="vertical-align: middle; text-align:center" > Saldo</th>
				<th style="vertical-align: middle; text-align:center">SHU </th>
				<th style="vertical-align: middle; text-align:center"> Total  </th>
				<th style="vertical-align: middle; text-align:center"> SHU  </th>
				</tr>';
					
					
					
					
			$no = 1;
			$subtotal_simpanan = 0;
			$subtotal_shu_simpanan = 0;
			$subtotal_shu_pinjaman = 0;
			$subtotal_cicilan = 0;
			$subtotal_total_shu= 0;
			$subtotal_pinjaman= 0;
			foreach($v AS $k=>$v2){
				$shu_simpanan = number_format((float)$index_simpanan * $v2['simpanan'] / 1000, 2, '.', '');
				$shu_pinjaman = number_format((float)$index_pinjaman * $v2['pinjaman'] / 1000, 2, '.', '');
				$total_shu = $shu_simpanan + $shu_pinjaman;
				$html .='<tr>
					<td>'.$no.'</td>
				
					<td>'.$v2['identitas'].'</td>
					<td>'.$v2['nama_anggota'].'</td>
					<td>'.number_format($v2['simpanan']).'</td>
					<td>'.number_format($shu_simpanan).'</td>
					<td>'.number_format($v2['pinjaman']).'</td>
					<td>'.number_format($shu_pinjaman).'</td>
					<td>'.number_format($total_shu).'</td>
				</tr>';
				$subtotal_simpanan += $v2['simpanan'];
				$subtotal_shu_simpanan += $shu_simpanan;
				$subtotal_pinjaman += $v2['pinjaman'];
				$subtotal_shu_pinjaman += $shu_pinjaman;
				$subtotal_total_shu += $total_shu;
				$no++;
			}
			
			$html .='<tr>
				<th></th>
				<th></th>
				<th>Sub Total</th>
				<th>'.number_format($subtotal_simpanan).'</th>
				<th>'.number_format($subtotal_shu_simpanan).'</th>
				<th>'.number_format($subtotal_pinjaman).'</th>
				<th>'.number_format($subtotal_shu_pinjaman).'</th>
				<th>'.number_format($subtotal_total_shu).'</th>
			</tr>';
			
			$total_simpanan += $subtotal_simpanan;
			$total_shu_simpanan += $subtotal_shu_simpanan;
			$total_pinjaman += $subtotal_pinjaman;
			$total_shu_pinjaman += $subtotal_shu_pinjaman;
			$total_total_shu += $subtotal_total_shu;
		}
		
		$html .='<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($total_simpanan).'</th>
			<th>'.number_format($total_shu_simpanan).'</th>
			<th>'.number_format($total_pinjaman).'</th>
			<th>'.number_format($total_shu_pinjaman).'</th>
			<th>'.number_format($total_total_shu).'</th>
		</tr>';
		$html .= '</table>';
 
		echo $html;
		echo "<script>window.print();</script>";
		die();
		
	}

}