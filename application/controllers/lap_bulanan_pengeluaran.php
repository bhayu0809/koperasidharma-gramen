<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_bulanan_pengeluaran extends OPPController {
	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_kas_anggota_m');
	}	

	public function index() {
		$this->load->library("pagination");

		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Data Bulanan';

		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/default/easyui.css';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/icon.css';
		$this->data['js_files'][] = base_url() . 'assets/easyui/jquery.easyui.min.js';
		
		$hasil = array();
		if(isset($_GET["hitung"])){
			$bulan = $_GET["bulan"];
			$tahun = $_GET["tahun"];
			$days=cal_days_in_month(CAL_GREGORIAN,(int)$bulan,$tahun);
			
			if($days > 0){
				for($i=1;$i<=$days;$i++){
					$tgl = $tahun."-".$bulan."-".$i;
					
					$simpanan_pokok= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(total_bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
							
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
									
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"jasa_pokok" => $total_jasa_pokok
					); 	
				}
			}
		}
		
		if(isset($_GET['cetak'])){
			$this->cetak_print();
		}
		$this->data["hasil"] = $hasil;
		$this->data["isi"] = $this->load->view('lap_bulanan_pengeluaran_v', $this->data,TRUE);
	
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	

	function cetak() {
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if($anggota == FALSE) {
			redirect('lap_bulanan');
			exit();
		}
		
			$bulan = $_GET["bulan"];
			$tahun = $_GET["tahun"];
			$days=cal_days_in_month(CAL_GREGORIAN,(int)$bulan,$tahun);
			$hasil = array();
			if($days > 0){
				for($i=1;$i<=$days;$i++){
					$tgl = $tahun."-".$bulan."-".$i;
					
					$simpanan_pokok= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(total_bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
							
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
										
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"jasa_pokok" => $total_jasa_pokok
					); 	
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
		'.$pdf->nsi_box($text = '<span class="txt_judul"> LAPORAN BULANAN</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		$simpanan_row_total = 0; 
	$simpanan_total = 0; 
	$penarikan_total = 0;

	$total_pokok = 0;
	$total_wajib = 0;
	$total_sukarela = 0;
	$total_cicilan = 0;
	$total_jasa = 0;
	$total_jml_simpanan = 0;
	
	
	$html .= '<table  border="1" >
		<tr class="header_kolom">
			<th style="width:50px; vertical-align: middle; text-align:center" rowspan="2" > Tanggal. </th>
			<th style="width:170px; vertical-align: middle; text-align:center" colspan="4"> Simpanan  </th>
			<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
			<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Jasa  </th>
		</tr>
		<tr class="header_kolom">
			<th style="vertical-align: middle; text-align:center" > Wajib</th>
			<th style="vertical-align: middle; text-align:center"> Pokok</th>
			<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
			<th style="vertical-align: middle; text-align:center"> Jumlah  </th>
			<th style="vertical-align: middle; text-align:center"> Pokok  </th>
			<th style="vertical-align: middle; text-align:center"> Sisa  </th>
			<th style="vertical-align: middle; text-align:center"> Dibayar  </th>
			<th style="vertical-align: middle; text-align:center"> Sisa  </th>
		</tr>';
	
		foreach($hasil AS $k=>$v){
				$html .= "<tr>";
				$html .= "<td align-text='center'>".$v['day']."</td>";
				$html .= "<td>".number_format((int)$v['simpanan_wajib'])."</td>";
				$html .= "<td>".number_format((int)$v['simpanan_pokok'])."</td>";
				$html .= "<td>".number_format((int)$v['simpanan_sukarela'])."</td>";
				$html .= "<td>".number_format((int)$v['simpanan_total'])."</td>";
				$html .= "<td>".number_format($v['pinjaman_pokok'])."</td>";
				$html .= "<td>".number_format($v['pinjaman_sisa_pokok'])."</td>";
				$html .= "<td>".number_format($v['jasa_dibayar'])."</td>";
				$html .= "<td>".number_format($v['sisa_jasa'])."</td>";
				$html .= "</tr>";
			}

		$html .= '</table>';

		
		$html .= '</table>';
		$pdf->nsi_html($html);
		$pdf->Output('lap_nominatif'.date('Ymd_His') . '.pdf', 'I');
	}

	public function cetak_print(){
		$bulan = $_GET["bulan"];
			$tahun = $_GET["tahun"];
			$days=cal_days_in_month(CAL_GREGORIAN,(int)$bulan,$tahun);
			$hasil = array();
			if($days > 0){
				for($i=1;$i<=$days;$i++){
					$tgl = $tahun."-".$bulan."-".$i;
					
					$simpanan_pokok= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'K')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(total_bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
							
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
										
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"jasa_pokok" => $total_jasa_pokok
					); 	
				}
			}
		$bulanarray=array("01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");
	
		$html = "<style>@page { margin-top: 0;margin-bottom: 0; }table,tr,td,th{border:1px solid black;border-collapse:collapse}";
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
		
		$html .= "<center><h2>LAPORAN RINGKAS BULAN</h2><h4>(Pengeluaran)</h4>";
		$html .= "<h4>Perbulan ".$bulanarray[$bulan]." ".$tahun ."</h4></center>";
		
		$html .= '<table style="font-size:10pt;width:700px;">
			<tr class="header_kolom">
				<th style="width:50px; -web-kit-background-color:#eeeeee; vertical-align: middle; text-align:center" rowspan="2" > Tanggal. </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="4"> Simpanan  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" rowspan="2"> Pinjaman  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" rowspan="2"> Jasa  </th>
			</tr>
			<tr class="header_kolom">
				<th style="vertical-align: middle; text-align:center">Pokok </th>
				<th style="vertical-align: middle; text-align:center" > Wajib</th>			
				<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				<th style="vertical-align: middle; text-align:center"> Jumlah  </th>
				
			</tr>';
		
		
		foreach($hasil AS $k=>$v){
				$html .= "<tr>";
				$html .= "<td align-text='center'>".$v['day']."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_pokok'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_wajib'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_sukarela'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_total'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format($v['pinjaman_pokok'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format($v['jasa_pokok'])."</td>";
				$html .= "</tr>";
				
				$total_pokok += $v['simpanan_pokok'];
				$total_wajib += $v['simpanan_wajib'];
				$total_sukarela += $v['simpanan_sukarela'];
				$total_total += $v['simpanan_total'];
				$total_pinjaman_pokok += $v['pinjaman_pokok'];
				$total_jasa_pokok += $v['jasa_pokok'];
		}
		
		$html .= '
			<tr class="tfoot">
				<th>Total</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_pokok).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_wajib).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_sukarela).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_total).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_pinjaman_pokok).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_jasa_pokok).'</th>
			</tr>';

		$html .= '</table>';

		
		echo $html; 
		
		echo "<script>window.print();</script>";
		redirect("/lap_bulanan");
	}
}

