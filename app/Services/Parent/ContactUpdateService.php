<?php

namespace App\Services\Parent;

use App\Models\Parents;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

/**
 * Contact Update Service
 * 
 * Handles the business logic for updating parent contact information
 * and cascading those changes to all associated patient records.
 * 
 * CASCADE FIELDS: contact_number, address, barangay
 * When a parent updates any of these fields, ALL patients linked to
 * that parent will also be updated with the same values.
 */
class ContactUpdateService
{
    /**
     * Update parent contact number and cascade to all children
     * 
     * @param Parents $parent The parent model to update
     * @param string $newContactNumber The new contact number
     * @return int Number of patient records updated
     */
    public function updateContactNumber(Parents $parent, string $newContactNumber): int
    {
        // Update the parent's contact number
        $parent->update([
            'contact_number' => $newContactNumber,
        ]);
        
        // CASCADE UPDATE: Update contact number for all patients under this parent
        $updatedCount = Patient::where('parent_id', $parent->id)
            ->update([
                'contact_no' => $newContactNumber,
            ]);
        
        // Log the cascade update for audit trail
        Log::info('Contact number cascaded', [
            'parent_id' => $parent->id,
            'parent_username' => $parent->username,
            'new_contact' => $newContactNumber,
            'patients_updated' => $updatedCount,
        ]);
        
        return $updatedCount;
    }
    
    /**
     * Update parent profile including contact, address, and barangay with cascade
     * 
     * CASCADE BEHAVIOR:
     * - contact_no: Updates patient.contact_no
     * - address: Updates patient.address
     * - barangay: Updates patient.barangay
     * 
     * @param Parents $parent The parent model to update
     * @param array $data Array containing contact_no, email, address, barangay
     * @return array ['parent_updated' => bool, 'patients_updated' => int, 'fields_cascaded' => array]
     */
    public function updateProfile(Parents $parent, array $data): array
    {
        // Update the parent's profile
        $parent->update([
            'contact_number' => $data['contact_no'],
            'address' => $data['address'],
            'barangay' => $data['barangay'],
            'email' => $data['email'],
        ]);
        
        // CASCADE UPDATE: Update contact_no, address, and barangay for ALL patients
        // This ensures all children of a parent share the same contact info
        $patientsUpdated = Patient::where('parent_id', $parent->id)
            ->update([
                'contact_no' => $data['contact_no'],
                'address' => $data['address'],
                'barangay' => $data['barangay'],
            ]);
        
        // Log the cascade update for audit trail
        Log::info('Parent profile cascaded to patients', [
            'parent_id' => $parent->id,
            'parent_username' => $parent->username,
            'new_contact' => $data['contact_no'],
            'new_address' => $data['address'],
            'new_barangay' => $data['barangay'],
            'patients_updated' => $patientsUpdated,
        ]);
        
        return [
            'parent_updated' => true,
            'patients_updated' => $patientsUpdated,
            'fields_cascaded' => ['contact_no', 'address', 'barangay'],
        ];
    }
    
    /**
     * Update only address and barangay for parent and cascade to all children
     * 
     * @param Parents $parent The parent model to update
     * @param string $newAddress The new address
     * @param string $newBarangay The new barangay
     * @return int Number of patient records updated
     */
    public function updateAddressAndBarangay(Parents $parent, string $newAddress, string $newBarangay): int
    {
        // Update the parent's address and barangay
        $parent->update([
            'address' => $newAddress,
            'barangay' => $newBarangay,
        ]);
        
        // CASCADE UPDATE: Update address and barangay for all patients under this parent
        $updatedCount = Patient::where('parent_id', $parent->id)
            ->update([
                'address' => $newAddress,
                'barangay' => $newBarangay,
            ]);
        
        // Log the cascade update for audit trail
        Log::info('Address and barangay cascaded', [
            'parent_id' => $parent->id,
            'parent_username' => $parent->username,
            'new_address' => $newAddress,
            'new_barangay' => $newBarangay,
            'patients_updated' => $updatedCount,
        ]);
        
        return $updatedCount;
    }
}
