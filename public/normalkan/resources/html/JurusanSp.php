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
<span class="title">Pedoman Tabel Program Pengajaran/Paket Keahlian Dilayani</span><br>
<span class="subtitle">Dapodik Direktorat Jenderal Pendidikan Anak Usia Dini, Pendidikan Dasar dan Pendidikan Menengah</span>
</div>
<div id="form">
<fieldset class="fieldset">
<legend>Program dan Layanan</legend>
<div><table class="table"><tr><td valign="top"><label>Vld</label></td><td valign="top">Keterangan status validasi dari masing-masing record di tabel jurusan_sp</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Nama Layanan</label></td><td valign="top">Nama layanan dari satuan pendidikan</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>SK Izin Operasional</label></td><td valign="top">Nomor SK izin operasional</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Tgl Izin Operasional</label></td><td valign="top">Tanggal berlaku SK Izin Operasional</td></tr></table></div>
</fieldset>
</div>
</body>
</html>
