@php
    /**
     * Print-ready report.
     *
     * "PDF" is produced by handing the browser a clean document and opening its
     * print dialog, rather than bundling a PDF engine. For tabular reports the
     * browser's own renderer gives a better result — real text, selectable and
     * searchable, correct page breaks and repeated table headers — and it keeps
     * the dependency list short.
     */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { --line: #e5e7eb; --ink: #111827; --muted: #6b7280; --brand: #4f46e5; }

        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f4f7;
            color: var(--ink);
            margin: 0;
            padding: 24px;
            font-size: 12px;
        }

        .sheet {
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 28px 30px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
        }

        .sheet-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 2px solid var(--brand);
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        h1 { font-size: 19px; margin: 0 0 4px; letter-spacing: -.02em; }
        .sub { color: var(--muted); font-size: 12px; margin: 0; }

        .meta { text-align: right; color: var(--muted); font-size: 11.5px; line-height: 1.7; }
        .meta strong { color: var(--ink); }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: #f3f4f6;
            text-align: left;
            font-size: 10px;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 8px 9px;
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }

        tbody td {
            padding: 8px 9px;
            border-bottom: 1px solid #f1f2f5;
            vertical-align: top;
            word-break: break-word;
        }

        tbody tr:nth-child(even) { background: #fafbfc; }

        .empty { text-align: center; color: var(--muted); padding: 40px 0; }

        .toolbar {
            max-width: 1400px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .toolbar button, .toolbar a {
            font: inherit;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar .primary { background: var(--brand); border-color: var(--brand); color: #fff; }

        @media print {
            body { background: #fff; padding: 0; font-size: 9.5px; }
            .sheet { box-shadow: none; border-radius: 0; padding: 0; max-width: none; }
            .toolbar { display: none !important; }
            thead { display: table-header-group; }   /* repeat headers on every page */
            tr { page-break-inside: avoid; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">Save as PDF / Print</button>
        <a href="javascript:window.close()">Close</a>
    </div>

    <div class="sheet">
        <div class="sheet-head">
            <div>
                <h1>{{ $title }}</h1>
                <p class="sub">{{ config('app.name') }} — admin report</p>
            </div>
            <div class="meta">
                <div>Generated <strong>{{ now()->format('d M Y, h:i A') }}</strong></div>
                @foreach ($meta as $label => $value)
                    <div>{{ $label }}: <strong>{{ $value }}</strong></div>
                @endforeach
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $count = 0; @endphp
                @foreach ($rows as $row)
                    @php $count++; @endphp
                    <tr>
                        @foreach ((array) $row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach

                @if ($count === 0)
                    <tr>
                        <td colspan="{{ count($headers) }}" class="empty">
                            Nothing matched the current filters.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <script>
        // Open the print dialog straight away — this page exists only to be printed.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 350);
        });
    </script>
</body>
</html>
