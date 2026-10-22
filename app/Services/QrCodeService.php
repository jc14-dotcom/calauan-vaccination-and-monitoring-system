<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate the QR code content URL for a patient.
     *
     * @param Patient $patient
     * @return string
     */
    public function getQrUrl(Patient $patient): string
    {
        return url("/qr/{$patient->qr_uuid}");
    }

    /**
     * Generate QR code as SVG string.
     * Cached for 24 hours per patient UUID.
     *
     * @param Patient $patient
     * @param int $size
     * @return string
     */
    public function generateSvg(Patient $patient, int $size = 200): string
    {
        $cacheKey = "qr_svg_{$patient->qr_uuid}_{$size}";
        
        return Cache::remember($cacheKey, 86400, function () use ($patient, $size) {
            $url = $this->getQrUrl($patient);
            return QrCode::size($size)
                ->format('svg')
                ->errorCorrection('M')
                ->generate($url);
        });
    }

    /**
     * Generate QR code as PNG base64 for embedding in HTML/PDF.
     * Cached for 24 hours per patient UUID.
     *
     * @param Patient $patient
     * @param int $size
     * @return string
     */
    public function generateBase64Png(Patient $patient, int $size = 200): string
    {
        $cacheKey = "qr_png_{$patient->qr_uuid}_{$size}";
        
        return Cache::remember($cacheKey, 86400, function () use ($patient, $size) {
            $url = $this->getQrUrl($patient);
            $png = QrCode::format('png')
                ->size($size)
                ->errorCorrection('M')
                ->generate($url);
            return 'data:image/png;base64,' . base64_encode($png);
        });
    }

    /**
     * Generate QR code as base64 SVG for embedding in HTML.
     * Cached for 24 hours per patient UUID.
     *
     * @param Patient $patient
     * @param int $size
     * @return string
     */
    public function generateBase64Svg(Patient $patient, int $size = 200): string
    {
        $cacheKey = "qr_b64svg_{$patient->qr_uuid}_{$size}";
        
        return Cache::remember($cacheKey, 86400, function () use ($patient, $size) {
            $svg = $this->generateSvgUncached($patient, $size);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        });
    }

    /**
     * Generate SVG without caching (internal use).
     *
     * @param Patient $patient
     * @param int $size
     * @return string
     */
    private function generateSvgUncached(Patient $patient, int $size): string
    {
        $url = $this->getQrUrl($patient);
        return QrCode::size($size)
            ->format('svg')
            ->errorCorrection('M')
            ->generate($url);
    }

    /**
     * Find patient by QR UUID.
     *
     * @param string $uuid
     * @return Patient|null
     */
    public function findPatientByUuid(string $uuid): ?Patient
    {
        return Patient::where('qr_uuid', $uuid)->first();
    }

    /**
     * Regenerate QR UUID for a patient (if needed for security).
     * Also clears cached QR codes for the old UUID.
     *
     * @param Patient $patient
     * @return string New UUID
     */
    public function regenerateUuid(Patient $patient): string
    {
        $oldUuid = $patient->qr_uuid;
        
        // Clear old cached QR codes
        if ($oldUuid) {
            Cache::forget("qr_svg_{$oldUuid}_200");
            Cache::forget("qr_png_{$oldUuid}_200");
            Cache::forget("qr_b64svg_{$oldUuid}_200");
            Cache::forget("qr_svg_{$oldUuid}_150");
            Cache::forget("qr_b64svg_{$oldUuid}_150");
        }
        
        $patient->qr_uuid = \Illuminate\Support\Str::uuid()->toString();
        $patient->save();

        return $patient->qr_uuid;
    }
    
    /**
     * Clear cached QR codes for a patient.
     *
     * @param Patient $patient
     * @return void
     */
    public function clearCache(Patient $patient): void
    {
        $uuid = $patient->qr_uuid;
        if ($uuid) {
            Cache::forget("qr_svg_{$uuid}_200");
            Cache::forget("qr_png_{$uuid}_200");
            Cache::forget("qr_b64svg_{$uuid}_200");
            Cache::forget("qr_svg_{$uuid}_150");
            Cache::forget("qr_b64svg_{$uuid}_150");
        }
    }
}
