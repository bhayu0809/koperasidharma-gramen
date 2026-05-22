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
$bulan=array("01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");

if(isset($_REQUEST['bulan']) && isset($_REQUEST['tahun'])) {
		$bulanget = $bulan[$_REQUEST['bulan']];
		$tahunget = $_REQUEST['tahun'];
	} else {
		$tgl_dari = date('Y') . '-01-01';
		$tgl_samp = date('Y') . '-12-31';
	}
	$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
	$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
	$tgl_periode_txt = $bulanget." ".$tahunget;
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
		<h3 class="box-title">Cetak Laporan Bulanan</h3>
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
				
				<tr>
					<td>
						Bulan
					</td>
					<td>&nbsp;</td>
					<td>
						<select name="bulan">
							<?php 
								
								foreach($bulan AS $k=>$v): ?>
									<option value="<?php echo $k ?>" <?php echo ($_GET["bulan"] == $k ? "selected" : ""); ?>><?php echo $v; ?></option>
							<?php endforeach; ?>
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
<p style="text-align:center; font-size: 15pt; font-weight: bold;"> Laporan Ringkas Bulan</p>
<h6 style="text-align:center; font-size: 15pt; font-weight: bold;"class="box-title">(Pengeluaran)</h6>
		<p style="text-align:center;font-weight:bold"> Per Bulan. <?php echo $tgl_periode_txt; ?> </p>

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
	
?>

	<table  class="table table-bordered body-table">
		<thead class="thead">
			<tr>
				<th rowspan="2">Tanggal</th>
				<th colspan="4">Simpanan</th>
				<th rowspan="2">Pinjaman</th>
				<th rowspan="2">Jasa</th>
			</tr>
			<tr>
				<th>Pokok</th>
				<th>Wajib</th>				
				<th>Sukarela</th>
				<th>Total</th>
			</tr>
		</thead>
		<?php 
			$total_pokok = 0;
			$total_wajib = 0;
			$total_sukarela = 0;
			$total_total = 0;
			$total_pinjaman_pokok = 0;
			$total_sisa_pokok = 0;
			$total_jasa_dibayar = 0;
			$total_sisa_jasa = 0;
			
			foreach($hasil AS $k=>$v){
				echo "<tr>";
				echo "<td style='text-align:center'>".$v['day']."</td>";
				echo "<td>".number_format((int)$v['simpanan_pokok'])."</td>";
				echo "<td>".number_format((int)$v['simpanan_wajib'])."</td>";
				echo "<td>".number_format((int)$v['simpanan_sukarela'])."</td>";
				echo "<td>".number_format((int)$v['simpanan_total'])."</td>";
				echo "<td>".number_format($v['pinjaman_pokok'])."</td>";
				echo "<td>".number_format($v['jasa_pokok'])."</td>";
				echo "</tr>";
				
				$total_pokok += $v['simpanan_pokok'];
				$total_wajib += $v['simpanan_wajib'];
				$total_sukarela += $v['simpanan_sukarela'];
				$total_total += $v['simpanan_total'];
				$total_pinjaman_pokok += $v['pinjaman_pokok'];
				$total_jasa_pokok += $v['jasa_pokok'];
			}
		
		?>
		<tfoot >
			<tr class="tfoot">
				<th>Total</th>
				<th><?php echo number_format($total_pokok); ?></th>
				<th><?php echo number_format($total_wajib); ?></th>
				<th><?php echo number_format($total_sukarela); ?></th>
				<th><?php echo number_format($total_total); ?></th>
				<th><?php echo number_format($total_pinjaman_pokok); ?></th>
				<th><?php echo number_format($total_jasa_pokok); ?></th>
			</tr>
		</tfoot>
	</table>
</div>
</div>
<style type="text/css">

   th{background-color:gray !important; -webkit-print-color-adjust: exact; }
	.tfoot th{text-align:right}
	.thead th{text-align:center}
	.body-table td{text-align:right}

	
</style>
<script type="text/javascript">
$(document).ready(function() {
	fm_filter_tgl();
	$(".dtpicker").datepicker({ dateFormat: 'yy-mm-dd' });
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


function Popup(data) 
{
    var mywindow = window.open('', 'my div', 'height=400,width=600');
    mywindow.document.write('<html><head><title>my div</title>');
     mywindow.document.write('<link rel="stylesheet" href="http://www.dynamicdrive.com/ddincludes/mainstyle.css" type="text/css" />');
    mywindow.document.write('</head><body >');
    mywindow.document.write(data);
    mywindow.document.write('</body></html>');

    mywindow.print();
  //  mywindow.close();

    return true;
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

function printer(){
	window.print();
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