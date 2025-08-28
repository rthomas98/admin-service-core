# Admin Service Core - Style Guide & Development Standards

## 1. Code Organization

### File Structure
```
admin-service-core/
├── app/
│   ├── Filament/          # Filament resources and components
│   │   ├── Resources/      # Organized by domain (Vehicles/, Customers/, etc.)
│   │   ├── Widgets/        # Dashboard widgets
│   │   └── Actions/        # Reusable actions
│   ├── Models/             # Eloquent models
│   ├── Enums/              # Application enums
│   ├── Services/           # Business logic services
│   ├── Mail/               # Mail classes
│   └── Jobs/               # Queued jobs
├── resources/
│   ├── js/
│   │   ├── Pages/          # Inertia page components
│   │   ├── Components/     # Reusable React components
│   │   └── lib/            # Utility functions
│   └── css/                # Stylesheets
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
└── context/                # Documentation and guidelines
```

### Module Organization
Group related functionality into logical modules:
- **Fleet Management**: Vehicles, Drivers, Maintenance, Fuel
- **Waste Management**: Routes, Collections, Disposal Sites
- **Financial**: Invoices, Payments, Pricing
- **Customer Service**: Customers, Service Orders, Quotes
- **Operations**: Emergency Services, Work Orders, Assignments

## 2. Naming Conventions

### PHP/Laravel

#### Models
```php
// ✅ Singular, PascalCase
class Vehicle extends Model
class DriverAssignment extends Model

// ❌ Avoid
class vehicles extends Model
class driver_assignment extends Model
```

#### Filament Resources
```php
// ✅ Resource suffix, organized by domain
class VehicleResource extends Resource
class CustomerResource extends Resource

// Directory structure
app/Filament/Resources/Vehicles/VehicleResource.php
app/Filament/Resources/Vehicles/Pages/ListVehicles.php
app/Filament/Resources/Vehicles/Schemas/VehicleForm.php
app/Filament/Resources/Vehicles/Tables/VehiclesTable.php
```

#### Enums
```php
// ✅ TitleCase for enum cases
enum VehicleStatus: string
{
    case Active = 'active';
    case InMaintenance = 'in_maintenance';
    case OutOfService = 'out_of_service';
}

// ❌ Avoid
case ACTIVE = 'active';
case in_maintenance = 'in_maintenance';
```

#### Methods & Variables
```php
// ✅ Descriptive camelCase
public function getActiveVehicles(): Collection
public function isAvailableForAssignment(): bool
$isRegisteredForDiscounts = true;

// ❌ Avoid
public function vehicles(): Collection
public function check(): bool
$discount = true;
```

### React/TypeScript

#### Components
```tsx
// ✅ PascalCase for components
export function VehicleCard() { }
export function DriverAssignmentForm() { }

// File naming: PascalCase.tsx
VehicleCard.tsx
DriverAssignmentForm.tsx
```

#### Props & Interfaces
```tsx
// ✅ Props suffix for component props
interface VehicleCardProps {
    vehicle: Vehicle;
    onEdit?: (id: number) => void;
}

// ✅ Descriptive interface names
interface Vehicle {
    id: number;
    registrationNumber: string;
    status: VehicleStatus;
}
```

#### Functions & Variables
```tsx
// ✅ camelCase for functions and variables
const handleVehicleUpdate = () => { };
const isVehicleActive = status === 'active';

// ✅ Destructure imports
import { useState, useEffect } from 'react';
import { router, Link } from '@inertiajs/react';
```

### Database

#### Tables
```sql
-- ✅ Plural, snake_case
CREATE TABLE vehicles (...)
CREATE TABLE driver_assignments (...)

-- ❌ Avoid
CREATE TABLE Vehicle (...)
CREATE TABLE DriverAssignment (...)
```

#### Columns
```sql
-- ✅ snake_case, descriptive
registration_number VARCHAR(255)
maintenance_due_date DATE
is_available BOOLEAN

-- ❌ Avoid
regNum VARCHAR(255)
maintDate DATE
available BOOLEAN
```

## 3. Coding Standards

### PHP/Laravel Standards

#### Form Requests
```php
// ✅ Always use Form Request classes for validation
class StoreVehicleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'unique:vehicles'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_number.required' => 'Vehicle registration is required.',
            'registration_number.unique' => 'This registration number already exists.',
        ];
    }
}
```

#### Service Classes
```php
// ✅ Single responsibility, clear methods
class VehicleMaintenanceService
{
    public function scheduleMaintenence(Vehicle $vehicle, Carbon $date): MaintenanceLog
    {
        // Business logic here
    }
    
    public function getUpcomingMaintenance(int $days = 30): Collection
    {
        // Query and return
    }
}
```

#### Eloquent Relationships
```php
// ✅ Always type hint relationships
public function driver(): BelongsTo
{
    return $this->belongsTo(Driver::class);
}

public function maintenanceLogs(): HasMany
{
    return $this->hasMany(MaintenanceLog::class);
}
```

### React/TypeScript Standards

#### Component Structure
```tsx
// ✅ Consistent component structure
import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { VehicleCardProps } from '@/types';

export function VehicleCard({ vehicle, onEdit }: VehicleCardProps) {
    // 1. State declarations
    const [isLoading, setIsLoading] = useState(false);
    
    // 2. Effects
    useEffect(() => {
        // Effect logic
    }, []);
    
    // 3. Event handlers
    const handleEdit = () => {
        if (onEdit) onEdit(vehicle.id);
    };
    
    // 4. Render
    return (
        <div className="rounded-lg border border-gray-200 p-4">
            {/* Component JSX */}
        </div>
    );
}
```

#### Inertia Forms
```tsx
// ✅ Use Inertia's router for form submissions
const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    
    router.post('/vehicles', formData, {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        },
        onError: (errors) => {
            // Handle errors
        }
    });
};
```

### Tailwind CSS Standards

#### Class Organization
```tsx
// ✅ Logical class grouping
<div className={clsx(
    // Layout
    'flex items-center justify-between',
    // Spacing
    'p-4 gap-4',
    // Styling
    'rounded-lg border border-gray-200',
    // States
    'hover:border-gray-300 focus:outline-none focus:ring-2',
    // Dark mode
    'dark:border-gray-700 dark:hover:border-gray-600'
)}>
```

#### Avoid Magic Numbers
```tsx
// ✅ Use Tailwind's scale
<div className="p-4 text-base font-medium">

// ❌ Avoid arbitrary values unless necessary
<div className="p-[17px] text-[15px]">
```

## 4. Filament-Specific Standards

### Resource Structure
```php
// ✅ Consistent resource organization
class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Fleet Management';
    protected static ?int $navigationSort = 1;
    
    public static function form(Form $form): Form
    {
        return $form->schema(
            VehicleForm::make()
        );
    }
    
    public static function table(Table $table): Table
    {
        return VehiclesTable::make($table);
    }
}
```

### Form Schemas
```php
// ✅ Organize into separate schema classes
class VehicleForm
{
    public static function make(): array
    {
        return [
            Section::make('Vehicle Information')
                ->schema([
                    TextInput::make('registration_number')
                        ->label('Registration Number')
                        ->required()
                        ->maxLength(255),
                    
                    Select::make('vehicle_type')
                        ->label('Vehicle Type')
                        ->options(VehicleType::class)
                        ->required(),
                ])
                ->columns(2),
        ];
    }
}
```

## 5. Testing Standards

### Pest Test Structure
```php
// ✅ Use Pest's clean syntax
it('creates a vehicle with valid data', function () {
    $vehicle = Vehicle::factory()->make();
    
    livewire(CreateVehicle::class)
        ->fillForm([
            'registration_number' => $vehicle->registration_number,
            'vehicle_type' => $vehicle->vehicle_type,
        ])
        ->call('create')
        ->assertRedirect();
    
    expect(Vehicle::count())->toBe(1);
});

// ✅ Use datasets for multiple scenarios
it('validates required fields', function (string $field) {
    livewire(CreateVehicle::class)
        ->fillForm([
            $field => '',
        ])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with([
    'registration_number',
    'vehicle_type',
]);
```

### Test Organization
```
tests/
├── Feature/
│   ├── Fleet/
│   │   ├── VehicleManagementTest.php
│   │   └── DriverAssignmentTest.php
│   ├── Financial/
│   │   └── InvoiceProcessingTest.php
│   └── Api/
│       └── QuoteSubmissionTest.php
└── Unit/
    ├── Models/
    │   └── VehicleTest.php
    └── Services/
        └── NotificationServiceTest.php
```

## 6. Documentation Standards

### Code Comments
```php
// ✅ Use PHPDoc for complex methods
/**
 * Schedule maintenance for a vehicle based on mileage or date.
 * 
 * @param Vehicle $vehicle The vehicle to schedule maintenance for
 * @param Carbon $date The scheduled date for maintenance
 * @param string|null $notes Optional notes about the maintenance
 * @return MaintenanceLog The created maintenance log entry
 * @throws VehicleNotAvailableException
 */
public function scheduleMaintenence(Vehicle $vehicle, Carbon $date, ?string $notes = null): MaintenanceLog
{
    // Implementation
}

// ❌ Avoid obvious comments
// Set the vehicle status to active
$vehicle->status = 'active'; // Bad - the code is self-explanatory
```

### README Documentation
Each module should have clear documentation:
```markdown
## Fleet Management Module

### Key Features
- Vehicle tracking and assignment
- Driver management
- Maintenance scheduling
- Fuel consumption tracking

### Common Commands
```bash
php artisan vehicle:check-maintenance  # Check vehicles due for maintenance
php artisan driver:update-status       # Update driver availability
```
```

## 7. Git Workflow

### Branch Naming
```bash
# ✅ Clear, descriptive branch names
feature/add-vehicle-maintenance-scheduling
fix/invoice-calculation-error
chore/update-dependencies
refactor/customer-service-module

# ❌ Avoid
feature/new-feature
fix/bug
update
```

### Commit Messages
```bash
# ✅ Clear, concise commit messages
git commit -m "Add vehicle maintenance scheduling with email notifications"
git commit -m "Fix invoice total calculation for multi-line items"
git commit -m "Refactor customer service module for better separation of concerns"

# ❌ Avoid
git commit -m "Fixed stuff"
git commit -m "Updates"
git commit -m "WIP"
```

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No console errors
```

## 8. Performance Guidelines

### Database Optimization
```php
// ✅ Use eager loading to prevent N+1 queries
$vehicles = Vehicle::with(['driver', 'maintenanceLogs'])->get();

// ✅ Use query scopes for common queries
public function scopeActive($query)
{
    return $query->where('status', VehicleStatus::Active);
}

// Usage
$activeVehicles = Vehicle::active()->get();
```

### Frontend Optimization
```tsx
// ✅ Use React.memo for expensive components
export const VehicleList = React.memo(({ vehicles }: VehicleListProps) => {
    // Component logic
});

// ✅ Lazy load heavy components
const HeavyChart = lazy(() => import('@/Components/Charts/HeavyChart'));
```

## 9. Security Standards

### Input Validation
```php
// ✅ Always validate and sanitize input
$validated = $request->validate([
    'email' => ['required', 'email', 'exists:users'],
    'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
]);

// ✅ Use authorization
public function update(Request $request, Vehicle $vehicle)
{
    $this->authorize('update', $vehicle);
    // Update logic
}
```

### Environment Variables
```php
// ✅ Use config files, never env() directly
$apiKey = config('services.sms.key');

// ❌ Avoid
$apiKey = env('SMS_API_KEY');
```

## 10. Development Workflow

### Before Starting Work
1. Read relevant documentation in `/context` folder
2. Review existing similar implementations
3. Check for reusable components
4. Plan implementation approach

### During Development
1. Follow TDD when possible
2. Run linting and formatting: `vendor/bin/pint`
3. Type check TypeScript: `npm run types`
4. Test as you go: `php artisan test --filter=YourTest`

### Before Committing
1. Run full test suite: `php artisan test`
2. Format code: `vendor/bin/pint`
3. Build assets: `npm run build`
4. Update documentation if needed

### Code Review Checklist
- [ ] Code follows naming conventions
- [ ] No unnecessary comments
- [ ] Tests cover new functionality
- [ ] No N+1 queries
- [ ] Responsive design verified
- [ ] Dark mode supported
- [ ] Accessibility standards met
- [ ] Performance impact assessed

---

**Note:** This style guide is a living document. Update it as patterns evolve and new conventions are established. All team members should follow these guidelines to maintain consistency across the codebase.