<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccinationReportSnapshot extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'year',
        'quarter_start',
        'quarter_end',
        'month_start',
        'month_end',
        'barangay',
        'vaccine_name',
        'version',
        'male_count',
        'female_count',
        'total_count',
        'percentage',
        'eligible_population',
        'eligible_population_under_1_year',
        'eligible_population_0_12_months',
        'eligible_population_13_23_months',
        'eligible_population_0_23_months',
        'data_source',
        'is_locked',
        'created_by',
        'updated_by',
        'saved_at',
        'saved_by',
        'deleted_by',
        'deletion_reason',
        'notes'
    ];
    
    protected $casts = [
        'year' => 'integer',
        'quarter_start' => 'integer',
        'quarter_end' => 'integer',
        'month_start' => 'integer',
        'month_end' => 'integer',
        'male_count' => 'integer',
        'female_count' => 'integer',
        'total_count' => 'integer',
        'percentage' => 'decimal:2',
        'eligible_population' => 'integer',
        'eligible_population_under_1_year' => 'integer',
        'eligible_population_0_12_months' => 'integer',
        'eligible_population_13_23_months' => 'integer',
        'eligible_population_0_23_months' => 'integer',
        'is_locked' => 'boolean',
    ];
    
    /**
     * Append accessors to model's array/JSON form
     */
    protected $appends = ['quarter_range', 'month_range', 'date_range'];
    
    /**
     * Get the health worker who created this snapshot
     */
    public function creator()
    {
        return $this->belongsTo(HealthWorker::class, 'created_by');
    }
    
    /**
     * Get the health worker who last updated this snapshot
     */
    public function updater()
    {
        return $this->belongsTo(HealthWorker::class, 'updated_by');
    }
    
    /**
     * Get the health worker who saved this version
     */
    public function saver()
    {
        return $this->belongsTo(HealthWorker::class, 'saved_by');
    }
    
    /**
     * Get the health worker who deleted this snapshot
     */
    public function deleter()
    {
        return $this->belongsTo(HealthWorker::class, 'deleted_by');
    }
    
    /**
     * Get formatted quarter range (e.g., "Q1", "Q1-Q2", "Q1-Q4")
     */
    public function getQuarterRangeAttribute()
    {
        if ($this->quarter_start === $this->quarter_end) {
            return "Q{$this->quarter_start}";
        }
        return "Q{$this->quarter_start}-Q{$this->quarter_end}";
    }
    
    /**
     * Get formatted month range (e.g., "January", "Jan-Mar", "Jan-Dec")
     */
    public function getMonthRangeAttribute()
    {
        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        $monthShort = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // If no month data, fall back to quarter range
        if (!$this->month_start || !$this->month_end) {
            return $this->quarter_range;
        }
        
        // Single month
        if ($this->month_start === $this->month_end) {
            return $monthNames[$this->month_start];
        }
        
        // Month range
        return $monthShort[$this->month_start] . '-' . $monthShort[$this->month_end];
    }
    
    /**
     * Get formatted date range based on months (or quarters if months not available)
     */
    public function getDateRangeAttribute()
    {
        // If we have month data, use it for more precise date range
        if ($this->month_start && $this->month_end) {
            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $daysInMonth = ['', 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            
            $startMonth = $monthNames[$this->month_start];
            $endMonth = $monthNames[$this->month_end];
            $endDay = $daysInMonth[$this->month_end];
            
            return "{$startMonth} 01, {$this->year} to {$endMonth} {$endDay}, {$this->year}";
        }
        
        // Check if quarter_start/quarter_end contain month numbers (1-12) or quarter numbers (1-4)
        // If > 4, they're month numbers (new format)
        if ($this->quarter_start > 4 || $this->quarter_end > 4) {
            // Month-based format (stored in quarter_start/quarter_end columns)
            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $daysInMonth = ['', 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            
            $startMonth = $monthNames[$this->quarter_start] ?? 'Jan';
            $endMonth = $monthNames[$this->quarter_end] ?? 'Dec';
            $endDay = $daysInMonth[$this->quarter_end] ?? 31;
            
            return "{$startMonth} 01, {$this->year} to {$endMonth} {$endDay}, {$this->year}";
        }
        
        // Fall back to quarter-based calculation for old data (1-4)
        $months = [
            1 => ['Jan', 'Feb', 'Mar'],
            2 => ['Apr', 'May', 'Jun'],
            3 => ['Jul', 'Aug', 'Sep'],
            4 => ['Oct', 'Nov', 'Dec']
        ];
        
        $startMonth = $months[$this->quarter_start][0];
        $endMonth = $months[$this->quarter_end][2];
        
        $startDay = "01";
        $endDay = $this->quarter_end == 1 ? "31" : ($this->quarter_end == 2 ? "30" : ($this->quarter_end == 3 ? "30" : "31"));
        
        return "{$startMonth} {$startDay}, {$this->year} to {$endMonth} {$endDay}, {$this->year}";
    }
    
    /**
     * Check if this snapshot is editable
     */
    public function isEditable()
    {
        return $this->data_source === 'manual_edit' || $this->data_source === 'imported';
    }
    
    /**
     * Scope to filter by year and quarters
     */
    public function scopeForPeriod($query, $year, $quarterStart, $quarterEnd)
    {
        return $query->where('year', $year)
                    ->where('quarter_start', $quarterStart)
                    ->where('quarter_end', $quarterEnd);
    }
    
    /**
     * Scope to get all snapshots for a specific report
     */
    public function scopeForReport($query, $year, $quarterStart, $quarterEnd)
    {
        return $query->forPeriod($year, $quarterStart, $quarterEnd)
                    ->orderBy('barangay')
                    ->orderBy('vaccine_name');
    }
}
