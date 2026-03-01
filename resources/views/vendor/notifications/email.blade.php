<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Lab IoT - Fakultas Vokasi UB</title>
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
                                        <img src="{{ $message->embed(public_path('images/logo-vokasi-ub.png')) }}" alt="Logo Vokasi UB" style="max-width: 65px; height: auto; display: block;">
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
                            <!-- Greeting with user name if available -->
                            <h2 style="font-size: 20px; color: #0B3B65; margin: 0 0 25px; font-weight: 600; letter-spacing: -0.3px;">
                                Halo{{ isset($notifiable->name) ? ' '.$notifiable->name : '' }}!
                            </h2>

                            <!-- Intro Lines -->
                            <div style="margin-bottom: 25px; color: #444;">
@foreach ($introLines as $line)
                                    <p style="margin: 0 0 15px; font-size: 15px; line-height: 1.7;">{{ $line }}</p>
@endforeach
                            </div>

                            <!-- Action Button -->
@isset($actionText)
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td align="center" style="padding: 30px 0;">
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $actionUrl }}" style="height:45px;v-text-anchor:middle;width:200px;" arcsize="10%" stroke="f" fillcolor="#0B3B65">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">{{ $actionText }}</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; background-color: #0B3B65; color: #ffffff; font-weight: 600; padding: 13px 30px; border-radius: 6px; text-decoration: none; font-size: 16px; line-height: 1.4; text-align: center; transition: all 0.2s ease; box-shadow: 0 3px 12px rgba(11, 59, 101, 0.2); -webkit-appearance: none; mso-hide: all;">{{ $actionText }}</a>
                                        </td>
                                    </tr>
                                </table>

                                <div style="margin-top: 5px;">
                                    <p style="margin: 0 0 8px; font-size: 14px; color: #555;">Jika tombol di atas tidak berfungsi, Anda dapat menyalin dan menempelkan URL berikut ke browser Anda:</p>
                                    <div style="background-color: #f4f7fc; border: 1px solid #e5e7eb; border-left: 4px solid #0B3B65; padding: 15px; margin: 10px 0 20px; border-radius: 6px; word-break: break-all; font-family: monospace; font-size: 13px; color: #333;">
                                        {{ $displayableActionUrl }}
                                    </div>
                                </div>

                                <!-- Warning message box -->
                                <div style="background-color: #fffaf2; border: 1px solid #feebc8; border-left: 4px solid #f6ad55; border-radius: 6px; padding: 16px; margin: 25px 0;">
                                    <p style="margin: 0 0 8px; font-size: 14px; color: #744210;">
                                        <strong style="font-weight: 600;">Perhatian:</strong> Link reset password ini akan kedaluwarsa dalam 60 menit. 
                                    </p>
                                    <p style="margin: 0; font-size: 14px; color: #744210;">
                                        Jika Anda tidak meminta reset password, abaikan email ini.
                                    </p>
                                </div>
@endisset

                            <!-- Outro Lines - Menghapus duplikasi -->
                            <div style="margin: 25px 0 0; color: #444;">
@foreach ($outroLines as $line)
                                    @if (!str_contains($line, 'kedaluwarsa') && !str_contains($line, 'abaikan email'))
                                        <p style="margin: 0 0 10px; font-size: 15px; line-height: 1.7;">{{ $line }}</p>
                                    @endif
@endforeach
                            </div>

                            <!-- Signature -->
                            <div style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
@if (! empty($salutation))
                                    <p style="margin: 0 0 5px; font-size: 15px; color: #444;">{{ $salutation }}</p>
@else
                                    <p style="margin: 0 0 5px; font-size: 15px; color: #444;">Terima kasih atas perhatian dan kerjasamanya,</p>
                                    <p style="margin: 15px 0 0; font-weight: 600; color: #0B3B65; font-size: 16px;">Tim Laboratorium IoT</p>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 14px;">Fakultas Vokasi, Universitas Brawijaya</p>
@endif
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
