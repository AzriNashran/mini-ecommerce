## Requirements

- PHP >= 8.2
- Composer
- Node.js and npm
- MySQL
- Laravel 12.0

## Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/AzriNashran/mini-ecommerce.git
   cd mini-ecommerce
   ```

2. **Install PHP dependencies**
   ```bash
   composer install or php composer.phar
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

## Database Seeding

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

## Key Features

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

#### Model Relationships
- **Customer** `hasMany` **Orders**
- **Order** `belongsTo` **Customer**
- **Order** `hasMany` **OrderItems**
- **OrderItem** `belongsTo` **Product**
- **Product** `belongsTo` **Category**

## Assumptions and Notes

1. **Database**: The project uses MySQL by default, but can be configured to use SQLite or other databases supported by Laravel.

2. **Order Dates**: The `order_date` field is automatically set to match the `created_at` timestamp when orders are created via the seeder.

3. **Authentication**: Laravel Breeze is used for authentication.

4. **Excel Export**: The project uses the `maatwebsite/excel` package for exporting reports to Excel format.

5. **Styling**: The project uses Bootstrap 5 and Tailwind CSS for styling.

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
