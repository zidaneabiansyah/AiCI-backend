<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 40px;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #1a56db;
            text-decoration: none;
        }
        .company-info {
            float: right;
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .content {
            margin-bottom: 60px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .table th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .status-paid { background-color: #def7ec; color: #03543f; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mt-10 { margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                <strong>AICI UMG</strong><br>
                Universitas Muhammadiyah Gresik<br>
                Jl. Sumatera No.101, Randuagung<br>
                Gresik, Jawa Timur 61121
            </div>
            <div class="logo">AICI UMG</div>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} AICI-UMG. All rights reserved.<br>
            Dokumen ini dihasilkan secara otomatis dan sah tanpa tanda tangan.
        </div>
    </div>
</body>
</html>
