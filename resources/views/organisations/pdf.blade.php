<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Organigramme</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            margin-top: 0;
        }
        .org-list {
            width: 100%;
            margin-bottom: 20px;
        }
        .org-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .org-content {
            padding: 8px;
            margin-bottom: 5px;
        }
        .root {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            margin-top: 20px;
        }
        .unit {
            border-left: 2px solid #dee2e6;
        }
        .code {
            color: #0d6efd;
            font-weight: bold;
            margin-right: 10px;
        }
        .name {
            font-weight: bold;
        }
        .description {
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Organigramme</h1>
    <p>Généré le {{ date('d/m/Y') }}</p>
</div>

<div class="org-list">
    @foreach($hierarchy as $item)
        @include('organisations.pdf-item', ['item' => $item])
    @endforeach
</div>

<div class="footer">
    Organigramme - Page {PAGE_NUM} sur {PAGE_COUNT}
</div>
</body>
</html>
