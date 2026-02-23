@php
    use Carbon\Carbon;

    $projectNameAr = optional($project)->name ?? '-';
    $projectNameEn = optional($project)->name ?? '-';
    $consortiumName = optional(optional($project)->company)->name
        ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'));

    $companyName = optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-');

    $logoAbsolute = public_path('logos/energychina.png');
    $logoPath = 'file://' . str_replace('\\', '/', $logoAbsolute);
    $hasLogo = file_exists($logoAbsolute);

    $arialRegularAbsolute = public_path('assets/fonts/arial/ARIAL.TTF');
    $arialBoldAbsolute = public_path('assets/fonts/arial/ARIALBD.TTF');
    $arialRegular = 'file://' . str_replace('\\', '/', $arialRegularAbsolute);
    $arialBold = 'file://' . str_replace('\\', '/', $arialBoldAbsolute);

    $baseDate = $worker->join_date ? $worker->join_date->copy() : now();
    $monthStart = $baseDate->copy()->startOfMonth();
    $daysInMonth = $monthStart->daysInMonth;
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف عمالة</title>
    <style>
        @page { margin: 8mm; }

        @if(file_exists($arialRegularAbsolute) && file_exists($arialBoldAbsolute))
        /* Embed local fonts so DOMPDF can resolve them on Linux servers */
        @font-face {
            font-family: 'ArialLocal';
            src: url('{{ $arialRegular }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'ArialLocal';
            src: url('{{ $arialBold }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }
        @endif

        body {
            font-family: 'DejaVu Sans', 'ArialLocal', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 auto; }
        td, th { border: 1px solid #000; padding: 5px; vertical-align: middle; }

        .logo-row { text-align: center; margin: 4px 0 6px; }
        .logo { height: 60px; object-fit: contain; }

        .label { background: #d9e1f2; font-weight: 700; text-align: center; font-size: 11px; line-height: 1.3; }
        .value { font-weight: 800; text-align: center; font-size: 11px; }
        .value-bold { font-weight: 900; font-size: 12px; }
        .header-block { text-align: center; font-weight: 800; line-height: 1.3; }
        .header-block .ar { font-size: 14px; font-weight: 900; }
        .header-block .en { font-size: 12px; font-weight: 900; }
        .header-block .cons { font-size: 11px; font-weight: 800; }

        .timesheet th { background: #d9e1f2; font-weight: 800; font-size: 11px; }
        .timesheet td { font-size: 11px; height: 22px; }
        .weekend td { background: #b30000; color: #fff; font-weight: 800; }
    </style>
</head>
<body>
    <div class="logo-row">
        @if($hasLogo)
            <img src="{{ $logoPath }}" alt="Energy China" class="logo">
        @else
            <div style="font-weight:900; font-size:18px; color:#0b5394;">ENERGY CHINA</div>
        @endif
    </div>

    <table dir="rtl" style="margin-bottom: 2px;">
        <tr>
            {{-- <td class="header-block" rowspan="3" style="width: 60%;">
                <div class="ar">{{ $projectNameAr }}</div>
                <div class="en">{{ $projectNameEn }}</div>
                <div class="cons">{{ $consortiumName }}</div>
            </td> --}}
            <td class="label" style="width: 20%;">Project Name<br><span style="font-weight:800;">اسم المشروع</span></td>
            <td class="value value-bold" style="width: 80%;">{{ $projectNameEn }}</td>
        </tr>
        <tr>
            <td class="label">Company Name<br><span style="font-weight:800;">اسم الشركة</span></td>
            <td class="value value-bold">{{ $companyName }}</td>
        </tr>
    </table>

    <table dir="rtl" style="margin-bottom: 4px;">
        <tr>
            <td class="label" style="width: 20%;">Worker Name<br><span style="font-weight:800;">اسم العامل</span></td>
            <td class="value value-bold" style="width: 30%;">{{ $worker->name }}</td>
                       <td class="label" style="width: 20%;">Mobile Number<br><span style="font-weight:800;">رقم الهاتف</span></td>
            <td class="value value-bold" style="width: 30%;">{{ $worker->phone_number ?? '-' }}</td>
     
        </tr>
        <tr>
            <td class="label" style="width: 20%;">Worker Job<br><span style="font-weight:800;">وظيفة العامل</span></td>
            <td class="value value-bold" style="width: 30%;">{{ optional($worker->jobType)->name ?? '-' }}</td>
            <td class="label" style="width: 20%;">ID Number<br><span style="font-weight:800;">رقم البطاقة</span></td>
            <td class="value value-bold" style="width: 30%;">{{ $worker->national_id ?? '-' }}</td>
        </tr>

        
    </table>

    <table  style="margin-bottom: 4px;">
        <tr>
            <td class="label">Access Code<br><span style="font-weight:800; width:20%">كود الدخول</span></td>
            <td class="value" style="text-align:center; font-weight:900; width:80%">{{ $worker->entity ?? $worker->id }}</td>
        </tr>
        </table>
    <table class="timesheet" dir="rtl">
        <thead>
            <tr>
                <th style="width: 5%;">رقم مسلسل</th>
                <th style="width: 12%;">التاريخ</th>
                <th style="width: 10%;">بداية العمل</th>
                <th style="width: 10%;">نهاية العمل</th>
                <th style="width: 10%;">ساعات الراحة</th>
                <th style="width: 10%;">عدد ساعات العمل</th>
                <th style="width: 15%;">مكان العمل</th>
                <th style="width: 12%;">ملاحظات</th>
                <th style="width: 13%;">توقيع المشرف</th>
                <th style="width: 13%;">توقيع المهندس المباشر</th>
            </tr>
        </thead>
        <tbody>
        @for($i = 0; $i < $daysInMonth; $i++)
            @php
                $day = $monthStart->copy()->addDays($i);
                $isWeekend = $day->isFriday();
            @endphp
            <tr class="{{ $isWeekend ? 'weekend' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $day->format('j/n/Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
        </tbody>
    </table>
</body>
</html>
