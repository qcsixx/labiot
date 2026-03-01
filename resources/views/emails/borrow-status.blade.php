<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang Lab IoT - {{ ucfirst($emailData['status']) }}</title>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
</head>
<body style="font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f7fa; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 30px 0; background-color: #f5f7fa;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.08);">
                    <!-- Header section with university logo and gradient background -->
                    <tr>
                        <td style="background-image: linear-gradient(120deg, #0B3B65 0%, #165C9C 100%); padding: 30px; color: white; text-align: left; position: relative;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td width="70">
                                        <!-- Logo di kiri -->
                                        <img src="{{ $message->embed($emailData['logo_url']) }}" alt="Logo Vokasi UB" style="max-width: 65px; height: auto; display: block;">
                                    </td>
                                    <td style="padding-left: 20px;">
                                        <h1 style="color: white; font-size: 24px; margin: 0; font-weight: 600; letter-spacing: -0.5px;">Peminjaman Laboratorium IoT</h1>
                                        <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0; font-size: 14px; font-weight: 500;">Fakultas Vokasi Universitas Brawijaya</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- White divider -->
                    <tr>
                        <td style="height: 5px; background-color: #ffffff;"></td>
                    </tr>
                    
                    <!-- Main content area -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 40px 30px;">
                            <!-- Greeting -->
                            <h2 style="font-size: 20px; color: #0B3B65; margin: 0 0 25px; font-weight: 600; letter-spacing: -0.3px;">
                                Halo {{ $emailData['userName'] ?? 'Pengguna' }},
                            </h2>
                            
                            <!-- Status Message -->
                            <div style="margin-bottom: 25px; color: #444;">
                                @if($emailData['emailType'] === 'approved')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        Permintaan peminjaman Anda telah <span style="color: #16a34a; font-weight: 600;">DISETUJUI</span>. Barang siap untuk diambil di Laboratorium IoT.
                                    </p>
                                @elseif($emailData['emailType'] === 'borrowed')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        Anda saat ini sedang meminjam barang dari Laboratorium IoT. Kami mengingatkan untuk menjaga kondisi barang dan melakukan tracking secara berkala.
                                    </p>
                                @elseif($emailData['emailType'] === 'h_minus_one')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        <span style="color: #0B3B65; font-weight: 600;">PENGINGAT:</span> Besok adalah batas waktu pengembalian barang. Pastikan barang dikembalikan tepat waktu untuk menghindari status terlambat.
                                    </p>
                                @elseif($emailData['emailType'] === 'deadline')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        <span style="color: #0B3B65; font-weight: 600;">PENTING:</span> Hari ini adalah batas waktu pengembalian. Harap segera kembalikan barang untuk menghindari status terlambat.
                                    </p>
                                @elseif($emailData['emailType'] === 'overdue')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7; color: #dc2626; font-weight: 600;">
                                        Peminjaman Anda telah melewati batas waktu pengembalian. Harap segera kembalikan barang untuk menghindari konsekuensi lebih lanjut.
                                    </p>
                                @elseif($emailData['emailType'] === 'pending_return')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        Pengajuan pengembalian Anda sedang diproses oleh admin. Harap tunggu konfirmasi lebih lanjut.
                                    </p>
                                @endif
                            </div>

                            <!-- Detail Peminjaman Card -->
                            <div style="background-color: #f4f7fc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin: 30px 0; border-left: 4px solid #0B3B65;">
                                <h3 style="margin: 0 0 20px; font-size: 18px; color: #0B3B65; font-weight: 600;">Detail Peminjaman:</h3>
                                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                    <tr>
                                        <td width="40%" style="padding: 10px 10px 10px 0; font-weight: 600; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">Nama Barang</td>
                                        <td style="padding: 10px 0; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">: {{ $emailData['itemName'] }}</td>
                                    </tr>
                                    <tr>
                                        <td width="40%" style="padding: 10px 10px 10px 0; font-weight: 600; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">Jumlah</td>
                                        <td style="padding: 10px 0; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">: {{ $emailData['itemQuantity'] ?? '1' }} unit</td>
                                    </tr>
                                    <tr>
                                        <td width="40%" style="padding: 10px 10px 10px 0; font-weight: 600; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">Tanggal Peminjaman</td>
                                        <td style="padding: 10px 0; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">: {{ $emailData['borrowDate'] ?? '-' }}</td>
                    </tr>
                    <tr>
                                        <td width="40%" style="padding: 10px 10px 10px 0; font-weight: 600; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">Batas Pengembalian</td>
                                        <td style="padding: 10px 0; color: #444; vertical-align: top; font-size: 15px; border-bottom: 1px solid #e5e7eb;">: {{ $emailData['deadline'] }}</td>
                    </tr>
                    <tr>
                                        <td width="40%" style="padding: 10px 10px 10px 0; font-weight: 600; color: #444; vertical-align: top; font-size: 15px;">Status</td>
                                        <td style="padding: 10px 0; vertical-align: top; font-size: 15px;">: 
                    @if($emailData['status'] === 'pending')
                                <span>Menunggu Persetujuan</span>
                    @elseif($emailData['status'] === 'approved')
                                            <span style="color: #16a34a; font-weight: 600;">Disetujui</span>
                    @elseif($emailData['status'] === 'rejected')
                                            <span style="color: #dc2626; font-weight: 600;">Ditolak</span>
                            @elseif($emailData['status'] === 'borrowed')
                                            <span style="color: #0B3B65; font-weight: 600;">Sedang Dipinjam</span>
                            @elseif($emailData['status'] === 'h_minus_one')
                                            <span style="color: #0B3B65; font-weight: 600;">H-1 Pengembalian</span>
                            @elseif($emailData['status'] === 'deadline')
                                            <span style="color: #0B3B65; font-weight: 600;">Hari H Pengembalian</span>
                    @elseif($emailData['status'] === 'pending-return')
                                <span>Menunggu Verifikasi Pengembalian</span>
                    @elseif($emailData['status'] === 'overdue')
                                            <span style="color: #dc2626; font-weight: 600;">Melewati Deadline</span>
                    @endif
                        </td>
                    </tr>
                </table>
            </div>
            
                            <!-- Instructions Section -->
                            <div style="margin-bottom: 25px; color: #444;">
            @if($emailData['status'] === 'approved')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        Silakan ambil barang di Laboratorium IoT dengan membawa KTM atau identitas resmi. Setelah mengambil barang, status peminjaman akan diperbarui.
                                    </p>
            @elseif($emailData['status'] === 'borrowed' || $emailData['status'] === 'h_minus_one' || $emailData['status'] === 'deadline')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">Selama masa peminjaman, harap:</p>
                                    <ul style="margin: 0 0 15px; padding-left: 20px;">
                                        <li style="margin-bottom: 8px; font-size: 15px;">Jaga kondisi barang tetap baik dan bersih</li>
                                        <li style="margin-bottom: 8px; font-size: 15px;">Upload foto kondisi barang secara berkala</li>
                                        <li style="margin-bottom: 8px; font-size: 15px;">Kembalikan barang tepat waktu</li>
        </ul>
            @elseif($emailData['status'] === 'overdue')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7; color: #dc2626; font-weight: 600;">
                                        Keterlambatan pengembalian dapat mempengaruhi histori peminjaman Anda dan dapat berpengaruh pada peminjaman berikutnya.
                                    </p>
        @elseif($emailData['status'] === 'pending-return')
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">
                                        Admin akan memverifikasi barang yang dikembalikan. Setelah proses verifikasi selesai, status peminjaman akan diperbarui.
                                    </p>
        @endif
                            </div>
        
                            <!-- Action Button -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
            @if($emailData['status'] === 'overdue' || $emailData['status'] === 'deadline')
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $emailData['returnUrl'] }}" style="height:45px;v-text-anchor:middle;width:240px;" arcsize="10%" stroke="f" fillcolor="#dc2626">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">Segera Kembalikan Barang</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <a href="{{ $emailData['returnUrl'] }}" target="_blank" style="display: inline-block; background-color: #dc2626; color: #ffffff; font-weight: 600; padding: 13px 30px; border-radius: 6px; text-decoration: none; font-size: 16px; line-height: 1.4; text-align: center; transition: all 0.2s ease; box-shadow: 0 3px 12px rgba(220, 38, 38, 0.2); -webkit-appearance: none; mso-hide: all;">Segera Kembalikan Barang</a>
            @else
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $emailData['returnUrl'] }}" style="height:45px;v-text-anchor:middle;width:240px;" arcsize="10%" stroke="f" fillcolor="#0B3B65">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">Lihat Detail Peminjaman</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <a href="{{ $emailData['returnUrl'] }}" target="_blank" style="display: inline-block; background-color: #0B3B65; color: #ffffff; font-weight: 600; padding: 13px 30px; border-radius: 6px; text-decoration: none; font-size: 16px; line-height: 1.4; text-align: center; transition: all 0.2s ease; box-shadow: 0 3px 12px rgba(11, 59, 101, 0.2); -webkit-appearance: none; mso-hide: all;">Lihat Detail Peminjaman</a>
            @endif
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Signature -->
                            <div style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 5px; font-size: 15px; color: #444;">Terima kasih atas perhatian dan kerjasamanya,</p>
                                <p style="margin: 15px 0 0; font-weight: 600; color: #0B3B65; font-size: 16px;">Tim Laboratorium IoT</p>
                                <p style="margin: 5px 0 0; color: #666; font-size: 14px;">Fakultas Vokasi, Universitas Brawijaya</p>
        </div>
                        </td>
                    </tr>
                    
                    <!-- Footer area -->
                    <tr>
                        <td style="background-color: #f8f9fb; border-top: 1px solid #e5e7eb; padding: 25px; text-align: center;">
                            <p style="margin: 0 0 10px; font-size: 13px; color: #666;">
                                Email ini dikirim otomatis oleh Sistem Peminjaman Laboratorium IoT.
                                Jika ada pertanyaan, silakan hubungi administrator laboratorium.
                            </p>
                            <p style="margin: 10px 0 0; font-size: 12px; color: #888;">
                                Laboratorium IoT &bull; Fakultas Vokasi &bull; Universitas Brawijaya<br>
                                Jl. Veteran No. 12-14, Malang, Jawa Timur, 65145
                            </p>
                            <p style="margin: 15px 0 0; font-size: 12px; color: #888;">
                                &copy; {{ date('Y') }} Universitas Brawijaya. Hak Cipta Dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html> 