# Tempus Auctions

Tempus Auctions is a Laravel-based watch auction platform built for Tempus Collective Indonesia.  
The system supports user registration, email verification, product auctions, bidding flow, winner selection, invoice generation, payment gateway integration, and cloud deployment.

## Live Demo

https://auctions.tempuscollective.com

> Payment is configured using Duitku Sandbox for demo purposes.

## Features

### User

- Register and login
- Email verification
- Browse active watch auctions
- View auction details
- Place bids
- Add auction items to watchlist
- Receive auction winner notification
- View invoice and payment information

### Admin

- Manage products
- Manage product images
- Manage auction lots
- Monitor bids
- Monitor invoices and payments
- Manage users

### System

- Auction start and end time
- Highest bid tracking
- Winner selection
- Invoice generation
- Duitku Sandbox payment integration
- Resend email integration
- RajaOngkir shipping cost support
- Railway deployment
- Laravel Scheduler support for auction closing

## Tech Stack

| Category | Technology |
|---|---|
| Backend | Laravel |
| Frontend | Blade, Tailwind CSS, JavaScript |
| Authentication | Laravel Jetstream / Fortify |
| Database | MySQL |
| Email | Resend |
| Payment Gateway | Duitku Sandbox |
| Shipping API | RajaOngkir |
| Deployment | Railway |

## Screenshots

Place screenshots inside:

```txt
public/docs/screenshots
```

Recommended files:

```txt
public/docs/screenshots/landing-page.png
public/docs/screenshots/auction-list.png
public/docs/screenshots/auction-detail.png
public/docs/screenshots/admin-products.png
public/docs/screenshots/invoice-payment.png
```

### Landing Page

![Landing Page](public/docs/screenshots/landing-page.png)

### Auction List

![Auction List](public/docs/screenshots/auction-list.png)

### Auction Detail

![Auction Detail](public/docs/screenshots/auction-detail.png)

### Admin Product Management

![Admin Product Management](public/docs/screenshots/admin-products.png)

### Invoice and Payment

![Invoice and Payment](public/docs/screenshots/invoice-payment.png)

## Demo Account

### Buyer Demo

```txt
Email    : demo@example.com
Password : Demo12345!
```

### Admin Demo

Admin access is available upon request.

> Admin credentials are not publicly shared for security reasons.

## Payment Testing

This project uses Duitku Sandbox for payment testing.

To simulate a successful payment in the sandbox environment, use:

https://sandbox.duitku.com/payment/demo/demosuccesstransaction.aspx

Payment flow:

```txt
User wins an auction
→ Invoice is generated
→ User proceeds to payment
→ Duitku sandbox page is opened
→ User simulates successful payment
→ Payment status is updated
```

> No real payment transaction is processed in the demo environment.

## Installation

Clone the repository:

```bash
git clone https://github.com/byochiram/watch-auction-system.git
cd watch-auction-system
```

Install dependencies:

```bash
composer install
npm install
```

Copy environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Run migration and seeder:

```bash
php artisan migrate --seed
```

Create storage link:

```bash
php artisan storage:link
```

Build assets:

```bash
npm run build
```

Run development server:

```bash
php artisan serve
```

Open:

```txt
http://127.0.0.1:8000
```

## Environment Variables

Configure these variables in `.env`:

```env
APP_NAME="Tempus Auctions"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tempus_auctions
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Tempus Auctions"

DUITKU_MERCHANT_CODE=
DUITKU_API_KEY=
DUITKU_CALLBACK_URL=
DUITKU_RETURN_URL=
DUITKU_MODE=sandbox

RAJAONGKIR_API_KEY=
RAJAONGKIR_ORIGIN_DISTRICT_ID=
```

> Do not commit real API keys, passwords, or production credentials to GitHub.

## Deployment

This project is deployed using Railway.

Production setup includes:

- Laravel web service
- MySQL database service
- Custom domain
- Resend email API
- Duitku Sandbox payment gateway
- RajaOngkir shipping API
- Railway volume for uploaded images
- Laravel Scheduler for auction closing

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

## Auction Scheduler

Auction closing is handled using Laravel Scheduler.

Recommended scheduler command:

```bash
php artisan schedule:run --verbose --no-interaction
```

Recommended Railway cron schedule:

```txt
*/5 * * * *
```

The scheduler checks ended auctions, determines the winner, generates invoice data, and sends notification email.

## Future Improvements

- Real-time bidding updates
- Queue worker for background email processing
- Invoice PDF generation
- Cloudinary / S3 / Cloudflare R2 image storage
- Payment reminder emails
- Auction analytics dashboard
- Audit log for admin actions
- Automated testing

## Author

Developed by Naufal Zufar.

GitHub: https://github.com/byochiram

## License

This project is developed for educational and portfolio purposes.