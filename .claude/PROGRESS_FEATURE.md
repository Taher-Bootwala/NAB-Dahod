# Progress Reports & Storage Feature

## Summary
Added a complete progress reporting system allowing:
- **Public side**: Users can view and download yearly progress report PDFs grouped by year
- **Admin side**: Upload PDF reports with title and year; view remaining storage quota

## What was added

### 1. Configuration (app/config.php)
- `BUCKET_PROGRESS` — storage bucket for progress PDFs
- `MAX_PDF_BYTES` — max PDF upload size (20 MB)
- `SUPABASE_STORAGE_LIMIT` — total storage quota (1 GB default)

### 2. Helpers (app/helpers.php)
- `store_pdf()` — upload PDF to Supabase or local fallback
- `format_bytes()` — human-readable file sizes (KB, MB, GB)

### 3. Database layer
- **Supabase.php**: `listBuckets()`, `listObjects()`, `storageUsage()`
- **Repo.php**: `progressReports()` method
- **schema.sql**: `progress_reports` table with RLS policies

### 4. Icons (app/icons.php)
- `document`, `download`, `database` icons

### 5. Navigation
- **Public header**: Added "Our Progress" tab
- **Admin header**: Added "Progress" and "Storage" tabs

### 6. Pages
- **public/progress.php**: Displays reports grouped by year with view/download buttons
- **admin/progress.php**: CRUD for progress reports (upload PDF + title + year)
- **admin/storage.php**: Shows storage usage across all buckets with visual progress bar

### 7. Security
- Updated `uploads/.htaccess` to allow PDFs alongside images
- PDFs validated by MIME type in `store_pdf()`

## Routes
- Public: `/progress.php` or `/progress`
- Admin: `/admin/progress.php` — manage reports
- Admin: `/admin/storage.php` — view storage usage

## Database setup (if using Supabase)
Run the updated `database/schema.sql` to create the `progress_reports` table and RLS policies, then create a public storage bucket named `progress`.

## Testing locally
1. Start the dev server: `start.bat` or `php -S localhost:8000 -t public public/router.php`
2. Navigate to http://localhost:8000/progress — should show empty state
3. Login to admin at http://localhost:8000/admin/login.php
4. Go to "Progress" tab → Add a sample PDF (title + year)
5. Go to "Storage" tab → verify usage is displayed
6. Return to public /progress → PDF should appear grouped under its year

## Notes
- PDFs are stored in `BUCKET_PROGRESS` on Supabase or `public/uploads/progress/` offline
- Storage page works offline (reads local `uploads/` folder) and online (aggregates all Supabase buckets)
- Theme matches the existing claymorphism design with brand colors
- All files validated with `php -l` — no syntax errors
