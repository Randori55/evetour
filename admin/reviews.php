<?php
require __DIR__.'/../includes/functions.php';
admin_required();

$edit=null;
$error='';
if(isset($_GET['edit'])){
  $st=$pdo->prepare('SELECT * FROM reviews WHERE id=?');
  $st->execute([(int)$_GET['edit']]);
  $edit=$st->fetch();
}
if(isset($_GET['delete'])){
  $pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([(int)$_GET['delete']]);
  header('Location:reviews.php');
  exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $old=$id?$pdo->query('SELECT image FROM reviews WHERE id='.$id)->fetchColumn():null;
  try{
    if(!$id && empty($_FILES['image']['name'])) throw new Exception('Please choose a review image.');
    $image=upload_image('image',$old);
    if($id){
      $pdo->prepare('UPDATE reviews SET image=?,sort_order=? WHERE id=?')->execute([$image,(int)$_POST['sort_order'],$id]);
    }else{
      $pdo->prepare('INSERT INTO reviews(name,location,review,rating,image,sort_order) VALUES(?,?,?,?,?,?)')->execute(['Review','','',5,$image,(int)$_POST['sort_order']]);
    }
    header('Location:reviews.php');
    exit;
  }catch(Exception $exception){
    $error=$exception->getMessage();
  }
}
$items=$pdo->query('SELECT * FROM reviews ORDER BY sort_order,id')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Reviews</title><link rel="stylesheet" href="admin.css"></head><body>
<div class="top"><div class="wrap"><b>Review images</b><a href="index.php" style="color:#fff">← Admin</a></div></div>
<div class="wrap" style="padding:30px 0"><div class="card"><h2><?= $edit?'Edit':'Add' ?> review image</h2><?php if($error):?><p class="err"><?=e($error)?></p><?php endif;?><form method="post" enctype="multipart/form-data"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>"><input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?=$edit?'':'required'?>><p class="muted">JPG, PNG or WebP. The image will appear automatically in the Reviews slider.</p><input name="sort_order" type="number" value="<?=e($edit['sort_order']??1)?>" placeholder="Sort order"><button class="button">Save image</button></form></div><div class="grid"><?php foreach($items as $x):?><div class="card"><?php if($x['image']):?><img class="thumb" src="../<?=e($x['image'])?>" alt="Review image"><?php endif;?><p class="muted">Order: <?=e($x['sort_order'])?></p><a class="button" href="?edit=<?=$x['id']?>">Edit</a> <a class="button danger" onclick="return confirm('Delete?')" href="?delete=<?=$x['id']?>">Delete</a></div><?php endforeach;?></div></div>
</body></html>
