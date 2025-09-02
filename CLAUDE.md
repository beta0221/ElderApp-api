# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ElderApp API - Laravel 5.8 Application

This is a Laravel 5.8 API backend for an elder care application with Vue.js frontend components and JWT authentication.

### Development Commands

**Backend (PHP/Laravel):**
```bash
# Install dependencies
composer install

# Start development server
php artisan serve

# Run database migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear application cache
php artisan cache:clear

# Generate application key
php artisan key:generate

# Run tests
./vendor/bin/phpunit
# or
php artisan test
```

**Frontend (Node.js/Vue.js):**
```bash
# Install dependencies
npm install

# Development build with watch
npm run dev
npm run watch

# Production build
npm run production

# Development with hot reload
npm run hot
```

### Architecture Overview

**Core Structure:**
- **Laravel 5.8** backend API with **JWT authentication** (tymon/jwt-auth)
- **Vue.js 2.6** frontend with **Vuetify 1.5** UI framework
- **MySQL** database with Eloquent ORM
- **Redis** support for caching and sessions
- **Laravel Mix** for asset compilation

**Key Models & Features:**
- User management with role-based permissions (admin, member roles)
- Multi-level member hierarchy with inviter relationships
- Event management system with certificates and frequency tracking
- E-commerce functionality (products, orders, cart, inventory)
- Clinic and location management
- Insurance integration
- LINE Bot webhook integration
- Image upload and processing with Intervention Image
- Excel export capabilities

**API Architecture:**
- RESTful API endpoints in `routes/api.php`
- JWT middleware for authentication (`middleware: ['JWT']`)
- Role-based access control (`middleware: ['admin']`, `role:accountant`)
- Organized controller structure with dedicated controllers for each feature

**Database:**
- Comprehensive migration system
- Models with established relationships
- Transaction logging for payment history
- Event-user association tracking

**Frontend Integration:**
- Vue Router for SPA navigation
- CKEditor for rich text editing
- FontAwesome icons
- Bootstrap 4 styling with Vuetify components

### Key Configuration Files

- `config/jwt.php` - JWT authentication settings
- `config/database.php` - Database connections
- `phpunit.xml` - Test configuration
- `.env.example` - Environment variables template
- `webpack.mix.js` - Asset compilation settings

### Testing

Tests are configured with PHPUnit and located in:
- `tests/Unit/` - Unit tests
- `tests/Feature/` - Feature/integration tests

### Important Notes

- Uses PHP 7.1+ requirements
- JWT tokens for API authentication
- Multi-language support (Chinese comments indicate Taiwan/Chinese market)
- Image processing and upload capabilities
- Excel export functionality for data management
- Role-based permission system with granular access control