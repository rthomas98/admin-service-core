<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentResourceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set the current panel
        Filament::setCurrentPanel('admin');
    }

    public function test_liv_transport_resources_only_visible_for_liv_transport_company(): void
    {
        // Create companies
        $livTransport = Company::factory()->create([
            'name' => 'LIV Transport LLC',
            'slug' => 'liv-transport',
        ]);
        
        $rawDisposal = Company::factory()->create([
            'name' => 'RAW Disposal LLC',
            'slug' => 'raw-disposal',
        ]);

        // Create admin user
        $user = User::factory()->create();
        $user->companies()->attach([
            $livTransport->id => ['role' => 'admin'],
            $rawDisposal->id => ['role' => 'admin'],
        ]);

        // Act as the user
        $this->actingAs($user);

        // Set LIV Transport as tenant
        Filament::setTenant($livTransport);
        
        // LIV Transport resources should be visible
        $this->assertTrue(\App\Filament\Resources\Vehicles\VehicleResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\Trailers\TrailerResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\FinanceCompanies\FinanceCompanyResource::canViewAny());
        $this->assertTrue(\App\Filament\Resources\VehicleFinances\VehicleFinanceResource::canViewAny());
        
        // Set RAW Disposal as tenant
        Filament::setTenant($rawDisposal);
        
        // LIV Transport resources should NOT be visible
        $this->assertFalse(\App\Filament\Resources\Vehicles\VehicleResource::canViewAny());
        $this->assertFalse(\App\Filament\Resources\Trailers\TrailerResource::canViewAny());
        $this->assertFalse(\App\Filament\Resources\FinanceCompanies\FinanceCompanyResource::canViewAny());
        $this->assertFalse(\App\Filament\Resources\VehicleFinances\VehicleFinanceResource::canViewAny());
    }

    public function test_raw_disposal_resources_only_visible_for_raw_disposal_company(): void
    {
        // Create companies
        $livTransport = Company::factory()->create([
            'name' => 'LIV Transport LLC',
            'slug' => 'liv-transport',
        ]);
        
        $rawDisposal = Company::factory()->create([
            'name' => 'RAW Disposal LLC',
            'slug' => 'raw-disposal',
        ]);

        // Create admin user
        $user = User::factory()->create();
        $user->companies()->attach([
            $livTransport->id => ['role' => 'admin'],
            $rawDisposal->id => ['role' => 'admin'],
        ]);

        // Act as the user
        $this->actingAs($user);

        // Set RAW Disposal as tenant
        Filament::setTenant($rawDisposal);
        
        // RAW Disposal resources should be visible
        $this->assertTrue(\App\Filament\Resources\Equipment\EquipmentResource::canViewAny());
        
        // Set LIV Transport as tenant
        Filament::setTenant($livTransport);
        
        // RAW Disposal resources should NOT be visible
        $this->assertFalse(\App\Filament\Resources\Equipment\EquipmentResource::canViewAny());
    }
}