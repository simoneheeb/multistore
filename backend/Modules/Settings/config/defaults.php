<?php

/*
|--------------------------------------------------------------------------
| Site settings defaults
|--------------------------------------------------------------------------
|
| Every editable part of the public site is described here as a settings
| "group". A group is stored as one row in the settings table (key = group
| name, value = JSON) and is merged over these defaults on read, so a group
| that has never been saved - or that was saved before a new field existed -
| still returns a complete, renderable structure to the frontend.
|
| The site is single-locale English, so each label is one field. An earlier
| revision carried `*_en` / `*_fa` pairs for a bilingual layout; those were
| collapsed when the site moved to English only.
|
*/

return [

    // ---------------------------------------------------------------- brand
    'identity' => [
        'site_name' => 'Beta Official',
        'tagline' => 'Sports Equipment Store',
        'description' => 'Shop sports gear and apparel online, with every item guaranteed authentic.',
        'logo' => '/assets/images/logo-header.png',
        'logo_dark' => '/assets/images/logo-header-light.png',
        'logo_footer' => '/assets/images/logo-footer.png',
        'favicon' => '/favicon.ico',
        'logo_width' => 168,
        'logo_height' => 44,
    ],

    // --------------------------------------------------------------- header
    'header' => [
        'sticky' => true,
        'show_search' => true,
        'topbar' => [
            'enabled' => true,
            'text' => 'Free shipping on orders over $150',
            'phone' => '+1 (555) 000-0000',
        ],
        // Categories and Brands are intentionally absent: the header renders
        // its own mega-menus for those two, and listing them here as well
        // would show each label twice in the main navigation. Adding them
        // back is still possible for anyone who turns the mega-menus off.
        'menu_items' => [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Products', 'url' => '/products'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Contact', 'url' => '/contact'],
        ],
        // Auto-generated mega menus built from the catalogue tree. Toggled
        // here so an admin can hide them without touching the code.
        'show_category_menu' => true,
        'show_brand_menu' => true,
        'cta' => [
            'enabled' => false,
            'label' => 'Contact us',
            'url' => '/contact',
        ],
    ],

    // --------------------------------------------------------------- footer
    'footer' => [
        'about' => [
            'title' => 'About us',
            'text' => 'Authentic sports equipment, backed by a no-questions return policy and support seven days a week.',
        ],
        'columns' => [
            [
                'title' => 'Information',
                'links' => [
                    ['label' => 'About us', 'url' => '/about'],
                    ['label' => 'Contact us', 'url' => '/contact'],
                    ['label' => 'Return policy', 'url' => '/about#returns'],
                ],
            ],
            [
                'title' => 'Catalogue',
                'links' => [
                    ['label' => 'All products', 'url' => '/products'],
                    ['label' => 'All categories', 'url' => '/categories'],
                    ['label' => 'All brands', 'url' => '/brands'],
                ],
            ],
        ],
        'contact' => [
            'title' => 'Get in touch',
            'address' => '1200 Market Street, Suite 40, San Francisco, CA',
            'phone' => '+1 (555) 000-0000',
            'mobile' => '+1 (555) 000-0001',
            'email' => 'info@example.com',
            'working_hours' => 'Monday to Friday, 9am to 6pm',
        ],
        'copyright' => '© '.date('Y').' Beta Official. All rights reserved.',
        'show_newsletter' => true,
    ],

    // ------------------------------------------------------------- socials
    'social' => [
        'items' => [
            ['platform' => 'instagram', 'label' => 'Instagram', 'url' => ''],
            ['platform' => 'telegram', 'label' => 'Telegram', 'url' => ''],
            ['platform' => 'whatsapp', 'label' => 'WhatsApp', 'url' => ''],
        ],
    ],

    // ----------------------------------------------------------------- hero
    'hero' => [
        'autoplay' => true,
        'interval' => 6000,
        'slides' => [
            [
                'id' => 1,
                'eyebrow' => 'Premium Quality',
                'title' => 'Welcome to our store',
                'description' => 'The best products at the best prices, with fast delivery and support that never sleeps.',
                'image' => '/assets/images/hero/slide-1.jpg',
                'cta_label' => 'Browse products',
                'cta_url' => '/products',
            ],
            [
                'id' => 2,
                'eyebrow' => 'Seasonal Sale',
                'title' => 'Up to 50% off',
                'description' => 'On selected items, this week only.',
                'image' => '/assets/images/hero/slide-2.jpg',
                'cta_label' => 'Start shopping',
                'cta_url' => '/products',
            ],
        ],
    ],

    // ------------------------------------------------------------- features
    'features' => [
        'enabled' => true,
        'eyebrow' => 'Why us',
        'title' => 'Why choose us',
        'items' => [
            ['icon' => 'truck', 'title' => 'Fast delivery', 'description' => 'Nationwide shipping in the shortest possible time.'],
            ['icon' => 'shield', 'title' => 'Authenticity guarantee', 'description' => 'Every item is original and covered by warranty.'],
            ['icon' => 'refresh', 'title' => 'Easy returns', 'description' => 'Seven days to return an item, no questions asked.'],
            ['icon' => 'headset', 'title' => 'Always on support', 'description' => 'Our specialists are available every day of the week.'],
        ],
    ],

    // ---------------------------------------------------------- promo strip
    'promo' => [
        'enabled' => true,
        'eyebrow' => 'New Season',
        'title' => 'New season collection',
        'description' => 'The latest sports gear with contemporary design, now in stock.',
        'badge' => 'New',
        'image' => '/assets/images/promo/promo-1.jpg',
        'cta_label' => 'View the collection',
        'cta_url' => '/products',
    ],

    // ---------------------------------------------------------- testimonials
    'testimonials' => [
        'enabled' => true,
        'eyebrow' => 'Reviews',
        'title' => 'What our customers say',
        'items' => [
            ['name' => 'Sample Customer', 'role' => 'Verified buyer', 'avatar' => '', 'rating' => 5, 'text' => 'Great build quality and the delivery was quicker than promised.'],
        ],
    ],

    // -------------------------------------------------------------- articles
    'articles' => [
        'enabled' => true,
        'eyebrow' => 'Journal',
        'title' => 'Latest articles',
        'items' => [],
    ],

    // ------------------------------------------------------------ newsletter
    'newsletter' => [
        'enabled' => true,
        'eyebrow' => 'Newsletter',
        'title' => 'Stay in the loop',
        'description' => 'Enter your email to hear about discounts and new arrivals first.',
        'placeholder' => 'Your email address',
        'button_label' => 'Subscribe',
    ],

    // ------------------------------------------------------------ about page
    'about_page' => [
        'title' => 'About us',
        'subtitle' => 'Our story, our values',
        'cover' => '/assets/images/pages/about-cover.jpg',
        'body' => 'Edit the full company introduction from the admin panel.',
        'stats' => [
            ['value' => '10+', 'label' => 'Years of experience'],
            ['value' => '5,000+', 'label' => 'Happy customers'],
            ['value' => '30+', 'label' => 'Brands stocked'],
        ],
        'sections' => [
            ['title' => 'Our mission', 'text' => 'Making quality sports equipment available to everyone.', 'image' => ''],
        ],
        'meta_title' => '',
        'meta_description' => '',
    ],

    // ---------------------------------------------------------- contact page
    'contact_page' => [
        'title' => 'Contact us',
        'subtitle' => 'We are here to help',
        'cover' => '/assets/images/pages/contact-cover.jpg',
        'intro' => 'Get in touch to place an order, track a delivery, or ask us anything.',
        'address' => '1200 Market Street, Suite 40, San Francisco, CA',
        'phones' => ['+1 (555) 000-0000'],
        'emails' => ['info@example.com'],
        'working_hours' => 'Monday to Friday, 9am to 6pm',
        'map_embed' => '',
        'form_enabled' => true,
        'meta_title' => '',
        'meta_description' => '',
    ],

    // ------------------------------------------------------------------ seo
    'seo' => [
        'default_title' => 'Beta Official',
        'title_template' => '%s | Beta Official',
        'default_description' => 'Online store for sports equipment, with guaranteed authentic products and fast delivery.',
        'default_keywords' => 'sports equipment, sportswear, sports store',
        'og_image' => '/assets/images/og-default.jpg',
        'twitter_handle' => '',
        'robots_index' => true,
        'google_site_verification' => '',
        'organization' => [
            'name' => 'Beta Official',
            'legal_name' => '',
            'logo' => '/assets/images/logo-header.png',
            'phone' => '+1 (555) 000-0000',
            'address' => 'San Francisco, CA, United States',
        ],
    ],

    // -------------------------------------------------- landing composition
    // Order and visibility of the landing page sections. The frontend walks
    // this array in order, so re-ordering here re-orders the page.
    'home_sections' => [
        'sections' => [
            ['key' => 'hero', 'enabled' => true],
            ['key' => 'features', 'enabled' => true],
            ['key' => 'categories', 'enabled' => true],
            ['key' => 'promo', 'enabled' => true],
            ['key' => 'featured_products', 'enabled' => true],
            ['key' => 'brands', 'enabled' => true],
            ['key' => 'latest_products', 'enabled' => true],
            ['key' => 'about', 'enabled' => true],
            ['key' => 'testimonials', 'enabled' => true],
            ['key' => 'articles', 'enabled' => true],
            ['key' => 'newsletter', 'enabled' => true],
            ['key' => 'contact', 'enabled' => true],
        ],
    ],

    // ---------------------------------------------------------------- theme
    // Defaults mirror the tokens in app/assets/css/main.css; changing them
    // here changes the live site without a rebuild.
    'theme' => [
        'primary' => '#007F58',
        'accent' => '#BF5702',
        'radius' => 'sm',
        'default_mode' => 'light',
        'container' => 'wide',
    ],
];
