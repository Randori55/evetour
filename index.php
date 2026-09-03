<?php
require __DIR__.'/includes/functions.php';
$about=$pdo->query('SELECT * FROM about WHERE id=1')->fetch();
$heroPositionX=max(0,min(100,(int)setting('hero_position_x','50'))); $heroPositionY=max(0,min(100,(int)setting('hero_position_y','50')));
$heroSlides=[];
if($about['image']) $heroSlides[]=['image'=>$about['image'],'x'=>$heroPositionX,'y'=>$heroPositionY];
foreach($pdo->query('SELECT id,image FROM hero_images ORDER BY sort_order,id')->fetchAll() as $heroImage) $heroSlides[]=['image'=>$heroImage['image'],'x'=>max(0,min(100,(int)setting('hero_image_'.$heroImage['id'].'_position_x','50'))),'y'=>max(0,min(100,(int)setting('hero_image_'.$heroImage['id'].'_position_y','50')))];
if(!$heroSlides) $heroSlides[]=['image'=>'assets/img/placeholder.svg','x'=>50,'y'=>50];
$tours=$pdo->query('SELECT * FROM tours ORDER BY sort_order,id LIMIT 8')->fetchAll(); $reviews=$pdo->query('SELECT * FROM reviews ORDER BY sort_order,id LIMIT 6')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(setting('site_name'))?></title><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/hero-full-width.css"></head><body>
<header><div class="nav"><a class="logo" href="#"><?=e(setting('site_name'))?></a><nav><a href="#about">About us</a><a href="#tours">Tours</a><a href="#reviews">Reviews</a><a href="#contact">Contact</a></nav><a class="navbtn" href="#contact">Book now</a></div></header><main>
<section id="about" class="hero"><div class="hero-image" id="hero-image" style="background-image:url('<?=e($heroSlides[0]['image'])?>');background-position:<?=$heroSlides[0]['x']?>% <?=$heroSlides[0]['y']?>%"></div><div class="hero-overlay"></div><div class="hero-content"><span class="eyebrow"><?=e(setting('hero_eyebrow','PRIVATE TRAVEL • LOCAL EXPERIENCES'))?></span><h1><?=e($about['title'])?></h1><p><?=nl2br(e($about['body']))?></p><a class="btn" href="<?=e($about['button_url'])?>"><?=e($about['button_text'])?> →</a></div></section>
<section id="tours" class="section"><div class="section-title"><span>Tours</span><h2>Choose your experience</h2></div><div class="tour-grid"><?php foreach($tours as $index=>$t): ?><article class="tour<?=$index===0 ? ' first-tour' : ''?>"><div class="tour-img" style="background-image:url('<?=e($t['image'] ?: 'assets/img/placeholder.svg')?>')"></div><div class="tour-body"><h3><?=e($t['title'])?></h3><p><?=e($t['description'])?></p><?php if($t['price']):?><b><?=e($t['price'])?></b><?php endif;?></div></article><?php endforeach;?></div></section>
<section id="reviews" class="section reviews"><div class="section-title"><span>Feedbacks - our reviews</span><h2>What our guests say</h2></div><div class="review-grid"><?php foreach($reviews as $r): ?><article class="review"><div class="avatar" style="background-image:url('<?=e($r['image'] ?: 'assets/img/avatar.svg')?>')"></div><div><div class="stars"><?=str_repeat('★',max(1,min(5,(int)$r['rating'])))?></div><p>“<?=e($r['review'])?>”</p><strong><?=e($r['name'])?></strong><small><?=e($r['location'])?></small></div></article><?php endforeach;?></div></section>
<section id="contact" class="contact"><div><span class="eyebrow">CONTACT</span><h2>Let's plan your next trip.</h2><p>Send us your wishes and we'll prepare a personal proposal.</p></div><form method="post" action="contact.php"><input name="name" placeholder="Your name" required><input name="email" type="email" placeholder="Email" required><textarea name="message" rows="5" placeholder="Tell us about your trip" required></textarea><button class="btn">Send request</button></form></section></main><footer><div><?=e(setting('site_name'))?></div><div><?=e(setting('email'))?> · <?=e(setting('phone'))?></div></footer>
<?php if(count($heroSlides)>1):?><script>const heroSlides=<?=json_encode(array_values($heroSlides),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;let heroImageIndex=0;setInterval(()=>{heroImageIndex=(heroImageIndex+1)%heroSlides.length;const slide=heroSlides[heroImageIndex],hero=document.getElementById('hero-image');hero.style.backgroundImage=`url("${slide.image}")`;hero.style.backgroundPosition=`${slide.x}% ${slide.y}%`;},3000);</script><?php endif;?>

    <!-- digər HTML kodları -->

   
<script>
document.addEventListener('DOMContentLoaded', function () {

    const tours = document.querySelectorAll('.tour');

    tours.forEach(function (tour) {

        tour.addEventListener('click', function (event) {
            event.stopPropagation();

            // Digər kartları bağla
            tours.forEach(function (card) {
                if (card !== tour) {
                    card.classList.remove('is-expanded');
                }
            });

            // Kliklənən kartı aç/bağla
            tour.classList.toggle('is-expanded');
        });

    });

    // Kartdan kənarda istənilən yerə klik
    document.addEventListener('click', function () {
        tours.forEach(function (tour) {
            tour.classList.remove('is-expanded');
        });
    });

    // ESC ilə də bağlamaq mümkün olsun
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            tours.forEach(function (tour) {
                tour.classList.remove('is-expanded');
            });
        }
    });

});
</script>
</body>
</html>


