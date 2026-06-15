# TikiToki

TikiToki is a Laravel project that works like a small TikTok-style social media app. It has vertical scrolling videos/photos, user accounts, a For You feed, a Following feed, likes, subscriptions, and database-backed demo content.

## What Was Built

- Replaced the default Laravel welcome page with a TikTok-style feed.
- Added account registration, login, and logout.
- Added two main pages:
    - `/fyp` - public For You feed with all posts.
    - `/following` - feed with posts from creators the logged-in user subscribed to.
- Added posts that can be either video or photo content.
- Added like/unlike functionality.
- Added subscribe/unsubscribe functionality between users.
- Added a post creation drawer for logged-in users.
- Added a responsive full-screen feed UI with snap scrolling.
- Added JavaScript that auto-plays the visible video and pauses videos that are off screen.
- Added database migrations and seed data for a working demo.

## Main Files Added Or Changed

- `routes/web.php` - app routes for feeds, auth, posts, likes, and subscriptions.
- `app/Http/Controllers/AuthController.php` - register, login, and logout logic.
- `app/Http/Controllers/FeedController.php` - For You and Following feeds.
- `app/Http/Controllers/PostController.php` - create posts and like posts.
- `app/Http/Controllers/SubscriptionController.php` - subscribe/unsubscribe logic.
- `app/Models/Post.php` - post model.
- `app/Models/Like.php` - like model.
- `app/Models/Subscription.php` - subscription model.
- `app/Models/User.php` - added profile fields and relationships.
- `database/migrations/*` - tables for profile fields, posts, likes, and subscriptions.
- `database/seeders/DatabaseSeeder.php` - demo users, posts, likes, and subscriptions.
- `resources/views/feed/index.blade.php` - main TikTok-style feed page.
- `resources/views/auth/login.blade.php` - login page.
- `resources/views/auth/register.blade.php` - register page.
- `resources/views/components/layouts/app.blade.php` - shared layout.
- `resources/css/app.css` - full app styling.
- `resources/js/app.js` - video auto-play and keyboard scrolling.
- `vite.config.js` - removed remote Bunny font fetch so builds work offline.

## Features

### Account System

Users can create an account, log in, and log out. A logged-in user can post content, like posts, and subscribe to creators.

### Feeds

The app has two feed pages:

- **For You**: shows all posts ordered by newest first.
- **Following**: shows only posts from creators the user subscribed to.

### Posts

Posts support:

- caption
- media type: `video` or `photo`
- media URL
- optional sound/song title

### Interactions

Explored 2 files
I’ve got the current feed structure. I’m going to keep the real Laravel features intact, but reshape the main page around the prototype: left navigation, center content strip, and right profile/messages panels.

Edited 1 file
The Blade layout now matches the prototype’s structure. I’m adding the visual rules next: matte left rail, muted center feed, profile/message panels on the right, and responsive fallbacks for smaller screens.

Users can:

- like or unlike posts
- subscribe or unsubscribe from creators
- create new posts from the feed page

## Database Tables

The project uses Laravel migrations for:

- `users`
- `posts`
- `likes`
- `comments`
- `subscriptions`
- Laravel default cache, jobs, sessions, and password reset tables

## Demo Account

After running the seeders, you can log in with:

```text
Email: test@example.com
Password: password
```

## Setup

Install PHP dependencies:

```bash
composer install
```

Install Node dependencies:

```bash
npm install
```

Create or update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tikitoki
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the MySQL database:

```sql
CREATE DATABASE IF NOT EXISTS tikitoki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations and seed data:

```bash
php artisan migrate --seed
```

Build frontend assets:

```bash
npm run build
```

Start the Laravel server:

```bash
php artisan serve
```

Open the app:

```text
http://127.0.0.1:8000/fyp
```

## Development

For local development, run:

```bash
npm run dev
```

In another terminal:

```bash
php artisan serve
```

## Notes

- Media uploads are URL-based in this version. Users paste a direct video or image URL.
- The app uses external demo media links in the seeder.
- Tests may require `pdo_sqlite` if using the default PHPUnit in-memory SQLite configuration.
