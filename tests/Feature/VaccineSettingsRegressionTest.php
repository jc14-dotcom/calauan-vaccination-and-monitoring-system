<?php

namespace Tests\Feature;

use App\Http\Controllers\HealthWorker\VaccineSettingsController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Spatie\Activitylog\Facades\Activity;
use Tests\TestCase;

class VaccineSettingsRegressionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_vaccine_destroy_does_not_crash_with_activity_logging(): void
    {
        $user = new class implements Authenticatable {
            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return 1;
            }

            public function getAuthPassword()
            {
                return '';
            }

            public function getRememberToken()
            {
                return null;
            }

            public function setRememberToken($value)
            {
                // no-op
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }

            public function getAuthPasswordName()
            {
                return 'password';
            }

            public function isRHU()
            {
                return true;
            }
        };

        $guard = Mockery::mock();
        $guard->shouldReceive('user')->once()->andReturn($user);
        Auth::shouldReceive('guard')->once()->with('health_worker')->andReturn($guard);

        $deletedVaccine = new class extends \Illuminate\Database\Eloquent\Model {
            public $id = 1;
            public $vaccine_name = 'Test Vaccine';
            public $deleted = false;

            public function delete()
            {
                $this->deleted = true;
                return true;
            }
        };

        $controller = new class($deletedVaccine) extends VaccineSettingsController {
            private $vaccine;

            public function __construct($vaccine)
            {
                $this->vaccine = $vaccine;
            }

            protected function getVaccineById($id)
            {
                return $this->vaccine;
            }

            protected function hasRecordedDoseData($id): bool
            {
                return false;
            }

            protected function deletePatientVaccineRecords($id): void
            {
                // intentionally no-op for regression test
            }

            protected function deleteDoseConfigs($id): void
            {
                // intentionally no-op for regression test
            }
        };

        $activityLogger = Mockery::mock(\Spatie\Activitylog\ActivityLogger::class);
        $activityLogger->shouldReceive('useLog')->once()->with('vaccine_settings')->andReturnSelf();
        $activityLogger->shouldReceive('logger')->once()->andReturnSelf();
        $activityLogger->shouldReceive('event')->once()->with('vaccine_deleted')->andReturnSelf();
        $activityLogger->shouldReceive('performedOn')->once()->with($deletedVaccine)->andReturnSelf();
        $activityLogger->shouldReceive('withProperties')->once()->andReturnSelf();
        $activityLogger->shouldReceive('log')->once()->andReturnNull();
        Activity::swap($activityLogger);

        Cache::shouldReceive('forget')->once()->with('vaccine_config')->andReturn(true);

        $response = $controller->destroy(new Request(), 1);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
        $this->assertTrue($deletedVaccine->deleted);
    }
}
