# Contributing to Duaya Task - User Activity Monitoring

Thank you for your interest in contributing to this project! This document provides guidelines and information for contributors.

## 🎯 Project Overview

This is a Laravel-based user activity monitoring system with:
- Real-time activity logging
- Inactivity monitoring with configurable timeouts
- Penalty system for excessive inactivity
- Role-based access control
- Vue.js frontend with Inertia.js

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Git

### Development Setup
```bash
# Fork and clone the repository
git clone https://github.com/your-username/duaya-task.git
cd duaya-task

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate:fresh --seed

# Start development
php artisan serve
npm run dev
```

## 📋 Development Guidelines

### Code Standards

#### PHP (Laravel)
- Follow PSR-12 coding standards
- Use type hints and return types
- Write comprehensive PHPDoc comments
- Follow Laravel best practices and conventions

#### JavaScript/Vue.js
- Use ES6+ features
- Follow Vue.js style guide
- Use consistent naming conventions
- Write clean, readable code

#### Database
- Use descriptive table and column names
- Add proper indexes for performance
- Use foreign key constraints
- Write migration rollbacks

### Git Workflow

#### Branch Naming
- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Critical fixes
- `refactor/description` - Code refactoring
- `docs/description` - Documentation updates

#### Commit Messages
Use conventional commits format:
```
type(scope): description

[optional body]

[optional footer]
```

Examples:
```
feat(auth): add user registration validation
fix(activity): resolve logging performance issue
docs(readme): update installation instructions
refactor(models): improve type safety
```

#### Pull Request Process
1. Create a feature branch from `main`
2. Make your changes
3. Write/update tests
4. Update documentation if needed
5. Submit a pull request
6. Address review feedback
7. Merge after approval

## 🧪 Testing

### PHP Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ActivityLogTest

# Run with coverage
php artisan test --coverage
```

### Frontend Tests
```bash
# Run Vue.js tests (when implemented)
npm run test

# Run linting
npm run lint
```

### Test Requirements
- Write unit tests for new features
- Write integration tests for API endpoints
- Maintain test coverage above 80%
- Test edge cases and error conditions

## 📝 Documentation

### Code Documentation
- Write PHPDoc comments for all public methods
- Document complex business logic
- Include usage examples in comments
- Update README.md for new features

### API Documentation
- Document all API endpoints
- Include request/response examples
- Document error codes and messages
- Update API documentation for changes

## 🏗️ Architecture Guidelines

### Backend (Laravel)
- Use Service classes for complex business logic
- Implement Repository pattern for data access
- Use Events and Listeners for activity logging
- Follow SOLID principles

### Frontend (Vue.js)
- Use Composition API for new components
- Implement proper state management
- Use TypeScript for type safety (when applicable)
- Follow component composition patterns

### Database
- Use migrations for all schema changes
- Add proper indexes for performance
- Use foreign key constraints
- Write seeders for test data

## 🐛 Bug Reports

When reporting bugs, please include:
- Clear description of the issue
- Steps to reproduce
- Expected vs actual behavior
- Environment details (OS, PHP version, etc.)
- Screenshots or error logs if applicable

## 💡 Feature Requests

When requesting features:
- Describe the use case
- Explain the expected behavior
- Consider implementation complexity
- Discuss with maintainers first

## 🔒 Security

- Never commit sensitive information
- Use environment variables for configuration
- Validate all user inputs
- Follow Laravel security best practices
- Report security issues privately

## 📊 Performance

- Optimize database queries
- Use eager loading for relationships
- Implement proper caching strategies
- Monitor application performance
- Profile code for bottlenecks

## 🎨 UI/UX Guidelines

- Follow consistent design patterns
- Use Tailwind CSS classes
- Ensure responsive design
- Test on different screen sizes
- Maintain accessibility standards

## 📋 Code Review Checklist

### Before Submitting
- [ ] Code follows project standards
- [ ] Tests are written and passing
- [ ] Documentation is updated
- [ ] No sensitive data in commits
- [ ] Performance considerations addressed

### During Review
- [ ] Code is readable and maintainable
- [ ] Logic is correct and efficient
- [ ] Error handling is proper
- [ ] Security considerations addressed
- [ ] Tests cover the changes

## 🚀 Release Process

1. Update version numbers
2. Update CHANGELOG.md
3. Create release notes
4. Tag the release
5. Deploy to production
6. Monitor for issues

## 📞 Getting Help

- Create an issue for questions
- Join the development discussion
- Contact maintainers directly
- Check existing documentation

## 📄 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

**Thank you for contributing! 🎉**
