<?php

namespace App\Console\Commands;

use App\Models\HealthWorker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateRhuAdmin extends Command
{
    protected $signature = 'app:create-rhu-admin';

    protected $description = 'Create the first RHU administrator interactively';

    public function handle(): int
    {
        if (HealthWorker::whereNull('barangay_id')->exists()) {
            $this->error('An RHU administrator already exists. This command can only be used for the first administrator.');

            return self::FAILURE;
        }

        $username = $this->askForUsername();
        $name = $this->askForRequiredValue('Full name');
        $email = $this->askForEmail();
        $password = $this->askForPassword();

        DB::transaction(function () use ($username, $name, $email, $password): void {
            HealthWorker::create([
                'username' => $username,
                'name' => $name,
                'password' => Hash::make($password),
                'email' => $email,
                'barangay_id' => null,
                'is_active' => true,
                'created_by' => null,
            ]);
        });

        $this->info("RHU administrator '{$username}' created successfully.");
        $this->warn('Store the credentials securely. The password is not displayed or saved by this command.');

        return self::SUCCESS;
    }

    private function askForUsername(): string
    {
        while (true) {
            $username = strtolower(trim((string) $this->ask('Username')));

            if (!preg_match('/^[a-z0-9_]{3,50}$/', $username)) {
                $this->error('Username must be 3-50 characters using lowercase letters, numbers, and underscores only.');
                continue;
            }

            if (HealthWorker::where('username', $username)->exists()) {
                $this->error('That username is already in use.');
                continue;
            }

            return $username;
        }
    }

    private function askForRequiredValue(string $label): string
    {
        while (true) {
            $value = trim((string) $this->ask($label));

            if ($value !== '') {
                return $value;
            }

            $this->error("{$label} is required.");
        }
    }

    private function askForEmail(): ?string
    {
        while (true) {
            $email = trim((string) $this->ask('Email (optional)'));

            if ($email === '') {
                return null;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Enter a valid email address or leave it blank.');
                continue;
            }

            if (HealthWorker::where('email', $email)->exists()) {
                $this->error('That email address is already in use.');
                continue;
            }

            return $email;
        }
    }

    private function askForPassword(): string
    {
        while (true) {
            $password = (string) $this->secret('Password (minimum 12 characters)');
            $confirmation = (string) $this->secret('Confirm password');

            if (strlen($password) < 12) {
                $this->error('Password must be at least 12 characters long.');
                continue;
            }

            if (!hash_equals($password, $confirmation)) {
                $this->error('The passwords do not match.');
                continue;
            }

            return $password;
        }
    }
}
