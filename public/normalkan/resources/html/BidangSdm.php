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
<span class="title">Pedoman Tabel Kompetensi</span><br>
<span class="subtitle">Dapodik Direktorat Jenderal Pendidikan Anak Usia Dini, Pendidikan Dasar dan Pendidikan Menengah</span>
</div>

<div id="deskripsi">
<p>Tabel ini untuk melengkapi data bidang studi yang diajarkan oleh PTK yang berprofesi sebagai guru, dengan memperhatikan tingkat kompetensi pada masing-masing bidang studi yang diajarkan.</p>
<p>Secara otomatis, tabel ini akan diisi sesuai data pada tabel Riwayat Sertifikasi maupun Riwayat Pendidikan Formal. Selain itu, dapat pula ditambah secara manual.</p>
<p>Data yang ditambahkan dapat lebih dari satu bidang kompetensi, namun salah satunya haruslah kompetensi utama.</p>
</div>

<div id="form">
<fieldset class="fieldset">
<legend>Kompetensi</legend>
<div><table class="table"><tr><td valign="top"><label>Bidang Studi</label></td><td valign="top">Bidang studi yang diajarkan oleh PTK.</td></tr></table></div>
<div><table class="table"><tr><td valign="top"><label>Urutan</label></td><td valign="top">Urutan bidang studi yang diajarkan oleh PTK. Kolom ini untuk menentukan bidang studi utama atau bukan, tingkat prioritas, maupun tingkat kompetensi penguasaan PTK terhadap bidang studi terkait. Isikan angka <strong>1</strong> pada kolom Urutan apabila mata pelajaran terkait adalah mata pelajaran utama yang diajarkan oleh PTK.</td></tr></table></div>
</fieldset>
</div>
</body>
</html>
