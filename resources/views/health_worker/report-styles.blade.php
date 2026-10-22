<style>
    /* ================================================================
       VACCINATION REPORT TABLE STYLES
       Comprehensive fix for sticky columns and proper cell alignment
       ================================================================ */
    
    /* ===== TABLE CONTAINER STACKING CONTEXT =====
       This ensures sticky z-indexes only apply within the table,
       preventing overlap with page header/navbar */
    .table-scroll-container {
        position: relative;
        z-index: 1;
        isolation: isolate; /* Creates new stacking context */
    }
    
    #vaccinationReportTable {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
        background-color: #ffffff;
    }
    
    /* ===== BASE STYLES FOR ALL CELLS ===== */
    
    /* All header cells */
    #vaccinationReportTable thead th {
        background-color: #E5E7EB;
        color: #111827;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        padding: 10px 6px;
        border: 1px solid #9CA3AF;
        white-space: normal;
        word-wrap: break-word;
        font-size: 12px;
    }
    
    /* All body cells */
    #vaccinationReportTable tbody td {
        padding: 8px 6px;
        border: 1px solid #D1D5DB;
        text-align: center;
        color: #374151;
        font-size: 13px;
        background-color: #FFFFFF;
    }
    
    /* ===== STICKY COLUMNS (Area + 3 Eligible Population) =====
       These columns use rowspan="2" so they exist only in tr:first-child
       We target them by their position in the first row
       Z-index values kept low (10-13) since they're isolated in container */
    
    /* Column 1: AREA - Header (rowspan=2) */
    #vaccinationReportTable thead tr:first-child th:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 13;
        background-color: #E5E7EB;
        color: #111827;
        font-weight: 700;
        min-width: 110px;
        width: 110px;
        font-size: 13px;
    }
    
    /* Column 1: AREA - Body */
    #vaccinationReportTable tbody td:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 9;
        background-color: #FFFFFF;
        font-weight: 600;
        min-width: 110px;
        width: 110px;
        font-size: 13px;
        text-align: left;
        padding-left: 10px;
    }
    
    /* Column 2: Eligible Pop (Under 1 yr) - Header */
    #vaccinationReportTable thead tr:first-child th:nth-child(2) {
        position: sticky;
        left: 110px;
        z-index: 12;
        background-color: #E5E7EB;
        color: #111827;
        min-width: 70px;
        width: 70px;
        font-size: 10px;
        padding: 8px 4px;
        line-height: 1.3;
    }
    
    /* Column 2: Eligible Pop (Under 1 yr) - Body */
    #vaccinationReportTable tbody td:nth-child(2) {
        position: sticky;
        left: 110px;
        z-index: 8;
        background-color: #FFFFFF;
        min-width: 70px;
        width: 70px;
    }
    
    /* Column 3: Eligible Pop (0-12 mos) - Header */
    #vaccinationReportTable thead tr:first-child th:nth-child(3) {
        position: sticky;
        left: 180px;
        z-index: 11;
        background-color: #E5E7EB;
        color: #111827;
        min-width: 70px;
        width: 70px;
        font-size: 10px;
        padding: 8px 4px;
        line-height: 1.3;
    }
    
    /* Column 3: Eligible Pop (0-12 mos) - Body */
    #vaccinationReportTable tbody td:nth-child(3) {
        position: sticky;
        left: 180px;
        z-index: 7;
        background-color: #FFFFFF;
        min-width: 70px;
        width: 70px;
    }
    
    /* Column 4: Eligible Pop (13-23 mos) - Header with right shadow */
    #vaccinationReportTable thead tr:first-child th:nth-child(4) {
        position: sticky;
        left: 250px;
        z-index: 10;
        background-color: #E5E7EB;
        color: #111827;
        min-width: 70px;
        width: 70px;
        font-size: 10px;
        padding: 8px 4px;
        line-height: 1.3;
        box-shadow: 4px 0 8px rgba(0,0,0,0.15);
    }
    
    /* Column 4: Eligible Pop (13-23 mos) - Body with right shadow */
    #vaccinationReportTable tbody td:nth-child(4) {
        position: sticky;
        left: 250px;
        z-index: 6;
        background-color: #FFFFFF;
        min-width: 70px;
        width: 70px;
        box-shadow: 4px 0 8px rgba(0,0,0,0.15);
    }
    
    /* ===== VACCINE HEADER COLUMNS (colspan=4) =====
       These start from the 5th column in the first row */
    
    #vaccinationReportTable thead tr:first-child th:nth-child(n+5) {
        background-color: #E5E7EB;
        min-width: 180px;
        font-size: 11px;
        z-index: 1;
    }
    
    /* ===== SUB-HEADER ROW (M/F/T/%) =====
       Row 2 only contains M/F/T/% cells (columns 1-4 are covered by rowspan)
       So th:nth-child(1) in row 2 = first M column (BCG) */
    
    #vaccinationReportTable thead tr:nth-child(2) th {
        background-color: #F3F4F6;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        min-width: 45px;
        width: 45px;
        color: #374151;
        z-index: 1;
        /* Ensure borders are visible */
        border: 1px solid #9CA3AF;
    }
    
    /* ===== VACCINE DATA COLUMNS (Body) ===== */
    
    #vaccinationReportTable tbody td:nth-child(n+5) {
        min-width: 45px;
        width: 45px;
        padding: 8px 4px;
        font-size: 13px;
    }
    
    /* Bold the Total (T) columns - every 4th column starting from 7 */
    #vaccinationReportTable tbody td:nth-child(4n+7) {
        font-weight: 700;
        color: #1F2937;
    }
    
    /* ===== TOTAL ROW STYLING ===== */
    
    #vaccinationReportTable tbody tr:last-child {
        background-color: #FEF3C7;
        font-weight: 700;
    }
    
    #vaccinationReportTable tbody tr:last-child td {
        background-color: #FEF3C7;
        border-top: 2px solid #92400E;
        border-bottom: 2px solid #92400E;
    }
    
    /* Keep sticky columns yellow in TOTAL row */
    #vaccinationReportTable tbody tr:last-child td:nth-child(1),
    #vaccinationReportTable tbody tr:last-child td:nth-child(2),
    #vaccinationReportTable tbody tr:last-child td:nth-child(3),
    #vaccinationReportTable tbody tr:last-child td:nth-child(4) {
        background-color: #FEF3C7;
    }
    
    /* ===== HOVER EFFECTS ===== */
    
    #vaccinationReportTable tbody tr:not(:last-child):hover td {
        background-color: #F9FAFB;
    }
    
    /* Keep sticky columns with distinct hover color */
    #vaccinationReportTable tbody tr:not(:last-child):hover td:nth-child(1),
    #vaccinationReportTable tbody tr:not(:last-child):hover td:nth-child(2),
    #vaccinationReportTable tbody tr:not(:last-child):hover td:nth-child(3),
    #vaccinationReportTable tbody tr:not(:last-child):hover td:nth-child(4) {
        background-color: #E0E7FF;
    }
    
    /* ===== SCROLLBAR STYLING ===== */
    
    .overflow-x-auto::-webkit-scrollbar {
        height: 12px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #F3F4F6;
        border-radius: 6px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #9CA3AF;
        border-radius: 6px;
        border: 2px solid #F3F4F6;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #6B7280;
    }
    
    /* ===== MOBILE RESPONSIVENESS ===== */
    
    @media (max-width: 768px) {
        #vaccinationReportTable {
            font-size: 11px;
        }
        
        #vaccinationReportTable thead th,
        #vaccinationReportTable tbody td {
            padding: 6px 3px;
        }
        
        /* Smaller sticky columns on mobile */
        #vaccinationReportTable thead tr:first-child th:nth-child(1),
        #vaccinationReportTable tbody td:nth-child(1) {
            min-width: 80px;
            width: 80px;
            font-size: 10px;
        }
        
        #vaccinationReportTable thead tr:first-child th:nth-child(2),
        #vaccinationReportTable tbody td:nth-child(2) {
            left: 80px;
            min-width: 50px;
            width: 50px;
            font-size: 9px;
        }
        
        #vaccinationReportTable thead tr:first-child th:nth-child(3),
        #vaccinationReportTable tbody td:nth-child(3) {
            left: 130px;
            min-width: 50px;
            width: 50px;
            font-size: 9px;
        }
        
        #vaccinationReportTable thead tr:first-child th:nth-child(4),
        #vaccinationReportTable tbody td:nth-child(4) {
            left: 180px;
            min-width: 50px;
            width: 50px;
            font-size: 9px;
        }
        
        #vaccinationReportTable thead tr:nth-child(2) th,
        #vaccinationReportTable tbody td:nth-child(n+5) {
            min-width: 35px;
            width: 35px;
            font-size: 10px;
        }
    }
    
    /* ===== PRINT STYLES ===== */
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        #vaccinationReportTable {
            font-size: 8px;
        }
        
        /* Remove sticky positioning for print */
        #vaccinationReportTable thead tr:first-child th:nth-child(1),
        #vaccinationReportTable thead tr:first-child th:nth-child(2),
        #vaccinationReportTable thead tr:first-child th:nth-child(3),
        #vaccinationReportTable thead tr:first-child th:nth-child(4),
        #vaccinationReportTable tbody td:nth-child(1),
        #vaccinationReportTable tbody td:nth-child(2),
        #vaccinationReportTable tbody td:nth-child(3),
        #vaccinationReportTable tbody td:nth-child(4) {
            position: static;
            box-shadow: none;
            background-color: #E5E7EB;
        }
    }
    
    /* Ensure table container doesn't let sticky elements escape */
    .overflow-x-auto {
        position: relative;
        z-index: 1;
        isolation: isolate;
    }

    /* ===== CUSTOM COLUMN STYLES ===== */
    .custom-column-header {
        background-color: #DBEAFE !important;
        color: #1E40AF !important;
    }
    .custom-column-subheader {
        background-color: #EFF6FF !important;
        color: #1E40AF !important;
    }
    .custom-column-cell {
        background-color: #F0F9FF !important;
    }
    .custom-header-actions {
        display: none;
    }

    /* FIC/CIC cell colors */
    [data-vaccine="__FIC__"] { color: #1E40AF !important; }
    [data-vaccine="__CIC__"] { color: #065F46 !important; }
</style>
