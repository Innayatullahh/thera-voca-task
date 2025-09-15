# Laravel Actor Submission System

A Laravel application that allows users to submit actor information through a form, processes the data using OpenAI API to extract detailed actor information, and displays all submissions in a paginated table.

## Features

- **Actor Information Form**: Submit email and actor description
- **AI-Powered Data Extraction**: Uses OpenAI API to extract:
  - First Name
  - Last Name
  - Address
  - Height
  - Weight
  - Gender
  - Age
- **Comprehensive Validation**: Ensures all required fields are present
- **Unique Constraints**: Email and description must be unique
- **Paginated Listing**: View all submissions in a clean table format
- **API Endpoint**: RESTful API for prompt validation
- **Comprehensive Testing**: Both unit and feature tests included

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 + Inertia.js
- **Styling**: Tailwind CSS 4
- **Database**: MySQL/SQLite
- **AI Integration**: OpenAI GPT-4o-mini
- **Testing**: PHPUnit
- **Development**: Laravel Herd

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- NPM
- OpenAI API Key
- Laravel Herd (recommended) or similar local development environment

## Installation

1. **Clone the repository**
   ```bash
   git clone git@github.com:Innayatullahh/thera-voca-task.git 
   cd thera-voca-task
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

5. **Configure environment variables**
   ```env
   # Database
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database.sqlite

   # OpenAI API
   OPENAI_API_KEY=your_openai_api_key_here
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start development server**
   ```bash
   # use Laravel's built-in server
   php artisan serve
   
   # For development with hot reloading
   npm run dev
   ```

## Usage

### Web Interface

1. **Submit Actor Information**
   - Navigate to `/actors/create`
   - Fill in email and actor description
   - Include first name, last name, address, height, weight, gender, and age in the description
   - Submit the form

2. **View Submissions**
   - Navigate to `/actors` to see all submissions
   - View paginated table with actor details
   - See first name, address, gender, and height columns

### API Endpoints

- **GET** `/api/actors/prompt-validation` - Returns validation prompt text

## 🧪 Testing

Run the comprehensive test suite:

```bash
# Run all tests
php artisan test

```

### Test Coverage

- **Unit Tests**: 16 tests covering ActorExtractionService
- **Feature Tests**: 25+ tests covering form submission, validation, and listing
- **API Tests**: Endpoint validation tests

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ActorController.php          # Main actor controller
│   │   │   └── Api/
│   │   │       └── ActorsPromptController.php # API controller
│   │   └── Requests/
│   │       └── StoreActorRequest.php        # Form validation
│   ├── Models/
│   │   └── Actor.php                        # Actor model
│   └── Services/
│       └── ActorExtractionService.php       # OpenAI integration
├── database/
│   ├── factories/
│   │   └── ActorFactory.php                 # Test data factory
│   └── migrations/
│       └── *_create_actors_table.php        # Database schema
├── resources/
│   ├── js/
│   │   └── pages/
│   │       └── Actors/
│   │           ├── Create.vue               # Submission form
│   │           └── Index.vue                # Listings table
│   └── views/
│       └── app.blade.php                    # Main layout
├── routes/
│   ├── web.php                              # Web routes
│   └── api.php                              # API routes
└── tests/
    ├── Feature/
    │   ├── ActorSubmissionTest.php          # Form submission tests
    │   ├── ActorsListingTest.php            # Listing page tests
    │   └── ActorsApiTest.php                # API endpoint tests
    └── Unit/
        └── ActorExtractionServiceTest.php   # Service layer tests
```

## Key Components

### ActorExtractionService

The core service that handles OpenAI API integration:

- Extracts actor information from natural language descriptions
- Uses knowledge-based extraction for famous actors
- Validates that all 7 required fields are present
- Handles API failures gracefully

### Form Validation

- **Email**: Required, valid email format, unique
- **Description**: Required, minimum 10 characters, unique
- **AI Validation**: All 7 fields must be extractable from description

### Database Schema

```sql
CREATE TABLE actors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    description TEXT UNIQUE NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    height VARCHAR(255),
    weight VARCHAR(255),
    gender VARCHAR(255),
    age INTEGER,
    raw_ai_response JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## UI/UX Features

- **Responsive Design**: Works on desktop and mobile
- **Real-time Validation**: Immediate feedback on form errors
- **Loading States**: Visual feedback during AI processing
- **Error Handling**: User-friendly error messages
- **Success Feedback**: Confirmation messages and redirects

## 🔒 Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Eloquent ORM prevents SQL injection
- **XSS Protection**: Vue.js templates escape output by default

## Deployment

### Production Setup

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

2. **Optimize for Production**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

3. **Set Proper Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Troubleshooting

### Common Issues

1. **OpenAI API Errors**
   - Ensure your API key is valid and has sufficient credits
   - Check network connectivity

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Ensure database server is running

3. **Asset Compilation Issues**
   - Clear npm cache: `npm cache clean --force`
   - Delete `node_modules` and reinstall: `rm -rf node_modules && npm install`

4. **Permission Issues**
   - Ensure storage and cache directories are writable
   - Check file ownership and permissions

### Getting Help

- Check the Laravel documentation: https://laravel.com/docs
- Review test files for usage examples
- Open an issue for bugs or feature requests

## Performance

- **Database Queries**: Optimized with proper indexing
- **Caching**: Route and config caching in production
- **Asset Optimization**: Minified CSS and JS in production
- **API Rate Limiting**: Configured for OpenAI API limits

---

