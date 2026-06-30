-- ============================================================
--  Blind School Dahod — Supabase / PostgreSQL schema
--  Run this in the Supabase SQL editor (or psql).
--  Then create a Storage bucket named "media" (public).
-- ============================================================

create extension if not exists "pgcrypto";

-- ---------- USERS (admin roles; linked to auth.users) ----------
create table if not exists public.users (
  id          uuid primary key references auth.users(id) on delete cascade,
  email       text unique not null,
  name        text,
  role        text not null default 'admin' check (role in (' super_admin','admin','user')),
  created_at  timestamptz not null default now()
);

-- ---------- ACTIVITIES ----------
create table if not exists public.activities (
  id          uuid primary key default gen_random_uuid(),
  slug        text unique,
  title       text not null,
  description text not null default '',
  category    text not null default 'General',
  date        date not null default current_date,
  images      jsonb not null default '[]'::jsonb,
  created_at  timestamptz not null default now()
);
create index if not exists activities_date_idx on public.activities (date desc);
create index if not exists activities_category_idx on public.activities (category);

-- ---------- GALLERY ----------
create table if not exists public.gallery (
  id          uuid primary key default gen_random_uuid(),
  image_url   text not null,
  title       text not null default '',
  description text not null default '',
  category    text not null default 'School Activities',
  date        date not null default current_date,
  created_at  timestamptz not null default now()
);
create index if not exists gallery_date_idx on public.gallery (date desc);

-- ---------- HOME DISPLAY IMAGES (home page hero strip) ----------
create table if not exists public.home_images (
  id          uuid primary key default gen_random_uuid(),
  image_url   text not null,
  title       text not null default '',
  created_at  timestamptz not null default now()
);
create index if not exists home_images_created_idx on public.home_images (created_at desc);

-- ---------- PROGRESS REPORTS (yearly PDF documents) ----------
create table if not exists public.progress_reports (
  id          uuid primary key default gen_random_uuid(),
  title       text not null default '',
  year        int  not null default (extract(year from current_date))::int,
  pdf_url     text not null,
  created_at  timestamptz not null default now()
);
create index if not exists progress_reports_year_idx on public.progress_reports (year desc);

-- ---------- TRUSTEES ----------
create table if not exists public.trustees (
  id          uuid primary key default gen_random_uuid(),
  name        text not null,
  position    text not null default '',
  photo       text,
  address     text not null default '',
  contact     text,
  sort_order  int not null default 100,
  created_at  timestamptz not null default now()
);

-- ---------- DONATIONS ----------
create table if not exists public.donations (
  id             uuid primary key default gen_random_uuid(),
  donor_name     text not null default 'Anonymous',
  email          text,
  amount         numeric(12,2) not null check (amount > 0),
  payment_status text not null default 'pending' check (payment_status in ('pending','success','failed')),
  transaction_id text,
  receipt_no     text,
  message        text,
  created_at     timestamptz not null default now()
);
create index if not exists donations_status_idx on public.donations (payment_status);
create index if not exists donations_created_idx on public.donations (created_at desc);

-- ---------- CONTACT MESSAGES ----------
create table if not exists public.contact_messages (
  id          uuid primary key default gen_random_uuid(),
  name        text not null,
  email       text not null,
  phone       text,
  message     text not null,
  status      text not null default 'new' check (status in ('new','resolved')),
  created_at  timestamptz not null default now()
);

-- ---------- SITE SETTINGS (CMS key/value) ----------
create table if not exists public.site_settings (
  id          uuid primary key default gen_random_uuid(),
  key         text unique not null,
  value       text not null default '',
  updated_at  timestamptz not null default now()
);

-- ---------- AUDIT LOGS ----------
create table if not exists public.audit_logs (
  id          uuid primary key default gen_random_uuid(),
  actor       text,
  action      text not null,
  detail      text,
  ip          text,
  created_at  timestamptz not null default now()
);

-- ============================================================
--  ROW LEVEL SECURITY
--  Public site reads via the service key (server-side, bypasses RLS).
--  These policies allow anon SELECT on public content and anon INSERT
--  for donations/contact, while protecting everything else.
-- ============================================================
alter table public.activities       enable row level security;
alter table public.gallery          enable row level security;
alter table public.home_images      enable row level security;
alter table public.progress_reports enable row level security;
alter table public.trustees         enable row level security;
alter table public.donations        enable row level security;
alter table public.contact_messages enable row level security;
alter table public.site_settings    enable row level security;
alter table public.users            enable row level security;
alter table public.audit_logs       enable row level security;

-- Public read of published content
create policy "public read activities"    on public.activities    for select using (true);
create policy "public read gallery"        on public.gallery       for select using (true);
create policy "public read home images"    on public.home_images   for select using (true);
create policy "public read progress"        on public.progress_reports for select using (true);
create policy "public read trustees"       on public.trustees      for select using (true);
create policy "public read settings"       on public.site_settings for select using (true);
-- Public read of successful donations only (for the transparency feed)
create policy "public read success donations" on public.donations for select using (payment_status = 'success');

-- Anyone may submit a donation or contact message
create policy "anyone insert donation" on public.donations        for insert with check (true);
create policy "anyone insert contact"  on public.contact_messages for insert with check (true);

-- Authenticated admins manage everything
create policy "admin all activities" on public.activities    for all to authenticated using (true) with check (true);
create policy "admin all gallery"     on public.gallery       for all to authenticated using (true) with check (true);
create policy "admin all home images" on public.home_images   for all to authenticated using (true) with check (true);
create policy "admin all progress"    on public.progress_reports for all to authenticated using (true) with check (true);
create policy "admin all trustees"    on public.trustees      for all to authenticated using (true) with check (true);
create policy "admin all settings"    on public.site_settings for all to authenticated using (true) with check (true);
create policy "admin all donations"   on public.donations     for all to authenticated using (true) with check (true);
create policy "admin all contact"     on public.contact_messages for all to authenticated using (true) with check (true);
create policy "admin read users"      on public.users         for select to authenticated using (true);
create policy "admin audit"           on public.audit_logs    for all to authenticated using (true) with check (true);

-- ============================================================
--  SEED DATA (mirrors app/data/seed.php so live + demo match)
-- ============================================================
insert into public.site_settings (key, value) values
  ('hero_title','NAB Dahod'),
  ('hero_subtitle','A free, residential school in Dahod, Gujarat — giving blind and low-vision children world-class education, Braille literacy, life skills and dignity since 1998.'),
  ('mission_statement','We believe that loss of sight is never a loss of vision. Every child who walks through our gates deserves an education that opens doors, not one that lowers expectations.'),
  ('stat_students','520'),
  ('stat_donations','11800000'),
  ('stat_years','27'),
  ('stat_volunteers','140'),
  ('about_history','National Association for the Blind, Dahod began in 1998 in a single rented room with seven students and one dedicated teacher. Today it is a fully residential campus serving over 500 visually impaired children from across the tribal belt of eastern Gujarat — at no cost to any family.'),
  ('principal_message','For twenty-seven years I have watched children arrive frightened of the dark and leave us reading, coding, singing and dreaming. Our promise is simple: your child will never be defined by what their eyes cannot do, only by what their mind and heart can.'),
  ('footer_about','A registered, non-profit residential school for visually impaired students in Dahod, Gujarat. 80G & 12A certified. Every rupee is published on our transparency page.')
on conflict (key) do nothing;

insert into public.trustees (name, position, photo, address, sort_order) values
  ('Dr. Rameshbhai Patel','Founder & Chairman','https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&q=70','Dahod, Gujarat',10),
  ('Smt. Meenaben Shah','Managing Trustee','https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=70','Dahod, Gujarat',20),
  ('Shri Kiritbhai Mehta','Treasurer','https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=600&q=70','Dahod, Gujarat',30)
on conflict do nothing;

insert into public.activities (slug, title, category, date, description, images) values
  ('annual-sports-day-2025','Annual Sports Day 2025','Sports','2025-12-12','Our biggest day of the year. 500+ students competed in beep-ball cricket, blind football, tandem cycling and guided sprints.','["https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&q=70"]'),
  ('braille-literacy-program','Braille Literacy Program','Education','2025-11-03','Braille is freedom on paper. Our intensive literacy program takes new students from their first dots to confident reading in under a year.','["https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=70"]')
on conflict (slug) do nothing;

-- ============================================================
--  AFTER RUNNING THIS:
--  1) Create public Storage buckets: "media", "activities", "gallery",
--     "trustees", "home", and "progress" (for yearly report PDFs).
--  2) Create an admin user in Authentication, then:
--       insert into public.users (id, email, role)
--       values ('<auth-user-uuid>', 'admin@blindschooldahod.org', 'super_admin');
--  3) Put SUPABASE_URL / SUPABASE_ANON_KEY / SUPABASE_SERVICE_KEY in .env
-- ============================================================
