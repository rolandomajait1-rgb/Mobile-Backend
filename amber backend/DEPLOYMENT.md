# Amber Backend Deployment Checklist

## Pre-Deployment

- [ ] Run tests: `php artisan test`
- [ ] Update `.env.production` with actual values
- [ ] Generate new APP_KEY: `php artisan key:generate`
- [ ] Set APP_DEBUG=false
- [ ] Set APP_ENV=production

## Database Setup (Render PostgreSQL)

1. Create PostgreSQL database on Render
2. Update `.env.production`:
   - DB_HOST
   - DB_PORT
   - DB_DATABASE
   - DB_USERNAME
   - DB_PASSWORD

## Deployment Steps

### Option 1: Render.com

1. Push code to GitHub
2. Connect Render to repository
3. Set environment variables from `.env.production`
4. Deploy

### Option 2: Railway.app

1. Push code to GitHub
2. Connect Railway to repository
3. Add PostgreSQL service
4. Set environment variables
5. Deploy

## Post-Deployment

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Test endpoints
- [ ] Verify email sending
- [ ] Check logs

## Environment Variables Required

```
APP_NAME
APP_ENV=production
APP_KEY
APP_DEBUG=false
APP_URL
FRONTEND_URL
DB_CONNECTION=pgsql
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_SSLMODE=require
MAIL_MAILER=smtp
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
CLOUDINARY_API_KEY
CLOUDINARY_API_SECRET
CLOUDINARY_URL
```

## Test Endpoints

- POST /api/register
- POST /api/login
- GET /api/user (authenticated)
- GET /api/articles
- GET /api/categories
- GET /api/tags
- POST /api/subscribers/subscribe
