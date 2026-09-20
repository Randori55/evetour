<?php
require __DIR__.'/includes/functions.php';
$locale=current_locale();

$about=$pdo->query('SELECT * FROM about WHERE id=1')->fetch();

$aboutTitle = $locale === 'ru'
    ? ($about['title_ru'] ?: $about['title_en'] ?: $about['title'])
    : ($about['title_en'] ?: $about['title']);

$aboutBody = $locale === 'ru'
    ? ($about['body_ru'] ?: $about['body_en'] ?: $about['body'])
    : ($about['body_en'] ?: $about['body']);

$aboutButton = $locale === 'ru'
    ? ($about['button_text_ru'] ?: $about['button_text_en'] ?: $about['button_text'])
    : ($about['button_text_en'] ?: $about['button_text']);
$heroPositionX=max(0,min(100,(int)setting('hero_position_x','50'))); $heroPositionY=max(0,min(100,(int)setting('hero_position_y','50')));
$heroSlides=[];
if($about['image']) $heroSlides[]=['image'=>$about['image'],'x'=>$heroPositionX,'y'=>$heroPositionY];
foreach($pdo->query('SELECT id,image FROM hero_images ORDER BY sort_order,id')->fetchAll() as $heroImage) $heroSlides[]=['image'=>$heroImage['image'],'x'=>max(0,min(100,(int)setting('hero_image_'.$heroImage['id'].'_position_x','50'))),'y'=>max(0,min(100,(int)setting('hero_image_'.$heroImage['id'].'_position_y','50')))];
if(!$heroSlides) $heroSlides[]=['image'=>'assets/img/placeholder.svg','x'=>50,'y'=>50];

$tours=$pdo->query('SELECT * FROM tours ORDER BY sort_order,id LIMIT 8')->fetchAll();

foreach($tours as &$tour){
    $tour['display_title'] = $locale === 'ru'
        ? ($tour['title_ru'] ?: $tour['title_en'] ?: $tour['title'])
        : ($tour['title_en'] ?: $tour['title']);

    $tour['display_description'] = $locale === 'ru'
        ? ($tour['description_ru'] ?: $tour['description_en'] ?: $tour['description'])
        : ($tour['description_en'] ?: $tour['description']);
}
unset($tour);

$reviews=$pdo->query('SELECT * FROM reviews ORDER BY sort_order,id')->fetchAll();

?>
<!doctype html><html lang="<?=e($locale)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(setting('site_name'))?></title><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/hero-full-width.css"><link rel="stylesheet" href="assets/css/reviews-slider.css"></head><body>
<header><div class="nav"><a class="logo" href="#about"><span class="logo-mark">E</span><?=e(setting('site_name'))?></a><nav><a href="#about"><?=e(tr('about'))?></a><a href="#tours"><?=e(tr('tours'))?></a><a href="#reviews"><?=e(tr('reviews'))?></a><a href="#contact"><?=e(tr('contact'))?></a></nav><div class="nav-actions"><div class="language-switcher"><?php foreach(supported_locales() as $code=>$label):?><a class="<?=$locale===$code?'active':''?>" href="?lang=<?=e($code)?>"><?=e($label)?></a><?php endforeach;?></div><a class="navbtn" href="#contact"><?=e(tr('book_now'))?></a></div></div></header><main>

    <section id="about" class="hero"><div class="hero-image" id="hero-image" style="background-image:url('<?=e($heroSlides[0]['image'])?>');background-position:<?=$heroSlides[0]['x']?>% <?=$heroSlides[0]['y']?>%"></div><div class="hero-overlay"></div><div class="hero-content"><span class="eyebrow"><?=e(setting('hero_eyebrow','PRIVATE TRAVEL • LOCAL EXPERIENCES'))?></span>
    <h1><?=e($aboutTitle)?></h1>
<p><?=nl2br(e($aboutBody))?></p>
<a class="btn" href="<?=e($about['button_url'])?>"><?=e($aboutButton)?> →</a>
</div></section>
<section id="tours" class="section"><div class="section-title"><span><?=e(tr('tour_label'))?></span><h2><?=e(tr('choose_experience'))?></h2></div><div class="tour-grid"><?php foreach($tours as $index=>$t): ?><article class="tour<?=$index===0 ? ' first-tour' : ''?>"><div class="tour-img" style="background-image:url('<?=e($t['image'] ?: 'assets/img/placeholder.svg')?>')"></div><div class="tour-body">
    <h3><?=e($t['display_title'])?></h3>
<p><?=e($t['display_description'])?></p>
    <?php if($t['price']):?><b><?=e($t['price'])?></b><?php endif;?></div></article><?php endforeach;?></div></section>
<section id="reviews" class="section reviews"><div class="section-title"><span>Feedbacks - our reviews</span><h2>What our guests say</h2></div><div class="reviews-slider"><button class="review-arrow review-arrow-prev" type="button" aria-label="Previous review">←</button><div class="review-viewport"><div class="review-track"><?php foreach($reviews as $r): ?><article class="review"><img src="<?=e($r['image'] ?: 'assets/img/avatar.svg')?>" alt="Review image" loading="lazy"></article><?php endforeach;?></div></div><button class="review-arrow review-arrow-next" type="button" aria-label="Next review">→</button></div></section>
<section id="contact" class="contact"><div><span class="eyebrow">CONTACT</span><h2>Let's plan your next trip.</h2><p>Send us your wishes and we'll prepare a personal proposal.</p></div><form method="post" action="contact.php"><input name="name" placeholder="Your name" required><input name="email" type="email" placeholder="Email" required><textarea name="message" rows="5" placeholder="Tell us about your trip" required></textarea><button class="btn">Send request</button></form></section></main><footer><div><?=e(setting('site_name'))?></div><div><?=e(setting('email'))?> · <?=e(setting('phone'))?></div></footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ui = <?=json_encode(['tour_label'=>tr('tour_label'),'choose_experience'=>tr('choose_experience'),'review_label'=>tr('review_label'),'what_guests_say'=>tr('what_guests_say'),'contact_label'=>tr('contact_label'),'plan_trip'=>tr('plan_trip'),'contact_intro'=>tr('contact_intro'),'your_name'=>tr('your_name'),'phone'=>tr('phone'),'trip_wishes'=>tr('trip_wishes'),'send_request'=>tr('send_request')],JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
    const text = (selector, value) => { const node=document.querySelector(selector); if(node) node.textContent=value; };
    text('#tours .section-title span',ui.tour_label); text('#tours .section-title h2',ui.choose_experience);
    text('#reviews .section-title span',ui.review_label); text('#reviews .section-title h2',ui.what_guests_say);
    text('#contact .eyebrow',ui.contact_label); text('#contact h2',ui.plan_trip); text('#contact p',ui.contact_intro);
    const contactForm=document.querySelector('#contact form');
    if(contactForm){
        const nameField=contactForm.querySelector('[name="name"]'), oldField=contactForm.querySelector('[name="email"]'), messageField=contactForm.querySelector('[name="message"]');
        nameField.placeholder=ui.your_name; messageField.placeholder=ui.trip_wishes; contactForm.querySelector('button').firstChild.nodeValue=ui.send_request+' ';
        const language=document.createElement('input'); language.type='hidden'; language.name='lang'; language.value='<?=e($locale)?>'; contactForm.prepend(language);
        oldField.name='phone'; oldField.type='tel'; oldField.inputMode='tel'; oldField.autocomplete='tel'; oldField.placeholder=ui.phone;
        const wrap=document.createElement('div'); wrap.className='phone-field'; const codes=[['AZ','994'],['TR','90'],['RU','7'],['GB','44'],['DE','49'],['AE','971'],['US','1']];
        const select=document.createElement('select'); select.setAttribute('aria-label','Country code'); codes.forEach(([country,code])=>{const option=new Option(country+' +'+code,code); option.dataset.country=country; select.add(option);});
        let region='AZ'; try { region=(new Intl.Locale(navigator.language).region || region).toUpperCase(); } catch(e) {} const timezone=Intl.DateTimeFormat().resolvedOptions().timeZone; if(timezone==='Asia/Baku') region='AZ'; const match=[...select.options].find(option=>option.dataset.country===region); if(match) select.value=match.value;
        oldField.value='+'+select.value+' '; select.addEventListener('change',()=>{const digits=oldField.value.replace(/^\+?\d+\s*/, '');oldField.value='+'+select.value+' '+digits;});
        oldField.parentNode.insertBefore(wrap,oldField); wrap.append(select,oldField);
    }

    /* =========================
       TOUR CARDS
       ========================= */

    const tours = [...document.querySelectorAll('.tour')];

    document.addEventListener('click', function (event) {

        if (tours.some(tour => tour.classList.contains('is-expanded'))) {

            tours.forEach(tour => {
                tour.classList.remove('is-expanded');
            });

            event.stopImmediatePropagation();
        }

    }, true);

    tours.forEach(function (tour) {

        tour.addEventListener('click', function () {

            tours.forEach(function (card) {
                card.classList.remove('is-expanded');
            });

            tour.classList.add('is-expanded');

        });

    });

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            tours.forEach(function (tour) {
                tour.classList.remove('is-expanded');
            });

        }

    });


    /* =========================
       REVIEWS INFINITE CAROUSEL
       ========================= */

    const viewport = document.querySelector('.review-viewport');
    const track = document.querySelector('.review-track');
    const previous = document.querySelector('.review-arrow-prev');
    const next = document.querySelector('.review-arrow-next');

    if (!viewport || !track) {
        return;
    }

    /* Original şəkilləri götürürük */

    const originalCards = [...track.querySelectorAll('.review')];

    if (originalCards.length <= 1) {
        return;
    }


    /* Şəkillərin kopyalarını yaradırıq.
       Beləliklə sonsuz karusel effekti alınır. */

    originalCards.forEach(function (card) {

        const clone = card.cloneNode(true);

        clone.setAttribute('aria-hidden', 'true');

        track.appendChild(clone);

    });


    /* =========================
       AUTO MOVEMENT
       ========================= */

    let position = 0;

    let isDragging = false;

    let startX = 0;
    let startPosition = 0;

    let animationFrame;

    /* Sürət.
       Kiçik rəqəm = daha yavaş
       Böyük rəqəm = daha sürətli */

    const speed = 1.05;


    function getCardWidth() {

        const card = track.querySelector('.review');

        if (!card) {
            return 0;
        }

        const gap =
            parseFloat(getComputedStyle(track).gap) || 0;

        return card.getBoundingClientRect().width + gap;

    }


    function getLoopWidth() {

        return getCardWidth() * originalCards.length;

    }


    function render() {

        track.style.transform =
            `translate3d(${-position}px, 0, 0)`;

    }


    function animate() {

        if (!isDragging) {

            position += speed;

            const loopWidth = getLoopWidth();

            if (position >= loopWidth) {
                position -= loopWidth;
            }

            render();

        }

        animationFrame =
            requestAnimationFrame(animate);

    }


    /* Başlat */

    animate();


    /* =========================
       MOUSE DRAG
       ========================= */

    viewport.addEventListener('mousedown', function (event) {

        isDragging = true;

        startX = event.clientX;

        startPosition = position;

        viewport.classList.add('is-dragging');

    });


    document.addEventListener('mousemove', function (event) {

        if (!isDragging) {
            return;
        }

        const distance =
            startX - event.clientX;

        position = startPosition + distance;

        const loopWidth = getLoopWidth();

        while (position < 0) {
            position += loopWidth;
        }

        while (position >= loopWidth) {
            position -= loopWidth;
        }

        render();

    });


    document.addEventListener('mouseup', function () {

        if (!isDragging) {
            return;
        }

        isDragging = false;

        viewport.classList.remove('is-dragging');

    });


    /* =========================
       TOUCH / MOBILE
       ========================= */

    viewport.addEventListener(
        'touchstart',
        function (event) {

            isDragging = true;

            startX =
                event.touches[0].clientX;

            startPosition = position;

        },
        { passive: true }
    );


    viewport.addEventListener(
        'touchmove',
        function (event) {

            if (!isDragging) {
                return;
            }

            const distance =
                startX -
                event.touches[0].clientX;

            position =
                startPosition + distance;

            const loopWidth = getLoopWidth();

            while (position < 0) {
                position += loopWidth;
            }

            while (position >= loopWidth) {
                position -= loopWidth;
            }

            render();

        },
        { passive: true }
    );


    viewport.addEventListener(
        'touchend',
        function () {

            isDragging = false;

        }
    );


    /* =========================
       ARROWS
       ========================= */

    if (previous) {

        previous.addEventListener(
            'click',
            function () {

                position -= getCardWidth();

                const loopWidth = getLoopWidth();

                while (position < 0) {
                    position += loopWidth;
                }

                render();

            }
        );

    }


    if (next) {

        next.addEventListener(
            'click',
            function () {

                position += getCardWidth();

                const loopWidth = getLoopWidth();

                if (position >= loopWidth) {
                    position -= loopWidth;
                }

                render();

            }
        );

    }


    /* =========================
       RESIZE
       ========================= */

    window.addEventListener('resize', function () {

        const loopWidth = getLoopWidth();

        if (loopWidth > 0 && position >= loopWidth) {
            position %= loopWidth;
        }

        render();

    });

});
</script>


<?php if(count($heroSlides)>1):?><script>const heroSlides=<?=json_encode(array_values($heroSlides),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;let heroImageIndex=0;setInterval(()=>{heroImageIndex=(heroImageIndex+1)%heroSlides.length;const slide=heroSlides[heroImageIndex],hero=document.getElementById('hero-image');hero.style.backgroundImage=`url("${slide.image}")`;hero.style.backgroundPosition=`${slide.x}% ${slide.y}%`;},3000);</script><?php endif;?>
</body></html>
