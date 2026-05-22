<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_nominatif extends OPPController {
	public function __construct() {
			parent::__construct();	
			$this->load->helper('fungsi');
			$this->load->model('general_m');
			$this->load->model('lap_kas_anggota_m');
		}	

	public function index() {
		if(isset($_GET['hitung'])){
			$area = $this->db->get("mst_area")->result();
			$tahun = $_GET['tahun'];
			$data_area = array();
			foreach($area AS $v){
				$data_area[$v->nama_area]= $v->nama_area;
			}
			
			$select = "
					SELECT b.id, b.nama,b.identitas,b.departement,tarikan_simpanan_pokok,jml_pinjaman,jml_pinjaman_pokok,tarikan_simpanan_wajib,tarikan_simpanan_sukarela,simpanan_pokok,simpanan_wajib,simpanan_sukarela,v2.tgl_transaksi,jumlah_bayar,jasa FROM tbl_anggota AS b 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '40' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '41' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
					
					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '40' GROUP BY anggota_id) AS p2  ) ON p2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '41' GROUP BY anggota_id) AS p3  ) ON p3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '32' GROUP BY anggota_id) AS p4  ) ON p4.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman_pokok, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' GROUP BY anggota_id) AS pinjp  ) ON pinjp.angid = b.id 
					
					
					LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) BETWEEN 2018 AND '".$tahun."' GROUP BY anggota_id) AS pinj  ) ON pinj.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jml_pokok_angsuran),0) AS jumlah_bayar,anggota_id AS angid  FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE YEAR(tgl_bayar) BETWEEN 2018 AND '".$tahun."' GROUP BY v_hitung_pinjaman.anggota_id) AS v5  ) ON v5.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(ROUND(jml_jasa_angsuran)),0) AS jasa,anggota_id AS angid  FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE YEAR(tgl_bayar) = '".$tahun."' GROUP BY v_hitung_pinjaman.anggota_id) AS v6  ) ON v6.angid = b.id 
					
					ORDER BY `b`.`identitas`,`b`.departement ASC 
				";
			
			$pinjaman_total = $this->db->select("SUM(jumlah) AS jml_pinjaman_total")->get_where("tbl_pinjaman_h",array('YEAR(tgl_pinjam)' => $tahun))->row();
			$data_anggota = $this->db->query($select)->result();
			$pinjamans = array();
			$sub_total = array();
			$data_exist_area = array();
			
			foreach($data_anggota AS $k=>$v2){			
				$pinjaman_total->jml_pinjaman_total -= $v2->jml_pinjaman;			
					
				if(isset($data_area[$v2->departement])){
					$sisa_pokok = ($v2->simpanan_pokok - $v2->tarikan_simpanan_pokok);
					$sisa_wajib = ($v2->simpanan_wajib - $v2->tarikan_simpanan_wajib);
					$sisa_sukarela = ($v2->simpanan_sukarela - $v2->tarikan_simpanan_sukarela);
					$total = $sisa_pokok + $sisa_sukarela + $sisa_wajib;
					$sisa_pinjaman = $sisa_pokok + $sisa_sukarela + $sisa_wajib;
					$pinjamans = $v2->jml_pinjaman - $v2->jumlah_bayar;				
		
					$data[$v2->departement][] = array(
						'nama_anggota' => $v2->nama,
						'identitas' => $v2->identitas,
						'simpanan_pokok' => $sisa_pokok,
						'simpanan_wajib' => $sisa_wajib,
						'simpanan_sukarela' => $sisa_sukarela,
						'jumlah_simpanan' => $total,
						'pokok' => $v2->jml_pinjaman_pokok,
						'jumlah_bayar' => $v2->jumlah_bayar,
						'sisa' => $pinjamans,
						'sisa_jasa' => (isset($sisa_jasa) ? $sisa_jasa : 0 ),
						'jasa_dibayar' => $v2->jasa,
					);				
				}
			}
		}
		
		if(isset($_GET['cetak'])){
			$this->cetak_print($_GET);
		}
		
		$this->data["data_hasil"] = $data;
		
		$this->data["data_jns_simpanan"] = $this->lap_kas_anggota_m->get_jenis_simpan(); // panggil seluruh data simpanan
		
		$this->data['isi'] = $this->load->view('lap_nominatif_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	
	function cetak() {
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if($anggota == FALSE) {
			redirect('lap_nominatif');
			exit();
		}
		
		$area = $this->db->get("mst_area")->result();
		$data_area = array();
		foreach($area AS $v){
			$data_area[$v->nama_area] = $v->nama_area; 
		}
		
		$data_anggota  = $this->lap_kas_anggota_m->lap_data_anggota(); // panggil seluruh data aanggota

		foreach($data_anggota AS $v2){				

			$pinjaman = $this->lap_kas_anggota_m->get_data_pinjam($v2->id,$_GET["tgl_mulai"],$_GET["tgl_akhir"]);
			$pinjam_id = @$pinjaman->id;
			$anggota_id = @$pinjaman->anggota_id;

			$jml_pj = $this->lap_kas_anggota_m->get_jml_pinjaman($v2->id,$_GET["tgl_mulai"],$_GET["tgl_akhir"]);
			$pj_anggota= @$jml_pj->total;

			//denda
			$denda = $this->lap_kas_anggota_m->get_jml_denda($pinjam_id);
			$tagihan= @$pinjaman->tagihan + $denda->total_denda;
				
			//dibayar
			$dibayar = $this->lap_kas_anggota_m->get_jml_bayar($pinjam_id);
			$sisa_tagihan = $tagihan - $dibayar->total;
			
			//jasa
			$totaljasa = @$pinjaman->lama_angsuran * @$pinjaman->bunga_pinjaman;
			$jasa_dibayar = @$dibayar->jml_jasa_angsuran; 
			$sisa_jasa = $totaljasa - $jasa_dibayar;
			
			$data_transaksi  = $this->lap_kas_anggota_m->transaksi_sp($v2->id, $_GET["tgl_mulai"],$_GET["tgl_akhir"]); // panggil seluruh data aanggota

			$total = $data_transaksi['simpanan']['pokok'] +  $data_transaksi['simpanan']['wajib'] + $data_transaksi['simpanan']['sukarela'] + $data_transaksi['simpanan']['jumlah_simpanan'] + $pinjaman->jumlah + $sisa_tagihan;
			if($total > 0){
				if(isset($data_area[$v2->departement])){
					$data[$v2->departement][] = array(
						'nama_anggota' => $v2->nama,
						'identitas' => $v2->identitas,
						'simpanan_pokok' => $data_transaksi['simpanan']['pokok'],
						'simpanan_wajib' => $data_transaksi['simpanan']['wajib'],
						'simpanan_sukarela' => $data_transaksi['simpanan']['sukarela'],
						'jumlah_simpanan' => $data_transaksi['simpanan']['jumlah_simpanan'],
						'pokok' => (isset($pinjaman->jumlah) ? $pinjaman->jumlah : 0 ),
						'sisa' => (isset($sisa_tagihan) ? $sisa_tagihan : 0 ),
					);
				}
			}
		}
		
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
		'.$pdf->nsi_box($text = '<span class="txt_judul"> LAPORAN NOMINATIF</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		$simpanan_row_total = 0; 
	$simpanan_total = 0; 
	$penarikan_total = 0;

	$total_pokok = 0;
	$total_wajib = 0;
	$total_sukarela = 0;
	$total_cicilan = 0;
	$total_jasa = 0;
	$total_jml_simpanan = 0;
	
	foreach($data AS $k=>$v){
		$html .= '<p style="text-align:left; font-size: 10pt; font-weight: bold;"> Area : '.ucwords(strtolower($k)).'</p>';
		
		$html .= '<table  border="1" >
			<tr class="header_kolom">
				<th style="width:30px; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
				<th style="width:40px; vertical-align: middle; text-align:center" rowspan="2">NBK </th>
				<th style="width:150px; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="4"> Simpanan  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Jasa  </th>
			</tr>
			<tr class="header_kolom">
				<th style="vertical-align: middle; text-align:center" > Pokok</th>
				<th style="vertical-align: middle; text-align:center">Wajib </th>
				<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				<th style="vertical-align: middle; text-align:center"> Jumlah  </th>
				<th style="vertical-align: middle; text-align:center"> Pokok  </th>
				<th style="vertical-align: middle; text-align:center"> Sisa  </th>
				<th style="vertical-align: middle; text-align:center"> Dibayar  </th>
				<th style="vertical-align: middle; text-align:center"> Sisa  </th>
			</tr>';
		$no = 1;
		$subtotal_pokok = 0;
		$subtotal_wajib = 0;
		$subtotal_sukarela = 0;
		$subtotal_cicilan = 0;
		$subtotal_jasa = 0;
		$subtotal_jml_simpanan= 0;
		foreach($v AS $k=>$v2){
			$html .= '<tr>
				<td>'.$no.'</td>
				<td>'.$v2['identitas'].'</td>
				<td>'.$v2['nama_anggota'].'</td>
				<td style="text-align:right">'.number_format($v2['simpanan_pokok']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['simpanan_wajib']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['simpanan_sukarela']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['jumlah_simpanan']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['pokok']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['sisa']).'<span style="color:white;"></span></td>
			</tr>';
			$subtotal_pokok += $v2['simpanan_pokok'];
			$subtotal_wajib += $v2['simpanan_wajib'];
			$subtotal_sukarela += $v2['simpanan_sukarela'];
			$subtotal_cicilan += $v2['pokok'];
			$subtotal_jasa += $v2['sisa'];
			$subtotal_jml_simpanan += $v2['jumlah_simpanan'];
			$no++;
		}
		
		$total_pokok += $subtotal_pokok;
		$total_wajib += $subtotal_wajib;
		$total_sukarela += $subtotal_sukarela;
		$total_jml_simpanan += $subtotal_jml_simpanan;
		$total_cicilan += $subtotal_cicilan;
		$total_jasa += $subtotal_jasa;
		
	}
			

		$html .= '</table>';

		
		$pdf->nsi_html($html);
		$pdf->Output('lap_nominatif'.date('Ymd_His') . '.pdf', 'I');
	} 
	
	public function cetak_print(){
		$tahun = $_GET['tahun'];
		$area = $this->db->get("mst_area")->result();
		$data_area = array();
		foreach($area AS $v){
				$data_area[$v->nama_area]= $v->nama_area;
			}
			
			$select = "
					SELECT b.id, b.nama,b.identitas,b.departement,tarikan_simpanan_pokok,jml_pinjaman,jml_pinjaman_pokok,tarikan_simpanan_wajib,tarikan_simpanan_sukarela,simpanan_pokok,simpanan_wajib,simpanan_sukarela,v2.tgl_transaksi,jumlah_bayar,jasa FROM tbl_anggota AS b 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '40' GROUP BY anggota_id) AS v2  ) ON v2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '41' GROUP BY anggota_id) AS v3  ) ON v3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='D' AND jenis_id = '32' GROUP BY anggota_id) AS v4  ) ON v4.angid = b.id 
					
					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_pokok, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '40' GROUP BY anggota_id) AS p2  ) ON p2.angid = b.id 

					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_wajib, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '41' GROUP BY anggota_id) AS p3  ) ON p3.angid = b.id 


					LEFT JOIN ((SELECT tgl_transaksi,COALESCE(SUM(jumlah),0) AS tarikan_simpanan_sukarela, anggota_id AS angid FROM tbl_trans_sp WHERE YEAR(tgl_transaksi) BETWEEN 2018 AND '".$tahun."' AND dk='K' AND jenis_id = '32' GROUP BY anggota_id) AS p4  ) ON p4.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman_pokok, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' GROUP BY anggota_id) AS pinjp  ) ON pinjp.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jumlah),0) AS jml_pinjaman, anggota_id AS angid FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) BETWEEN 2018 AND '".$tahun."' GROUP BY anggota_id) AS pinj  ) ON pinj.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(jml_pokok_angsuran),0) AS jumlah_bayar,anggota_id AS angid  FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE YEAR(tgl_bayar) BETWEEN 2018 AND '".$tahun."' GROUP BY v_hitung_pinjaman.anggota_id) AS v5  ) ON v5.angid = b.id 
					
					LEFT JOIN ((SELECT COALESCE(SUM(ROUND(jml_jasa_angsuran)),0) AS jasa,anggota_id AS angid  FROM tbl_pinjaman_d INNER JOIN v_hitung_pinjaman ON v_hitung_pinjaman.id = tbl_pinjaman_d.pinjam_id WHERE YEAR(tgl_bayar) = '".$tahun."' GROUP BY v_hitung_pinjaman.anggota_id) AS v6  ) ON v6.angid = b.id 
					
					ORDER BY `b`.`identitas`,`b`.departement ASC 
				";
			
			$pinjaman_total = $this->db->select("SUM(jumlah) AS jml_pinjaman_total")->get_where("tbl_pinjaman_h",array('YEAR(tgl_pinjam)' => $tahun))->row();
			$data_anggota = $this->db->query($select)->result();
			$pinjamans = array();
			$sub_total = array();
			$data_exist_area = array();
			
			foreach($data_anggota AS $k=>$v2){			
				$pinjaman_total->jml_pinjaman_total -= $v2->jml_pinjaman;			
					
				if(isset($data_area[$v2->departement])){
					$sisa_pokok = ($v2->simpanan_pokok - $v2->tarikan_simpanan_pokok);
					$sisa_wajib = ($v2->simpanan_wajib - $v2->tarikan_simpanan_wajib);
					$sisa_sukarela = ($v2->simpanan_sukarela - $v2->tarikan_simpanan_sukarela);
					$total = $sisa_pokok + $sisa_sukarela + $sisa_wajib;
					$sisa_pinjaman = $sisa_pokok + $sisa_sukarela + $sisa_wajib;
					$pinjamans = $v2->jml_pinjaman - $v2->jumlah_bayar;	
					$data[$v2->departement][] = array(
						'nama_anggota' => $v2->nama,
						'identitas' => $v2->identitas,
						'simpanan_pokok' => $sisa_pokok,
						'simpanan_wajib' => $sisa_wajib,
						'simpanan_sukarela' => $sisa_sukarela,
						'jumlah_simpanan' => $total,
						'pokok' => $v2->jml_pinjaman_pokok,
						'jumlah_bayar' => $v2->jumlah_bayar,
						'sisa' => $pinjamans,
						'sisa_jasa' => (isset($sisa_jasa) ? $sisa_jasa : 0 ),
						'jasa_dibayar' => $v2->jasa,
					);				
				}
			}
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
		
		$html .= "<center><h2>Laporan Nominatif Simpanan dan Pinjaman Anggota</h2>";
		$html .= "<h4>Pertanggal ".$tgl_periode_txt ."</h4></center>";
	
	$simpanan_row_total = 0; 
	$simpanan_total = 0; 
	$penarikan_total = 0;

	$total_pokok = 0;
	$total_wajib = 0;
	$total_sukarela = 0;
	$total_cicilan = 0;
	$total_jasa = 0;
	$total_jml_simpanan = 0;
	$html .= '<table>';
	foreach($data AS $k=>$v){
		$html .= '<tr style="border:none"><td style="border:none;vertical-align:bottom" colspan="5"><br/><h5> Area :'.ucwords(strtolower($k)).' </h5></td></tr>';
			
		$html .= '
			<tr class="header_kolom">
				<th style="width:30px; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
				<th style="width:40px; vertical-align: middle; text-align:center" rowspan="2">NBK </th>
				<th style="width:150px; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="4"> Simpanan  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" rowspan="2"> Jasa dibayar  </th>
			</tr>
			<tr class="header_kolom">
				<th style="vertical-align: middle; text-align:center" > Pokok</th>
				<th style="vertical-align: middle; text-align:center">Wajib </th>
				<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				<th style="vertical-align: middle; text-align:center"> Jumlah  </th>
				<th style="vertical-align: middle; text-align:center"> Pokok  </th>
				<th style="vertical-align: middle; text-align:center"> Sisa  </th>
			</tr>';
		$no = 1;
		$subtotal_pokok = 0;
		$subtotal_wajib = 0;
		$subtotal_sukarela = 0;
		$subtotal_cicilan = 0;
		$subtotal_jasa = 0;
		$subtotal_sisa = 0;
		$subtotal_jml_simpanan= 0;
		$subtotal_jasa_dibayar = 0;
		$subtotal_sisa_jasa = 0;
		//$jumlah_subtotal =0;
		foreach($v AS $k=>$v2){
			$jml  = $v2['sisa'];
			$html .= '<tr>
				<td>'.$no.'</td>
				<td>'.$v2['identitas'].'</td>
				<td>'.$v2['nama_anggota'].'</td>
				<td style="text-align:right">'.number_format($v2['simpanan_pokok']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['simpanan_wajib']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['simpanan_sukarela']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['jumlah_simpanan']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['pokok']).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($jml).'<span style="color:white;"></span></td>
				<td style="text-align:right">'.number_format($v2['jasa_dibayar']).'</td>
			</tr>';
			$subtotal_pokok += $v2['simpanan_pokok'];
			$subtotal_wajib += $v2['simpanan_wajib'];
			$subtotal_sukarela += $v2['simpanan_sukarela'];
			$subtotal_pinjaman_pokok += $v2['pokok'];			
			$subtotal_dibayar += $v2['jumlah_bayar'];
			$subtotal_jml_simpanan += $v2['jumlah_simpanan'];			
			$subtotal_jasa_dibayar += $v2['jasa_dibayar'];
			$subtotal_sisa += $jml;
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
			<th>'.number_format($subtotal_pinjaman_pokok).'</th>
			<th>'.number_format($subtotal_sisa).'</th>
			<th>'.number_format($subtotal_jasa_dibayar).'</th>
		</tr>';
			
			$total_pokok += $subtotal_pokok;
		$total_wajib += $subtotal_wajib;
		$total_sukarela += $subtotal_sukarela;
		$total_jml_simpanan += $subtotal_jml_simpanan;
		$total_pinjaman_pokok = $subtotal_pinjaman_pokok;
		$total_dibayar = $subtotal_dibayar;
		$jumlah_subtotal += $subtotal_sisa;

		$total_jasa_dibayar += $subtotal_jasa_dibayar;
		$total_sisa_jasa += $subtotal_sisa_jasa;
			
	}
	
		$html .= '<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($total_pokok).'</th>
			<th>'.number_format($total_wajib).'</th>
			<th>'.number_format($total_sukarela).'</th>
			<th>'.number_format($total_jml_simpanan).'</th>
			<th>'.number_format($total_pinjaman_pokok).'</th>
			<th>'.number_format($jumlah_subtotal).'</th>
			<th>'.number_format($total_jasa_dibayar).'</th>
			
		</tr></table>';
			

		
		
		echo $html;
		echo "<script>window.print();</script>";
		die();
	}
}