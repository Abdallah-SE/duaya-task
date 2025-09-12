# User Activity Logs & Inactivity Monitoring System

A Laravel application that tracks user activities and monitors inactivity with automatic logout and penalty system.

## 🎯 Objective

Track all user activities in the system (especially employees), log CRUD operations, and monitor user interaction with progressive warnings leading to automatic logout and penalties.

## ✨ Features

### Activity Logs
- ✅ Log all CRUD operations (Create, Read, Update, Delete)
- ✅ Track login/logout events
- ✅ Monitor important actions (Approve/Reject, Upload, Download)
- ✅ Store user_id, action, model/table, record_id, timestamp, ip_address, device/browser info

### Inactivity Tracking
- ✅ Monitor user activity (mouse movement, keyboard strokes)
- ✅ 5 seconds idle → Alert popup
- ✅ Second idle → Warning counter
- ✅ Third idle → Auto Logout + Penalty
- ✅ Log number of idle sessions per user

### Penalty System
- ✅ Track penalties in dedicated table
- ✅ Fields: user_id, reason, count, date
- ✅ Display in admin dashboard

### Configurable Settings
- ✅ Admin can configure idle timeout from dashboard
- ✅ Enable/disable idle monitoring per user role

## 🏗️ Technical Implementation

### Backend (Laravel)
- **Middleware**: `LogActivity` middleware for CRUD operations
- **Events/Listeners**: Event-driven activity logging
- **Database Tables**: `activity_logs`, `penalties`, `idle_sessions`, `idle_settings`, `role_settings`
- **Models**: ActivityLog, Penalty, IdleSession, IdleSetting, RoleSetting

### Frontend (Vue.js)
- **IdleMonitor Component**: Real-time idle monitoring with Vue.js
- **Event Listeners**: mousemove, keydown, scroll, click, touchstart
- **Modal System**: Alert and warning popups with countdown
- **Auto Logout**: API endpoint call when threshold reached

## 📊 Database Schema

```sql
-- Activity Logs
CREATE TABLE activity_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    device VARCHAR(100) NULL,
    browser VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_action_created (action, created_at)
);

-- Penalties
CREATE TABLE penalties (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    reason VARCHAR(255) NULL,
    count INT UNSIGNED DEFAULT 1,
    date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date)
);

-- Idle Sessions
CREATE TABLE idle_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    idle_started_at TIMESTAMP NOT NULL,
    idle_ended_at TIMESTAMP NULL,
    duration_seconds INT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);

-- Idle Settings (Global)
CREATE TABLE idle_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    idle_timeout TINYINT UNSIGNED DEFAULT 5,
    max_idle_warnings TINYINT UNSIGNED DEFAULT 3,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Role Settings
CREATE TABLE role_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT NOT NULL,
    idle_monitoring_enabled BOOLEAN DEFAULT TRUE,
    idle_timeout TINYINT UNSIGNED DEFAULT 5,
    max_idle_warnings TINYINT UNSIGNED DEFAULT 3,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

## 🚀 Installation

```bash
# Clone repository
git clone https://github.com/Abdallah-SE/duaya-task.git
cd duaya-task

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start server
php artisan serve
```

## 📝 Usage Guide

### 🚀 Getting Started

#### 1. Login to the System
```bash
# Access the application
http://localhost:8000

# Login as Admin
Email: admin@duaya.com
Password: password

# Login as Employee
Email: employee1@duaya.com
Password: password
```

#### 2. Admin Dashboard Overview
- **Activity Statistics**: View total activities, CRUD operations, login/logout events
- **Idle Monitoring**: See idle sessions, warnings, and penalties
- **User Management**: Manage users and employees
- **Settings**: Configure idle timeouts and monitoring preferences

#### 3. Employee Dashboard
- **Personal Activity Log**: View your own activities
- **Idle Session History**: Track your idle sessions
- **Settings**: Configure personal idle preferences

### 🔍 How to Use Activity Logging

#### Automatic Activity Logging
The system automatically logs activities through middleware:

```php
// All these actions are automatically logged:
- User login/logout
- CRUD operations on users/employees
- Dashboard views
- Settings changes
- File uploads/downloads
```

#### Manual Activity Logging
```php
// In your controllers, log specific activities
ActivityLog::logActivity(
    userId: auth()->id(),
    action: 'approve_request',
    subjectType: 'App\Models\Request',
    subjectId: $request->id,
    ipAddress: request()->ip(),
    device: 'Desktop',
    browser: 'Chrome'
);
```

#### View Activity Logs
1. Go to **Activities** page
2. Filter by user, action type, or date range
3. View detailed activity information including IP, device, and browser

### ⏰ How to Use Idle Monitoring

#### For Users (Automatic)
1. **Login** to the system
2. **Idle monitoring starts automatically** when you're logged in
3. **Stay active** by moving mouse or typing
4. **If you become idle**:
   - After 5 seconds: **Alert popup** appears
   - Click "I'm Still Here" to dismiss
   - If idle again: **Warning counter** increases
   - Third time: **Automatic logout** + **Penalty applied**

#### For Admins (Configuration)
1. Go to **Admin Dashboard** → **Settings**
2. **Configure Global Settings**:
   - Set idle timeout (default: 5 seconds)
   - Set maximum warnings (default: 3)
3. **Configure Role Settings**:
   - Enable/disable monitoring per role
   - Set different timeouts for different roles

#### Idle Monitoring Flow
```
User Activity → 5s Idle → Alert Popup → User Response
                     ↓
                If Still Idle → Warning Counter → User Response
                     ↓
                If Still Idle → Auto Logout + Penalty
```

### 🎯 How to Use Penalty System

#### Automatic Penalties
- Applied when user reaches 3rd idle warning
- Reason: "Auto logout due to inactivity"
- Count: 1 penalty per occurrence

#### Manual Penalties (Admin Only)
1. Go to **Admin Dashboard** → **Activities**
2. Click **Apply Penalty** for specific user
3. Enter reason and penalty count
4. Penalty is recorded in the system

#### View Penalties
- **Admin Dashboard**: See all penalties across all users
- **User Profile**: See individual user's penalty history
- **Statistics**: View penalty trends and counts

### ⚙️ How to Configure Settings

#### Global Settings (Admin Only)
1. Navigate to **Admin Dashboard** → **Settings**
2. **Idle Timeout**: Set default idle time (5-60 seconds)
3. **Max Warnings**: Set warning threshold (1-10 warnings)
4. **Save Changes**: Apply to all users

#### Role-Based Settings (Admin Only)
1. Go to **Settings** → **Role Settings**
2. **Select Role**: Choose admin or employee
3. **Enable/Disable**: Turn monitoring on/off per role
4. **Custom Timeout**: Set different timeouts per role
5. **Save**: Apply role-specific settings

#### User Settings (Self-Management)
1. Go to **Settings** page
2. **View Current Settings**: See your idle configuration
3. **Request Changes**: Contact admin for modifications

### 📊 How to View Reports

#### Admin Dashboard Reports
1. **Activity Statistics**:
   - Total activities today/this week
   - CRUD operations breakdown
   - Login/logout events
   - Most active users

2. **Idle Monitoring Reports**:
   - Total idle sessions
   - Average idle time per user
   - Warning statistics
   - Penalty counts

3. **User Activity Breakdown**:
   - Activities per employee
   - Recent activity feed
   - Device/browser statistics

#### Employee Reports
1. **Personal Activity Log**: Your own activities
2. **Idle Session History**: Your idle patterns
3. **Penalty History**: Your penalty record

### 🔧 Developer Usage

#### Adding Activity Logging to New Features
```php
// In your controller
public function store(Request $request)
{
    // Your business logic
    $user = User::create($request->validated());
    
    // Activity is automatically logged via middleware
    // Or manually log specific actions:
    ActivityLog::logActivity(
        userId: auth()->id(),
        action: 'create_user',
        subjectType: 'App\Models\User',
        subjectId: $user->id,
        ipAddress: request()->ip()
    );
    
    return redirect()->back();
}
```

#### Adding Idle Monitoring to New Pages
```vue
<!-- In your Vue component -->
<template>
    <div>
        <!-- Your page content -->
        
        <!-- Add IdleMonitor component -->
        <IdleMonitor 
            :user-id="user.id"
            :initial-settings="idleSettings"
            :is-idle-monitoring-enabled="isMonitoringEnabled"
        />
    </div>
</template>

<script setup>
import IdleMonitor from '@/Components/IdleMonitor.vue'

const props = defineProps({
    user: Object,
    idleSettings: Object,
    isMonitoringEnabled: Boolean
})
</script>
```

### 🚨 Troubleshooting

#### Idle Monitoring Not Working
1. Check if monitoring is enabled for your role
2. Verify browser allows JavaScript
3. Check console for JavaScript errors
4. Ensure you're not in an iframe

#### Activities Not Being Logged
1. Verify `LogActivity` middleware is applied to routes
2. Check database connection
3. Verify user authentication
4. Check application logs

#### Penalties Not Applied
1. Verify penalty system is enabled
2. Check idle monitoring settings
3. Verify warning threshold is reached
4. Check database for penalty records

### 📱 Mobile Usage

The system works on mobile devices with touch events:
- **Touch events** reset idle timer
- **Responsive design** for mobile dashboards
- **Touch-friendly** warning modals
- **Mobile-optimized** activity logs

## 🔧 Configuration

### Environment Variables
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duaya_task
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Default Users
- **Admin**: admin@duaya.com / password
- **Employee 1**: employee1@duaya.com / password
- **Employee 2**: employee2@duaya.com / password
- **Employee 3**: employee3@duaya.com / password

## 📚 API Endpoints

### Idle Monitoring
- `POST /api/idle-monitoring/start-session` - Start idle session
- `POST /api/idle-monitoring/handle-warning` - Handle idle warning
- `POST /api/idle-monitoring/end-session` - End idle session
- `GET /api/idle-monitoring/settings` - Get idle settings
- `POST /api/idle-monitoring/update-settings` - Update settings (Admin)

### Activity Logs
- `GET /activities` - List activities
- `GET /activities/user/{userId}` - Get user activities
- `POST /activities/penalty` - Apply penalty

### Settings
- `GET /settings/api` - Get user settings
- `PUT /settings` - Update user settings
- `GET /api/idle-monitoring/role-settings` - Get role settings (Admin)

## ✅ Acceptance Criteria

- ✅ Every CRUD operation is logged in activity_logs
- ✅ First inactivity → Alert popup
- ✅ Second inactivity → Warning counter  
- ✅ Third inactivity → Auto Logout + Penalty entry
- ✅ Idle timeout can be configured in settings
- ✅ Dashboard report shows:
  - Number of actions per employee
  - Number of idle sessions
  - Number of penalties

## 🎨 Frontend Components

- `IdleMonitor.vue` - Real-time idle monitoring
- `ActivityLog.vue` - Activity display
- `Dashboard.vue` - Admin dashboard with statistics
- `SettingsPanel.vue` - Configuration interface

## 🔒 Security

- Laravel Sanctum for API authentication
- Spatie Laravel Permission for role-based access
- CSRF protection on all forms
- Input validation and sanitization
- SQL injection protection via Eloquent ORM

## 📄 License

This project is proprietary software developed for Duaya. All rights reserved.

---

**Built with Laravel, Vue.js, and modern web technologies.**