<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточка учета животного № {{ $animal->registrationCard->registration_number ?? $animal->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .card-info {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .field {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .field-inline {
            display: inline;
        }
        
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .signature-section {
            margin-top: 30px;
        }
        
        .signature-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 200px;
        }
        
        .photos-section {
            margin: 20px 0;
            display: flex;
            gap: 20px;
            justify-content: center;
            page-break-inside: avoid;
        }
        
        .photo-container {
            text-align: center;
            flex: 1;
            max-width: 45%;
        }
        
        .photo-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #000;
            display: block;
            margin: 0 auto 5px;
        }
        
        .photo-label {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #4F46E5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #4338CA;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Печать</button>
    
    <div class="container">
        <div class="header">
            <h1>КАРТОЧКА УЧЕТА ЖИВОТНОГО</h1>
        </div>
        
        <div class="card-info">
            № {{ $animal->registrationCard->registration_number ?? $animal->id }} 
            {{ $animal->arrived_at ? $animal->arrived_at->format('d.m.Y') : '' }} г.
        </div>
        
        <!-- Фотографии -->
        @if($animal->registrationCard && ($animal->registrationCard->photo_face || $animal->registrationCard->photo_profile))
        <div class="photos-section">
            @if($animal->registrationCard->photo_face)
            <div class="photo-container">
                <div class="photo-label">Фото морды</div>
                <img src="{{ asset('storage/' . $animal->registrationCard->photo_face) }}" alt="Фото морды">
            </div>
            @endif
            
            @if($animal->registrationCard->photo_profile)
            <div class="photo-container">
                <div class="photo-label">Фото профиля (с линейкой)</div>
                <img src="{{ asset('storage/' . $animal->registrationCard->photo_profile) }}" alt="Фото профиля">
            </div>
            @endif
        </div>
        @endif
        
        <div class="field">
            <strong>Категория животного:</strong> 
            @if($animal->type === 'dog')
                собака
            @elseif($animal->type === 'cat')
                кошка
            @else
                {{ $animal->type }}
            @endif
        </div>
        
        <div class="field">
            <strong>Дата поступления:</strong> 
            {{ $animal->arrived_at ? $animal->arrived_at->format('d.m.Y') : '–' }}г.
        </div>
        
        <div class="field">
            <strong>Пол:</strong> 
            @if($animal->gender === 'male')
                кобель
            @elseif($animal->gender === 'female')
                сука
            @else
                не определен
            @endif
        </div>
        
        <div class="field">
            <strong>Порода:</strong> {{ $animal->breed ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Окрас:</strong> {{ $animal->color ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Шерсть:</strong> {{ $animal->registrationCard->coat ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Уши:</strong> {{ $animal->registrationCard->ears ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Хвост:</strong> {{ $animal->registrationCard->tail ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Размер:</strong> {{ $animal->registrationCard->size ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Возраст (примерный):</strong> {{ $animal->age ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Особые приметы:</strong> {{ $animal->special_marks ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Акт отлова:</strong> 
            @if($animal->registrationCard && $animal->registrationCard->capture_act_number)
                № {{ $animal->registrationCard->capture_act_number }} 
                от {{ $animal->registrationCard->capture_act_date ? \Carbon\Carbon::parse($animal->registrationCard->capture_act_date)->format('d.m.Y') : '' }} года
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>ВСД (дата, №):</strong> 
            @if($animal->registrationCard && $animal->registrationCard->vet_doc_number)
                № {{ $animal->registrationCard->vet_doc_number }} 
                от {{ $animal->registrationCard->vet_doc_date ? \Carbon\Carbon::parse($animal->registrationCard->vet_doc_date)->format('d.m.Y') : '' }}
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Адрес и описание места отлова:</strong> 
            {{ $animal->registrationCard->capture_location_address ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Дата клинического осмотра, заключение:</strong> 
            @if($animal->registrationCard)
                {{ $animal->registrationCard->clinical_exam_date ? \Carbon\Carbon::parse($animal->registrationCard->clinical_exam_date)->format('d.m.Y') : '' }} г. 
                {{ $animal->registrationCard->clinical_exam_conclusion ?? '' }}
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Информация о наличии (отсутствии) у животного агрессивности, проявляет признаки немотивированной агрессивности:</strong> 
            {{ $animal->registrationCard->aggression_notes ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Информация о мероприятиях по корректировке поведения животного:</strong> 
            {{ $animal->registrationCard->behavior_correction_notes ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Вакцинация, вид прививки, акт (дата, №):</strong> 
            @if($animal->registrationCard && $animal->registrationCard->vaccination_history)
                @php
                    $vaccinations = is_string($animal->registrationCard->vaccination_history) 
                        ? json_decode($animal->registrationCard->vaccination_history, true) 
                        : $animal->registrationCard->vaccination_history;
                @endphp
                @if(is_array($vaccinations) && count($vaccinations) > 0)
                    @foreach($vaccinations as $vacc)
                        {{ $vacc['vaccine'] ?? '' }}
                        @if(isset($vacc['date']))
                            от {{ \Carbon\Carbon::parse($vacc['date'])->format('d.m.Y') }}
                        @endif
                        @if(!$loop->last), @endif
                    @endforeach
                @else
                    –
                @endif
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Дата дегельминтизации:</strong> 
            {{ $animal->registrationCard->deworming_date ? \Carbon\Carbon::parse($animal->registrationCard->deworming_date)->format('d.m.Y') : '–' }} г.
        </div>
        
        <div class="field">
            <strong>Дата стерилизации:</strong> 
            {{ $animal->registrationCard->sterilization_date ? \Carbon\Carbon::parse($animal->registrationCard->sterilization_date)->format('d.m.Y') : '–' }} г.
        </div>
        
        <div class="field">
            <strong>Ф.И.О. специалиста в области ветеринарии, произведшего стерилизацию:</strong> 
            {{ $animal->registrationCard->sterilization_vet ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Дата маркирования:</strong> 
            {{ $animal->registrationCard->marking_date ? \Carbon\Carbon::parse($animal->registrationCard->marking_date)->format('d.m.Y') : '–' }} г.
        </div>
        
        <div class="field">
            <strong>№ бирки (клейма):</strong> № {{ $animal->registrationCard->tag_number_card ?? $animal->tag_number ?? '–' }}
        </div>
        
        <div class="field">
            <strong>№ чипа:</strong> {{ $animal->registrationCard->chip_number_card ?? $animal->chip_number ?? '–' }}
        </div>
        
        <div class="field">
            <strong>Наличие/отсутствие немотивированной агрессивности, а также сведения о проведенных мероприятиях по корректировке поведения животного:</strong> 
            @if($animal->registrationCard)
                @if($animal->registrationCard->aggression_notes && $animal->registrationCard->aggression_notes !== '–')
                    {{ $animal->registrationCard->aggression_notes }}
                    @if($animal->registrationCard->behavior_correction_notes && $animal->registrationCard->behavior_correction_notes !== 'не проводились')
                        Мероприятия: {{ $animal->registrationCard->behavior_correction_notes }}
                    @endif
                @else
                    –
                @endif
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Выбытие (причина, дата):</strong> 
            @if($animal->registrationCard && $animal->registrationCard->outcome_reason)
                {{ $animal->registrationCard->outcome_reason }}
                {{ $animal->registrationCard->outcome_date ? ' - ' . \Carbon\Carbon::parse($animal->registrationCard->outcome_date)->format('d.m.Y') : '' }}
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Ветеринарный сопроводительный документ (дата, №):</strong> 
            @if($animal->registrationCard && $animal->registrationCard->vet_doc_number)
                № {{ $animal->registrationCard->vet_doc_number }} 
                от {{ $animal->registrationCard->vet_doc_date ? \Carbon\Carbon::parse($animal->registrationCard->vet_doc_date)->format('d.m.Y') : '' }}
            @else
                –
            @endif
        </div>
        
        <div class="field">
            <strong>Адрес и описание места возвращения (размещения):</strong> 
            {{ $animal->registrationCard->release_address ?? '–' }}
        </div>
        
        <div class="signature-section">
            <div class="field">
                <strong>Для юридических лиц организация:</strong> МБУ «Зеленхоз»
            </div>
            
            <div class="field">
                <strong>фактический адрес:</strong> г. Воронеж, ул. Балашовская 29/1
            </div>
            
            <div class="field">
                <strong>Фамилия, имя, отчество (при наличии) руководителя:</strong> директор Блохинов В.В
            </div>
            
            <div class="field" style="margin-top: 15px;">
                <strong>Для физических лиц:</strong>
            </div>
            
            <div class="field">
                <strong>Фамилия, имя, отчество (при наличии):</strong>
            </div>
            
            <div class="field">
                <strong>Адрес:</strong>
            </div>
            
            <div class="field">
                <strong>Телефон:</strong>
            </div>
            
            <div class="field" style="margin-top: 20px;">
                <strong>Фамилия, имя, отчество специалиста в области ветеринарии:</strong>
            </div>
            <div class="field">
                Шведова В.Н.
            </div>
            <div class="field">
                <strong>Подпись:</strong> <span class="signature-line"></span>
            </div>
            
            <div class="field" style="margin-top: 15px;">
                <strong>Фамилия, имя, отчество представителя организации:</strong> заместитель директора
            </div>
            <div class="field">
                Бурахин А.М.
            </div>
            <div class="field">
                <strong>Подпись:</strong> <span class="signature-line"></span>
            </div>
            
            <div class="field" style="margin-top: 15px;">
                <strong>Фамилия, имя, отчество (при наличии) руководителя:</strong> директор
            </div>
            <div class="field">
                Блохинов В.В
            </div>
            <div class="field">
                <strong>Подпись:</strong> <span class="signature-line"></span>
            </div>
        </div>
    </div>
</body>
</html>
