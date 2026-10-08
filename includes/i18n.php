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
            'hero_eyebrow_default' => 'PRIVATE TRAVEL - LOCAL EXPERIENCES',
            'hero_title_default' => 'Welcome to EVE tour!',
            'hero_body_default' => 'Create memorable private tours and experiences with a local travel team.',
            'hero_button_default' => 'Discover more',
            'hero_azerbaijan' => 'Explore Azerbaijan',
            'your_name' => 'Your name',
            'your_message' => 'Your message',
            'phone' => 'Phone number',
            'trip_wishes' => 'Tell us about your trip',
            'send_request' => 'Send',
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
            'hero_eyebrow_default' => "\u{418}\u{41d}\u{414}\u{418}\u{412}\u{418}\u{414}\u{423}\u{410}\u{41b}\u{42c}\u{41d}\u{42b}\u{415}\u{20}\u{422}\u{423}\u{420}\u{42b}\u{20}\u{b7}\u{20}\u{41c}\u{415}\u{421}\u{422}\u{41d}\u{42b}\u{415}\u{20}\u{412}\u{41f}\u{415}\u{427}\u{410}\u{422}\u{41b}\u{415}\u{41d}\u{418}\u{42f}",
            'hero_title_default' => "\u{414}\u{43e}\u{431}\u{440}\u{43e}\u{20}\u{43f}\u{43e}\u{436}\u{430}\u{43b}\u{43e}\u{432}\u{430}\u{442}\u{44c}\u{20}\u{432}\u{20}EVE TOUR!",
            'hero_body_default' => "\u{41e}\u{442}\u{43a}\u{440}\u{43e}\u{439}\u{442}\u{435}\u{20}\u{434}\u{43b}\u{44f}\u{20}\u{441}\u{435}\u{431}\u{44f}\u{20}\u{43d}\u{435}\u{437}\u{430}\u{431}\u{44b}\u{432}\u{430}\u{435}\u{43c}\u{44b}\u{435}\u{20}\u{438}\u{43d}\u{434}\u{438}\u{432}\u{438}\u{434}\u{443}\u{430}\u{43b}\u{44c}\u{43d}\u{44b}\u{435}\u{20}\u{442}\u{443}\u{440}\u{44b}\u{20}\u{438}\u{20}\u{432}\u{43f}\u{435}\u{447}\u{430}\u{442}\u{43b}\u{435}\u{43d}\u{438}\u{44f}\u{20}\u{432}\u{43c}\u{435}\u{441}\u{442}\u{435}\u{20}\u{441}\u{20}\u{43c}\u{435}\u{441}\u{442}\u{43d}\u{43e}\u{439}\u{20}\u{442}\u{443}\u{440}\u{438}\u{441}\u{442}\u{438}\u{447}\u{435}\u{441}\u{43a}\u{43e}\u{439}\u{20}\u{43a}\u{43e}\u{43c}\u{430}\u{43d}\u{434}\u{43e}\u{439}\u{2e}",
            'hero_button_default' => "\u{423}\u{437}\u{43d}\u{430}\u{442}\u{44c}\u{20}\u{431}\u{43e}\u{43b}\u{44c}\u{448}\u{435}",
            'hero_azerbaijan' => "\u{41e}\u{442}\u{43a}\u{440}\u{43e}\u{439}\u{442}\u{435}\u{20}\u{410}\u{437}\u{435}\u{440}\u{431}\u{430}\u{439}\u{434}\u{436}\u{430}\u{43d}",
            'your_name' => 'Ваше имя',
            'your_message' => 'Ваше сообщение',
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
