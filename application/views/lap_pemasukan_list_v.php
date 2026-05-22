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

if(isset($_REQUEST['tgl_mulai'])) {
		$tgl_dari = $_REQUEST['tgl_dari'];
		$tgl_samp = $_REQUEST['tgl_samp'];
	} else {
		$tgl_dari = date('Y') . '-01-01';
		$tgl_samp = date('Y') . '-12-31';
	}
	$tgl_dari_txt = jin_date_ina($_GET['tgl_mulai'], 'p');
	$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
	$tgl_periode_txt = $tgl_dari_txt
?>


<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="box-title">Laporan Pemasukan Harian</h3>
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
						Tanggal Mulai
					</td>
					<td>&nbsp;</td>
					<td>
						<input id="tgl_mulai" autocomplete="off" name="tgl_mulai" class="dtpicker"value="<?php echo $_GET['tgl_mulai']; ?>" style="width:200px; height:25px" class="">
					</td>
				</tr>
			</table>
				
			<hr/>
		
			<table>
				<tr>
					<td>
						<input id="hitung" name="load" class="btn btn-success" type="submit" value="Load" >
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
<p style="text-align:center; font-size: 15pt; font-weight: bold;"> Laporan Transaksi Harian (Pemasukan)</p><p style="text-align:center;font-weight:bold"> Per Tanggal. <?php echo $_GET['tgl_mulai']; ?> </p>

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
	echo '<table  class="table table-bordered">';
	foreach($data_hasil AS $k=>$v){
		
		echo '<tr>
				<td style="width:5%; border:none;vertical-align: middle; font-weight:bold;text-align:left" colspan="8" > Area :'.ucwords(strtolower($k)).' </td>
			</tr>
			<tr class="header_kolom">
				<th style="width:5%; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
				<th style="width:35%; vertical-align: middle; text-align:center" rowspan="2">No Anggota </th>
				<th style="width:35%; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
				<th style="width:20%; vertical-align: middle; text-align:center" colspan="3"> Simpanan  </th>
				<th style="width:20%; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
			</tr>
			<tr class="header_kolom">
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
			echo '<tr>
				<td>'.$no.'</td>
				<td>'.$v2['identitas'].'</td>
				<td>'.$v2['nama_anggota'].'</td>
				<td>'.number_format($v2['simpanan_pokok']).'</td>
				<td>'.number_format($v2['simpanan_wajib']).'</td>
				<td>'.number_format($v2['simpanan_sukarela']).'</td>
				<td>'.number_format($v2['cicilan']).'</td>
				<td>'.number_format($v2['jasa']).'</td>
			</tr>';
			$v2['jasa'] = str_replace(',','',number_format($v2['jasa']));
			$v2['simpanan_sukarela'] = str_replace(',','',number_format($v2['simpanan_sukarela']));
			$subtotal_pokok += $v2['simpanan_pokok'];
			$subtotal_wajib += $v2['simpanan_wajib'];
			$subtotal_sukarela += $v2['simpanan_sukarela'];
			$subtotal_cicilan += $v2['cicilan'];
			$subtotal_jasa += $v2['jasa'];
			$no++;
		}
		
		echo '<tr>
			<th></th>
			<th></th>
			<th>Sub Total</th>
			<th>'.number_format($subtotal_pokok).'</th>
			<th>'.number_format($subtotal_wajib).'</th>
			<th>'.number_format($subtotal_sukarela).'</th>
			<th>'.number_format($subtotal_cicilan).'</th>
			<th>'.number_format($subtotal_jasa).'</th>
		</tr>';
		
		$total_pokok += $subtotal_pokok;
		$total_wajib += $subtotal_wajib;
		$total_sukarela += $subtotal_sukarela;
		$total_cicilan += $subtotal_cicilan;
		$total_jasa += $subtotal_jasa;
		
	}
			
		echo '<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($total_pokok).'</th>
			<th>'.number_format($total_wajib).'</th>
			<th>'.number_format($total_sukarela).'</th>
			<th>'.number_format($total_cicilan).'</th>
			<th>'.number_format($total_jasa).'</th>
			
		</tr>';

	echo '</table>';

?>
</div>
</div>
	
<script type="text/javascript">
$(document).ready(function() {
	fm_filter_tgl();
	$(".dtpicker").datepicker({ changeMonth: true,
    changeYear: true,dateFormat: 'dd-MM-yy' });
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
	window.location.href = '<?php echo site_url("lap_pemasukan_harian"); ?>';
}

function doSearch() {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	$('input[name=tgl_dari]').val(tgl_dari);
	$('input[name=tgl_samp]').val(tgl_samp);
	$('#fmCari').attr('action', '<?php echo site_url('lap_pemasukan_harian'); ?>');
	$('#fmCari').submit();	
}


function cetak () {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	//$('input[name=tgl_dari]').val(tgl_dari);
	//$('input[name=tgl_samp]').val(tgl_samp);
	//$('#fmCari').attr('action', '<?php echo site_url('lap_trans_kas/cetak'); ?>');
	//$('#fmCari').submit();

	var win = window.open('<?php echo site_url("lap_pemasukan_harian/cetak/?tgl_dari=' + tgl_dari + '&tgl_samp=' + tgl_samp + '"); ?>');
	if (win) {
		win.focus();
	} else {
		alert('Popup jangan di block');
	}

}
</script>