<?php
require __DIR__.'/../includes/functions.php';
admin_required();
$edit=null;$msg='';
if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM tours WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
if(isset($_GET['delete'])){$id=(int)$_GET['delete'];$st=$pdo->prepare('SELECT image FROM tours WHERE id=?');$st->execute([$id]);$path=$st->fetchColumn();if($path!==false)delete_stored_image($path);$st=$pdo->prepare('DELETE FROM tours WHERE id=?');$st->execute([$id]);header('Location:tours.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $id=(int)($_POST['id']??0);$old=$id?($pdo->query('SELECT image FROM tours WHERE id='.$id)->fetchColumn()):null;
  if($id){
   $image=upload_image('image',$old,'tours',$id);
   $pdo->prepare('UPDATE tours SET title=?,description=?,price=?,image=?,sort_order=? WHERE id=?')->execute([$_POST['title'],$_POST['description'],$_POST['price'],$image,(int)$_POST['sort_order'],$id]);
  }else{
   $pdo->prepare('INSERT INTO tours(title,description,price,image,sort_order) VALUES(?,?,?,?,?)')->execute([$_POST['title'],$_POST['description'],$_POST['price'],'',(int)$_POST['sort_order']]);
   $id=(int)$pdo->lastInsertId();$image=upload_image('image',null,'tours',$id);
   if($image)$pdo->prepare('UPDATE tours SET image=? WHERE id=?')->execute([$image,$id]);
  }
  header('Location:tours.php');exit;
 }catch(Throwable $e){$msg=$e->getMessage();}
}
$items=$pdo->query('SELECT * FROM tours ORDER BY sort_order,id')->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><title>Tours</title><link rel="stylesheet" href="admin.css"></head><body><div class="top"><div class="wrap"><b>Tours</b><a href="index.php" style="color:#fff">? Admin</a></div></div><div class="wrap" style="padding:30px 0"><div class="card"><h2><?= $edit?'Edit':'Add' ?> tour</h2><?php if($msg):?><p class="err"><?=e($msg)?></p><?php endif;?><form method="post" enctype="multipart/form-data"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>"><input name="title" placeholder="Tour title" value="<?=e($edit['title']??'')?>" required><textarea name="description" placeholder="Description" rows="4"><?=e($edit['description']??'')?></textarea><input name="price" placeholder="Price" value="<?=e($edit['price']??'')?>"><input name="sort_order" type="number" value="<?=e($edit['sort_order']??1)?>"><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><?php if(!empty($edit['image'])):?><img class="thumb" src="<?=e(site_image($edit['image']))?>"><?php endif;?><button class="button">Save</button></form></div><div class="grid"><?php foreach($items as $x):?><div class="card"><?php if($x['image']):?><img class="thumb" src="<?=e(site_image($x['image']))?>"><?php endif;?><h3><?=e($x['title'])?></h3><p class="muted"><?=e($x['price'])?></p><a class="button" href="?edit=<?=$x['id']?>">Edit</a> <a class="button danger" onclick="return confirm('Delete?')" href="?delete=<?=$x['id']?>">Delete</a></div><?php endforeach;?></div></div></body></html>
