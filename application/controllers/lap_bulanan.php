<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_bulanan extends OPPController {
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
			$bulan = (strlen($_GET["bulan"]) > 1 ? $_GET["bulan"] : "0".$_GET["bulan"]);
			$tahun = $_GET["tahun"];
			$days=cal_days_in_month(CAL_GREGORIAN,(int)$bulan,$tahun);
			$get_tahunbulan = date("Y-m-d",strtotime(date("Y-m-d",strtotime($tahun."-".$bulan."-01"))."-1 MONTH"));
			//$get_tahunbulan = date("Y-m-d",strtotime($tahun."-".$bulan."-01"));
			list($tahun_old,$bulan_old,$tgl_old) = explode("-",$get_tahunbulan);
			
			$select_pinjam = "SELECT SUM(jumlah) AS jumlah_pinjam FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' AND MONTH(tgl_pinjam) BETWEEN '01' AND '".$bulan_old."' ";
			$query_pinjam = $this->db->query($select_pinjam)->row();
			$bulan_bayar = (($_GET["bulan"] <= 1) ? $_GET["bulan"] : ($_GET["bulan"]-1)); 
			$bulan_bayar = (strlen($bulan_bayar) > 1 ? $bulan_bayar : "0".$bulan_bayar);			
			$select_bayar = "SELECT SUM(jml_pokok_angsuran) total_angsuran FROM tbl_pinjaman_d WHERE YEAR(tgl_bayar) = '".$tahun."' AND MONTH(tgl_bayar) BETWEEN '01' AND '".$bulan_bayar."' ";
			$query_bayar = $this->db->query($select_bayar)->row();
			
			$total_pinjam = $query_pinjam->jumlah_pinjam - intval($query_bayar->total_angsuran) ;
			var_dump($query_bayar->total_angsuran);
				
			if($days > 0){
				for($i=1;$i<=$days;$i++){
					$tgl = $tahun."-".$bulan."-".$i;
					
					$simpanan_pokok= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(total_bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
						
					$pinjamans += $pinjaman->jml_pokok_pinjaman;
										
					$bayar= $this->db->select("SUM(jml_pokok_angsuran) AS jml_pokok_angsuran,SUM(ROUND(jml_jasa_angsuran)) AS jml_jasa_angsuran")
						->where('dk', 'd')
						->where('DATE(tgl_bayar)', $tgl)
						->get("tbl_pinjaman_d d")	
						->row();
						
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
					$sisa_pinjaman = $jml_pinjaman - $bayar->jml_pokok_angsuran;
					$sisa_jasa = $pinjaman->total_pokok_jasa - $bayar->jml_jasa_angsuran;
					$jasa_dibayar = $bayar->jml_jasa_angsuran;
									
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					
					$pinjamans = ($pinjamans - $bayar->jml_pokok_angsuran);
					$pinjamans2 = ($total_pinjam + $pinjamans);
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"cicilan" => $bayar->jml_pokok_angsuran,
						"pinjaman_sisa_pokok" => ($pinjamans2),
						"pokok_jasa" => $pinjaman->total_pokok_jasa,
						"jasa_dibayar" => $jasa_dibayar,
						"sisa_jasa" => $sisa_jasa,
					); 	
				}
			}
		}
		
		if(isset($_GET['cetak'])){
			$this->cetak_print();
		}
		$this->data["hasil"] = $hasil;
		$this->data["isi"] = $this->load->view('lap_bulanan_v', $this->data,TRUE);
	
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
						->where('d.dk', 'D')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(lama_angsuran*bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
										
					$bayar= $this->db->select("SUM(jml_pokok_angsuran) AS jml_pokok_angsuran,SUM(jml_jasa_angsuran) AS jml_jasa_angsuran")
						->where('dk', 'd')
						->where('DATE(tgl_bayar)', $tgl)
						->get("tbl_pinjaman_d d")	
						->row();
						
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
					$sisa_pinjaman = $jml_pinjaman - $bayar->jml_pokok_angsuran;
					$sisa_jasa = $pinjaman->total_pokok_jasa - $bayar->jml_jasa_angsuran;
					$jasa_dibayar = $bayar->jml_jasa_angsuran;
									
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"pinjaman_sisa_pokok" => $sisa_pinjaman,
						"jasa_dibayar" => $jasa_dibayar,
						"sisa_jasa" => $sisa_jasa,
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
			$get_tahunbulan = date("Y-m-d",strtotime(date("Y-m-d",strtotime($tahun."-".$bulan."-01"))."-1 MONTH"));
			//$get_tahunbulan = date("Y-m-d",strtotime($tahun."-".$bulan."-01"));
			list($tahun_old,$bulan_old,$tgl_old) = explode("-",$get_tahunbulan);
			
			$select_pinjam = "SELECT SUM(jumlah) AS jumlah_pinjam FROM tbl_pinjaman_h WHERE YEAR(tgl_pinjam) = '".$tahun."' AND MONTH(tgl_pinjam) BETWEEN '01' AND '".$bulan_old."' ";
			$query_pinjam = $this->db->query($select_pinjam)->row();
			$bulan_bayar = (($_GET["bulan"] <= 1) ? $_GET["bulan"] : ($_GET["bulan"]-1)); 
			$bulan_bayar = (strlen($bulan_bayar) > 1 ? $bulan_bayar : "0".$bulan_bayar);			
			$select_bayar = "SELECT SUM(jml_pokok_angsuran) total_angsuran FROM tbl_pinjaman_d WHERE YEAR(tgl_bayar) = '".$tahun."' AND MONTH(tgl_bayar) BETWEEN '01' AND '".$bulan_bayar."' ";
			$query_bayar = $this->db->query($select_bayar)->row();
			$total_pinjam = $query_pinjam->jumlah_pinjam - $query_bayar->total_angsuran;
			
			if($days > 0){
				for($i=1;$i<=$days;$i++){
					$tgl = $tahun."-".$bulan."-".$i;
					
					$simpanan_pokok= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '40')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_wajib= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '41')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
						
					$simpanan_sukarela= $this->db->select("SUM(jumlah) AS simpanan_pokok")
						->where('d.dk', 'D')
						->where('d.jenis_id', '32')
						->where('DATE(tgl_transaksi)', $tgl)
						->get("tbl_trans_sp d")	
						->row();
					
					$pinjaman= $this->db->select("SUM(jumlah) AS jml_pokok_pinjaman,SUM(total_bunga_pinjaman) AS total_pokok_jasa")
						->where('DATE(tgl_pinjam)', $tgl)
						->get("v_hitung_pinjaman d")	
						->row();
										
					$bayar= $this->db->select("SUM(jml_pokok_angsuran) AS jml_pokok_angsuran,SUM(ROUND(jml_jasa_angsuran)) AS jml_jasa_angsuran")
						->where('dk', 'd')
						->where('DATE(tgl_bayar)', $tgl)
						->get("tbl_pinjaman_d d")	
						->row();
					
					$pinjamans += $pinjaman->jml_pokok_pinjaman;
					
					$jml_pinjaman = $pinjaman->jml_pokok_pinjaman;
					$total_jasa_pokok = $pinjaman->total_pokok_jasa;
					$sisa_pinjaman = $jml_pinjaman - $bayar->jml_pokok_angsuran;
					$sisa_jasa = $pinjaman->total_pokok_jasa - $bayar->jml_jasa_angsuran;
					$jasa_dibayar = $bayar->jml_jasa_angsuran;
									
					$simpanan_total = $simpanan_pokok->simpanan_pokok + $simpanan_wajib->simpanan_pokok + $simpanan_sukarela->simpanan_pokok;
					$pinjamans = ($pinjamans - $bayar->jml_pokok_angsuran);
					$pinjamans2 = ($total_pinjam + $pinjamans);
					
					$hasil[] = array(
						"day" => $i,
						"simpanan_pokok" => $simpanan_pokok->simpanan_pokok,
						"simpanan_wajib" => $simpanan_wajib->simpanan_pokok,
						"simpanan_sukarela" => $simpanan_sukarela->simpanan_pokok,
						"simpanan_total" => $simpanan_total,
						"pinjaman_pokok" => $jml_pinjaman,
						"cicilan" => $bayar->jml_pokok_angsuran,
						"pinjaman_sisa_pokok" => $pinjamans2,
						"pokok_jasa" => $pinjaman->total_pokok_jasa,
						"jasa_dibayar" => $jasa_dibayar,
						"sisa_jasa" => $sisa_jasa,
					); 	
				}
			}
		$bulanarray=array("01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");
	
		$html = "<style>@page { margin-top: 0;margin-bottom: 0; }table,tr,td,th{border:1px solid black;border-collapse:collapse}";
		//$html .= "background-image:";
		$html .= "</style>";
		$html .= "<div class='header' style='float:left'>";
		$html .= "<img style='float:left'src='".base_url()."assets/theme_admin/img/logo.png' width='100' height='100' alt='logo' />";
		$html .= "<div style='font-size:16px'><br/>KOPERASI DWP BINA SEJAHTERA</div>";
		$html .= "<div>JL. KISAMAUN NO.1 KEL.SUKARASA</div>";
		$html .= "<div>Tel.021-55795402 <br/>Email :</div>";
		$html .= "<div/>";
		$html .= "<div style='clear:both'></div>";
		$html .= "<hr/>";
		
		$html .= "<center><h2>LAPORAN RINGKAS BULAN</h2><h4>(Pemasukan)</h4>";
		$html .= "<h4>Perbulan ".$bulanarray[$bulan]." ".$tahun ."</h4></center>";
		
		$html .= '<table style="font-size:10pt;width:700px;" >
			<tr class="header_kolom">
				<th style="width:50px; -web-kit-background-color:#eeeeee; vertical-align: middle; text-align:center" rowspan="2" > Tanggal </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="4"> Simpanan  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				<th style="width:170px; vertical-align: middle; text-align:center" rowspan="2"> Jasa Dibayar</th>
			</tr>
			<tr class="header_kolom">
				<th style="vertical-align: middle; text-align:center">Pokok </th>
				<th style="vertical-align: middle; text-align:center" > Wajib</th>			
				<th style="vertical-align: middle; text-align:center"> Sukarela  </th>
				<th style="vertical-align: middle; text-align:center"> Jumlah  </th>
				<th style="vertical-align: middle; text-align:center"> Cicilan  </th>
				<th style="vertical-align: middle; text-align:center"> Sisa  </th>
			</tr>';
		
		
		foreach($hasil AS $k=>$v){
				$html .= "<tr>";
				$html .= "<td style='text-align:right;padding-right:7px'>".$v['day']."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_pokok'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_wajib'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_sukarela'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format((int)$v['simpanan_total'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format($v['cicilan'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format($v['pinjaman_sisa_pokok'])."</td>";
				$html .= "<td style='text-align:right;padding-right:7px'>".number_format($v['jasa_dibayar'])."</td>";
				$html .= "</tr>";
				
				$total_pokok += $v['simpanan_pokok'];
				$total_wajib += $v['simpanan_wajib'];
				$total_sukarela += $v['simpanan_sukarela'];
				$total_total += $v['simpanan_total'];
				$total_cicilan += $v['cicilan'];
				$total_sisa_pokok = $v['pinjaman_sisa_pokok'];
				$total_pokok_jasa += $v['jasa_dibayar'];
		}
		
		$html .= '
			<tr class="tfoot">
				<th>Total</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_pokok).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_wajib).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_sukarela).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_total).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_cicilan).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_sisa_pokok).'</th>
				<th style="text-align:right;padding-right:7px">'.number_format($total_pokok_jasa).'</th>
			</tr>';

		$html .= '</table>';

		
		echo $html; 
		
		echo "<script>window.print();</script>";
		redirect("/lap_bulanan");
	}
}

