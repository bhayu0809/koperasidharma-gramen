<style type="text/css">
.panel * {
	font-family: "Arial","​Helvetica","​sans-serif";
}
.fa {
	font-family: "FontAwesome";
}
.datagrid-header-row * {
	font-weight: bold;
}
.messager-window * a:focus, .messager-window * span:focus {
	color: blue;
	font-weight: bold;
}
.daterangepicker * {
	font-family: "Source Sans Pro","Arial","​Helvetica","​sans-serif";
	box-sizing: border-box;
}
.glyphicon	{font-family: "Glyphicons Halflings"}

.form-control {
	height: 20px;
	padding: 4px;
}	
</style>

<?php 

	if(isset($_REQUEST['tahun'])) {
		$tahunget = $_REQUEST['tahun'];
	} else {
		$tahunget = date('Y');
	}
	$tgl_periode_txt = $tahunget;
?>

<!--<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="box-title">Cetak Data Simpanan</h3>
		<div class="box-tools pull-right">
			<button class="btn btn-primary btn-sm" data-widget="collapse">
				<i class="fa fa-minus"></i>
			</button>
		</div>
	</div>
	<div class="box-body">
		<div>
			<form id="fmCari" method="GET">
				<input type="hidden" name="tgl_dari" id="tgl_dari">
				<input type="hidden" name="tgl_samp" id="tgl_samp">
				<table>
					<tr>
						
						<td>
							<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" plain="false" onclick="cetak()">Cetak Laporan</a>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>-->
<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="box-title">Laporan Sisa Jasa</h3>
		<div class="box-tools pull-right">
			<button class="btn btn-primary btn-sm" data-widget="collapse">
				<i class="fa fa-minus"></i>
			</button>
		</div>
	</div>
	<div class="box-body">
		<form id="fmCari" method="GET">
			<table>
				<tr>
					<td>
						Tahun
					</td>
					<td>&nbsp;</td>
					<td>
						<select name="tahun">
							<?php for($i=2018;$i <= date("Y");$i++): ?>
								<option value="<?php echo $i ?>" <?php echo ($tahunget == $i ? "selected" : ""); ?>><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
			</table>
				
			<hr/>
		
			<table>
				<tr>
					<td>
						<input id="hitung" name="hitung" class="btn btn-success" type="submit" value="Hitung" >
					</td>
					<td>
						<input id="hitung" name="cetak" onclick="$('form').attr('target', '_blank');" class="btn btn-info" type="submit" value="Cetak" >
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>

<div class="box box-primary">
<div class="box-body">
<p></p>
<p style="text-align:center; font-size: 15pt; font-weight: bold;"> Laporan Sisa Jasa Anggota</p><p style="text-align:center;font-weight:bold"> Per Tanggal. <?php echo $tgl_periode_txt; ?> </p>

	<?php

	$no = $offset + 1;
	$mulai=1;
	$simpanan_arr = array();
	$simpanan_row_total = 0; 
	$simpanan_total = 0; 
	$penarikan_total = 0;

	$total_pokok = 0;
	$total_wajib = 0;
	$total_sukarela = 0;
	$total_cicilan = 0;
	$total_jasa = 0;
	$total_jml_simpanan = 0;
	//$jumlah_subtotal = 0;
	//$subtotal_sisa = 0;
	$no = 0;
	
	
	echo '<table  class="table table-bordered">';
	foreach($data_hasil AS $k=>$v){
			
		echo '<tr>
				<td style="width:5%; border:none;vertical-align: middle; font-weight:bold;text-align:left" colspan="8" > Area :'.ucwords(strtolower($k)).' </td>
			</tr>
			<tr class="header_kolom">
				<th style="width:5%; vertical-align: middle; text-align:center" > No. </th>
				<th style="width:35%; vertical-align: middle; text-align:center" >No Anggota </th>
				<th style="width:35%; vertical-align: middle; text-align:center" >Nama Anggota </th>
				<th style="width:20%; vertical-align: middle; text-align:center" > Sisa Jasa  </th>
			</tr>
		';
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
		foreach($v AS $k2=>$v2){
			$jml  = $v2['sisa'];
			echo '<tr>
				<td>'.$no.'</td>
				<td>'.$v2['identitas'].'</td>
				<td>'.$v2['nama_anggota'].'</td>
				<td>'.number_format($v2['jasa_dibayar']).'</td>
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
		
		echo '<tr>
			<th></th>
			<th></th>
			<th>Sub Total</th>
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
	$no++;
	}
	
		echo '<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($total_jasa_dibayar).'</th>
			
		</tr>';

	echo '</table>';

?>
</div>
</div>
	
<script type="text/javascript">
$(document).ready(function() {
	fm_filter_tgl();
	$(".dtpicker").datepicker({ changeMonth: true,
    changeYear: true,dateFormat: 'yy-mm-dd' });
}); // ready

function fm_filter_tgl() {
	$('#daterange-btn').daterangepicker({
		ranges: {
			'Hari ini': [moment(), moment()],
			'Kemarin': [moment().subtract('days', 1), moment().subtract('days', 1)],
			'7 Hari yang lalu': [moment().subtract('days', 6), moment()],
			'30 Hari yang lalu': [moment().subtract('days', 29), moment()],
			'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
			'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
			'Tahun ini': [moment().startOf('year').startOf('month'), moment().endOf('year').endOf('month')],
			'Tahun kemarin': [moment().subtract('year', 1).startOf('year').startOf('month'), moment().subtract('year', 1).endOf('year').endOf('month')]
		},
		locale: 'id',
		showDropdowns: true,
		format: 'YYYY-MM-DD',
		<?php 
			if(isset($tgl_dari) && isset($tgl_samp)) {
				echo "
					startDate: '".$tgl_dari."',
					endDate: '".$tgl_samp."'
				";
			} else {
				echo "
					startDate: moment().startOf('year').startOf('month'),
					endDate: moment().endOf('year').endOf('month')
				";
			}
		?>
	},

	function (start, end) {
		doSearch();
	});
	 $( "#datepicker" ).datepicker(
		{ 
			dateFormat: 'yy-mm-dd' 
	 });
}

function clearSearch(){
	window.location.href = '<?php echo site_url("lap_nominatif"); ?>';
}

function doSearch() {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	$('input[name=tgl_dari]').val(tgl_dari);
	$('input[name=tgl_samp]').val(tgl_samp);
	$('#fmCari').attr('action', '<?php echo site_url('lap_nominatif'); ?>');
	$('#fmCari').submit();	
}


function cetak () {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	//$('input[name=tgl_dari]').val(tgl_dari);
	//$('input[name=tgl_samp]').val(tgl_samp);
	//$('#fmCari').attr('action', '<?php echo site_url('lap_trans_kas/cetak'); ?>');
	//$('#fmCari').submit();

	var win = window.open('<?php echo site_url("lap_nominatif/cetak/?tgl_dari=' + tgl_dari + '&tgl_samp=' + tgl_samp + '"); ?>');
	if (win) {
		win.focus();
	} else {
		alert('Popup jangan di block');
	}

}
</script>