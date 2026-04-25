# Admiral APIs - Email Validation Platform

A powerful email validation API that checks email addresses in real-time using syntax validation, MX records, and SMTP verification. Built with Laravel.

## Features

- **Single Email Validation** - Validate one email at a time
- **Bulk Email Validation** - Process thousands of emails via CSV/Excel upload
- **Real-time Verification** - SMTP, MX, and syntax checks
- **Detailed Results** - Score, validity status, disposable detection, and more
- **RESTful API** - Simple JSON API with API key authentication

## Tech Stack

- **Framework**: Laravel 13
- **Database**: SQLite (development) / PostgreSQL (production)
- **PHP**: 8.3+
- **Frontend**: Blade templates with custom dark theme

## Installation

```bash
# Clone the repository
git clone https://github.com/your-repo/admiral-apis.git
cd admiral-apis

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

## API Endpoints

### Single Email Validation

```http
POST /api/validate-email
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "valid": true,
    "email": "user@example.com",
    "score": 0.95,
    "mx": true,
    "smtp": true,
    "free": false,
    "disposable": false,
    "catch_all": false,
    "syntax": true
}
```

### Bulk Email Validation

```http
POST /api/bulk-validate-email
Authorization: Bearer YOUR_API_KEY

# Option 1: JSON array
{
    "emails": ["a@test.com", "b@test.com"]
}

# Option 2: File upload (multipart/form-data)
# Field: file
# Formats: .csv, .xlsx, .xls
```

**Response:**
```json
{
    "job_id": 1,
    "total": 2,
    "status": "pending",
    "message": "Bulk validation job queued successfully."
}
```

### Check Job Status

```http
GET /api/bulk-jobs/{id}
Authorization: Bearer YOUR_API_KEY
```

## Environment Variables

```env
APP_NAME="Admiral APIs"
APP_URL=http://localhost:8000

# Database (SQLite for dev, PostgreSQL for prod)
DB_CONNECTION=sqlite
# DB_CONNECTION=pgsql
# DB_HOST=localhost
# DB_PORT=5432
# DB_DATABASE=your_database
# DB_USERNAME=your_user
# DB_PASSWORD=your_password

# Optional: External API for email validation fallback
MXCHECK_KEY=your_mxcheck_api_key
APIXIES_KEY=your_apixies_api_key
```

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── APIController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EmailValidationController.php
│   │   │   └── UsageController.php
│   │   └── Services/
│   │       └── EmailValidationService.php
│   ├── Jobs/
│   │   └── ProcessBulkEmailValidation.php
│   ├── Models/
│   │   ├── BulkJob.php
│   │   ├── Domain.php
│   │   ├── Email.php
│   │   ├── EmailSignal.php
│   │   └── Validation.php
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── layout/
│       │   └── app.blade.php
│       └── pages/
│           ├── dashboard.blade.php
│           ├── docs/
│           │   └── index.blade.php
│           ├── home.blade.php
│           ├── usage.blade.php
│           └── bulk-validation.blade.php
├── routes/
│   ├── api.php
│   └── web.php
└── database/
    └── database.sqlite
```

## Dashboard Routes

| Route | Description |
|-------|-------------|
| `/dashboard` | Overview with stats and jobs |
| `/usage` | Detailed validation history with filters |
| `/bulk-validation` | Bulk jobs management |
| `/docs` | API documentation |
| `/login` | User login |
| `/register` | User registration |

## API Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 202 | Job queued (bulk) |
| 400 | Bad request / Invalid email |
| 401 | Unauthorized (invalid API key) |
| 402 | Insufficient credits |
| 500 | Server error |

## Validation Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `valid` | boolean | Overall validation result |
| `email` | string | The validated email |
| `score` | float | Confidence score (0-1) |
| `mx` | boolean | Has valid MX records |
| `smtp` | boolean | SMTP verification passed |
| `free` | boolean | Free email provider (gmail, yahoo, etc.) |
| `disposable` | boolean | Disposable email detected |
| `catch_all` | boolean | Domain has catch-all |
| `syntax` | boolean | RFC syntax valid |
| `source` | string | Validation source (internal/external_api) |

## License

MIT License - feel free to use for personal or commercial projects.