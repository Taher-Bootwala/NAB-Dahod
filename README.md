# National Association for the Blind Website

A premium, modern, accessible website for Blind School Dahod with complete admin panel.

## Quick Start

### Windows (XAMPP)
```bash
start.bat
```
Opens http://localhost:8000

### Manual Start
```bash
php -S localhost:8000 -t public public\router.php
```

## Database

### Offline Mode (Default)
Works with JSON files in `/storage/` and seed data from `app/data/seed.php`.

### Supabase Mode (Production)
1. Create a Supabase project
2. Run `database/schema.sql` in SQL Editor
3. Create Storage bucket named `media` (public)
4. Fill `.env` with credentials:
   ```
   SUPABASE_URL=https://xxx.supabase.co
   SUPABASE_ANON_KEY=your_anon_key
   SUPABASE_SERVICE_KEY=your_service_key
   ```

## File Structure

```
public/
├── admin/               # Admin panel
│   ├── login.php       # Authentication
│   ├── index.php       # Dashboard
│   ├── activities.php  # Activities CRUD
│   ├── gallery.php     # Gallery CRUD
│   ├── trustees.php    # Trustees CRUD
│   ├── donations.php   # Donations viewer
│   ├── messages.php    # Contact messages
│   ├── content.php     # CMS editor
│   └── logout.php      # Logout
├── index.php           # Home
├── about.php           # About
├── activities.php      # Activities listing
├── activity.php        # Single activity
├── gallery.php         # Photo gallery
├── trustees.php        # Trustees
├── contact.php         # Contact form
└── donate.php          # Donation page

app/
├── bootstrap.php       # App initialization
├── auth.php           # Authentication
├── Repo.php           # Data repository
├── Supabase.php       # Supabase client
├── config.php         # Configuration
├── helpers.php        # Helper functions
├── icons.php          # SVG icons
├── layout/
│   ├── header.php     # Public site header
│   ├── footer.php     # Public site footer
│   ├── admin_header.php  # Admin header
│   └── admin_footer.php  # Admin footer
└── data/
    └── seed.php       # Demo data

storage/               # JSON storage (offline mode)
database/             # SQL schemas
```

## Security

- Admin routes protected by `require_admin()`
- Session-based authentication
- CSRF-ready (add tokens to forms)
- Security headers enabled
- Demo mode for offline testing

## Next Steps

1. **Connect Supabase** for production data
2. **Add CSRF tokens** to all forms
3. **Implement file uploads** to Supabase Storage
4. **Add email notifications** for donations
5. **Set up PWA** (service worker)
6. **Add rate limiting** on forms
7. **Create admin audit log viewer** (`/admin/logs.php`)

## Tech Stack

- PHP 8+
- Tailwind CSS (CDN)
- Vanilla JavaScript
- Supabase (PostgreSQL)
- No frameworks or Composer dependencies

## License

Built for National Association for the Blind, Dahod, Gujarat, India.
