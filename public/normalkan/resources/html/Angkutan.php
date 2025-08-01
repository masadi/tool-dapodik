<html>
<head>
<style>
@font-face {
    font-family: 'franklin';
    src: url('/resources/fonts/Frabk.ttf');
}
body{
	padding-left: 10px;
	font-family: 'franklin';
}
#head-logo {
    background: url('/resources/images/tutwuri-polos-small.png') no-repeat left center;
    padding-left: 60px;
	height: 50px;
	margin-bottom: 20px;
}
#head-logo .title {
	font-weight: bold;
	font-size: 20px;
}
#head-logo .subtitle {
	font-weight:lighter;
	font-size: 14px;
}
#form {
	font-size: 14px;
	float: left;
}
label {
	width: 180px;
	display: block;
	float: left;
	font-size: 1em;
	font-weight:bold;
}
.fieldset {
	display: inline-block;
	width: 600;
}
.table {
    font-family: 'franklin';
	border-spacing: 0px;
    border-collapse: separate;
	font-size: 1em;
}

</style>
</head>
<body>

<div id="head-logo">
<span class="title">Pedoman Tabel Angkutan</span><br>
<span class="subtitle">Dapodik Direktorat Jenderal Pendidikan Anak Usia Dini, Pendidikan Dasar dan Pendidikan Menengah</span>
</div>
<div id="form">
<fieldset class="fieldset">
<legend>Angkutan</legend>
<div><table class="table"><tr><td valign="top"><label>Vld</label></td><td valign="top">Keterangan status validasi dari masing-masing record di tabel angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Jenis Sarana</label></td><td valign="top">Pilih jenis sarana</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Nama</label></td><td valign="top">Nama Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Spesifikasi</label></td><td valign="top">Spesifikasi Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Merk</label></td><td valign="top">Merk Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>No Polisi</label></td><td valign="top">Nomor Polisi Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>No BPKB</label></td><td valign="top">Nomor Bpkb Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Alamat</label></td><td valign="top">Alamat Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Kepemilikan</label></td><td valign="top">Kepemilikan_Sarpras_Id Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Peminjam/yang Meminjamkan</label></td><td valign="top">Ptk_Id Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Hapus buku</label></td><td valign="top">Id_Hapus_Buku Angkutan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Tgl Hapus Buku</label></td><td valign="top">Tanggal Hapus Buku Angkutan</td></tr></table></div>
</fieldset>
</div>
</body>
</html>
