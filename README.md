# Mini Ecommerce - Report

A Laravel-based e-commerce reporting system that provides comprehensive order analytics, revenue tracking, and product performance insights.

## Features

- **User Authentication**: Secure login system with Laravel Breeze
- **Order Management**: Track orders with customer information and order items
- **Reporting Dashboard**: View comprehensive reports including:
  - Total orders and revenue
  - Average order value
  - Top products by sales
  - Detailed order breakdowns
- **Data Export**: Export reports to Excel format
- **Date Filtering**: Filter reports by date range
- **Malaysian States Support**: Customer data includes all 16 Malaysian states and federal territories

## Requirements

- PHP >= 8.2
- Composer
- Node.js and npm
- MySQL
- Laravel 12.0

## Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd mini-ecommerce
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   
   Edit the `.env` file and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mini_ecommerce
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Build Assets**
   ```bash
   npm run build
   ```

8. **Start the Development Server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

## Database Seeding Instructions

The seeder will create sample data including:
- 1 admin user
- 5 product categories (Electronics, Accessories, Home, Books, Toys)
- 20 products with random prices
- 15 customers from various Malaysian states
- 60 orders with order items

**To seed the database:**

```bash
php artisan db:seed
```

Or if you want to refresh the database and reseed:

```bash
php artisan migrate:fresh --seed
```

### Default Admin Credentials

After seeding, you can log in with:

- **Email**: `admin@app.com`
- **Password**: `Password1`

## Project Structure

```
mini-ecommerce/
├── app/
│   ├── Exports/          # Excel export classes
│   ├── Http/
│   │   └── Controllers/   # Application controllers
│   └── Models/            # Eloquent models
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── views/            # Blade templates
│   │   ├── auth/         # Authentication views
│   │   ├── layouts/      # Layout templates
│   │   └── report/       # Report views
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
└── routes/
    ├── web.php           # Web routes
    └── auth.php          # Authentication routes
```

## Key Features Explained

### Authentication
- The root route (`/`) redirects unauthenticated users to the login page
- Authenticated users are redirected to the dashboard
- Protected routes require authentication

### Reporting System
- Access reports at `/report`
- Filter orders by date range
- View summary statistics (total orders, revenue, average order value)
- See top 3 products by quantity sold
- Export data to Excel format

### Data Models
- **Users**: Admin and regular users
- **Customers**: Customer information with Malaysian state data
- **Categories**: Product categories
- **Products**: Product catalog with pricing
- **Orders**: Order records with customer relationships
- **OrderItems**: Individual items within orders

## Assumptions and Notes

1. **Database**: The project uses MySQL by default, but can be configured to use SQLite or other databases supported by Laravel.

2. **Order Dates**: The `order_date` field is automatically set to match the `created_at` timestamp when orders are created via the seeder.

3. **Malaysian States**: The seeder includes all 13 Malaysian states and 3 federal territories:
   - States: Johor, Kedah, Kelantan, Melaka, Negeri Sembilan, Pahang, Perak, Perlis, Pulau Pinang, Sabah, Sarawak, Selangor, Terengganu
   - Federal Territories: Kuala Lumpur, Labuan, Putrajaya

4. **Currency**: All monetary values are displayed in Malaysian Ringgit (RM).

5. **Authentication**: Laravel Breeze is used for authentication, providing a simple, clean authentication scaffolding.

6. **Excel Export**: The project uses the `maatwebsite/excel` package for exporting reports to Excel format.

7. **Styling**: The project uses Bootstrap 5 and Tailwind CSS for styling.

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
The project uses Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

### Building Assets for Production
```bash
npm run build
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues, questions, or contributions, please open an issue on the GitHub repository.
