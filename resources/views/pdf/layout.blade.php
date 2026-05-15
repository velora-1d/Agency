<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dokumen Legal')</title>
    <style>
        @page {
            margin: 2.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1c1917;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 2cm;
            border-bottom: 2px solid #1c1917;
            padding-bottom: 10px;
        }
        .brand-name {
            font-size: 24pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        .brand-tagline {
            font-size: 10pt;
            color: #57534e;
            margin: 5px 0 0 0;
        }
        .document-title {
            text-align: center;
            text-transform: uppercase;
            font-size: 16pt;
            font-weight: bold;
            margin-top: 1cm;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .document-subtitle {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1cm;
        }
        h2 {
            font-size: 12pt;
            text-transform: uppercase;
            border-bottom: 1px solid #e7e5e4;
            padding-bottom: 5px;
            margin-top: 1cm;
        }
        h3 {
            font-size: 11pt;
            margin-top: 0.8cm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1cm;
        }
        table.no-border td {
            border: none;
            padding: 5px 0;
            vertical-align: top;
        }
        table.bordered th, table.bordered td {
            border: 1px solid #d6d3d1;
            padding: 8px;
            text-align: left;
        }
        table.bordered th {
            background-color: #f5f5f4;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mt-10 { margin-top: 1cm; }
        .mb-0 { margin-bottom: 0; }
        
        .signature-section {
            margin-top: 2cm;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            float: left;
        }
        .signature-box.right {
            float: right;
        }
        .signature-space {
            height: 2.5cm;
        }
        .footer {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #a8a29e;
            text-align: center;
            border-top: 1px solid #e7e5e4;
            padding-top: 5px;
        }
        .page-number:after {
            content: counter(page);
        }
        .prose {
            text-align: justify;
        }
        .stamp-duty {
            font-size: 8pt;
            font-style: italic;
            color: #78716c;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="brand-name">{{ $workspace->name }}</h1>
        <p class="brand-tagline">Digital Agency & Technology Solutions</p>
    </div>

    @yield('content')

    <div class="footer">
        &copy; {{ date('Y') }} {{ $workspace->name }} &mdash; Dokumen ini bersifat rahasia dan mengikat secara hukum &mdash; Halaman <span class="page-number"></span>
    </div>
</body>
</html>
