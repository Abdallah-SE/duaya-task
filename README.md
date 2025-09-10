# User Activity Logs & Inactivity Monitoring System

A comprehensive Laravel application for tracking user activities and monitoring inactivity with automatic logout and penalty system.

## 🎯 Project Overview

This system provides complete user activity monitoring with:
- **Activity Logging**: Track all CRUD operations and user actions
- **Inactivity Monitoring**: Real-time idle state detection with configurable timeouts
- **Penalty System**: Automatic penalties for excessive inactivity
- **Role-Based Access**: Admin and employee roles with different permissions
- **Configurable Settings**: Customizable idle timeouts and monitoring preferences

## ✨ Features

### Activity Logs
- ✅ Log all CRUD operations (Create, Read, Update, Delete)
- ✅ Track login/logout events
- ✅ Monitor important actions (Approve/Reject, Upload, Download)
- ✅ Store device/browser information and IP addresses
- ✅ Polymorphic relationships for flexible subject tracking

### Inactivity Tracking
- ✅ Real-time mouse/keyboard activity monitoring
- ✅ Configurable idle timeout (default: 5 seconds)
- ✅ Progressive warning system (Alert → Warning → Auto Logout)
- ✅ Session-based idle state tracking
- ✅ Automatic penalty application

### Penalty System
- ✅ Track penalties with detailed reasons
- ✅ Count-based penalty accumulation
- ✅ Date-based penalty tracking
- ✅ User-specific penalty statistics

### Configurable Settings
- ✅ Per-user idle timeout configuration
- ✅ Enable/disable monitoring per user
- ✅ Role-based access control
- ✅ Admin dashboard for settings management

## 🏗️ Technical Architecture

### Backend (Laravel)
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Sanctum (API tokens)
- **Authorization**: Spatie Laravel Permission
- **Database**: MySQL with optimized indexes
- **Architecture**: MVC with Service Layer pattern

### Frontend
- **Framework**: Vue.js with Inertia.js
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **State Management**: Inertia.js shared state

### Database Schema
- **activity_logs**: User activity tracking
- **idle_sessions**: Idle state monitoring
- **penalties**: Penalty system tracking
- **idle_settings**: User-specific configuration
- **users**: User management with roles

## 📊 Database Design

### Core Tables
```sql
-- Activity Logs
activity_logs (id, user_id, action, subject_type, subject_id, ip_address, device, browser, timestamps)

-- Idle Sessions
idle_sessions (id, user_id, idle_started_at, idle_ended_at, duration_seconds, timestamps)

-- Penalties
penalties (id, user_id, reason, count, date, timestamps)

-- Idle Settings
idle_settings (id, user_id, idle_timeout, idle_monitoring_enabled, max_idle_warnings, timestamps)
```

## 🚀 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Setup
```bash
# Clone the repository
git clone https://github.com/Abdallah-SE/duaya-task.git
cd duaya-task

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## 🔧 Configuration

### Environment Variables
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duaya_task
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### Default Users
- **Admin**: admin@duaya.com / password
- **Employee 1**: employee1@duaya.com / password
- **Employee 2**: employee2@duaya.com / password
- **Employee 3**: employee3@duaya.com / password

## 📝 Usage

### Activity Logging
```php
// Log user activity
ActivityLog::logActivity(
    userId: $userId,
    action: 'create',
    subjectType: 'App\Models\User',
    subjectId: $user->id,
    ipAddress: request()->ip(),
    device: 'Desktop',
    browser: 'Chrome'
);
```

### Idle Monitoring
```php
// Get user idle settings
$settings = IdleSetting::getForUser($userId);

// Check if monitoring is enabled
if ($settings->isMonitoringEnabled()) {
    $timeout = $settings->getTimeoutInMilliseconds();
    // Frontend JavaScript implementation
}
```

### Penalty Management
```php
// Create penalty
Penalty::createPenalty(
    userId: $userId,
    reason: 'Auto logout due to inactivity',
    count: 1
);

// Get user penalty statistics
$totalPenalties = Penalty::getTotalCountForUser($userId);
```

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 📈 Performance

### Database Optimizations
- Strategic indexes on frequently queried columns
- Optimized data types (unsigned integers, proper string lengths)
- Composite indexes for multi-column queries
- Foreign key constraints with cascade deletes

### Query Optimization
- Eloquent scopes for common queries
- Eager loading for relationships
- Database-level constraints and validations

## 🔒 Security

- **Authentication**: Laravel Sanctum for API authentication
- **Authorization**: Spatie Laravel Permission for role-based access
- **Data Validation**: Comprehensive input validation
- **SQL Injection**: Eloquent ORM protection
- **XSS Protection**: Blade template escaping

## 📚 API Endpoints

### Activity Logs
- `GET /api/activities` - List user activities
- `POST /api/activities` - Log new activity

### Idle Monitoring
- `POST /api/idle/check` - Check idle state
- `POST /api/idle/warning` - Record warning
- `POST /api/idle/logout` - Force logout

### Settings
- `GET /api/settings/idle` - Get idle settings
- `PUT /api/settings/idle` - Update idle settings

## 🎨 Frontend Components

### Vue.js Components
- `IdleMonitor.vue` - Real-time idle monitoring
- `ActivityLog.vue` - Activity display component
- `SettingsPanel.vue` - Configuration interface
- `Dashboard.vue` - Admin dashboard

## 📊 Dashboard Features

### Admin Dashboard
- User activity statistics
- Idle session reports
- Penalty tracking
- System configuration
- User management

### Employee Dashboard
- Personal activity log
- Idle session history
- Settings configuration

## 🛠️ Development

### Code Standards
- PSR-12 coding standards
- Laravel best practices
- Type hints and return types
- Comprehensive documentation
- Unit and feature tests

### Git Workflow
- Feature branches
- Pull request reviews
- Conventional commits
- Automated testing

## 📄 License

This project is proprietary software developed for Duaya. All rights reserved.

## 👥 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📞 Support

For support and questions, please contact the development team.

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies.**