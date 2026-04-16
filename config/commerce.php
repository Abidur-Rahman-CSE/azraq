<?php

return [
    'product_types' => [
        'standard',
        'light_customizable',
        'advanced_personalized',
        'bundle',
        'service',
    ],
    'homepage_sections' => [
        'announcement_bar',
        'hero',
        'featured_categories',
        'signature_nikah',
        'best_sellers',
        'combo_spotlight',
        'trust_badges',
        'testimonials',
        'booking_cta',
        'faq_preview',
    ],
    'storefront' => [
        'nav' => [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Shop', 'href' => '/shop'],
            ['label' => 'Nikah Collection', 'href' => '/categories/nikah-collection'],
            ['label' => 'Bridal Wear', 'href' => '/categories/customized-bridal-wear'],
            ['label' => 'Accessories', 'href' => '/categories/bridal-accessories'],
            ['label' => 'Packages / Combos', 'href' => '/categories/packages-combos'],
            ['label' => 'Mehendi & Event', 'href' => '/categories/mehendi-event'],
            ['label' => 'Bookings', 'href' => '/shop?type=service'],
            ['label' => 'About', 'href' => '/pages/about'],
        ],
        'mobile_nav_groups' => [
            [
                'label' => 'Browse',
                'items' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'All Products', 'href' => '/shop'],
                    ['label' => 'Best Sellers', 'href' => '/collections/best-sellers'],
                ],
            ],
            [
                'label' => 'Collections',
                'items' => [
                    ['label' => 'Nikah Collection', 'href' => '/categories/nikah-collection'],
                    ['label' => 'Bridal Wear', 'href' => '/categories/customized-bridal-wear'],
                    ['label' => 'Accessories', 'href' => '/categories/bridal-accessories'],
                    ['label' => 'Packages / Combos', 'href' => '/categories/packages-combos'],
                    ['label' => 'Mehendi & Event', 'href' => '/categories/mehendi-event'],
                ],
            ],
            [
                'label' => 'Support',
                'items' => [
                    ['label' => 'Wishlist', 'href' => '/wishlist'],
                    ['label' => 'Track Order', 'href' => '/track-order'],
                    ['label' => 'FAQ', 'href' => '/faq'],
                    ['label' => 'Contact', 'href' => '/pages/contact'],
                ],
            ],
        ],
        'featured_categories' => [
            [
                'title' => 'Customized Bridal Wear',
                'description' => 'Dupattas and statement bridal pieces designed for gifting and ceremony styling.',
                'href' => '#',
            ],
            [
                'title' => 'Nikah Collection',
                'description' => 'Nikah Nama, premium certificates, booklets, and companion keepsakes.',
                'href' => '#',
            ],
            [
                'title' => 'Bridal Accessories',
                'description' => 'Mirror sets, badges, tassel pillows, and curated add-ons for the full look.',
                'href' => '#',
            ],
            [
                'title' => 'Mehendi & Event',
                'description' => 'Organic mehendi and booking-led offerings for bridal and non-bridal events.',
                'href' => '#',
            ],
        ],
        'featured_products' => [
            [
                'name' => 'Signature Nikah Nama',
                'type' => 'Advanced Personalized',
                'price' => 'From BDT 2,500',
                'description' => 'Template-driven personalization with premium typography and proof-ready layouts.',
            ],
            [
                'name' => 'Customized Pen',
                'type' => 'Light Customizable',
                'price' => 'From BDT 650',
                'description' => 'A clean add-on gift with a simple text personalization flow.',
            ],
            [
                'name' => 'Bridal Mirror / Ayna',
                'type' => 'Standard Product',
                'price' => 'From BDT 1,400',
                'description' => 'Elegant handcrafted finishing for rituals, photography, and gifting.',
            ],
        ],
        'trust_points' => [
            'Structured product types for clean operations',
            'Reusable storefront blocks for fast iteration',
            'Nikah Nama flow isolated from standard products',
        ],
    ],
];
