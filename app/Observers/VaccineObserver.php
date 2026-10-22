<?php

namespace App\Observers;

use App\Jobs\BackfillPatientVaccineRecordsJob;
use App\Models\Vaccine;

class VaccineObserver
{
    /**
     * When a NEW vaccine is created with is_standard = true, backfill patient
     * records so all existing patients immediately get a row for this vaccine.
     */
    public function created(Vaccine $vaccine): void
    {
        if ($vaccine->is_standard) {
            BackfillPatientVaccineRecordsJob::dispatch($vaccine->id);
        }
    }

    /**
     * When a vaccine's is_standard flag is turned on via update, backfill patient
     * records for all existing patients who don't yet have a record for this vaccine.
     */
    public function updated(Vaccine $vaccine): void
    {
        if ($vaccine->wasChanged('is_standard') && $vaccine->is_standard) {
            BackfillPatientVaccineRecordsJob::dispatch($vaccine->id);
        }
    }
}
