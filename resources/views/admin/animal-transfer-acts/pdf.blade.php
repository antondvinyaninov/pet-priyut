<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акт приема-передачи {{ $animalTransferAct->act_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .row {
            display: flex;
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            min-width: 150px;
        }
        .value {
            flex: 1;
        }
        .parties {
            display: flex;
            gap: 40px;
            margin-bottom: 20px;
        }
        .party {
            flex: 1;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
        }
        .party-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .animals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .animals-table th,
        .animals-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .animals-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            min-width: 200px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        .status {
            float: right;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status.draft {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.signed {
            background-color: #d4edda;
            color: #155724;
        }
        .description {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        @media print {
            body {
                margin: 0;
                font-size: 11px;
            }
            .status {
                float: none;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- Заголовок -->
    <div class="header">
        <h1>АКТ ПРИЕМА-ПЕРЕДАЧИ ЖИВОТНЫХ</h1>
        <p>№ {{ $animalTransferAct->act_number }}</p>
        <p>от {{ \Carbon\Carbon::parse($animalTransferAct->act_date)->format('d.m.Y') }}</p>
        <span class="status {{ $animalTransferAct->status }}">
            @if($animalTransferAct->status === 'draft')
                ЧЕРНОВИК
            @else
                ПОДПИСАН
            @endif
        </span>
    </div>

    <!-- Основная информация -->
    <div class="section">
        <div class="section-title">Основная информация</div>
        <div class="row">
            <div class="label">Номер акта:</div>
            <div class="value">{{ $animalTransferAct->act_number }}</div>
        </div>
        <div class="row">
            <div class="label">Дата акта:</div>
            <div class="value">{{ \Carbon\Carbon::parse($animalTransferAct->act_date)->format('d.m.Y') }}</div>
        </div>
        <div class="row">
            <div class="label">Дата создания:</div>
            <div class="value">{{ $animalTransferAct->created_at->format('d.m.Y H:i') }}</div>
        </div>
        @if($animalTransferAct->signed_at)
            <div class="row">
                <div class="label">Дата подписания:</div>
                <div class="value">{{ $animalTransferAct->signed_at->format('d.m.Y H:i') }}</div>
            </div>
        @endif
    </div>

    <!-- Стороны -->
    <div class="section">
        <div class="section-title">Стороны передачи</div>
        <div class="parties">
            <div class="party">
                <div class="party-title">ПЕРЕДАЮЩАЯ СТОРОНА</div>
                <div class="row">
                    <div class="label">Организация:</div>
                    <div class="value">{{ $animalTransferAct->from_organization }}</div>
                </div>
                <div class="row">
                    <div class="label">Ответственное лицо:</div>
                    <div class="value">{{ $animalTransferAct->from_person }}</div>
                </div>
                @if($animalTransferAct->from_position)
                    <div class="row">
                        <div class="label">Должность:</div>
                        <div class="value">{{ $animalTransferAct->from_position }}</div>
                    </div>
                @endif
            </div>
            <div class="party">
                <div class="party-title">ПРИНИМАЮЩАЯ СТОРОНА</div>
                <div class="row">
                    <div class="label">Организация:</div>
                    <div class="value">{{ $animalTransferAct->to_organization }}</div>
                </div>
                <div class="row">
                    <div class="label">Ответственное лицо:</div>
                    <div class="value">{{ $animalTransferAct->to_person }}</div>
                </div>
                @if($animalTransferAct->to_position)
                    <div class="row">
                        <div class="label">Должность:</div>
                        <div class="value">{{ $animalTransferAct->to_position }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Список животных -->
    <div class="section">
        <div class="section-title">Передаваемые животные</div>
        @if($animalTransferAct->animals->count() > 0)
            <table class="animals-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Кличка</th>
                        <th>Вид</th>
                        <th>Пол</th>
                        <th>Порода</th>
                        <th>Окрас</th>
                        <th>Вольер</th>
                        <th>Чип</th>
                        <th>Бирка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($animalTransferAct->animals as $index => $animal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $animal->name ?? 'Без имени' }}</td>
                            <td>{{ ucfirst($animal->type) }}</td>
                            <td>{{ ucfirst($animal->gender) }}</td>
                            <td>{{ $animal->breed ?? '-' }}</td>
                            <td>{{ $animal->color ?? '-' }}</td>
                            <td>{{ $animal->cage_number ?? '-' }}</td>
                            <td>{{ $animal->chip_number ?? '-' }}</td>
                            <td>{{ $animal->tag_number ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p><strong>Всего животных:</strong> {{ $animalTransferAct->animals->count() }}</p>
        @else
            <p>Нет животных для передачи</p>
        @endif
    </div>

    <!-- Дополнительная информация -->
    <div class="section">
        <div class="section-title">Дополнительная информация</div>
        
        <div class="row">
            <div class="label">Причина передачи:</div>
        </div>
        <div class="description">
            {{ $animalTransferAct->transfer_reason }}
        </div>

        @if($animalTransferAct->conditions)
            <div class="row">
                <div class="label">Условия передачи:</div>
            </div>
            <div class="description">
                {{ $animalTransferAct->conditions }}
            </div>
        @endif

        @if($animalTransferAct->notes)
            <div class="row">
                <div class="label">Примечания:</div>
            </div>
            <div class="description">
                {{ $animalTransferAct->notes }}
            </div>
        @endif
    </div>

    <!-- Подписи -->
    <div class="signatures">
        <div class="signature">
            <div class="signature-line"></div>
            <div>{{ $animalTransferAct->from_person }}</div>
            <div>({{ $animalTransferAct->from_organization }})</div>
            <div>Передающая сторона</div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <div>{{ $animalTransferAct->to_person }}</div>
            <div>({{ $animalTransferAct->to_organization }})</div>
            <div>Принимающая сторона</div>
        </div>
    </div>

    <!-- Дата и подпись -->
    <div style="margin-top: 30px; text-align: center;">
        <p>
            <strong>Дата составления акта:</strong> {{ \Carbon\Carbon::parse($animalTransferAct->act_date)->format('d.m.Y') }}
        </p>
        @if($animalTransferAct->signed_at)
            <p>
                <strong>Дата подписания:</strong> {{ $animalTransferAct->signed_at->format('d.m.Y H:i') }}
            </p>
        @endif
        <p>
            <strong>Создал:</strong> {{ $animalTransferAct->creator->name ?? 'Неизвестно' }}
        </p>
    </div>
</body>
</html> 