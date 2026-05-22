<!-- Styler -->
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



<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="box-title">Cetak Laporan SHU</h3>
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
						Tanggal Cetak
					</td>
					<td>&nbsp;</td>
					<td>
						<input id="tgl_cetak" name="tgl_cetak" class="dtpicker"value="<?php echo $_GET['tgl_cetak']; ?>" style="width:200px; height:25px" class="">
					</td>
				</tr>
				<tr>
					<td>
						Periode
					</td>
					<td>&nbsp;</td>
					<td>
						<div id="filter_tgl" class="input-group" style="display: inline;">
							<select name="tahun">
								<option value="">Pilih Tahun</option>
								<?php
									for($i=2010;$i<=date('Y');$i++){
										$selected = ($i == $_GET['tahun']) ? 'selected' : ''; 
										echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
									}
								?>
							</select>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						Total SHU
					</td>
					<td>&nbsp;</td>
					<td>
						<input id="total_shu" name="total_shu" value="<?php echo $_GET['total_shu']; ?>" style="width:200px; height:25px" class="">
					</td>					
				</tr>
				</table>
				
				<hr/>
		
			<table style="float:left;width:40%">
				<tr>
					<td>SHU</td>
				</tr>
				<tr>
					
					<td>
						Simpanan
					</td>
					
					<td>&nbsp;</td>
					
					<td>
						<input id="persen_simpanan" name="persen_simpanan" value="20" style="width:40px; height:25px" class="">
					</td>
					<td style="width:30px;">% =
					</td>
					<?php 
						$total_shu_simpanan =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
						$total_shu_simpanan_tampil =  ($_GET['total_shu'] * $_GET['persen_simpanan'] / 100);
					?>
					<td>
						<input id="hasil_simpanan_deviden" readonly name="total_simpanan" value="<?php echo number_format($total_shu_simpanan_tampil); ?>" style="background-color:#eee; width:200px; height:25px" class="">
					</td>
				</tr>
				<tr>
					<td>
						Pinjaman
					</td>
					<td>&nbsp;</td>
					<td>
						<input id="persen_pinjaman" name="persen_pinjaman" value="25" style="width:40px; height:25px" class="">
					</td>
					<td style="width:30px;">% =
					</td>
					<?php 
						$total_shu_pinjaman =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
						$total_shu_pinjaman_tampil =  ($_GET['total_shu'] * $_GET['persen_pinjaman'] / 100);
					?>
					<td>
						<input id="hasil_pinjaman_deviden" readonly name="total_pinjaman" value="<?php echo number_format($total_shu_pinjaman_tampil) ?>" style="background-color:#eee; width:200px; height:25px" class="">
					</td>
				</tr>
			</table>
			
			<table >
				<tr>
					<td>DEVIDEN</td>
				</tr>
				<tr>
					<td>
						Simpanan
					</td>
					<td>&nbsp;</td>
					<td>
						<input id="deviden_simpanan" name="deviden_simpanan" value="<?php echo $_GET['deviden_simpanan']; ?>" style="width:200px; height:25px" class="">
					</td>
					<td style="width:30px;">% =
					</td>
					<?php
						$index_simpanan = $total_shu_simpanan * 1000 / $_GET['deviden_simpanan'];
						
					?>
					<td>
						<input id="hasil_simpanan_deviden" readonly name="hasil_simpanan_deviden" value="<?php echo number_format($total_shu_simpanan_tampil) ?>"style="background-color:#eee; width:200px; height:25px" class="">
					</td>
				</tr>
				<tr>
					<td>
						Pinjaman
					</td>
					<td>&nbsp;</td>
					<?php
						$index_pinjaman = $total_shu_pinjaman * 1000 / $_GET['deviden_pinjaman'];
					?>
					<td>
						<input id="deviden_pinjaman" name="deviden_pinjaman"  value="<?php echo $_GET['deviden_pinjaman']; ?>" style="width:200px; height:25px" class="">
					</td>
					<td style="width:30px;">% =
					</td>
				
					<td>
						<input id="hasil_pinjaman_deviden" readonly name="hasil_pinjaman_deviden" value="<?php echo number_format($total_shu_pinjaman_tampil) ?>" style="background-color:#eee; width:200px; height:25px" class="">
					</td>
					
				</tr>
				<input name="index_simpanan" value="<?php echo number_format((float)$index_simpanan, 2, '.', '');  ?>" type="hidden"/>
				<input name="index_pinjaman" value="<?php echo number_format((float)$index_pinjaman, 2, '.', '');  ?>" type="hidden"/>
			</table>
			<table>
				<tr>
					<td>
						<input id="hitung" name="hitung" class="btn btn-success" type="submit" value="Hitung" >
					</td>
					<td>
						<input id="hitung" name="cetak" class="btn btn-info" type="submit" value="Cetak" >
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>
<?php if(isset($_GET['hitung'])){ ?>
<div class="box box-primary">
	<div class="box-body">
		<p></p>
		<p style="text-align:center; font-size: 15pt; font-weight: bold;"> KEADAAN SIMPANAN, PINJAMAN DAN SHU</p>
		<p style="text-align:center;font-weight:bold"> Tanggal Cetak <?php echo jin_date_ina($_GET['tgl_cetak'], 'p'); ?> </p>

		<?php echo $tgl_periode_txt; ?> </p>
		
		<table>
			<tr>
				<td>Index Simpanan</td>
				<td>:</td>
				<td><?php echo number_format($index_simpanan);  ?></td>
			</tr>
			
			<tr>
				<td>Index Pinjaman</td>
				<td>:</td>
				<td><?php echo number_format($index_pinjaman); ?></td>
			</tr>
		</table>

	<?php
	
	$no = 1;
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
	
	
	
	echo '<table  class="table table-bordered">';
	foreach($data_hasil AS $k=>$v){
		echo '<tr>
				<td style="width:5%; border:none;vertical-align: middle; font-weight:bold;text-align:left" colspan="8" > Area :'.ucwords(strtolower($k)).' </td>
			</tr>
			<tr class="header_kolom">
				<th style="width:5%; vertical-align: middle; text-align:center" rowspan="2" > No. </th>
				<th style="width:35%; vertical-align: middle; text-align:center" rowspan="2">No Anggota </th>
				<th style="width:35%; vertical-align: middle; text-align:center" rowspan="2">Nama Anggota </th>
				<th style="width:20%; vertical-align: middle; text-align:center" colspan="2"> Simpanan  </th>
				<th style="width:20%; vertical-align: middle; text-align:center" colspan="2"> Pinjaman  </th>
				<th style="width:20%; vertical-align: middle; text-align:center" rowspan="2"> TOTAL SHU  </th>
			</tr>
			<tr class="header_kolom">
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
		$subtotal_total_shu = 0;
		$subtotal_pinjaman= 0;
		foreach($v AS $k=>$v2){
			$shu_simpanan = number_format((float)$index_simpanan * $v2['simpanan'] / 1000, 2, '.', '');
			$shu_pinjaman = number_format((float)$index_pinjaman * $v2['pinjaman'] / 1000, 2, '.', '');
			$total_shu = $shu_simpanan + $shu_pinjaman;
			echo '<tr>
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
		
		echo '<tr>
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
			
		echo '<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th>'.number_format($total_simpanan).'</th>
			<th>'.number_format($total_shu_simpanan).'</th>
			<th>'.number_format($total_pinjaman).'</th>
			<th>'.number_format($total_shu_pinjaman).'</th>
			<th>'.number_format($total_total_shu).'</th>
			
		</tr>';

	echo '</table>';

?>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
	 $(document).ready(function() {
			$(".dtpicker").datepicker({ dateFormat: 'yy-mm-dd' });

	}); // ready

	// function fm_filter_tgl() {
		// $('#daterange-btn').daterangepicker({
			// ranges: {
				// 'Tahun ini': [moment().startOf('year').startOf('month'), moment().endOf('year').endOf('month')],
				// 'Tahun kemarin': [moment().subtract('year', 1).startOf('year').startOf('month'), moment().subtract('year', 1).endOf('year').endOf('month')]
			// },
			// locale: 'id',
			// showDropdowns: true,
			// format: 'YYYY-MM-DD',
			// <?php 
			// if(isset($tgl_dari) && isset($tgl_samp)) {
				// echo "
				// startDate: '".$tgl_dari."',
				// endDate: '".$tgl_samp."'
				// ";
			// } else {
				// echo "
				// startDate: moment().startOf('year').startOf('month'),
				// endDate: moment().endOf('year').endOf('month')
				// ";
			// }
			// ?>
		// },

		// function (start, end) {
			// doSearch();
		// });
	// }

	// function clearSearch(){
		// window.location.href = '<?php echo site_url("perhitungan_shu"); ?>';
	// }

	// function doSearch() {
		// var tgl_dari = $('input[name=daterangepicker_start]').val();
		// var tgl_samp = $('input[name=daterangepicker_end]').val();
		// $('input[name=tgl_dari]').val(tgl_dari);
		// $('input[name=tgl_samp]').val(tgl_samp);
		// $('#fmCari').attr('action', '<?php echo site_url('perhitungan_shu'); ?>');
		// $('#fmCari').submit();
	// }

	function cetak () {	
		<?php 
		if(isset($_REQUEST['anggota_id'])) {
			echo 'var anggota_id = "'.$_REQUEST['anggota_id'].'";';
		} else {
			echo 'var anggota_id = $("input[name=anggota_id]").val();';
		}
		?>

		var tgl_dari = $('input[name=daterangepicker_start]').val();
		var tgl_samp = $('input[name=daterangepicker_end]').val();
		var js_modal = $('#js_modal').val();
		var js_usaha = $('#js_usaha').val();
		var tot_pendpatan = $('#tot_pendpatan').val();
		var tot_simpanan = $('#tot_simpanan').val();

		$('input[name=tgl_dari]').val(tgl_dari);
		$('input[name=tgl_samp]').val(tgl_samp);

		var win = window.open('<?php echo site_url("lap_shu_anggota/cetak_laporan/?anggota_id=' + anggota_id + '&tgl_dari='+ tgl_dari +'&tgl_samp='+ tgl_samp +'&js_modal='+ js_modal +'&js_usaha='+ js_usaha +'&tot_pendpatan='+ tot_pendpatan +'&tot_simpanan='+ tot_simpanan +'"); ?>');
		if (win) {
			win.focus();
		} else {
			alert('Popup jangan di block');
		}

	//$('#fmCari').attr('action', '<?php echo site_url('lap_shu_anggota/cetak_laporan'); ?>');
	//$('#fmCari').submit();
}
</script>