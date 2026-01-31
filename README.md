# AUSO PBX Management System

A Laravel-based PBX (Private Branch Exchange) management system with Filament admin panel for managing companies, branches, departments, users, and extensions.

## Features

### Core Management
- **Companies** - Create and manage multiple companies
- **Branches** - Manage company branches
- **Departments** - Create departments within branches
- **Users** - User management with role-based access control
- **Extensions** - Manage VoIP extensions with multiple protocols

### Extension Management (PBC)
The PBC (Private Branch Exchange Control) module includes:

#### Extension Types
- Manage protocol types: SIP, IAX2, PJSIP
- Create custom extension types
- Located under `PBC` navigation group

#### Extensions
- Create extensions with 3-4 digit numbers
- Link extensions to companies
- Assign extension types (SIP/IAX2/PJSIP)
- Company-scoped visibility for users
- Unique extension numbers per company

### Auso API Integration
- **Automatic API Sync** - Extensions are automatically synced to Auso API on creation
- **API Tracking** - All API calls, payloads, and responses are logged in the database
- **Resync Capability** - Manual resync button available for failed API calls
- **Error Recovery** - Failed API calls don't prevent extension creation in the database
- **Detailed Logging** - Complete audit trail of all API interactions

### Features
- **Role-Based Access Control** - User roles and permissions management
- **Company Isolation** - Users can be scoped to specific companies
- **Inline Resource Creation** - Create related resources without navigation
- **Filament Admin Panel** - Clean, modern admin interface
- **Database Migrations** - Version-controlled schema management

## Installation

### Prerequisites
- PHP 8.2+
- Laravel 11+
- PostgreSQL/MySQL
- Composer

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd auso-pbx
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

## Demo Credentials

The following demo credentials are available after running the `RoleAndPermissionSeeder`:

### Company
- **Name**: Auso
- **Domain**: auso-world.com

### Super Admin User
- **Email**: admin@pbx.test
- **Password**: password
- **Role**: super_admin

### Company Admin User
- **Email**: admin@auso-world.com
- **Password**: password
- **Role**: company_admin
- **Company**: Auso

### Company User
- **Email**: user@auso-world.com
- **Password**: password
- **Role**: user
- **Company**: Auso

To seed these demo credentials, run:
```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

### Demo Extensions

The `CompanyExtensionSeeder` creates sample extensions for the Auso company:

**SIP Extensions (5 total):**
- 1001, 1002, 1003, 1004, 1005

**IAX2 Extensions (3 total):**
- 2001, 2002, 2003

To seed these extensions, run:
```bash
php artisan db:seed --class=CompanyExtensionSeeder
```

Or include them in the full database seed:
```bash
php artisan db:seed
```

## Project Structure

```
app/
├── Models/
│   ├── User.php
│   ├── Company.php
│   ├── Branch.php
│   ├── Department.php
│   ├── Extension.php
│   └── ExtensionType.php
├── Filament/
│   └── Resources/
│       ├── Companies/
│       ├── Branches/
│       ├── Departments/
│       ├── Users/
│       ├── Extensions/
│       └── ExtensionTypes/
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php

database/
├── migrations/
│   ├── 2025_12_30_144832_create_companies_table.php
│   ├── 2025_12_30_144833_create_branches_table.php
│   ├── 2025_12_30_144834_create_departments_table.php
│   ├── 2025_12_30_160000_create_extension_types_table.php
│   ├── 2025_12_30_160001_create_extensions_table.php
│   └── 2026_01_31_073426_add_api_tracking_to_extensions_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── RoleAndPermissionSeeder.php
    ├── ExtensionTypeSeeder.php
    └── CompanyExtensionSeeder.php
```

## Resource Management

### Companies
- **Route**: `/admin/companies`
- **Navigation Group**: Settings
- **Fields**: Name, Domain, Email, Hotline, Primary Email
- **Permissions**: Super admin only

### Branches
- **Route**: `/admin/branches`
- **Navigation Group**: Settings
- **Fields**: Name, Code, Company
- **Permissions**: Super admin only or scoped to company

### Departments
- **Route**: `/admin/departments`
- **Navigation Group**: Settings
- **Fields**: Name, Company, Branch
- **Create from Branch**: Yes (with branch pre-filled)
- **Branch Creation Modal**: Inline creation support

### Users
- **Route**: `/admin/users`
- **Navigation Group**: Settings
- **Fields**: Name, Email, Company, Role
- **Permissions**: Role-based

### Extensions (PBC)
- **Route**: `/admin/extensions`
- **Navigation Group**: PBC (Sort: 2)
- **Fields**: 
  - Extension Number (3-4 digits, required)
  - Company (auto-filled for company users)
  - Extension Type (with inline creation)
  - API Status (tracks sync status with Auso API)
  - API Payload (stores the data sent to API)
  - API Response (stores the API response/error)
- **Validation**: 
  - Number must be 3-4 digits: `^\d{3,4}$`
  - Unique per company
- **Actions**: 
  - Resync button (yellow warning color) - appears when api_status ≠ 200
  - Requires confirmation before executing
- **API Integration**: 
  - Automatic sync on creation to Auso API endpoint `/auExtenAPI/create_exten.php`
  - Multipart form data with: extension, password, context, status, exten_type, type, updatedby
  - Error tracking without preventing record creation
- **Permissions**: Super admin full access, company users see own extensions

### Extension Types (PBC)
- **Route**: `/admin/extension-types`
- **Navigation Group**: PBC (Sort: 1)
- **Fields**: Name (SIP, IAX2, PJSIP, etc.)
- **Pre-populated**: SIP, IAX2, PJSIP
- **Inline Creation**: Available in Extension form

## Database Schema

### extensions table
```sql
CREATE TABLE extensions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    number VARCHAR(4) NOT NULL,
    company_id BIGINT NOT NULL REFERENCES companies(id),
    extension_type_id BIGINT NOT NULL REFERENCES extension_types(id),
    password VARCHAR(255) NOT NULL,
    api_status INT NULLABLE,
    api_payload JSON NULLABLE,
    api_response JSON NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(company_id, number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (extension_type_id) REFERENCES extension_types(id) ON DELETE CASCADE
);
```

### extension_types table
```sql
CREATE TABLE extension_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Auso API Integration

### Configuration

Configure the Auso API connection in `.env`:

```env
AUSO_API_URL=http://your-auso-server:port/path/
AUSO_API_USERNAME=your_username
AUSO_API_PASSWORD=your_password
AUSO_API_TIMEOUT=30
AUSO_API_RETRY_ATTEMPTS=3
```

### API Service (`AusoApiManager`)

Located at `app/Services/AusoApiManager.php`, provides:

- **GET requests**: `$apiManager->get(endpoint, query)`
- **POST requests**: `$apiManager->post(endpoint, data)`
- **PUT requests**: `$apiManager->put(endpoint, data)`
- **DELETE requests**: `$apiManager->delete(endpoint)`
- **Create Extension**: `$apiManager->createExtension(apiData)` - multipart form data

**Features:**
- Automatic Basic Auth with configured credentials
- Retry logic with exponential backoff
- Configurable timeout and retry attempts
- Exception handling and error messages

### Extension API Workflow

1. **Extension Creation** (User creates extension in Filament)
   - Extension saved to database
   - `afterCreate()` hook triggers API call
   - API payload and response stored in database

2. **API Call Details**
   - **Endpoint**: `/auExtenAPI/create_exten.php`
   - **Method**: POST (multipart form data)
   - **Data Fields**:
     - `extension`: The extension number
     - `password`: Extension password
     - `context`: Company context
     - `status`: Extension status (default: "ACTIVE")
     - `exten_type`: Extension protocol type
     - `type`: Extension protocol type (duplicate)
     - `updatedby`: User making the change (default: "ADMIN")

3. **API Response Tracking**
   - **Success** (HTTP 200): `api_status = 200`
   - **Failure**: `api_status = NULL`, error stored in `api_response`
   - **Retry**: "Resync with Auso" button available on extension edit page

4. **Error Handling**
   - Failed API calls don't prevent extension creation
   - Errors logged to storage/logs/laravel.log
   - Manual resync available without recreating the extension

### API Tracking Fields

All extensions store:
- **`api_status`**: HTTP status code (200 = success, NULL = failed/not attempted)
- **`api_payload`**: JSON array of data sent to API
- **`api_response`**: JSON response from API or error message

### Manual Resync

To resync a failed extension:

1. Navigate to the extension edit page
2. Look for the "Resync with Auso" button (appears only if status ≠ 200)
3. Click the button and confirm
4. System will attempt the API call again
5. Success/error notification will appear
6. Database tracking fields will be updated

## Development Workflow

### Adding a New Resource

1. **Create Model** in `app/Models/`
   ```php
   php artisan make:model ModelName --migration
   ```

2. **Create Migration** with schema

3. **Create Filament Resource**
   ```bash
   php artisan make:filament-resource ResourceName
   ```

4. **Create Form Schema** in `Schemas/ResourceNameForm.php`

5. **Create Table Configuration** in `Tables/ResourceNameTable.php`

6. **Configure Resource** class with navigation and relationships

### Company Scoping Pattern

To scope a resource to a user's company:

```php
public static function table(Table $table): Table
{
    return YourTable::configure($table)
        ->modifyQueryUsing(fn ($query) => self::scopeByCompany($query));
}

protected static function scopeByCompany($query)
{
    $user = auth()->user();
    if ($user && $user->company_id) {
        return $query->where('company_id', $user->company_id);
    }
    return $query;
}
```

### Inline Resource Creation

To enable creating related resources from a Select field:

```php
Select::make('extension_type_id')
    ->label('Extension Type')
    ->options(ExtensionType::pluck('name', 'id'))
    ->createOptionForm(fn ($form) => $form
        ->schema([
            TextInput::make('name')
                ->required()
                ->unique('extension_types', 'name'),
        ])
    )
    ->createOptionUsing(function ($data) {
        return ExtensionType::create($data)->id;
    })
```

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## Troubleshooting

### Extensions not showing in admin
- Clear cache: `php artisan cache:clear`
- Ensure resources are in `app/Filament/Resources/`
- Check that models exist in `app/Models/`

### Create buttons not visible
- Ensure `CreateAction` is added to table's `headerActions`
- Verify user has proper permissions
- Check browser cache

### Extension number validation
- Must be exactly 3-4 digits
- Pattern: `^\d{3,4}$`
- Invalid examples: "100" (2 digits), "10001" (5 digits), "100a" (contains letter)

## License

This project is open-source software licensed under the MIT license.

