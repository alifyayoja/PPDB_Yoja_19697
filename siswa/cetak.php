<?php
require_once '../config/config.php';
require_once '../lib/fpdf/fpdf.php';

// Pastikan siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: " . BASE_URL . "/siswa/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil semua data pendaftar yang relevan
$query = "SELECT u.nama_lengkap, u.nik, u.email, p.tempat_lahir, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.asal_sekolah, p.nisn, s.status
          FROM users u
          LEFT JOIN pendaftar p ON u.id = p.user_id
          LEFT JOIN seleksi s ON u.id = s.user_id
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data pendaftar tidak ditemukan.");
}
$data = $result->fetch_assoc();
$stmt->close();

// Buat PDF
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Bukti Pendaftaran PPDB Online',0,1,'C');
        $this->SetFont('Arial','',12);
        $this->Cell(0,7,'Tahun Ajaran 2024/2025',0,1,'C');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabel data
    function FancyTable($header, $data)
    {
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        
        // Lebar kolom
        $w = array(60, 120);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        
        $this->SetFont('','');
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
            $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w),0,' ','T');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Data untuk ditampilkan
$pdf_data = [
    ['Nomor Pendaftaran', $user_id],
    ['Status Pendaftaran', $data['status'] ?? 'Belum Diverifikasi'],
    ['Nama Lengkap', $data['nama_lengkap']],
    ['NIK', $data['nik']],
    ['Email', $data['email']],
    ['Tempat, Tanggal Lahir', $data['tempat_lahir'] . ', ' . date("d-m-Y", strtotime($data['tanggal_lahir']))],
    ['Jenis Kelamin', $data['jenis_kelamin']],
    ['Alamat', $data['alamat']],
    ['Asal Sekolah', $data['asal_sekolah']],
    ['NISN', $data['nisn']]
];

$header = ['Keterangan', 'Data'];
$pdf->FancyTable($header, $pdf_data);

$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0, 5, 'Harap simpan bukti pendaftaran ini dengan baik. Dokumen ini merupakan bukti sah bahwa Anda telah melakukan pendaftaran awal di sistem PPDB Online. Informasi mengenai seleksi akan diumumkan kemudian.');

$pdf->Output('D', 'bukti_pendaftaran_'.$user_id.'.pdf');
?>
