<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'AICI Notification' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #255d74 0%, #1a4557 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .email-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-top: 8px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #255d74;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .email-body p {
            color: #555555;
            font-size: 15px;
            margin-bottom: 15px;
            line-height: 1.8;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #255d74;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            color: #255d74;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666666;
            min-width: 140px;
            font-size: 14px;
        }
        .info-value {
            color: #333333;
            font-size: 14px;
            flex: 1;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #ff4d30 0%, #e63c1e 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 77, 48, 0.3);
        }
        .button:hover {
            background: linear-gradient(135deg, #e63c1e 0%, #ff4d30 100%);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            color: #666666;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #255d74;
            text-decoration: none;
            font-size: 13px;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 30px 20px;
            }
            .email-body {
                padding: 30px 20px;
            }
            .email-footer {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ $headerTitle ?? 'AICI' }}</h1>
            <p>{{ $headerSubtitle ?? 'Artificial Intelligence Center Indonesia' }}</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>AICI - Artificial Intelligence Center Indonesia</strong></p>
            <p>{{ config('app.address', 'Jl. Contoh No. 123, Jakarta') }}</p>
            <p>
                Email: {{ config('app.email', 'info@aici.id') }} | 
                Phone: {{ config('app.phone', '+62 21 1234 5678') }}
            </p>
            
            <div class="social-links">
                <a href="{{ config('app.instagram_url', '#') }}">Instagram</a> |
                <a href="{{ config('app.linkedin_url', '#') }}">LinkedIn</a> |
                <a href="{{ config('app.youtube_url', '#') }}">YouTube</a>
            </div>

            <div class="divider"></div>

            <p style="font-size: 12px; color: #999999;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi kami melalui kontak di atas.
            </p>
            
            <p style="font-size: 11px; color: #999999; margin-top: 15px;">
                © {{ date('Y') }} AICI. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
