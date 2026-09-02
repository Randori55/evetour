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
  $image=upload_image('image',$a['image']);
  $pdo->prepare('UPDATE about SET title=?,body=?,button_text=?,button_url=?,image=? WHERE id=1')->execute([$_POST['title'],$_POST['body'],$_POST['button_text'],$_POST['button_url'],$image]);
  save_setting('hero_eyebrow',trim($_POST['eyebrow'] ?? ''));
  foreach(upload_images('hero_images') as $position=>$extra){
   $pdo->prepare('INSERT INTO hero_images(image,sort_order) VALUES(?,?)')->execute([$extra,$position]);
  }
  $a=$pdo->query('SELECT * FROM about WHERE id=1')->fetch(); $msg='Saved.';
 }catch(Throwable $e){$msg=$e->getMessage();}
}
$heroImages=$pdo->query('SELECT * FROM hero_images ORDER BY sort_order,id')->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Homepage hero</title><link rel="stylesheet" href="admin.css"></head>
<body><div class="top"><div class="wrap"><b>Homepage hero</b><a href="index.php" style="color:#fff">&larr; Admin</a></div></div>
<div class="wrap" style="padding:30px 0"><div class="card">
<h2>Discover your next journey</h2><p class="muted">Change every text in this homepage section and upload its background image here.</p>
<?php if($msg):?><p><?=e($msg)?></p><?php endif;?>
<form method="post" enctype="multipart/form-data">
 <label>Small heading</label><input name="eyebrow" value="<?=e(setting('hero_eyebrow','PRIVATE TRAVEL • LOCAL EXPERIENCES'))?>">
 <label>Main heading</label><input name="title" value="<?=e($a['title'])?>" required>
 <label>Description</label><textarea name="body" rows="7"><?=e($a['body'])?></textarea>
 <label>Button text</label><input name="button_text" value="<?=e($a['button_text'])?>">
 <label>Button link</label><input name="button_url" value="<?=e($a['button_url'])?>">
 <label>Background image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp">
 <p class="muted">Upload JPG, PNG or WebP. The image will appear behind this section.</p>
 <?php if($a['image']):?><p><img class="thumb" src="../<?=e($a['image'])?>"></p><?php endif;?>
 <label>Additional rotating background images (optional)</label><input type="file" name="hero_images[]" accept="image/jpeg,image/png,image/webp" multiple>
 <p class="muted">Select several images at once or add more later.</p><button class="button">Save changes</button>
</form></div>
<?php if($heroImages):?><div class="grid"><?php foreach($heroImages as $x):?><div class="card"><img class="thumb" src="../<?=e($x['image'])?>"><p><a class="button danger" onclick="return confirm('Delete this image?')" href="?delete_image=<?=$x['id']?>">Delete</a></p></div><?php endforeach;?></div><?php endif;?>
</div></body></html>
