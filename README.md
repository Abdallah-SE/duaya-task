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

## 🛠️ Tools & Technologies Used

### Backend Technologies
- **Laravel 11.x** - PHP framework for web applications
- **PHP 8.2+** - Server-side programming language
- **MySQL 8.0+** - Relational database management system
- **Laravel Sanctum** - API authentication system
- **Spatie Laravel Permission** - Role and permission management
- **Laravel Inertia.js** - Modern monolith approach
- **Laravel Events & Listeners** - Event-driven architecture
- **Laravel Middleware** - Request/response filtering
- **Laravel Eloquent ORM** - Database abstraction layer

### Frontend Technologies
- **Vue.js 3** - Progressive JavaScript framework
- **Inertia.js** - Modern monolith with SPA-like experience
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Fast build tool and development server
- **Axios** - HTTP client for API requests
- **JavaScript ES6+** - Modern JavaScript features

### Development Tools
- **Composer** - PHP dependency manager
- **NPM** - Node.js package manager
- **Git** - Version control system
- **Laravel Artisan** - Command-line interface
- **Laravel Migrations** - Database version control
- **Laravel Seeders** - Database seeding
- **Laravel Factories** - Model factories for testing

### Database & Storage
- **MySQL** - Primary database
- **Laravel Migrations** - Database schema management
- **Database Indexing** - Performance optimization
- **Foreign Key Constraints** - Data integrity
- **Polymorphic Relationships** - Flexible data modeling

### Security & Authentication
- **Laravel Sanctum** - API token authentication
- **Spatie Laravel Permission** - Role-based access control
- **CSRF Protection** - Cross-site request forgery protection
- **Input Validation** - Data sanitization and validation
- **SQL Injection Protection** - Eloquent ORM security
- **XSS Protection** - Cross-site scripting prevention

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

### Prerequisites
- PHP 8.2+, Composer, Node.js 18+, MySQL 8.0+

### Quick Setup
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

# Configure database in .env file
DB_DATABASE=duaya_task
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Database setup
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start server
php artisan serve
```

### Access Application
- **Admin**: http://localhost:8000/admin/login
- **Employee**: http://localhost:8000/employee/login

**Default Credentials:**
- Admin: `admin@duaya.com` / `password`
- Employee: `employee1@duaya.com` / `password`

## 📝 Usage Guide

### 🚀 Getting Started

#### 1. Authentication System
The system has **role-based authentication** with separate login flows:

**Admin Login:**
- URL: `http://localhost:8000/admin/login`
- Email: `admin@duaya.com`
- Password: `password`
- Redirects to: `/admin/dashboard`

**Employee Login:**
- URL: `http://localhost:8000/employee/login`
- Email: `employee1@duaya.com` (or employee2, employee3)
- Password: `password`
- Redirects to: `/employee/dashboard`

**General Login:**
- URL: `http://localhost:8000/login`
- Select role (admin/employee) + credentials
- Automatic redirect based on role

#### 2. Admin Dashboard Features
**Main Statistics Cards:**
- **Activity Logs**: Total activities + today's count
- **CRUD Operations**: Create, Read, Update, Delete counts
- **Idle Sessions**: Total sessions + active sessions
- **Penalties**: Total penalties + today's penalties

**Recent Activities Feed:**
- Real-time activity stream with user details
- Color-coded action types (create=green, update=yellow, delete=red)
- User information including department and job title
- IP address and device information

**Penalty System Display:**
- List of recent penalties with user names
- Penalty reasons and counts
- Date stamps for each penalty

**Navigation Options:**
- Dashboard (main view)
- Settings (idle monitoring configuration)

#### 3. Employee Dashboard Features
**Personal Statistics:**
- **Today's Activities**: Your daily activity count
- **Penalties**: Your personal penalty count
- **Idle Sessions**: Your idle session history

**Personal Information Display:**
- Job title and department
- Greeting message based on time of day
- Refresh button for real-time updates

**Navigation Options:**
- Dashboard (personal view)
- Users (manage other users)
- Employees (manage employee records)
- Settings (personal preferences)

#### 4. Sidebar Navigation
**For Admin Users:**
- Dashboard
- Settings (admin settings)
- Logout

**For Employee Users:**
- Dashboard
- Users (user management)
- Employees (employee management)
- Settings (personal settings)
- Logout

**Sidebar Features:**
- User information display
- Role-based navigation
- Mobile-responsive design
- Active page highlighting

### 🔍 How to Use Activity Logging

#### Automatic Activity Logging
The system automatically logs activities through the `LogActivity` middleware:

```php
// All these actions are automatically logged:
- User login/logout (with role selection)
- CRUD operations on users/employees
- Dashboard views and navigation
- Settings changes
- Idle monitoring events
```

#### View Activity Logs
**In Admin Dashboard:**
- **Recent Activities Feed**: Shows last 25 activities with user details
- **Activity Statistics**: Total counts and breakdowns
- **User Information**: Name, email, department, job title
- **Device Details**: IP address, device type, browser

**Activity Display Features:**
- Color-coded action types (create=green, update=yellow, delete=red, login=indigo)
- Time stamps with relative time (e.g., "2h ago", "1d ago")
- User role and department information
- Filtered to show only relevant activities (redundant views removed)

### ⏰ How to Use Idle Monitoring

#### For Users (Automatic)
1. **Login** to the system (admin or employee)
2. **Idle monitoring starts automatically** via the `IdleMonitor` component
3. **Stay active** by moving mouse, typing, scrolling, or touching (mobile)
4. **If you become idle**:
   - After 5 seconds: **Alert popup** appears with countdown
   - Click "I'm Still Here" to dismiss and reset
   - If idle again: **Warning counter** increases (2nd warning)
   - Third time: **Automatic logout** + **Penalty applied**

#### Idle Monitor Component Features
- **Real-time countdown timer** (10 seconds to respond)
- **Progressive warning system** (Alert → Warning → Auto Logout)
- **Visual progress bars** for warning progression
- **Multiple event listeners** (mousemove, keydown, scroll, click, touchstart)
- **Mobile-friendly** touch event support

#### Idle Monitoring Flow
```
User Activity → 5s Idle → Alert Popup (10s countdown) → User Response
                     ↓
                If Still Idle → Warning Counter (2nd) → User Response
                     ↓
                If Still Idle → Auto Logout + Penalty (3rd)
```

### 🎯 How to Use Penalty System

#### Automatic Penalties
- Applied when user reaches 3rd idle warning
- Reason: "Auto logout due to inactivity"
- Count: 1 penalty per occurrence
- Automatically logged in the system

#### View Penalties
**In Admin Dashboard:**
- **Penalty System Section**: Shows recent penalties (last 5)
- **User Information**: Name and penalty details
- **Penalty Details**: Reason, count, and date
- **Statistics**: Total penalties and today's count

**In Employee Dashboard:**
- **Personal Penalties**: Your own penalty count
- **Penalty History**: Your penalty record

### ⚙️ How to Configure Settings

#### Admin Settings (Admin Only)
1. Navigate to **Admin Dashboard** → **Settings**
2. **Global Idle Settings**:
   - Idle timeout (default: 5 seconds)
   - Maximum warnings (default: 3)
3. **Role-Based Settings**:
   - Enable/disable monitoring per role
   - Custom timeouts for different roles
4. **Save Changes**: Apply to all users

#### Employee Settings (Self-Management)
1. Go to **Settings** page (from sidebar)
2. **View Current Settings**: See your idle configuration
3. **Personal Preferences**: View your monitoring status
4. **Request Changes**: Contact admin for modifications

### 📊 How to View Reports

#### Admin Dashboard Reports
**Statistics Cards:**
- **Activity Logs**: Total activities + today's count
- **CRUD Operations**: Create, Read, Update, Delete counts
- **Idle Sessions**: Total sessions + active sessions
- **Penalties**: Total penalties + today's penalties

**Recent Activities Feed:**
- Last 25 activities with user details
- Color-coded action types
- User information (name, department, job title)
- Device and IP information
- Time stamps with relative time

**Penalty System Display:**
- Recent penalties (last 5)
- User names and penalty details
- Penalty reasons and counts
- Date stamps

#### Employee Dashboard Reports
**Personal Statistics:**
- **Today's Activities**: Your daily activity count
- **Penalties**: Your personal penalty count
- **Idle Sessions**: Your idle session history

**Personal Information:**
- Job title and department
- Greeting message based on time
- Refresh button for real-time updates

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