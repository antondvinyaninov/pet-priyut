<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акт отлова № {{ $act->act_number }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10pt;
            margin: 5px 0;
        }
        .content {
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .field-row {
            display: flex;
            margin-bottom: 10px;
        }
        .field-label {
            width: 250px;
            font-weight: bold;
        }
        .field-value {
            flex: 1;
        }
        .footer {
            margin-top: 50px;
        }
        .signature {
            display: inline-block;
            width: 45%;
            margin-top: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 200px;
            margin-left: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>АКТ ОТЛОВА БЕЗНАДЗОРНОГО ЖИВОТНОГО</h1>
        <p>№ {{ $act->act_number }} от {{ $act->capture_date->format('d.m.Y') }} г.</p>
    </div>
    
    <div class="content">
        <div class="section">
            <p>
                Настоящий акт составлен о том, что {{ $act->user ? $act->user->name : '____________________' }} 
                произведен отлов животного без владельца (безнадзорного животного)
                {{ $act->capture_date->format('d.m.Y') }}
                {{ $act->capture_time ? ' в ' . \Carbon\Carbon::parse($act->capture_time)->format('H:i') : '' }}
                по адресу: {{ $act->capture_location }}.
            </p>
        </div>
        
        <div class="section">
            <div class="section-title">Информация о животном</div>
            
            <div class="field-row">
                <div class="field-label">Вид животного:</div>
                <div class="field-value">
                    @if($act->animal_type === 'cat')
                        Кошка
                    @elseif($act->animal_type === 'dog')
                        Собака
                    @elseif($act->animal_type)
                        {{ $act->animal_type }}
                    @else
                        ____________________
                    @endif
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Пол:</div>
                <div class="field-value">
                    @if($act->animal_gender === 'male')
                        Самец
                    @elseif($act->animal_gender === 'female')
                        Самка
                    @elseif($act->animal_gender)
                        {{ $act->animal_gender }}
                    @else
                        ____________________
                    @endif
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Порода:</div>
                <div class="field-value">
                    {{ $act->animal_breed ?: '____________________' }}
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Окрас:</div>
                <div class="field-value">
                    {{ $act->animal_color ?: '____________________' }}
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Размер:</div>
                <div class="field-value">
                    @if($act->animal_size === 'small')
                        Маленький
                    @elseif($act->animal_size === 'medium')
                        Средний
                    @elseif($act->animal_size === 'large')
                        Крупный
                    @elseif($act->animal_size)
                        {{ $act->animal_size }}
                    @else
                        ____________________
                    @endif
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Способ отлова:</div>
                <div class="field-value">
                    @if($act->capturing_method === 'net')
                        Сеть
                    @elseif($act->capturing_method === 'cage')
                        Клетка-ловушка
                    @elseif($act->capturing_method === 'pole')
                        Сачок
                    @elseif($act->capturing_method === 'hand')
                        Руками
                    @elseif($act->capturing_method)
                        {{ $act->capturing_method }}
                    @else
                        ____________________
                    @endif
                </div>
            </div>
            
            @if($act->animal_features)
            <div class="field-row">
                <div class="field-label">Особые приметы:</div>
                <div class="field-value">
                    {{ $act->animal_features }}
                </div>
            </div>
            @endif
            
            @if($act->animal_behavior)
            <div class="field-row">
                <div class="field-label">Особенности поведения:</div>
                <div class="field-value">
                    {{ $act->animal_behavior }}
                </div>
            </div>
            @endif
        </div>
        
        @if($act->notes)
        <div class="section">
            <div class="section-title">Примечания</div>
            <p>{{ $act->notes }}</p>
        </div>
        @endif
        
        <div class="section">
            <p>Животное направлено в приют для последующего проведения мероприятий, предусмотренных порядком осуществления деятельности по обращению с животными без владельцев (ОСВВ).</p>
        </div>
        
        <div class="footer">
            <div class="signature">
                <div>Организация осуществляющая отлов:</div>
                <div>ООО "Центр обращения с животными"</div>
                <div>
                    <span>Подпись</span>
                    <span class="signature-line"></span>
                </div>
                <div>М.П.</div>
            </div>
            
            <div class="signature">
                <div>Специалист по отлову:</div>
                <div>{{ $act->user ? $act->user->name : '____________________' }}</div>
                <div>
                    <span>Подпись</span>
                    <span class="signature-line"></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 