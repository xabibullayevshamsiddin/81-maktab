<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif;">
    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th colspan="{{ (!isset($selectedExamId) || !$selectedExamId) ? 9 : 8 }}" style="background-color: #1e3a8a; color: #ffffff; font-size: 18px; font-weight: bold; text-align: center; padding: 15px; border: 1px solid #1e3a8a;">
                    IMTIHON NATIJALARI HISOBOTI
                    <br>
                    <span style="font-size: 12px; font-weight: normal; color: #bfdbfe;">Sana: {{ now()->format('d.m.Y H:i') }}</span>
                </th>
            </tr>
            <tr>
                <th width="40" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">#</th>
                <th width="250" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: left;">O'quvchi ismi</th>
                <th width="180" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: left;">Telefon / Email</th>
                <th width="110" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">Sinf</th>
                @if(!isset($selectedExamId) || !$selectedExamId)
                    <th width="250" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: left;">Imtihon</th>
                @endif
                <th width="120" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">To'plagan Ball</th>
                <th width="120" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">Max ball</th>
                <th width="130" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">Natija</th>
                <th width="210" style="background-color: #3b82f6; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #2563eb; text-align: center;">Topshirgan Sana</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
                @php
                    $bgColor = $index % 2 === 1 ? '#f8fafc' : '#ffffff';
                    $passedColor = $result->passed ? '#dcfce7' : '#fee2e2';
                    $passedTextColor = $result->passed ? '#166534' : '#991b1b';
                    $passedText = $result->passed ? "O'tdi" : 'Yiqildi';
                    $phone = (string) ($result->user->phone ?? '');
                    $email = (string) ($result->user->email ?? '');
                    $contactText = $phone !== '' ? $phone : ($email !== '' ? $email : '-');
                @endphp
                <tr>
                    <td style="background-color: {{ $bgColor }}; color: #64748b; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $index + 1 }}</td>
                    <td style="background-color: {{ $bgColor }}; color: #0f172a; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->user->name ?? trim(($result->user->first_name ?? '') . ' ' . ($result->user->last_name ?? '')) }}</td>
                    <td style="background-color: {{ $bgColor }}; color: #334155; font-weight: 600; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px; mso-number-format:'\@';">{{ $contactText }}</td>
                    <td style="background-color: {{ $bgColor }}; color: #334155; font-weight: 600; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->user_grade ?? $result->user->grade ?? '—' }}</td>
                    @if(!isset($selectedExamId) || !$selectedExamId)
                        <td style="background-color: {{ $bgColor }}; color: #334155; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->exam->title ?? '-' }}</td>
                    @endif
                    <td style="background-color: #eff6ff; color: #1d4ed8; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->points_earned ?? 0 }}</td>
                    <td style="background-color: {{ $bgColor }}; color: #64748b; font-weight: 600; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->points_max ?? 0 }}</td>
                    <td style="background-color: {{ $passedColor }}; color: {{ $passedTextColor }}; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $passedText }}</td>
                    <td style="background-color: {{ $bgColor }}; color: #64748b; font-weight: 600; text-align: center; vertical-align: middle; border: 1px solid #cbd5e1; padding: 8px;">{{ $result->submitted_at?->format('d.m.Y H:i') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
