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

## Default Credentials

- **Email**: admin@pbx.test
- **Password**: password
- **Role**: super_admin

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
│   └── 2025_12_30_160001_create_extensions_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── RoleAndPermissionSeeder.php
    └── ExtensionTypeSeeder.php
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
- **Validation**: 
  - Number must be 3-4 digits: `^\d{3,4}$`
  - Unique per company
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

