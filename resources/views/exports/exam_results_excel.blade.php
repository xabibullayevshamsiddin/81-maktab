<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .table-excel { border-collapse: collapse; width: 100%; }
        .table-excel th {
            background-color: #0d3f78;
            color: #ffffff;
            font-weight: bold;
            padding: 12px 8px;
            border: 1px solid #0a2f5e;
            text-align: left;
        }
        .table-excel td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .color-primary { color: #0d3f78; }
        .color-success { color: #16a34a; }
        .color-danger { color: #b91c1c; }
        .bg-light { background-color: #f8fafc; }
    </style>
</head>
<body>
    <table class="table-excel">
        <thead>
            <tr>
                <th width="30">#</th>
                <th width="200">O'quvchi ismi</th>
                <th width="150">Telefon / Email</th>
                <th width="100">Sinf</th>
                @if(!isset($selectedExamId) || !$selectedExamId)
                    <th width="200">Imtihon</th>
                @endif
                <th width="80" class="text-center">Ball</th>
                <th width="80" class="text-center">Max ball</th>
                <th width="100" class="text-center">Natija</th>
                <th width="120" class="text-center">Sana</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
                <tr @if($index % 2 === 1) class="bg-light" @endif>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $result->user->name ?? $result->user->first_name . ' ' . $result->user->last_name }}</td>
                    <td>{{ $result->user->phone ?? $result->user->email ?? '-' }}</td>
                    <td>{{ $result->user_grade ?? $result->user->grade ?? '—' }}</td>
                    @if(!isset($selectedExamId) || !$selectedExamId)
                        <td>{{ $result->exam->title ?? '-' }}</td>
                    @endif
                    <td class="text-center font-bold color-primary">{{ $result->points_earned ?? 0 }}</td>
                    <td class="text-center">{{ $result->points_max ?? 0 }}</td>
                    <td class="text-center font-bold">
                        @if($result->passed)
                            <span class="color-success">O'tdi</span>
                        @else
                            <span class="color-danger">Yiqildi</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $result->submitted_at?->format('d.m.Y H:i') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
