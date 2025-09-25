# Ovon Group Identity Provider (IDP)

A first-party Single Sign-On (SSO) Identity Provider for Ovon Group software products, featuring an integrated application launcher for seamless user experience across multiple applications.

## Overview

This Laravel-based identity provider serves as the central authentication and authorization system for Ovon Group's software ecosystem. It provides:

- **Single Sign-On (SSO)** authentication for multiple applications
- **Application Launcher** widget for easy access to authorized applications
- **Role-based Access Control (RBAC)** with application-specific permissions
- **Multi-factor Authentication (MFA)** support via OTP and Passkeys
- **Account Management** with application-specific access controls
- **API Integration** for client applications via OAuth 2.0

## Supported Applications

Currently supports the following Ovon Group applications:

- **Protego** - Warranty, national service plans and breakdown recovery solutions
- **Wheel2Web** - Dealership prep tool to shorten time to market for your stock

## Architecture

### Core Components

- **Authentication System**: Laravel Passport OAuth 2.0 implementation
- **User Management**: Multi-tenant user system with account-based access control
- **Application Registry**: Centralized application configuration and URL management
- **Permission System**: Spatie Laravel Permission with application-scoped roles
- **Admin Interface**: Filament-based administration panel

### Key Models

- `User`: Core user entity with UUID, multi-factor auth, and application access
- `Account`: Multi-tenant account system with application associations
- `Client`: OAuth 2.0 client applications
- `Role` & `Permission`: Application-scoped authorization system

## Developer Onboarding

### Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js and npm
- MySQL/PostgreSQL database
- Redis (for queues and caching)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd identity
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **OAuth setup**
   ```bash
   php artisan passport:install
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

### Development Environment

For local development, use the provided development script:

```bash
composer run dev
```

This command runs:
- Laravel development server
- Queue worker
- Log monitoring (Pail)
- Vite asset compilation

## Client Application Integration

### OvonOne SSO Laravel SDK

Client applications integrate with this IDP using the [OvonOne SSO Laravel SDK](https://github.com/ovon-group/ovonone-sso-laravel). This package provides:

- Seamless SSO authentication flow
- User session management
- Automatic token refresh
- Application-specific user data synchronization

### SDK Installation

In your client application:

```bash
composer require ovon-group/ovonone-sso-laravel
```

### SDK Configuration

```php
// config/ovonone-sso.php
return [
    'models' => [
        'user' => App\Models\User::class,
        'account' => App\Models\Account::class,
    ],

    'oauth_middleware' => ['web', 'guest'],

    'roles' => [
//        [
//            'name' => 'Admin',
//            'is_internal' => true,
//            'permissions' => [
//                'accounts.manage',
//                '...',
//            ],
//        ],
    ],
];
```

## API Documentation

### Authentication Endpoints

#### User Authentication
```http
GET /api/user
Authorization: Bearer {access_token}
```

Returns authenticated user information including:
- User UUID, name, email
- Associated accounts
- Application-specific roles
- Access permissions

#### Account Synchronization
```http
POST /api/accounts
Authorization: Bearer {client_credentials_token}
Content-Type: application/json

{
  "accounts": [
    {
      "uuid": "account-uuid",
      "name": "Account Name",
      "short_name": "Short Name",
      "deleted_at": null
    }
  ]
}
```

#### User Synchronization
```http
POST /api/users
Authorization: Bearer {client_credentials_token}
Content-Type: application/json

{
  "users": [
    {
      "uuid": "user-uuid",
      "name": "User Name",
      "email": "user@example.com",
      "mobile": "+44123456789",
      "is_internal": false,
      "roles": ["role-uuid-1", "role-uuid-2"],
      "accounts": ["account-uuid-1"]
    }
  ]
}
```

#### Role and Permission Synchronization
```http
POST /api/roles
Authorization: Bearer {client_credentials_token}
Content-Type: application/json

{
  "roles": [
    {
      "uuid": "role-uuid",
      "name": "role-name",
      "guard_name": "web",
      "is_internal": false,
      "permissions": [
        {
          "uuid": "permission-uuid",
          "name": "permission-name",
          "guard_name": "web"
        }
      ]
    }
  ]
}
```

## Application Management

### Adding New Applications

1. **Update ApplicationEnum**
   ```php
   // app/Enums/ApplicationEnum.php
   case NewApp = 'newapp';
   ```

2. **Configure application details**
   ```php
   public function getLabel(): string
   {
       return match ($this) {
           // ... existing cases
           self::NewApp => __('New Application'),
       };
   }
   
   public function getUrl(): string
   {
       return match (app()->environment()) {
           'production' => match ($this) {
               // ... existing cases
               self::NewApp => 'https://newapp.ovongroup.com',
           },
           'local' => match ($this) {
               // ... existing cases
               self::NewApp => 'https://newapp.test',
           },
       };
   }
   ```

3. **Create OAuth Client**
   ```bash
   php artisan passport:client --name="New Application"
   ```

4. **Update client application**
   - Install the OvonOne SSO Laravel SDK
   - Configure OAuth credentials
   - Implement SSO authentication flow

### Application Launcher

The application launcher widget automatically displays authorized applications for each user. It:

- Checks user access permissions per application
- Generates application-specific URLs with user context
- Provides visual indicators for application status
- Supports custom icons and branding per application

## Security Features

### Multi-Factor Authentication

- **One-Time Passwords (OTP)**: Email and SMS delivery
- **Passkeys**: WebAuthn/FIDO2 support for passwordless authentication
- **Traditional Passwords**: Fallback authentication method

### Access Control

- **Account-based Access**: Users can only access applications associated with their accounts
- **Role-based Permissions**: Application-specific roles and permissions
- **Internal User Override**: Internal users have access to all applications
- **Soft Deletion**: Support for user and account deactivation

### OAuth 2.0 Security

- **Client Credentials**: Secure API access for application synchronization
- **Authorization Code Flow**: Standard OAuth 2.0 for user authentication
- **Token Expiration**: Configurable access token lifetimes
- **Scope-based Access**: Fine-grained permission control

## Testing

Run the test suite:

```bash
composer run test
```

The test suite includes:
- Feature tests for authentication flows
- Unit tests for user and account management
- Integration tests for API endpoints
- Application filtering and access control tests

## Deployment

### Production Requirements

- PHP 8.4+
- MySQL/PostgreSQL
- Redis
- SSL/TLS certificates
- Environment-specific configuration

### Deployment Checklist

1. **Environment Configuration**
   - Set `APP_ENV=production`
   - Configure production database
   - Set up Redis for queues and caching
   - Configure mail and SMS services

2. **OAuth Setup**
   - Generate production OAuth keys
   - Configure client applications
   - Set up redirect URIs

3. **Asset Compilation**
   ```bash
   npm run build
   ```

4. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

5. **Queue Configuration**
   - Set up queue workers
   - Configure job processing

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## Support

For technical support and questions:
- Create an issue in the repository
- Contact the development team
- Check the OvonOne SSO Laravel SDK documentation

## License

This project is proprietary software owned by Ovon Group.
