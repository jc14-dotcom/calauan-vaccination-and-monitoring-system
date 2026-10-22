<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vaccination Record</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="stylesheet" href="{{ asset('css/vaccination_form.css') }}">
    <style>
        /* Updated styles for viewing on PC/laptop/mobile */
        .print-button {
            background-color: #7a5bbd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px auto;
            display: block;
            transition: background-color 0.3s;
        }
        
        .print-button:hover {
            background-color: #6241a8;
        }
        
        .date-cell {
            position: relative;
            display: flex;
            gap: 20px;
            padding: 5px;
            justify-content: space-between;
            align-items: center;
        }
        
        .single-date input[type="date"] {
            width: 100%;
        }
        
        .double-date {
            display: flex;
            justify-content: space-between;
            position: relative;
            width: 98%;
            gap: 5px;
        }
        
        .double-date input[type="date"] {
            width: calc(50% - 10px);
        }
        
        .triple-date {
            display: flex;
            justify-content: space-between;
            position: relative;
            width: 98%;
            gap: 5px;
        }
        
        .triple-date input[type="date"] {
            width: calc(33.33% - 13px);
        }
        
        .double-date::after,
        .triple-date::before,
        .triple-date::after {
            display: none;
        }
        
        input[type="date"] {
            padding: 8px;
            border: 1px solid #7a5bbd;
            border-radius: 5px;
            font-size: 14px;
            height: 25px;
            background-color: white;
            color: #333;
            text-align: center;
        }
        
        .form-group:has(#fathers_name) {
            margin-bottom: 3px !important;
        }
        
        .form-group {
            margin-bottom: 10px; /* Increased for spacing */
            padding: 0;
            display: flex;
            flex-direction: column; /* Stack label above field */
            align-items: flex-start; /* Align to left */
        }
        
        .form-group label {
            margin-bottom: 5px; /* Space between label and field */
            text-align: left; /* Left-align labels */
            font-size: 14px;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%; /* Full width for fields */
            padding: 8px;
            border: 1px solid #7a5bbd;
            border-radius: 5px;
            font-size: 14px;
            height: 40px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: auto; /* Allow textarea to expand */
        }
        
        .form-group-row {
            margin-top: 10px;
            margin-bottom: 1px;
            display: flex;
            gap: 20px; /* Space between small inputs */
            align-items: flex-start;
        }
        
        .small-input {
            width: 50%;
            display: flex;
            flex-direction: column; /* Stack label above field */
            align-items: flex-start;
        }
        
        .small-input label {
            margin-bottom: 10   px;
            text-align: left;
            font-size: 14px;
        }
        
        .small-input input {
            width: 100%; /* Full width for small inputs */
            padding: 8px;
            border: 1px solid #7a5bbd;
            border-radius: 5px;
            font-size: 14px;
            height: 45px;
            box-sizing: border-box;
        }
        
        /* PRINT-SPECIFIC STYLES - Unchanged */
        @media print {
            select {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                border: 1px solid #7a5bbd !important;
                background-color: white !important;
                color: black !important;
                padding: 1px 2px !important;
                font-size: 7pt !important;
                height: 12px !important;
                width: auto !important;
                max-width: 100% !important;
                overflow: hidden !important;
                white-space: nowrap !important;
                text-overflow: ellipsis !important;
                text-align: center !important;
            }

            select::-ms-expand {
                display: none;
            }

            select {
                display: inline-block !important;
            }

            select::before {
                content: attr(data-value);
            }
            @page {
                size: landscape;
                margin: 0.2cm;
            }
            
            body {
                width: 100%;
                height: 50%;
                margin: 0;
                padding: 0;
                font-size: 7pt;
                overflow: hidden;
            }
            
            header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 2px 5px;
                background-color: #7a5bbd !important;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
                margin-bottom: 3px;
            }
            
            .title {
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 10pt;
            }
            
            .logos-left img, .logos-right img {
                height: 18px;
            }
            
            .back-button, .print-button, #okButton {
                display: none;
            }
            
            .form-container {
                display: flex;
                padding: 0;
                margin: 0 0 3px 0;
            }
            
            .left-column, .right-column {
                width: 49%;
                padding: 0 0.5%;
            }
            
            .form-group {
                display: flex;
                flex-direction: row;
                align-items: center;
                margin-bottom: 3px;
                width: 100%;
                padding: 0;
            }

            .form-group:has(#fathers_name) {
                margin-bottom: 3px !important;
            }

            .form-group label {
                width: 80px;
                font-size: 7pt;
                margin-right: 5px;
                white-space: nowrap;
                flex-shrink: 0;
                text-align: right;
            }
            
            .left-column .form-group label {
                width: 80px;
                text-align: right;
                margin-left: 0;
                padding-left: 0;
            }
            
            .right-column .form-group label {
                width: 80px;
                display: inline-block;
                text-align: right;
            }

            .form-group input, 
            .form-group select, 
            .form-group textarea,
            .date-formatted,
            .select-formatted {
                flex-grow: 1;
                width: 0;
                min-width: 50px;
                margin-left: 2px !important;
                border: 1px solid #7a5bbd !important;
                border-radius: 3px;
                padding: 0 2px !important;
                font-size: 7pt !important;
                height: 12px !important;
                line-height: 12px;
                text-align: center !important;
            }

            .form-group-row {
                display: flex;
                gap: 3px;
                margin-top: 3px;
                margin-bottom: 3px;
            }

            .small-input {
                width: 50%;
                display: flex;
                align-items: center;
            }

            .small-input label {
                width: 80px;
                font-size: 7pt;
                margin-right: 5px;
                white-space: nowrap;
                text-align: right;
            }

            .small-input input {
                flex-grow: 1;
                width: 0;
                min-width: 30px;
                text-align: center !important;
            }

            .date-cell {
                display: flex;
                width: 100%;
                gap: 3px;
                padding: 0 !important;
            }

            .single-date input[type="date"],
            .single-date .date-formatted {
                width: 100% !important;
                text-align: center !important;
            }

            .double-date {
                display: flex;
                width: 100%;
                gap: 3px;
            }

            .double-date input[type="date"],
            .double-date .date-formatted {
                width: 49% !important;
                text-align: center !important;
            }

            .triple-date {
                display: flex;
                width: 100%;
                gap: 3px;
            }

            .triple-date input[type="date"],
            .triple-date .date-formatted {
                width: 32% !important;
                text-align: center !important;
            }

            input, select, textarea {
                padding: 0 2px;
                border: 1px solid #7a5bbd;
                border-radius: 3px;
                font-size: 7pt;
                text-align: center;
            }
            
            input[type="date"]::-webkit-calendar-picker-indicator,
            input[type="date"]::-webkit-inner-spin-button {
                display: none;
                -webkit-appearance: none;
            }
            
            textarea.remarks-input {
                height: 12px !important;
                min-height: unset !important;
                font-size: 7pt !important;
                padding: 0 2px !important;
                resize: none !important;
                text-align: center !important;
            }
            
            #successModal, .alert {
                display: none !important;
            }

            /* Support Forced Colors Mode */
            @media (forced-colors: active) {
                .print-button, header, th {
                    forced-color-adjust: auto;
                    background-color: Canvas !important;
                    color: CanvasText !important;
                    border-color: CanvasText !important;
                }
            }
        }
    </style>
</head>
<body>
<header>
    <div class="back-button">
        <a href="{{ route('health_worker.patients') }}" class="btn btn-secondary">
            <img src="{{ asset('images/arrow.png') }}" alt="Back">
        </a>
    </div>
    <div class="logos-left">
        <img src="{{ asset('images/todo.png') }}" alt="Logo Left 1">
    </div>
    <div class="title">IMMUNIZATION CARD</div>
    <div class="logos-right">
        <img src="{{ asset('images/doh-logo.png') }}" alt="Logo Right 1">
        <img src="{{ asset('images/right.png') }}" alt="Logo Right 2">
    </div>
</header>

@if(session('success'))
<div class="modal" id="successModal">
    <div class="modal-content">
        <p>{{ session('success') }}</p>
        <button id="okButton" onclick="redirectToPatients()">OK!</button>
    </div>
</div>
@endif
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<form action="/save-record" method="POST" id="editForm">
    @csrf
    <div class="form-container">
        <div class="left-column">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Firstname, M.I, Lastname" value="{{ $patient->name }}" readonly>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $patient->date_of_birth }}" readonly>
            </div>
            <div class="form-group">
                <label for="place_of_birth">Place of Birth:</label>
                <input type="text" id="place_of_birth" name="place_of_birth" value="{{ $patient->place_of_birth }}" readonly>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="{{ $patient->address }}" readonly>
            </div>
            <div class="form-group">
                <label for="barangay">Barangay:</label>
                <select id="barangay" name="barangay" disabled>
                    <option value="" disabled>Select Barangay</option>
                    <option value="Balayhangin" {{ $patient->barangay == 'Balayhangin' ? 'selected' : '' }}>Balayhangin</option>
                    <option value="Bangyas" {{ $patient->barangay == 'Bangyas' ? 'selected' : '' }}>Bangyas</option>
                    <option value="Dayap" {{ $patient->barangay == 'Dayap' ? 'selected' : '' }}>Dayap</option>
                    <option value="Hanggan" {{ $patient->barangay == 'Hanggan' ? 'selected' : '' }}>Hanggan</option>
                    <option value="Imok" {{ $patient->barangay == 'Imok' ? 'selected' : '' }}>Imok</option>
                    <option value="Kanluran" {{ $patient->barangay == 'Kanluran' ? 'selected' : '' }}>Kanluran</option>
                    <option value="Lamot 1" {{ $patient->barangay == 'Lamot 1' ? 'selected' : '' }}>Lamot 1</option>
                    <option value="Lamot 2" {{ $patient->barangay == 'Lamot 2' ? 'selected' : '' }}>Lamot 2</option>
                    <option value="Limao" {{ $patient->barangay == 'Limao' ? 'selected' : '' }}>Limao</option>
                    <option value="Mabacan" {{ $patient->barangay == 'Mabacan' ? 'selected' : '' }}>Mabacan</option>
                    <option value="Masiit" {{ $patient->barangay == 'Masiit' ? 'selected' : '' }}>Masiit</option>
                    <option value="Paliparan" {{ $patient->barangay == 'Paliparan' ? 'selected' : '' }}>Paliparan</option>
                    <option value="Perez" {{ $patient->barangay == 'Perez' ? 'selected' : '' }}>Perez</option>
                    <option value="Prinza" {{ $patient->barangay == 'Prinza' ? 'selected' : '' }}>Prinza</option>
                    <option value="San Isidro" {{ $patient->barangay == 'San Isidro' ? 'selected' : '' }}>San Isidro</option>
                    <option value="Santo Tomas" {{ $patient->barangay == 'Santo Tomas' ? 'selected' : '' }}>Santo Tomas</option>
                    <option value="Silangan" {{ $patient->barangay == 'Silangan' ? 'selected' : '' }}>Silangan</option>
                </select>
            </div>
        </div>
        <div class="right-column">
            <div class="form-group">
                <label for="mothers_name">Mother's Name:</label>
                <input type="text" id="mothers_name" name="mothers_name" value="{{ $patient->mother_name }}" readonly>
            </div>
            <div class="form-group">
                <label for="fathers_name">Father's Name:</label>
                <input type="text" id="fathers_name" name="fathers_name" value="{{ $patient->father_name }}" readonly>
            </div>
            <div class="form-group-row">
                <div class="form-group small-input">
                    <label for="birth_weight">Birth Weight (kg):</label>
                    <input type="number" id="birth_weight" name="birth_weight" value="{{ $patient->birth_weight }}" readonly>
                </div>
                <div class="form-group small-input">
                    <label for="birth_height">Birth Height (cm):</label>
                    <input type="number" id="birth_height" name="birth_height" value="{{ $patient->birth_height }}" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select id="sex" name="sex" disabled>
                    <option value="" disabled>Select Sex</option>
                    <option value="Male" {{ $patient->sex == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ $patient->sex == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="contact_no">Contact No.:</label>
                <input type="text" id="contact_no" name="contact_no" value="{{ $patient->contact_no }}" readonly>
            </div>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>BAKUNA</th>
                    <th>DOSES</th>
                    <th>PETSA NG BAKUNA</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patient->vaccines as $index => $vaccine)
                <tr>
                    <td>{{ $vaccine->vaccine->vaccine_name }}</td>
                    <td>{{ $vaccine->vaccine->doses_description }}</td>
                    <td>
                        @php
                        $totalDoses = max(1, min(3, (int) optional($vaccine->vaccine)->total_doses));
                    @endphp
                    @if($totalDoses === 1)
                            <div class="date-cell single-date">
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_1_date]" value="{{ $vaccine->dose_1_date }}" readonly>
                            </div>
                        @elseif($totalDoses === 3)
                            <div class="date-cell triple-date">
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_1_date]" value="{{ $vaccine->dose_1_date }}" readonly>
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_2_date]" value="{{ $vaccine->dose_2_date }}" readonly>
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_3_date]" value="{{ $vaccine->dose_3_date }}" readonly>
                            </div>
                        @else
                            <div class="date-cell double-date">
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_1_date]" value="{{ $vaccine->dose_1_date }}" readonly>
                                <input type="date" class="date-input" name="vaccines[{{ $index }}][dose_2_date]" value="{{ $vaccine->dose_2_date }}" readonly>
                            </div>
                        @endif
                    </td>
                    <td>
                        <textarea name="vaccines[{{ $index }}][remarks]" class="remarks-input" readonly>{{ $vaccine->remarks }}</textarea>
                    </td>
                </tr>
                @endforeach
                
                @php
                    $hasTetranus = false;
                    $hasHPV = false;
                    foreach($patient->vaccines as $vaccine) {
                        if($vaccine->vaccine->vaccine_name == 'Tetanus') {
                            $hasTetranus = true;
                        }
                        if($vaccine->vaccine->vaccine_name == 'Human Papillomavirus') {
                            $hasHPV = true;
                        }
                    }
                @endphp
                
                @if(!$hasTetranus)
                <tr>
                    <td>Tetanus</td>
                    <td>Grade 1 & Grade 7</td>
                    <td>
                        <div class="date-cell double-date">
                            <input type="date" class="date-input" readonly>
                            <input type="date" class="date-input" readonly>
                        </div>
                    </td>
                    <td>
                        <textarea class="remarks-input" readonly></textarea>
                    </td>
                </tr>
                @endif
                
                @if(!$hasHPV)
                <tr>
                    <td>Human Papillomavirus</td>
                    <td>9-14 years old</td>
                    <td>
                        <div class="date-cell single-date">
                            <input type="date" class="date-input" readonly>
                        </div>
                    </td>
                    <td>
                        <textarea class="remarks-input" readonly></textarea>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</form>

<button class="print-button" id="printBtn">Print Immunization Card</button>

<script>
    function redirectToPatients() {
        window.location.href = '/health_worker/patients';
    }
    
    document.getElementById('printBtn').addEventListener('click', function() {
        // Create a Trusted Types policy if supported
        let trustedTypesPolicy = null;
        if (window.trustedTypes && trustedTypes.createPolicy) {
            trustedTypesPolicy = trustedTypes.createPolicy('printPolicy', {
                createHTML: (string) => {
                    // Basic sanitization: Remove script tags
                    return string.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                }
            });
        }

        const printFrame = document.createElement('iframe');
        printFrame.id = 'print-frame';
        printFrame.style.position = 'fixed';
        printFrame.style.left = '-9999px';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        document.body.appendChild(printFrame);
        
        printFrame.onload = function() {
            const frameDoc = printFrame.contentDocument || printFrame.contentWindow.document;
            let headContent = document.head.innerHTML;
            
            const printStyle = `
                <style>
                    @page {
                        size: landscape;
                        margin: 0.2cm;
                    }
                    
                    body {
                        width: 100%;
                        height: 50%;
                        margin: 0;
                        padding: 0;
                        font-size: 7pt;
                        overflow: hidden;
                    }
                    
                    header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 2px 5px;
                        background-color: #7a5bbd !important;
                        -webkit-print-color-adjust: exact; 
                        print-color-adjust: exact;
                        margin-bottom: 3px;
                    }
                    
                    .title {
                        color: white !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                        font-size: 10pt;
                    }
                    
                    .logos-left img, .logos-right img {
                        height: 18px;
                    }
                    
                    .back-button, .print-button, #okButton {
                        display: none;
                    }
                    
                    .form-container {
                        display: flex;
                        padding: 0;
                        margin: 0 0 3px 0;
                    }
                    
                    .left-column, .right-column {
                        width: 49%;
                        padding: 0 0.5%;
                    }
                    
                    .form-group {
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        margin-bottom: 3px;
                        width: 100%;
                        padding: 0;
                    }
                    
                    .form-group:has(#fathers_name) {
                        margin-bottom: 3px !important;
                    }
                    
                    .form-group label {
                        width: 80px;
                        font-size: 7pt;
                        margin-right: 5px;
                        white-space: nowrap;
                        flex-shrink: 0;
                        text-align: right;
                    }
                    
                    .left-column .form-group label {
                        width: 80px;
                        text-align: right;
                        margin-left: 0;
                        padding-left: 0;
                    }
                    
                    .right-column .form-group label {
                        width: 80px;
                        display: inline-block;
                        text-align: right;
                    }
                    
                    input, select, textarea, .date-formatted, .select-formatted {
                        flex-grow: 1;
                        width: 0;
                        min-width: 50px;
                        margin-left: 2px !important;
                        border: 1px solid #7a5bbd !important;
                        border-radius: 3px;
                        padding: 0 2px !important;
                        font-size: 7pt !important;
                        height: 12px !important;
                        line-height: 12px;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                        color: black !important;
                        background-color: white !important;
                        overflow: hidden !important;
                        white-space: nowrap !important;
                        text-overflow: ellipsis !important;
                        text-align: center !important;
                    }
                    
                    .form-group-row {
                        display: flex;
                        gap: 3px;
                        margin-top: 3px;
                        margin-bottom: 3px;
                    }
                    
                    .small-input {
                        width: 50%;
                        display: flex;
                        align-items: center;
                    }
                    
                    .small-input label {
                        width: 80px;
                        font-size: 7pt;
                        white-space: nowrap;
                        margin-right: 5px;
                        text-align: right;
                    }
                    
                    .small-input input {
                        flex-grow: 1;
                        width: 0;
                        min-width: 30px;
                        text-align: center !important;
                    }
                    
                    .table-container {
                        margin-top: 0;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    
                    th {
                        background-color: #7a5bbd !important;
                        color: white !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                        padding: 2px !important;
                        font-size: 7pt;
                        text-align: center;
                    }
                    
                    td {
                        padding: 1px 2px !important;
                        font-size: 7pt;
                        border: 1px solid #ddd !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                        vertical-align: middle;
                    }
                    
                    .date-cell {
                        padding: 0 !important;
                        display: flex !important;
                        justify-content: space-between !important;
                        width: 100% !important;
                        gap: 3px !important;
                    }
                    
                    .single-date, .double-date, .triple-date {
                        width: 100% !important;
                        display: flex !important;
                    }
                    
                    .single-date .date-formatted {
                        width: 100% !important;
                        text-align: center !important;
                    }
                    
                    .double-date .date-formatted {
                        width: 49% !important;
                        text-align: center !important;
                    }
                    
                    .triple-date .date-formatted {
                        width: 32% !important;
                        text-align: center !important;
                    }
                    
                    textarea.remarks-input {
                        height: 12px !important;
                        min-height: unset !important;
                        font-size: 7pt !important;
                        padding: 0 2px !important;
                        resize: none !important;
                        text-align: center !important;
                    }
                    
                    input, select, textarea, .date-formatted, .select-formatted {
                        color: black !important;
                        background-color: white !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                    }

                    @media (forced-colors: active) {
                        .print-button, header, th {
                            forced-color-adjust: auto;
                            background-color: Canvas !important;
                            color: CanvasText !important;
                            border-color: CanvasText !important;
                        }
                    }
                </style>
            `;
            
            frameDoc.open();
            const htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Immunization Card</title>
                    ${headContent}
                    ${printStyle}
                </head>
                <body>
                    ${document.querySelector('header').outerHTML}
                    <div id="mainContent"></div>
                </body>
                </html>
            `;
            frameDoc.write(trustedTypesPolicy ? trustedTypesPolicy.createHTML(htmlContent) : htmlContent);
            frameDoc.close();
            
            const formContent = document.getElementById('editForm').cloneNode(true);
            frameDoc.getElementById('mainContent').appendChild(formContent);
            
            const formatDateForPrint = function(dateValue) {
                if (!dateValue) return '';
                const date = new Date(dateValue);
                return date.toLocaleDateString('en-PH', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric'
                });
            };
            
            const dobField = frameDoc.getElementById('date_of_birth');
            if (dobField && dobField.value) {
                const formattedDate = formatDateForPrint(dobField.value);
                const textInput = frameDoc.createElement('input');
                textInput.type = 'text';
                textInput.className = 'date-formatted';
                textInput.value = formattedDate;
                textInput.id = 'date_of_birth';
                textInput.name = 'date_of_birth';
                textInput.readOnly = true;
                dobField.parentNode.replaceChild(textInput, dobField);
            }
            
            const selectElements = frameDoc.querySelectorAll('select');
            selectElements.forEach(function(select) {
                if (select.selectedIndex >= 0) {
                    const selectedOption = select.options[select.selectedIndex];
                    const textInput = frameDoc.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'select-formatted';
                    textInput.value = selectedOption.text;
                    textInput.name = select.name;
                    textInput.readOnly = true;
                    select.parentNode.replaceChild(textInput, select);
                }
            });
            
            const dateContainers = frameDoc.querySelectorAll('.date-cell');
            dateContainers.forEach(function(container) {
                const dateInputs = container.querySelectorAll('.date-input');
                dateInputs.forEach(function(input) {
                    const formattedDate = formatDateForPrint(input.value);
                    const textInput = frameDoc.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'date-formatted';
                    textInput.value = formattedDate;
                    textInput.name = input.name;
                    textInput.readOnly = true;
                    input.parentNode.replaceChild(textInput, input);
                });
            });
            
            setTimeout(function() {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                setTimeout(function() {
                    document.body.removeChild(printFrame);
                }, 1000);
            }, 500);
        };
        
        printFrame.src = 'about:blank';
    });
</script>
</body>
</html>