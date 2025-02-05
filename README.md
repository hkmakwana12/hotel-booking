# Hotel Booking System

## Introduction

This is a hotel booking system built with Laravel and Filament. It provides an admin panel for managing hotel branches, room categories, and individual rooms.

## Features

- Admin panel for hotel management
- Branch management
- Room category management
- Room management
- User-friendly interface with Filament

## Requirements

- PHP 8.1+
- Composer
- Node.js and npm

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/hkmakwana12/hotel-booking.git
   ```

2. Navigate to the project directory:
   ```bash
   cd hotel-booking
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Install JavaScript dependencies:
   ```bash
   npm install
   ```

5. Copy the `.env.example` file to `.env` and configure your environment variables:
   ```bash
   cp .env.example .env
   ```

6. Generate an application key:
   ```bash
   php artisan key:generate
   ```

7. Run database migrations:
   ```bash
   php artisan migrate --seed
   ```

8. Build assets:
   ```bash
   npm run build
   ```

## Usage

1. Start the development server:
   ```bash
   php artisan serve
   ```

2. Access the admin panel at `http://localhost:8000/admin`

## Project Structure

The main components of the project are:

- `app/Filament/Resources`: Contains Filament resource classes for Branch, Category, and Room
- `app/Models`: Contains Eloquent models for Branch, Category, Room, and User
- `app/Providers/Filament`: Contains the AdminPanelProvider for Filament configuration

## Development

To run the development server and watch for asset changes:

```bash
npm run dev
```

## Testing

Run the tests with:

```bash
php artisan test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
