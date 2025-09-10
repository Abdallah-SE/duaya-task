# Setup Guide - User Activity Monitoring System

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Git

### 1. Clone and Install
```bash
# Clone the repository
git clone https://github.com/Abdallah-SE/duaya-task.git
cd duaya-task

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duaya_task
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Database Setup
```bash
# Run migrations and seeders
php artisan migrate:fresh --seed

# This will create:
# - 4 users (1 admin, 3 employees)
# - Roles and permissions
# - Sample activity logs
# - Idle sessions data
# - Penalty records
```

### 4. Frontend Setup
```bash
# Build assets for production
npm run build

# Or run development server
npm run dev
```

### 5. Start Development
```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite (if using dev mode)
npm run dev
```

## 🔧 Configuration

### Default Users
- **Admin**: admin@duaya.com / password
- **Employee 1**: employee1@duaya.com / password
- **Employee 2**: employee2@duaya.com / password
- **Employee 3**: employee3@duaya.com / password

### Environment Variables
```env
# Application
APP_NAME="Duaya Task - User Activity Monitoring"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duaya_task
DB_USERNAME=root
DB_PASSWORD=

# Sanctum (for API authentication)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SANCTUM_GUARD=web

# Idle Monitoring Settings
IDLE_DEFAULT_TIMEOUT=5
IDLE_DEFAULT_WARNINGS=2
IDLE_MONITORING_ENABLED=true
```

## 📁 Project Structure

```
duaya-task/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── IdleSession.php
│   │   ├── IdleSetting.php
│   │   ├── Penalty.php
│   │   └── User.php
│   └── Providers/
├── database/
│   ├── migrations/
│   │   ├── create_activity_logs_table.php
│   │   ├── create_idle_sessions_table.php
│   │   ├── create_penalties_table.php
│   │   └── create_idle_settings_table.php
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   └── Pages/
│   └── views/
├── routes/
└── public/
```

## 🗄️ Database Schema

### Core Tables
- **activity_logs**: User activity tracking
- **idle_sessions**: Idle state monitoring  
- **penalties**: Penalty system tracking
- **idle_settings**: User-specific configuration
- **users**: User management with roles

### Key Features
- Polymorphic relationships for flexible activity logging
- Optimized indexes for performance
- Foreign key constraints with cascade deletes
- Proper data types and constraints

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=ActivityLogTest
```

## 🚀 Deployment

### Production Build
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

## 🔍 Troubleshooting

### Common Issues

1. **Permission denied errors**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

2. **Composer memory limit**
   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install
   ```

3. **Node modules issues**
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

4. **Database connection issues**
   - Check MySQL service is running
   - Verify database credentials in .env
   - Ensure database exists

### Logs
- Laravel logs: `storage/logs/laravel.log`
- Nginx/Apache logs: Check your web server configuration

## 📚 Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue.js Documentation](https://vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

---

**Happy Coding! 🚀**
