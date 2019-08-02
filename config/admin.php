<?php

return [
    'expert' => [6, 231, 25569, 135128, 25568, 57597, 135137, 135132, 135140, 135141, 135136, 135139],

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'public_key' => env('PUSHER_KEY'),
        'secret_key' => env('PUSHER_SECRET')
    ],

    'package' => [
        1 => ['limit' => 2, 'minute' => 90, 'max' => 1440, 'color' => '#BFB1E0'],
        2 => ['limit' => 2, 'minute' => 90, 'max' => 1440, 'color' => '#A1D7D9'],
        3 => ['limit' => 5, 'minute' => 30, 'max' => 1440, 'color' => '#E6B0E4'],
        4 => ['limit' => 10, 'minute' => 10, 'max' => 1440, 'color' => '#F2C6CB'],
        5 => [
            'limit' => 10,
            'minute' => 10,
            'max' => 1440,
            'color' => '#BFB1E0',
            'phone_number' => '01884552370',
            'title_en' => 'প্রেসক্রিপশন ও ৩০ মিনিটে উত্তর পেতে নিন “মায়া প্রেসক্রিপশন” প্যাকেজ',
            'title_bn' => 'প্রেসক্রিপশন ও ৩০ মিনিটে উত্তর পেতে নিন “মায়া প্রেসক্রিপশন” প্যাকেজ',
            'subtitle_en' => 'আপনার প্রিমিয়াম কোটা শেষ!!
দ্রুত উত্তর পেতে পছন্দমত প্যাকেজ কিনুন!',
            'subtitle_bn' => 'আপনার প্রিমিয়াম কোটা শেষ!!
দ্রুত উত্তর পেতে পছন্দমত প্যাকেজ কিনুন!',
            'average_time' => "30"
        ],
        6 => [
            'limit' => 2,
            'minute' => 30,
            'max' => 1440,
            'color' => '#A1D7D9',
            'phone_number' => '01884552370',
            'average_time' => "10 - 20"
        ],
        7 => [
            'limit' => 5,
            'minute' => 30,
            'max' => 1440,
            'color' => '#BFB1E0',
            'phone_number' => '01884552370',
            'average_time' => "10"
        ],
        8 => [
            'limit' => 2,
            'minute' => 30,
            'max' => 1440,
            'color' => '#A1D7D9',
            'phone_number' => '01884552370',
            'average_time' => "10 - 20"
        ],
        9 => [
            'limit' => 2,
            'minute' => 90,
            'max' => 1440,
            'title_en' => '৯০ মিনিটেই উত্তর পেতে নিন “মায়া সিলভার” প্যাকেজ।',
            'title_bn' => '৯০ মিনিটেই উত্তর পেতে নিন “মায়া সিলভার” প্যাকেজ।',
            'subtitle_en' => 'আপনার প্রিমিয়াম কোটা শেষ!!
দ্রুত উত্তর পেতে পছন্দমত প্যাকেজ কিনুন!',
            'subtitle_bn' => 'আপনার প্রিমিয়াম কোটা শেষ!!
দ্রুত উত্তর পেতে পছন্দমত প্যাকেজ কিনুন!',
            'color' => '#A1D7D9'
        ]
    ],

    'prePostTags' => [
        ['id' => 5, 'name' => 'Menstruation'],
        ['id' => 72, 'name' => 'Pregnancy'],
        ['id' => 75, 'name' => 'Emergency Contraceptive Pill'],
        ['id' => 76, 'name' => 'Weaning Food']
    ]
];
