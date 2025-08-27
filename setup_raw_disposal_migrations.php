<?php

// This is a setup script to configure all RAW Disposal migrations
// Run: php setup_raw_disposal_migrations.php

$migrations = [
    // ServiceOrders
    '2025_08_26_184053_create_service_orders_table.php' => '
    public function up(): void
    {
        Schema::create(\'service_orders\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'customer_id\')->constrained()->cascadeOnDelete();
            $table->string(\'order_number\')->unique();
            $table->enum(\'service_type\', [\'delivery\', \'pickup\', \'exchange\', \'service\']);
            $table->enum(\'status\', [\'quote\', \'scheduled\', \'delivered\', \'active\', \'pickup_scheduled\', \'completed\', \'cancelled\'])->default(\'quote\');
            $table->date(\'delivery_date\')->nullable();
            $table->date(\'pickup_date\')->nullable();
            $table->text(\'delivery_address\');
            $table->string(\'delivery_city\')->nullable();
            $table->string(\'delivery_state\')->nullable();
            $table->string(\'delivery_zip\')->nullable();
            $table->decimal(\'latitude\', 10, 7)->nullable();
            $table->decimal(\'longitude\', 10, 7)->nullable();
            $table->text(\'special_instructions\')->nullable();
            $table->decimal(\'total_amount\', 10, 2)->nullable();
            $table->string(\'po_number\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'status\']);
            $table->index([\'company_id\', \'customer_id\']);
            $table->index([\'company_id\', \'delivery_date\']);
        });
    }',
    
    // Pricings
    '2025_08_26_184053_create_pricings_table.php' => '
    public function up(): void
    {
        Schema::create(\'pricings\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->enum(\'equipment_type\', [\'dumpster\', \'portable_toilet\', \'handwash_station\', \'holding_tank\', \'water_tank\']);
            $table->string(\'size\')->nullable();
            $table->decimal(\'daily_rate\', 10, 2);
            $table->decimal(\'weekly_rate\', 10, 2);
            $table->decimal(\'monthly_rate\', 10, 2);
            $table->decimal(\'delivery_fee\', 10, 2)->default(0);
            $table->decimal(\'pickup_fee\', 10, 2)->default(0);
            $table->decimal(\'cleaning_fee\', 10, 2)->default(0);
            $table->decimal(\'damage_waiver_fee\', 10, 2)->default(0);
            $table->boolean(\'is_active\')->default(true);
            $table->date(\'effective_date\')->nullable();
            $table->string(\'region\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'equipment_type\', \'is_active\']);
        });
    }',
    
    // DeliverySchedules
    '2025_08_26_184053_create_delivery_schedules_table.php' => '
    public function up(): void
    {
        Schema::create(\'delivery_schedules\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'service_order_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'equipment_id\')->nullable()->constrained()->nullOnDelete();
            $table->foreignId(\'driver_id\')->nullable()->constrained()->nullOnDelete();
            $table->enum(\'type\', [\'delivery\', \'pickup\', \'exchange\']);
            $table->date(\'scheduled_date\');
            $table->time(\'scheduled_time\')->nullable();
            $table->dateTime(\'actual_datetime\')->nullable();
            $table->enum(\'status\', [\'pending\', \'assigned\', \'en_route\', \'completed\', \'cancelled\'])->default(\'pending\');
            $table->text(\'notes\')->nullable();
            $table->string(\'route_number\')->nullable();
            $table->integer(\'route_order\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'scheduled_date\', \'status\']);
            $table->index([\'company_id\', \'driver_id\', \'scheduled_date\']);
        });
    }',
    
    // ServiceSchedules
    '2025_08_26_184054_create_service_schedules_table.php' => '
    public function up(): void
    {
        Schema::create(\'service_schedules\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'equipment_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'technician_id\')->nullable()->constrained(\'drivers\')->nullOnDelete();
            $table->enum(\'service_type\', [\'cleaning\', \'maintenance\', \'repair\', \'inspection\']);
            $table->date(\'scheduled_date\');
            $table->dateTime(\'completed_datetime\')->nullable();
            $table->enum(\'status\', [\'scheduled\', \'in_progress\', \'completed\', \'cancelled\'])->default(\'scheduled\');
            $table->text(\'notes\')->nullable();
            $table->enum(\'frequency\', [\'one_time\', \'weekly\', \'bi_weekly\', \'monthly\'])->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'scheduled_date\', \'status\']);
            $table->index([\'company_id\', \'equipment_id\']);
        });
    }',
    
    // Drivers
    '2025_08_26_184054_create_drivers_table.php' => '
    public function up(): void
    {
        Schema::create(\'drivers\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'user_id\')->nullable()->constrained()->nullOnDelete();
            $table->string(\'first_name\');
            $table->string(\'last_name\');
            $table->string(\'email\')->nullable();
            $table->string(\'phone\');
            $table->string(\'license_number\')->nullable();
            $table->date(\'license_expiry\')->nullable();
            $table->string(\'vehicle_assigned\')->nullable();
            $table->json(\'service_areas\')->nullable();
            $table->enum(\'status\', [\'active\', \'on_leave\', \'inactive\'])->default(\'active\');
            $table->enum(\'type\', [\'driver\', \'technician\', \'both\'])->default(\'driver\');
            $table->timestamps();
            
            $table->index([\'company_id\', \'status\']);
        });
    }',
    
    // Invoices
    '2025_08_26_184054_create_invoices_table.php' => '
    public function up(): void
    {
        Schema::create(\'invoices\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'service_order_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'customer_id\')->constrained()->cascadeOnDelete();
            $table->string(\'invoice_number\')->unique();
            $table->decimal(\'subtotal\', 10, 2);
            $table->decimal(\'tax_amount\', 10, 2)->default(0);
            $table->decimal(\'total_amount\', 10, 2);
            $table->enum(\'status\', [\'draft\', \'sent\', \'paid\', \'partial\', \'overdue\', \'cancelled\'])->default(\'draft\');
            $table->date(\'issue_date\');
            $table->date(\'due_date\');
            $table->date(\'paid_date\')->nullable();
            $table->text(\'notes\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'status\']);
            $table->index([\'company_id\', \'customer_id\']);
            $table->index([\'company_id\', \'due_date\']);
        });
    }',
    
    // Payments
    '2025_08_26_184054_create_payments_table.php' => '
    public function up(): void
    {
        Schema::create(\'payments\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'invoice_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'customer_id\')->constrained()->cascadeOnDelete();
            $table->decimal(\'amount\', 10, 2);
            $table->enum(\'payment_method\', [\'credit_card\', \'ach\', \'check\', \'cash\', \'wire\']);
            $table->string(\'transaction_id\')->nullable();
            $table->string(\'check_number\')->nullable();
            $table->date(\'payment_date\');
            $table->text(\'notes\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'payment_date\']);
            $table->index([\'company_id\', \'customer_id\']);
        });
    }',
    
    // Quotes
    '2025_08_26_184054_create_quotes_table.php' => '
    public function up(): void
    {
        Schema::create(\'quotes\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'customer_id\')->nullable()->constrained()->nullOnDelete();
            $table->string(\'quote_number\')->unique();
            $table->string(\'contact_name\')->nullable();
            $table->string(\'contact_email\')->nullable();
            $table->string(\'contact_phone\')->nullable();
            $table->json(\'items\'); // Array of quoted items
            $table->decimal(\'subtotal\', 10, 2);
            $table->decimal(\'tax_amount\', 10, 2)->default(0);
            $table->decimal(\'total_amount\', 10, 2);
            $table->date(\'valid_until\');
            $table->enum(\'status\', [\'draft\', \'sent\', \'accepted\', \'rejected\', \'expired\'])->default(\'draft\');
            $table->foreignId(\'converted_to_order_id\')->nullable()->constrained(\'service_orders\')->nullOnDelete();
            $table->text(\'notes\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'status\']);
            $table->index([\'company_id\', \'valid_until\']);
        });
    }',
    
    // ServiceAreas
    '2025_08_26_184054_create_service_areas_table.php' => '
    public function up(): void
    {
        Schema::create(\'service_areas\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->string(\'name\');
            $table->json(\'zip_codes\')->nullable();
            $table->json(\'parishes\')->nullable();
            $table->decimal(\'delivery_surcharge\', 10, 2)->default(0);
            $table->decimal(\'radius_miles\', 5, 2)->nullable();
            $table->boolean(\'is_active\')->default(true);
            $table->timestamps();
            
            $table->index([\'company_id\', \'is_active\']);
        });
    }',
    
    // MaintenanceLogs
    '2025_08_26_184055_create_maintenance_logs_table.php' => '
    public function up(): void
    {
        Schema::create(\'maintenance_logs\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'equipment_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'technician_id\')->nullable()->constrained(\'drivers\')->nullOnDelete();
            $table->enum(\'service_type\', [\'preventive\', \'repair\', \'inspection\', \'cleaning\']);
            $table->date(\'service_date\');
            $table->decimal(\'cost\', 10, 2)->nullable();
            $table->text(\'parts_used\')->nullable();
            $table->text(\'work_performed\');
            $table->date(\'next_service_due\')->nullable();
            $table->integer(\'hours_spent\')->nullable();
            $table->timestamps();
            
            $table->index([\'company_id\', \'equipment_id\', \'service_date\']);
        });
    }',
    
    // EmergencyServices
    '2025_08_26_184055_create_emergency_services_table.php' => '
    public function up(): void
    {
        Schema::create(\'emergency_services\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'company_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'customer_id\')->constrained()->cascadeOnDelete();
            $table->enum(\'urgency_level\', [\'low\', \'medium\', \'high\', \'critical\']);
            $table->json(\'equipment_needed\');
            $table->dateTime(\'requested_datetime\');
            $table->dateTime(\'response_datetime\')->nullable();
            $table->integer(\'response_time_minutes\')->nullable();
            $table->decimal(\'surcharge_amount\', 10, 2)->default(0);
            $table->text(\'description\');
            $table->enum(\'status\', [\'requested\', \'dispatched\', \'completed\', \'cancelled\'])->default(\'requested\');
            $table->timestamps();
            
            $table->index([\'company_id\', \'urgency_level\', \'status\']);
            $table->index([\'company_id\', \'requested_datetime\']);
        });
    }'
];

// Additional relationship table
$pivotTable = '2025_08_26_184056_create_service_order_equipment_table.php';
$pivotContent = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'service_order_equipment\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'service_order_id\')->constrained()->cascadeOnDelete();
            $table->foreignId(\'equipment_id\')->constrained(\'equipment\')->cascadeOnDelete();
            $table->integer(\'quantity\')->default(1);
            $table->decimal(\'rate\', 10, 2)->nullable();
            $table->timestamps();
            
            $table->unique([\'service_order_id\', \'equipment_id\']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'service_order_equipment\');
    }
};
';

// Write pivot table
file_put_contents(__DIR__ . '/database/migrations/' . $pivotTable, $pivotContent);

echo "RAW Disposal migrations setup complete!\n";
echo "Created pivot table for service_order_equipment relationship\n";

// Note: The actual migration files will be updated programmatically