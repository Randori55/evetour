<?php
/** Public interface translations. English and Russian only. */

function supported_locales(): array
{
    return [
        'en' => 'EN',
        'ru' => 'RU',
    ];
}

function current_locale(): string
{
    static $locale = null;

    if ($locale !== null) {
        return $locale;
    }

    $requested = strtolower(
        (string)($_GET['lang'] ?? $_COOKIE['evetour_lang'] ?? 'en')
    );

    $locale = array_key_exists($requested, supported_locales())
        ? $requested
        : 'en';

    return $locale;
}

function tr(string $key, ?string $fallback = null): string
{
    static $translations = [
        'en' => [
            'about' => 'About us',
            'tours' => 'Tours',
            'reviews' => 'Reviews',
            'contact' => 'Contact',
            'book_now' => 'Book now',
            'tour_label' => 'Explore Azerbaijan',
            'choose_experience' => 'Choose your experience',
            'review_label' => 'Guest stories',
            'what_guests_say' => 'What our guests say',
            'contact_label' => 'Plan with us',
            'plan_trip' => "Let's plan your next trip.",
            'contact_intro' => "Share your wishes and we'll prepare a personal proposal.",
            'your_name' => 'Your name',
            'phone' => 'Phone number',
            'trip_wishes' => 'Tell us about your trip',
            'send_request' => 'Send request',
            'previous_review' => 'Previous review',
            'next_review' => 'Next review',
        ],

        'ru' => [
            'about' => 'О нас',
            'tours' => 'Туры',
            'reviews' => 'Отзывы',
            'contact' => 'Контакты',
            'book_now' => 'Забронировать',
            'tour_label' => 'Откройте Азербайджан',
            'choose_experience' => 'Выберите своё путешествие',
            'review_label' => 'Истории гостей',
            'what_guests_say' => 'Что говорят наши гости',
            'contact_label' => 'Планируйте с нами',
            'plan_trip' => 'Давайте спланируем ваше следующее путешествие.',
            'contact_intro' => 'Расскажите о ваших пожеланиях — мы подготовим персональное предложение.',
            'your_name' => 'Ваше имя',
            'phone' => 'Номер телефона',
            'trip_wishes' => 'Расскажите о вашей поездке',
            'send_request' => 'Отправить заявку',
            'previous_review' => 'Предыдущий отзыв',
            'next_review' => 'Следующий отзыв',
        ],
    ];

    $locale = current_locale();

    return $translations[$locale][$key]
        ?? $translations['en'][$key]
        ?? $fallback
        ?? $key;
}
