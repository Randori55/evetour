<?php
require __DIR__.'/../includes/functions.php';
admin_required();
$a=$pdo->query('SELECT * FROM about WHERE id=1')->fetch();
$msg='';

if(isset($_GET['delete_main'])){
 try{delete_stored_image($a['image']??'');$pdo->prepare('UPDATE about SET image=? WHERE id=1')->execute(['']);header('Location:about.php#images');exit;}
 catch(Throwable $e){$msg=$e->getMessage();}
}
if(isset($_GET['delete_image'])){
 $id=(int)$_GET['delete_image'];
 $st=$pdo->prepare('SELECT image FROM hero_images WHERE id=?');$st->execute([$id]);$path=$st->fetchColumn();
 try{
  if($path!==false)delete_stored_image($path);
  $pdo->prepare('DELETE FROM hero_images WHERE id=?')->execute([$id]);
  header('Location:about.php#images');exit;
 }catch(Throwable $e){$msg=$e->getMessage();}
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['reorder_images'])){
 try{
  $pdo->beginTransaction();
  $update=$pdo->prepare('UPDATE hero_images SET sort_order=? WHERE id=?');
  foreach(array_values(array_unique(array_map('intval',$_POST['image_order']??[]))) as $position=>$id)$update->execute([$position+1,$id]);
  $pdo->commit();header('Content-Type: application/json');echo json_encode(['ok'=>true]);exit;
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(500);header('Content-Type: application/json');echo json_encode(['ok'=>false]);exit;}
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['add_images'])){
 try{
  $files=$_FILES['hero_uploads']??null;
  if(!$files||!is_array($files['name']??null))throw new RuntimeException('Select at least one image.');
  $added=0;
  foreach($files['name'] as $i=>$name){
   if($name==='')continue;
   $maxOrder=(int)$pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM hero_images')->fetchColumn();
   $pdo->prepare('INSERT INTO hero_images(image,sort_order) VALUES(?,?)')->execute(['',$maxOrder]);
   $id=(int)$pdo->lastInsertId();
   try{
    $image=save_uploaded_image(['name'=>$name,'type'=>$files['type'][$i]??'','tmp_name'=>$files['tmp_name'][$i]??'','error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$i]??0],'hero',$id);
    $pdo->prepare('UPDATE hero_images SET image=? WHERE id=?')->execute([$image,$id]);$added++;
   }catch(Throwable $e){$pdo->prepare('DELETE FROM hero_images WHERE id=?')->execute([$id]);throw $e;}
  }
  header('Location:about.php?added='.$added.'#images');exit;
 }catch(Throwable $e){$msg=$e->getMessage();}
}

if($_SERVER['REQUEST_METHOD']==='POST'&&!isset($_POST['reorder_images'])&&!isset($_POST['add_images'])){
 try{
  $pdo->prepare('UPDATE about SET title=?,body=?,button_text=?,button_url=? WHERE id=1')->execute([$_POST['title'],$_POST['body'],$_POST['button_text'],$_POST['button_url']]);
  save_setting('hero_eyebrow',trim($_POST['eyebrow']??''));
  $a=$pdo->query('SELECT * FROM about WHERE id=1')->fetch();$msg='All changes saved.';
 }catch(Throwable $e){$msg=$e->getMessage();}
}
$heroImages=$pdo->query('SELECT * FROM hero_images WHERE image<>\'\' ORDER BY sort_order,id')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Homepage hero</title><link rel="stylesheet" href="admin.css"><style>
.image-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:18px}.image-card{position:relative;background:#fff;border:1px solid #e5e9e8;border-radius:12px;padding:10px;cursor:grab;box-shadow:0 5px 20px #12354a10}.image-card.dragging{opacity:.45}.image-card img{display:block;width:100%;height:210px;object-fit:cover;border-radius:8px;pointer-events:none}.image-card .delete-image{display:inline-block;margin-top:10px}.image-card.drag-over{outline:2px solid #0d756d}
</style></head><body>
<div class="top"><div class="wrap"><b>Homepage hero</b><a href="index.php" style="color:#fff">&larr; Admin</a></div></div>
<div class="wrap" style="padding:30px 0">
<form method="post"><div class="card"><h2>Homepage content</h2><?php if($msg):?><p class="err"><?=e($msg)?></p><?php endif;?>
<label>Small heading</label><input name="eyebrow" value="<?=e(setting('hero_eyebrow','PRIVATE TRAVEL ? LOCAL EXPERIENCES'))?>">
<label>Main heading</label><input name="title" value="<?=e($a['title'])?>" required>
<label>Description</label><textarea name="body" rows="7"><?=e($a['body'])?></textarea>
<label>Button text</label><input name="button_text" value="<?=e($a['button_text'])?>">
<label>Button link</label><input name="button_url" value="<?=e($a['button_url'])?>">
<button class="button">Save changes</button></div></form>
<section class="card" id="images"><h2>Hero images</h2>
<form method="post" enctype="multipart/form-data"><input type="file" name="hero_uploads[]" accept="image/jpeg,image/png,image/webp" multiple required><button class="button" type="submit" name="add_images" value="1">Add</button></form>
<form id="image-order-form" method="post"><input type="hidden" name="reorder_images" value="1"><div class="image-gallery" id="image-gallery">
<?php if(!empty($a['image'])):?><article class="image-card" draggable="false"><img src="<?=e(site_image($a['image']))?>" alt="Main hero image"><a class="button danger delete-image" onclick="return confirm('Delete this image?')" href="?delete_main=1">Delete</a></article><?php endif;?>
<?php foreach($heroImages as $x):?><article class="image-card" draggable="true" data-id="<?=$x['id']?>"><input type="hidden" name="image_order[]" value="<?=$x['id']?>"><img src="<?=e(site_image($x['image']))?>" alt="Hero image"><a class="button danger delete-image" onclick="return confirm('Delete this image?')" href="?delete_image=<?=$x['id']?>">Delete</a></article><?php endforeach;?>
</div></form></section></div>
<script>
const gallery=document.getElementById('image-gallery'),orderForm=document.getElementById('image-order-form');let moving=null;
gallery?.addEventListener('dragstart',e=>{moving=e.target.closest('.image-card');if(moving){moving.classList.add('dragging');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',moving.dataset.id)}});
gallery?.addEventListener('dragend',()=>{gallery.querySelectorAll('.image-card').forEach(card=>card.classList.remove('dragging','drag-over'));moving=null});
gallery?.addEventListener('dragover',e=>{e.preventDefault();const target=e.target.closest('.image-card');if(!moving||!target||target===moving)return;const r=target.getBoundingClientRect(),after=(e.clientY-r.top)>r.height/2;if(after)target.after(moving);else target.before(moving)});
gallery?.addEventListener('drop',async e=>{e.preventDefault();if(!moving)return;const fields=new FormData(orderForm);try{const response=await fetch(location.pathname,{method:'POST',body:fields,headers:{'X-Requested-With':'fetch'}});if(!response.ok)throw new Error();}catch(_){location.reload()}});
</script></body></html>
