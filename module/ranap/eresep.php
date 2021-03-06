<form method="post">
  <?php
  if (isset($_POST['ok_obat'])) {
      if (($_POST['kode_obat'] <> "") and ($no_rawat <> "")) {

          $get_obatmaxtgl = fetch_assoc(query("SELECT MAX(tanggal) AS tanggal FROM riwayat_barang_medis WHERE kode_brng = '{$_POST['kode_obat']}' AND kd_bangsal = 'B0001'"));
          $get_obatmaxjam = fetch_assoc(query("SELECT MAX(jam) AS jam FROM riwayat_barang_medis WHERE kode_brng = '{$_POST['kode_obat']}' AND tanggal = '{$get_obatmaxtgl['tanggal']}' AND kd_bangsal = 'B0001'"));
          $get_obatstok = fetch_assoc(query("SELECT * FROM riwayat_barang_medis WHERE kode_brng = '{$_POST['kode_obat']}' AND tanggal = '{$get_obatmaxtgl['tanggal']}' AND jam = '{$get_obatmaxjam['jam']}' AND kd_bangsal = 'B0001'"));

          if($get_obatstok['stok_akhir'] < 10 ) {
          	$errors[] = 'Maaf stok obat di depo rawat inap tidak mencukupi';
          }

          if(!empty($errors)) {
              foreach($errors as $error) {
                  echo validation_errors($error);
              }
          } else {

              $onhand = query("SELECT no_resep FROM resep_obat WHERE no_rawat = '{$no_rawat}' AND tgl_peresepan = '{$date}' AND kd_dokter = '{$_SESSION['username']}'");
              $dtonhand = fetch_array($onhand);
              $get_number = fetch_array(query("select ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) from resep_obat where tgl_perawatan like '%{$date}%'"));
              $lastNumber = substr($get_number[0], 0, 4);
              $_next_no_resep = sprintf('%04s', ($lastNumber + 1));
              $tgl_resep = date('Ymd');
              $next_no_resep = $tgl_resep.''.$_next_no_resep;

              if ($dtonhand['0'] > 1) {
                if ($_POST['aturan_pakai_lainnya'] == "") {
                  $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                } else {
                  $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                }
                redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
              } else {
                  $insert = query("INSERT INTO resep_obat VALUES ('{$next_no_resep}', '{$date}', '{$time}', '{$no_rawat}', '{$_SESSION['username']}', '{$date}', '{$time}', '{$status_lanjut}')");
                  if ($_POST['aturan_pakai_lainnya'] == "") {
                    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                  } else {
                    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                  }
                  redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
              }
          }
      }
  }
  ?>
<dl class="dl-horizontal">
    <dt>Nama Obat</dt>
    <dd><select name="kode_obat" class="kd_obat" style="width:100%"></select></dd><br>
    <dt>Jumlah Obat</dt>
    <dd><input class="form-control" name="jumlah" value="10" style="width:100%"></dd><br>
    <dt>Aturan Pakai</dt>
    <dd>
        <select name="aturan_pakai" class="aturan_pakai" id="lainnya" style="width:100%">
        <?php
        $sql = query("SELECT aturan FROM master_aturan_pakai");
        while($row = fetch_array($sql)){
            echo '<option value="'.$row[0].'">'.$row[0].'</option>';
        }
        ?>
        <option value="lainnya">Lainnya</option>
        </select>
    </dd><br>
    <div id="row_dim">
    <dt></dt>
    <dd><input class="form-control" name="aturan_pakai_lainnya" style="width:100%"></dd><br>
    </div>
    <dt></dt>
    <dd><button type="submit" name="ok_obat" value="ok_obat" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_obat\'">OK</button></dd><br>
    <dt></dt>
</dl>
<div class="table-responsive">
 <table class="table table-striped">
    <thead>
        <tr>
            <th>Nama Obat</th>
            <th>Tanggal/Jam</th>
            <th>Jumlah</th>
            <th>Aturan Pakai</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $query_resep = query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep, c.tgl_peresepan, c.jam_peresepan FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$no_rawat}' AND c.kd_dokter = '{$_SESSION['username']}' ");
    while ($data_resep = fetch_array($query_resep)) {
    ?>
        <tr>
            <td><?php echo $data_resep['3']; ?> <a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_obat&kode_obat=<?php echo $data_resep['0']; ?>&no_resep=<?php echo $data_resep['4']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></td>
            <td><?php echo $data_resep['5']; ?> <?php echo $data_resep['6']; ?></td>
            <td><?php echo $data_resep['1']; ?></td>
            <td><?php echo $data_resep['2']; ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
</div>
</form>
