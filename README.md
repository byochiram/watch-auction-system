# Tempus Auctions

Tempus Auctions is a web-based watch auction system built with Laravel.

This project was developed as an undergraduate thesis project by **Rosidah Rahmati** for the case study of **PT. Tempus Collective Indonesia**. The system was designed using the **ICONIX Process** method and focuses on online watch auction management, bidding, winner selection, invoice generation, payment processing, shipping cost calculation, and email notification.

## Live Demo

Production URL:

```txt
https://auctions.tempuscollective.com
```

> Payment is configured using Duitku Sandbox for demo and testing purposes.

## Project Overview

Tempus Auctions supports the end-to-end flow of an online watch auction system.

Main flow:

```txt
User registers
→ User verifies email
→ User browses active auctions
→ User places a bid
→ Auction ends
→ System determines the winner
→ Invoice is generated
→ Winner receives email notification
→ User completes payment
→ Admin processes shipment
→ User confirms received order
```

The system provides public pages for guests, bidder features for registered users, and an admin dashboard for managing products, auction lots, users, bids, invoices, and shipment information.

## Academic Context

This project was developed as an academic final project / undergraduate thesis.

```txt
Title      : Rancang Bangun Aplikasi Sistem Lelang Jam Tangan Berbasis Web Menggunakan Metode ICONIX Process
Case Study : PT. Tempus Collective Indonesia
Developer  : Rosidah Rahmati
Method     : ICONIX Process
Platform   : Web Application
Framework  : Laravel
```

## Features

### Public / Guest

- View auction catalog
- Search, filter, and sort auction lots
- View auction detail
- View guide and rules page
- View about page
- Register account
- Login
- Reset password

### Bidder

- Email verification
- Manage profile
- Add or remove auction lots from watchlist
- Place bids on active auction lots
- View real-time highest bid information
- View auction countdown
- View bidding history
- View won or lost auction status
- Checkout invoice after winning an auction
- Manage shipping address
- Calculate shipping cost
- Complete payment through Duitku Sandbox
- Confirm received shipment

### Admin / Superadmin

- View admin dashboard
- Manage products
- Manage product images
- Manage auction lots
- Schedule auction lots
- Edit scheduled auction lots
- Cancel active auction lots
- Monitor bids
- Manage users
- Suspend or activate users
- Resend email verification
- Manage invoices and transactions
- Input or update shipment tracking number
- Monitor payment and shipment status

### Auction

- Auction start and end time
- Countdown timer
- Highest bid tracking
- Bid validation
- Bid increment validation
- Anti-sniping support
- Automatic winner selection
- Invoice generation for auction winner
- Auction closing support using Laravel Scheduler

### Notification

- Email verification notification
- Password reset notification
- Auction won notification
- Auction lost notification
- Auction cancelled notification
- Payment reminder notification
- Payment paid notification
- Account suspended notification
- Shipment on the way notification
- Shipment received notification

### Payment and Shipping

- Duitku Sandbox payment gateway integration
- Payment callback handling
- Payment return handling
- Invoice status update
- RajaOngkir integration
- Shipping cost calculation
- Shipment tracking number management

## Tech Stack

| Category | Technology |
|---|---|
| Backend | Laravel |
| Frontend | Blade, Tailwind CSS, JavaScript |
| Authentication | Laravel Jetstream / Fortify |
| Database | MySQL |
| Email Service | Resend |
| Payment Gateway | Duitku Sandbox |
| Shipping API | RajaOngkir |
| Deployment | Railway |
| Version Control | Git & GitHub |

## Screenshots

Place all screenshots inside this folder:

```txt
public/docs/screenshots
```

Recommended screenshot files:

```txt
public/docs/screenshots/landing-page.png
public/docs/screenshots/auction-list.png
public/docs/screenshots/auction-detail.png
public/docs/screenshots/register-page.png
public/docs/screenshots/login-page.png
public/docs/screenshots/bid-history.png
public/docs/screenshots/watchlist.png
public/docs/screenshots/invoice-payment.png
public/docs/screenshots/admin-dashboard.png
public/docs/screenshots/admin-products.png
public/docs/screenshots/admin-auction-lots.png
public/docs/screenshots/admin-transactions.png
```

### Landing Page

![Landing Page](public/docs/screenshots/landing-page.png)

### Auction List

![Auction List](public/docs/screenshots/auction-list.png)

### Auction Detail

![Auction Detail](public/docs/screenshots/auction-detail.png)

### Register Page

![Register Page](public/docs/screenshots/register-page.png)

### Login Page

![Login Page](public/docs/screenshots/login-page.png)

### Bid History

![Bid History](public/docs/screenshots/bid-history.png)

### Watchlist

![Watchlist](public/docs/screenshots/watchlist.png)

### Invoice and Payment

![Invoice and Payment](public/docs/screenshots/invoice-payment.png)

### Admin Dashboard

![Admin Dashboard](public/docs/screenshots/admin-dashboard.png)

### Admin Product Management

![Admin Product Management](public/docs/screenshots/admin-products.png)

### Admin Auction Lot Management

![Admin Auction Lot Management](public/docs/screenshots/admin-auction-lots.png)

### Admin Transaction Management

![Admin Transaction Management](public/docs/screenshots/admin-transactions.png)

## Demo Account

### Buyer Demo

```txt
Email    : demo@example.com
Password : Demo12345!
```

You may also register using your own email address to test the email verification flow.

### Admin Demo

Admin access is available upon request.

> Admin credentials are not publicly shared for security reasons.

## Payment Testing

This project uses **Duitku Sandbox** for payment testing.

To simulate a successful payment in the sandbox environment, use:

```txt
https://sandbox.duitku.com/payment/demo/demosuccesstransaction.aspx
```

Payment flow:

```txt
User wins an auction
→ Invoice is generated
→ User proceeds to payment
→ Duitku sandbox payment page is opened
→ User simulates successful payment
→ Payment callback or return flow updates the invoice status
```

> No real payment transaction is processed in the demo environment.

## Installation

Clone the repository:

```bash
git clone https://github.com/byochiram/watch-auction-system.git
cd watch-auction-system
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Copy the environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tempus_auctions
DB_USERNAME=root
DB_PASSWORD=
```

Run migration and seeder:

```bash
php artisan migrate --seed
```

Create storage link:

```bash
php artisan storage:link
```

Build frontend assets:

```bash
npm run build
```

Run local development server:

```bash
php artisan serve
```

Open the application:

```txt
http://127.0.0.1:8000
```

## Environment Variables

Configure these variables in `.env`.

### Application

```env
APP_NAME="Tempus Auctions"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tempus_auctions
DB_USERNAME=root
DB_PASSWORD=
```

### Mail / Resend

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=noreply@auctions.tempuscollective.com
MAIL_FROM_NAME="Tempus Auctions"
QUEUE_CONNECTION=sync
```

### Duitku Payment Gateway

```env
DUITKU_MERCHANT_CODE=
DUITKU_API_KEY=
DUITKU_CALLBACK_URL=
DUITKU_RETURN_URL=
DUITKU_MODE=sandbox
```

### RajaOngkir

```env
RAJAONGKIR_API_KEY=
RAJAONGKIR_ORIGIN_DISTRICT_ID=
```

> Do not commit real API keys, passwords, database credentials, or production secrets to GitHub.

## Deployment

This project is deployed using **Railway**.

Production setup includes:

- Laravel web service
- MySQL database service
- Railway environment variables
- Custom domain
- Resend email API
- Duitku Sandbox payment gateway
- RajaOngkir shipping API
- Railway volume for uploaded images
- Laravel Scheduler support for auction closing

### Railway Build Command

```bash
npm run build
```

### Railway Pre-deploy Command

```bash
php artisan optimize:clear && php artisan migrate --force && php artisan db:seed --class=AdminSeeder --force && php artisan optimize
```

### Railway Start Command

```bash
mkdir -p storage/app/public/products && rm -rf public/storage && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT
```

### Production URL

```txt
https://auctions.tempuscollective.com
```

## Auction Scheduler

Auction closing is handled using Laravel Scheduler.

The scheduler is responsible for:

```txt
Checking ended auction lots
Selecting the highest bidder
Updating auction status
Generating invoice data
Sending notification email
Handling unpaid or expired invoices
```

Recommended scheduler command:

```bash
php artisan schedule:run --verbose --no-interaction
```

Recommended Railway cron schedule:

```txt
*/5 * * * *
```

This means the auction closing process runs every 5 minutes in the deployment environment.

## Project Structure

```txt
app/
├── Actions
├── Console
├── Http
│   ├── Controllers
│   └── Middleware
├── Models
├── Notifications
├── Providers
└── Services

database/
├── migrations
└── seeders

resources/
├── css
├── js
└── views

public/
├── build
├── docs
├── storage
└── tempus

routes/
├── web.php
├── auth.php
└── console.php
```

## Security Notes

- Production debug mode is disabled
- Real credentials are stored in environment variables
- Email verification is enabled
- Admin credentials are not publicly shared
- Payment gateway runs in sandbox mode for demo
- Signed verification links are protected
- API keys and production secrets are not committed to GitHub

## Development Notes

Several production-related concerns were handled in this project:

- Railway deployment configuration
- Custom domain setup
- MySQL database migration
- Resend email integration
- Relative signed URL support for email verification
- Duitku sandbox payment flow
- RajaOngkir shipping integration
- Storage handling for uploaded product images
- Laravel Scheduler support for auction closing

## Author

Developed by **Rosidah Rahmati**.

```txt
Undergraduate Thesis Project
Department of Informatics
Faculty of Science and Mathematics
Universitas Diponegoro
2026
```

## Repository

```txt
https://github.com/byochiram/watch-auction-system
```

## License

This project is intended for academic final project, thesis documentation, and portfolio purposes.