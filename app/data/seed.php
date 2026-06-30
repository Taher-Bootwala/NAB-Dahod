<?php
/**
 * seed.php — realistic sample content so the site is fully functional
 * BEFORE Supabase is connected. The same shapes are mirrored by the
 * SQL schema in /database/schema.sql.
 *
 * Images use Unsplash source URLs (royalty-free) as stand-ins; replace
 * via the admin panel once Supabase Storage is connected.
 */

declare(strict_types=1);

function seed_data(): array
{
    return [
        'site_settings' => [
            'hero_title'        => 'NAB Dahod',
            'hero_subtitle'     => 'A free, residential school in Dahod, Gujarat — giving blind and low-vision children world-class education, Braille literacy, life skills and dignity since 1998.',
            'mission_statement' => 'We believe that loss of sight is never a loss of vision. Every child who walks through our gates deserves an education that opens doors, not one that lowers expectations.',
            'stat_students'     => '520',
            'stat_donations'    => '11800000',
            'stat_years'        => '27',
            'stat_volunteers'   => '140',
            'about_history'     => 'National Association for the Blind Dahod began in 1998 in a single rented room with seven students and one dedicated teacher. Today it is a fully residential campus serving over 500 visually impaired children from across the tribal belt of eastern Gujarat — at no cost to any family.',
            'principal_message' => 'For twenty-seven years I have watched children arrive frightened of the dark and leave us reading, coding, singing and dreaming. Our promise is simple: your child will never be defined by what their eyes cannot do, only by what their mind and heart can.',
            'footer_about'      => 'A registered, non-profit residential school for visually impaired students in Dahod, Gujarat. 80G & 12A certified. Every rupee is published on our transparency page.',
        ],

        'activities' => [
            [
                'id' => 'a1', 'slug' => 'annual-sports-day-2025',
                'title' => 'Annual Sports Day 2025',
                'category' => 'Sports',
                'date' => '2025-12-12',
                'description' => "Our biggest day of the year. 500+ students competed in beep-ball cricket, blind football, tandem cycling and 100m guided sprints. The roar of the crowd, the bells inside the footballs, and the sheer joy on every face proved that sport needs no sight — only spirit. District officials and parents joined as our athletes brought home twelve medals from the state para-sports meet.",
                'images' => [
                    'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&q=70',
                    'https://images.unsplash.com/photo-1543351611-58f69d7c1781?w=1200&q=70',
                ],
            ],
            [
                'id' => 'a2', 'slug' => 'braille-literacy-program',
                'title' => 'Braille Literacy Program',
                'category' => 'Education',
                'date' => '2025-11-03',
                'description' => "Braille is freedom on paper. Our intensive literacy program takes new students from their first dots to confident reading in under a year. Using slate-and-stylus, Perkins Braillers and refreshable Braille displays, children learn Gujarati, Hindi, English and mathematics — unlocking textbooks, stories and the entire world of the written word.",
                'images' => [
                    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=70',
                ],
            ],
            [
                'id' => 'a3', 'slug' => 'computer-screen-reader-lab',
                'title' => 'Computer & Screen-Reader Lab',
                'category' => 'Technology',
                'date' => '2025-10-18',
                'description' => "Our 20-seat computer lab runs NVDA and JAWS screen readers, teaching students to type, browse, email and even code by ear. Older students build websites, edit spreadsheets and prepare for competitive exams — graduating with the exact digital skills employers ask for. Three alumni now work in IT support roles.",
                'images' => [
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&q=70',
                ],
            ],
            [
                'id' => 'a4', 'slug' => 'navratri-cultural-festival',
                'title' => 'Navratri Cultural Festival',
                'category' => 'Cultural',
                'date' => '2025-10-02',
                'description' => "Nine nights of garba, music and celebration. Our students' choir and orchestra performed to a packed hall, and the whole campus danced. Cultural confidence matters as much as academics — on this stage, our children are simply performers, applauded for their talent.",
                'images' => [
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=1200&q=70',
                ],
            ],
            [
                'id' => 'a5', 'slug' => 'independent-living-skills',
                'title' => 'Independent Living Skills Workshop',
                'category' => 'Skill Development',
                'date' => '2025-09-20',
                'description' => "Orientation and mobility, cooking, money handling, and white-cane navigation. These workshops turn dependence into independence — teaching students to move through the world, manage a household and travel safely on their own. Graduation here means walking out the gate, cane in hand, ready for life.",
                'images' => [
                    'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1200&q=70',
                ],
            ],
            [
                'id' => 'a6', 'slug' => 'music-therapy-and-choir',
                'title' => 'Music Therapy & Choir',
                'category' => 'Cultural',
                'date' => '2025-08-15',
                'description' => "Music is one of our students' greatest strengths. From tabla and harmonium to keyboard and vocals, our award-winning choir has performed across Gujarat. Music therapy also builds memory, confidence and calm — and several students now earn from performances.",
                'images' => [
                    'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=1200&q=70',
                ],
            ],
        ],

        'gallery' => [
            ['id' => 'g1', 'title' => 'Reading by touch', 'category' => 'Classroom Learning', 'date' => '2025-11-10', 'description' => 'A student reads her first full Braille story.', 'image_url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=900&q=70'],
            ['id' => 'g2', 'title' => 'Beep-ball cricket', 'category' => 'Sports', 'date' => '2025-12-12', 'description' => 'Fielding by sound at Sports Day.', 'image_url' => 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=900&q=70'],
            ['id' => 'g3', 'title' => 'Garba night', 'category' => 'Festivals', 'date' => '2025-10-02', 'description' => 'Navratri celebrations on campus.', 'image_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=900&q=70'],
            ['id' => 'g4', 'title' => 'In the computer lab', 'category' => 'Classroom Learning', 'date' => '2025-10-18', 'description' => 'Learning to code with a screen reader.', 'image_url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=900&q=70'],
            ['id' => 'g5', 'title' => 'Annual Day stage', 'category' => 'Annual Events', 'date' => '2025-12-20', 'description' => 'Our choir performs for parents.', 'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=900&q=70'],
            ['id' => 'g6', 'title' => 'White-cane training', 'category' => 'School Activities', 'date' => '2025-09-20', 'description' => 'Orientation and mobility practice.', 'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=900&q=70'],
            ['id' => 'g7', 'title' => 'Morning assembly', 'category' => 'School Activities', 'date' => '2025-11-01', 'description' => 'A new day begins together.', 'image_url' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?w=900&q=70'],
            ['id' => 'g8', 'title' => 'Track sprint', 'category' => 'Sports', 'date' => '2025-12-12', 'description' => 'Guided 100m sprint final.', 'image_url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=900&q=70'],
            ['id' => 'g9', 'title' => 'Hands on a globe', 'category' => 'Classroom Learning', 'date' => '2025-10-25', 'description' => 'Tactile geography lesson.', 'image_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=900&q=70'],
        ],

        'trustees' => [
            ['id' => 't1', 'name' => 'Dr. Rameshbhai Patel', 'position' => 'Founder & Chairman', 'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => 'chairman@blindschooldahod.org'],
            ['id' => 't2', 'name' => 'Smt. Meenaben Shah', 'position' => 'Managing Trustee', 'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => ''],
            ['id' => 't3', 'name' => 'Shri Kiritbhai Mehta', 'position' => 'Treasurer', 'photo' => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => ''],
            ['id' => 't4', 'name' => 'Dr. Anjali Desai', 'position' => 'Academic Director', 'photo' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => ''],
            ['id' => 't5', 'name' => 'Shri Hasmukhbhai Joshi', 'position' => 'Trustee', 'photo' => 'https://images.unsplash.com/photo-1542909168-82c3e7fdca5c?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => ''],
            ['id' => 't6', 'name' => 'Smt. Priya Nair', 'position' => 'Trustee — Community Outreach', 'photo' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&q=70', 'address' => 'Dahod, Gujarat', 'contact' => ''],
        ],

        'testimonials' => [
            ['name' => 'Kavya, Class 10 student', 'role' => 'Student', 'quote' => 'I came here unable to read a single letter. Last month I read the whole of my favourite novel in Braille. This school gave me back the world.'],
            ['name' => 'Rajeshbhai, Parent', 'role' => 'Parent', 'quote' => 'When my son lost his sight, I thought his future was over. Today he uses a computer better than I do and dreams of being a teacher. I have no words, only gratitude.'],
            ['name' => 'Ananya, Volunteer', 'role' => 'Volunteer', 'quote' => 'I came to teach for one summer. Three years later I am still here. These children do not need our pity — they need our belief, and they return it a hundredfold.'],
            ['name' => 'Imran, Alumnus', 'role' => 'Alumnus', 'quote' => 'I graduated in 2019 and now work in IT support in Ahmedabad. Everything I know about computers began in that little lab. I send back what I can, every month.'],
        ],

        'donations_recent' => [
            ['donor_name' => 'Anonymous',        'amount' => 5000,  'created_at' => '2026-06-12T10:24:00Z'],
            ['donor_name' => 'Nikhil S.',        'amount' => 1000,  'created_at' => '2026-06-11T18:02:00Z'],
            ['donor_name' => 'Patel Family',     'amount' => 25000, 'created_at' => '2026-06-10T09:40:00Z'],
            ['donor_name' => 'Anonymous',        'amount' => 500,   'created_at' => '2026-06-09T21:15:00Z'],
            ['donor_name' => 'Sangita M.',       'amount' => 2100,  'created_at' => '2026-06-08T12:30:00Z'],
            ['donor_name' => 'Wellspring Trust', 'amount' => 100000,'created_at' => '2026-06-05T08:00:00Z'],
        ],

        'timeline' => [
            ['year' => '1998', 'title' => 'Where it began', 'text' => 'Founded in a single rented room with 7 students and one teacher.'],
            ['year' => '2004', 'title' => 'Our own campus', 'text' => 'Moved into a purpose-built residential campus on Godhra Road.'],
            ['year' => '2010', 'title' => 'Braille library', 'text' => 'Opened a 4,000-volume Braille and audio-book library.'],
            ['year' => '2015', 'title' => 'Going digital', 'text' => 'Launched the screen-reader computer lab and assistive-tech program.'],
            ['year' => '2020', 'title' => 'Reaching further', 'text' => 'Began village outreach to enrol blind children from remote tribal areas.'],
            ['year' => '2025', 'title' => '500+ and growing', 'text' => 'Crossed 500 students with a 96% literacy success rate.'],
        ],
    ];
}
