<?php
require __DIR__.'/../includes/functions.php';
admin_required();
$a=$pdo->query('SELECT * FROM about WHERE id=1')->fetch();
$msg='';
if(isset($_GET['delete_image'])){
 $pdo->prepare('DELETE FROM hero_images WHERE id=?')->execute([(int)$_GET['delete_image']]);
 header('Location:about.php'); exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $uploads=upload_images('hero_uploads');
  $image=$a['image'];
  if(!$image && $uploads) $image=array_shift($uploads);
  $pdo->prepare('UPDATE about SET title=?,body=?,button_text=?,button_url=?,image=? WHERE id=1')->execute([$_POST['title'],$_POST['body'],$_POST['button_text'],$_POST['button_url'],$image]);
  save_setting('hero_eyebrow',trim($_POST['eyebrow'] ?? ''));
  save_setting('hero_position_x',(string)max(0,min(100,(int)($_POST['hero_main_position_x'] ?? 50))));
  save_setting('hero_position_y',(string)max(0,min(100,(int)($_POST['hero_main_position_y'] ?? 50))));
  foreach($uploads as $position=>$extra) $pdo->prepare('INSERT INTO hero_images(image,sort_order) VALUES(?,?)')->execute([$extra,$position]);
  foreach($pdo->query('SELECT id FROM hero_images')->fetchAll() as $row){
   $id=(int)$row['id'];
   save_setting('hero_image_'.$id.'_position_x',(string)max(0,min(100,(int)(($_POST['hero_position_x'][$id] ?? 50)))));
   save_setting('hero_image_'.$id.'_position_y',(string)max(0,min(100,(int)(($_POST['hero_position_y'][$id] ?? 50)))));
  }
  $a=$pdo->query('SELECT * FROM about WHERE id=1')->fetch(); $msg='All changes saved.';
 }catch(Throwable $e){$msg=$e->getMessage();}
}
$heroImages=$pdo->query('SELECT * FROM hero_images ORDER BY sort_order,id')->fetchAll();
$mainX=max(0,min(100,(int)setting('hero_position_x','50'))); $mainY=max(0,min(100,(int)setting('hero_position_y','50')));
?>
<!doctype html><html><head><meta charset="utf-8"><title>Homepage hero</title><link rel="stylesheet" href="admin.css"><style>#hero-position-preview,.hero-position-preview{width:100%;height:auto;aspect-ratio:1160/560;background-color:#dfe5e1;background-size:cover;background-repeat:no-repeat;cursor:grab;position:relative;touch-action:none}.preview-hint{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);padding:10px 14px;background:#17221dcc;color:#fff;font-size:12px;text-align:center;pointer-events:none}#hero-position-preview.dragging,.hero-position-preview.dragging{cursor:grabbing}.image-gallery{display:grid;grid-template-columns:1fr;gap:20px}.image-gallery .card{margin:0}@media(min-width:800px){.image-gallery{grid-template-columns:repeat(2,1fr)}}</style></head>
<body><div class="top"><div class="wrap"><b>Homepage hero</b><a href="index.php" style="color:#fff">&larr; Admin</a></div></div>
<div class="wrap" style="padding:30px 0"><form method="post" enctype="multipart/form-data"><div class="card">
<h2>Discover your next journey</h2><p class="muted">Upload and position all hero images below. Drag each preview, then save everything with one button.</p>
<?php if($msg):?><p><?=e($msg)?></p><?php endif;?>
 <label>Small heading</label><input name="eyebrow" value="<?=e(setting('hero_eyebrow','PRIVATE TRAVEL • LOCAL EXPERIENCES'))?>">
 <label>Main heading</label><input name="title" value="<?=e($a['title'])?>" required>
 <label>Description</label><textarea name="body" rows="7"><?=e($a['body'])?></textarea>
 <label>Button text</label><input name="button_text" value="<?=e($a['button_text'])?>">
 <label>Button link</label><input name="button_url" value="<?=e($a['button_url'])?>">
 <label>Upload background images</label><input id="hero-upload" type="file" name="hero_uploads[]" accept="image/jpeg,image/png,image/webp" multiple>
 <p class="muted">Select all images in one place. They will appear in the gallery below after saving.</p>
</div><div class="card"><h2>Images and positions</h2><p class="muted">This preview uses the same cover behavior as the homepage. Drag each image to the exact visible position you want.</p><div class="image-gallery">
 <div class="card"><label>Main background image</label><input type="hidden" name="hero_main_position_x" id="main-x" value="<?=$mainX?>"><input type="hidden" name="hero_main_position_y" id="main-y" value="<?=$mainY?>"><div id="hero-position-preview" style="background-image:url('../<?=e($a['image'] ?: 'assets/img/placeholder.svg')?>');background-position:<?=$mainX?>% <?=$mainY?>%"><span class="preview-hint">Drag to reposition</span></div></div>
 <?php foreach($heroImages as $x):$positionX=max(0,min(100,(int)setting('hero_image_'.$x['id'].'_position_x','50')));$positionY=max(0,min(100,(int)setting('hero_image_'.$x['id'].'_position_y','50')));?><div class="card"><label>Background image #<?=$x['id']?></label><input type="hidden" class="slide-x" name="hero_position_x[<?=$x['id']?>]" value="<?=$positionX?>"><input type="hidden" class="slide-y" name="hero_position_y[<?=$x['id']?>]" value="<?=$positionY?>"><div class="hero-position-preview slide-preview" style="background-image:url('../<?=e($x['image'])?>');background-position:<?=$positionX?>% <?=$positionY?>%"><span class="preview-hint">Drag to reposition</span></div><p><a class="button danger" onclick="return confirm('Delete this image?')" href="?delete_image=<?=$x['id']?>">Delete image</a></p></div><?php endforeach;?>
 </div><button class="button" style="margin-top:20px">Save all changes</button></div></form></div>
<script>const clamp=v=>Math.max(0,Math.min(100,v));function enableDrag(preview,xInput,yInput){let dragging=false,startX=0,startY=0,originX=50,originY=50;function move(event){const rect=preview.getBoundingClientRect(),x=clamp(originX-(event.clientX-startX)*100/rect.width),y=clamp(originY-(event.clientY-startY)*100/rect.height);xInput.value=Math.round(x);yInput.value=Math.round(y);preview.style.backgroundPosition=x+'% '+y+'%'}preview.addEventListener('pointerdown',event=>{dragging=true;startX=event.clientX;startY=event.clientY;originX=Number(xInput.value)||50;originY=Number(yInput.value)||50;preview.classList.add('dragging');preview.setPointerCapture(event.pointerId)});preview.addEventListener('pointermove',event=>{if(dragging)move(event)});['pointerup','pointercancel'].forEach(name=>preview.addEventListener(name,()=>{dragging=false;preview.classList.remove('dragging')}))}enableDrag(document.getElementById('hero-position-preview'),document.getElementById('main-x'),document.getElementById('main-y'));document.querySelectorAll('.slide-preview').forEach(preview=>enableDrag(preview,preview.parentElement.querySelector('.slide-x'),preview.parentElement.querySelector('.slide-y')));</script></body></html>
