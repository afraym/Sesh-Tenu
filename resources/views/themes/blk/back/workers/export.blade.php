@php
    use Carbon\Carbon;

    $projectNameAr = config('app.project_name_ar', 'محطة كهرباء أبو سمبل٢ للطاقة الشمسية بقدرة 1000 ميجاوات');
    $projectNameEn = config('app.project_name_en', 'PV Power Plant Abydos 2 Solar (MW1000)');
    $consortiumName = config('app.project_consortium', 'تحالف الشيماء الزراعية للمقاولات والتوريدات FM+ للمقاولات');

    $logoPath = public_path('logos/energychina.png');
    $hasLogo = file_exists($logoPath);

    $arialRegular = 'file://' . str_replace('\\', '/', public_path('assets/fonts/arial/ARIAL.TTF'));
    $arialBold = 'file://' . str_replace('\\', '/', public_path('assets/fonts/arial/ARIALBD.TTF'));

    $baseDate = $worker->join_date ? $worker->join_date->copy() : now();
    $monthStart = $baseDate->startOfMonth();
    $daysInMonth = $monthStart->daysInMonth;
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl" >
<head>
    <meta charset="UTF-8">
    <title>كشف عمالة</title>
    <style>
        @page { margin: 8mm; }

        @font-face {
            font-family: 'ArialCustom';
            src: url('{{ $arialRegular }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'ArialCustom';
            src: url('{{ $arialBold }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        body {
            font-family: 'ArialCustom', 'DejaVu Sans', 'Arial', 'Amiri', 'Scheherazade New', 'Tahoma', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }

        .page {
            border: 1px solid #bfbfbf;
            padding: 10px 12px 6px;
            direction: rtl;
        }

        .logo-row {
            width: 100%;
            text-align: center;
            margin-bottom: 6px;
        }

        .logo {
            height: 42px;
            object-fit: contain;
        }

        .logo-fallback {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 1px;
            color: #0b5394;
            text-transform: uppercase;
        }

        .header-table,
        .identity-table,
        .timesheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .rtl {
            direction: rtl;
            unicode-bidi: embed;
        }

        .header-table th,
        .header-table td,
        .identity-table th,
        .identity-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
        }

        .header-table .project-block {
            text-align: center;
            font-weight: 700;
            line-height: 1.55;
        }

        .header-table .project-block .ar {
            font-size: 15px;
            font-weight: 800;
        }

        .header-table .project-block .en {
            font-size: 13px;
            font-weight: 800;
        }

        .header-table .project-block .cons {
            font-size: 12px;
            font-weight: 700;
        }

        .header-table .label {
            background: #d9e1f2;
            font-weight: 700;
            width: 16%;
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
        }

        .header-table .value {
            font-weight: 800;
            font-size: 12px;
            text-align: center;
        }

        .identity-table .label {
            background: #d9e1f2;
            font-weight: 700;
            text-align: center;
            width: 18%;
            line-height: 1.35;
            font-size: 11px;
        }

        .identity-table .value {
            font-weight: 800;
            text-align: right;
            font-size: 13px;
            padding-right: 10px;
        }

        .timesheet th,
        .timesheet td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            height: 22px;
        }

        .timesheet thead th {
            background: #d9e1f2;
            font-weight: 700;
        }

        .timesheet .weekend td {
            background: #b30000;
            color: #fff;
            font-weight: 700;
        }

        .timesheet .wide {
            width: 16%;
        }

        .timesheet .narrow {
            width: 9%;
        }

        .timesheet .serial {
            width: 7%;
        }

        .timesheet .date {
            width: 11%;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="logo-row">
        @if($hasLogo)
            <img src="{{ $logoPath }}" alt="Energy China" class="logo">
        @else
            <div class="logo-fallback">ENERGY CHINA</div>
        @endif
    </div>

    <table class="header-table rtl">
        <tr>
            <td class="project-block rtl" rowspan="3">
                <div class="ar">{{ $projectNameAr }}</div>
                <div class="en">{{ $projectNameEn }}</div>
                <div class="cons">{{ $consortiumName }}</div>
            </td>
            <td class="label">Project Name<br><span style="font-size:11px; font-weight:800;">اسم المشروع</span></td>
            <td class="value" colspan="2">{{ $projectNameEn }}</td>
        </tr>
        <tr>
            <td class="label">Company Name<br><span style="font-size:11px; font-weight:800;">اسم الشركة</span></td>
            <td class="value" colspan="2">{{ optional($worker->company)->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Worker Name<br><span style="font-size:11px; font-weight:800;">اسم العامل</span></td>
            <td class="value" colspan="2" style="text-align:right; padding-right:10px;">{{ $worker->name }}</td>
        </tr>
        <tr>
            <td class="project-block">&nbsp;</td>
            <td class="label">Worker Job<br><span style="font-size:11px; font-weight:800;">وظيفة العامل</span></td>
            <td class="value">{{ optional($worker->jobType)->name ?? '-' }}</td>
            <td class="value">
                <div style="font-weight:800; font-size:11px; line-height:1.35;">Access Code<br><span style="font-size:11px; font-weight:800;">كود الدخول</span></div>
                <div style="font-size:13px; font-weight:900;">{{ $worker->entity ?? $worker->id }}</div>
            </td>
        </tr>
    </table>

    <table class="identity-table rtl" style="margin-top: 4px;">
        <tr>
            <td class="value" style="font-size: 14px; font-weight: 900;">{{ $worker->national_id ?? '-' }}</td>
            <td class="label" style="width: 18%;">ID Number<br><span style="font-size:11px; font-weight:800;">رقم البطاقة</span></td>
            <td class="value" style="font-size: 14px; font-weight: 900;">{{ $worker->phone_number ?? '-' }}</td>
            <td class="label" style="width: 18%;">Mobile Number<br><span style="font-size:11px; font-weight:800;">رقم الهاتف</span></td>
        </tr>
    </table>

    <table class="timesheet" style="margin-top: 6px;">
        <thead>
            <tr>
                <th class="serial">رقم مسلسل</th>
                <th class="date">التاريخ</th>
                <th class="narrow">بداية العمل</th>
                <th class="narrow">نهاية العمل</th>
                <th class="narrow">ساعات الراحة</th>
                <th class="narrow">عدد ساعات العمل</th>
                <th class="wide">مكان العمل</th>
                <th class="wide">ملاحظات</th>
                <th class="narrow">توقيع المشرف</th>
                <th class="narrow">توقيع المهندس المباشر</th>
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
</div>
</body>
</html>
