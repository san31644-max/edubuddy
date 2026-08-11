<?php
$allowed=[
 'subjects'=>['name_en','name_si','name_ta','subject_code','icon'],
 'units'=>['name_en','name_si','name_ta','unit_number','subject_id'],
 'lessons'=>['title_en','title_si','title_ta','medium','subject_id','unit_id'],
 'quizzes'=>['title_en','title_si','title_ta','subject_id','pass_mark']
];
if(!isset($allowed[$entity]))exit('Invalid catalog.');
require_once __DIR__.'/../includes/auth.php';require_admin();
$grades=db()->query("SELECT id,grade_number FROM grades WHERE status='active' ORDER BY grade_number")->fetch_all(MYSQLI_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);$action=$_POST['action']??'';
 if($id&&in_array($action,['active','inactive'],true)){$s=db()->prepare("UPDATE $entity SET status=? WHERE id=?");$s->bind_param('si',$action,$id);$s->execute();flash('success','Status updated.');redirect('admin/'.$entity.'.php');}
 if($action==='add'){
  $gradeId=filter_input(INPUT_POST,'grade_id',FILTER_VALIDATE_INT);
  if(!$gradeId||!query_one("SELECT id FROM grades WHERE id=? AND status='active'",'i',[$gradeId])){flash('error','Choose a valid grade.');redirect('admin/'.$entity.'.php');}
  $data=[];foreach($allowed[$entity] as $field)$data[$field]=trim((string)($_POST[$field]??''));
  if($entity==='lessons'&&!in_array($data['medium'],['Sinhala','Tamil','English','All'],true)){flash('error','Use Sinhala, Tamil, English or All as the lesson medium.');redirect('admin/lessons.php');}
  if(isset($data['subject_id'])&&!query_one('SELECT id FROM subjects WHERE id=? AND grade_id=?','ii',[(int)$data['subject_id'],$gradeId])){flash('error','The selected subject does not belong to this grade.');redirect('admin/'.$entity.'.php');}
  if(isset($data['unit_id'])&&!query_one('SELECT id FROM units WHERE id=? AND grade_id=? AND subject_id=?','iii',[(int)$data['unit_id'],$gradeId,(int)$data['subject_id']])){flash('error','The selected unit does not belong to this grade and subject.');redirect('admin/'.$entity.'.php');}
  $data['grade_id']=$gradeId;if($entity==='lessons')$data['content_source']='textbook';
  $fields=array_keys($data);$marks=implode(',',array_fill(0,count($fields),'?'));$s=db()->prepare("INSERT INTO $entity(".implode(',',$fields).") VALUES($marks)");$types='';$values=[];
  foreach($data as $field=>$value){$integer=in_array($field,['grade_id','unit_number','subject_id','unit_id','pass_mark'],true);$types.=$integer?'i':'s';$values[]=$integer?(int)$value:$value;}$s->bind_param($types,...$values);
  $saved=$s->execute();flash($saved?'success':'error',$saved?'Item added to the selected grade.':'Could not add item. Check its grade and relationships.');redirect('admin/'.$entity.'.php');
 }
}
$pageTitle=ucfirst($entity);include __DIR__.'/_top.php';$rows=db()->query("SELECT x.*,g.grade_number FROM $entity x JOIN grades g ON g.id=x.grade_id ORDER BY g.grade_number DESC,x.id DESC");
?>
<nav class="row" style="margin-bottom:16px"><a class="btn alt" href="subjects.php">Subjects</a><a class="btn alt" href="units.php">Units</a><a class="btn alt" href="lessons.php">Lessons</a><a class="btn" href="lesson_editor.php">Edit lesson content &amp; preview book</a></nav>
<h1><?=e(ucfirst($entity))?></h1>
<section class="card"><h2>Add <?=e(rtrim($entity,'s'))?></h2><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add"><label>Grade<select name="grade_id" required><option value="">Choose grade</option><?php foreach($grades as $g):?><option value="<?=$g['id']?>">Grade <?=$g['grade_number']?></option><?php endforeach;?></select></label><?php foreach($allowed[$entity] as $field):?><label><?=e(ucwords(str_replace('_',' ',$field)))?><input name="<?=e($field)?>"<?=$field==='medium'?' placeholder="Sinhala, Tamil, English or All"':''?> required></label><?php endforeach;?><button>Add</button></form></section>
<section class="card scroll"><table><tr><th>ID</th><th>Grade</th><th>Name/title</th><th>Status</th><th>Action</th></tr><?php while($row=$rows->fetch_assoc()):?><tr><td><?=$row['id']?></td><td>Grade <?=$row['grade_number']?></td><td><?=e($row['name_en']??$row['title_en']??'')?></td><td><?=e($row['status'])?></td><td><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['id']?>"><button class="<?=$row['status']==='active'?'danger':'good'?>" name="action" value="<?=$row['status']==='active'?'inactive':'active'?>"><?=$row['status']==='active'?'Deactivate':'Activate'?></button></form></td></tr><?php endwhile;?></table></section>
<?php if($entity==='quizzes'):?><section class="card"><p>Import multilingual quiz questions through the Assessments workspace.</p></section><?php endif;include __DIR__.'/../includes/footer.php';?>
