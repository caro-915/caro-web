<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    |
    | Contact details displayed on the website.
    |
    */

    'contact_email' => env('AUTODZ_CONTACT_EMAIL', 'contact@elsayara.com'),
    'contact_phone' => env('AUTODZ_CONTACT_PHONE', null),

    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    |
    | URLs to your social media profiles. Leave null to hide the icon.
    |
    */

    'social_facebook' => env('AUTODZ_SOCIAL_FACEBOOK', null),
    'social_instagram' => env('AUTODZ_SOCIAL_INSTAGRAM', null),
    'social_twitter' => env('AUTODZ_SOCIAL_TWITTER', null),
    'social_tiktok' => env('AUTODZ_SOCIAL_TIKTOK', null),

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Default SEO settings for the website.
    |
    */

    'site_name' => env('AUTODZ_SITE_NAME', 'ElSayara'),
    'default_og_image' => env('AUTODZ_OG_IMAGE', '/images/og-default.jpg'),

];
