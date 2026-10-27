<?php require 'lib.php';$u=need();$id=(int)($_GET['id']??0);
$c=q('SELECT c.*,o.name officer FROM cases c LEFT JOIN users o ON o.id=c.officer_id WHERE c.id=?',[$id])->fetch();if(!$c){http_response_code(404);exit('Case not found.');}
$mine=$c['officer_id']==$u['id'];
function setstatus($c,$u,$to,$why){db()->beginTransaction();q('UPDATE cases SET status=? WHERE id=?',[$to,$c['id']]);q('INSERT INTO status_history(case_id,actor_id,from_status,to_status,reason,created_at) VALUES(?,?,?,?,?,?)',[$c['id'],$u['id'],$c['status'],$to,$why,now()]);audit($u,"Status {$c['status']} -> $to",'CASE',$c['id'],$why);db()->commit();}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$act=$_POST['act']??'';
 if($act==='status'){$to=$_POST['to']??'';$ok=in_array($to,TR[$c['type']][$c['status']]);
  $allowed=$u['role']==='ADMIN'||$mine||($c['type']==='SOS'&&$c['status']==='RECEIVED'&&$to==='ACKNOWLEDGED');
  if(!$ok)flash('That status change is not allowed.');elseif(!$allowed)flash('Only the assigned officer or an administrator can update this case.');
  else{setstatus($c,$u,$to,trim($_POST['why']??''));flash('Status updated.');}}
 if($act==='assign'&&$u['role']==='ADMIN'){$o=q("SELECT * FROM users WHERE id=? AND role='SECURITY' AND active=1",[(int)$_POST['officer']])->fetch();
  if(!$o)flash('Choose an active security officer.');else{q('UPDATE cases SET officer_id=? WHERE id=?',[$o['id'],$id]);audit($u,'Assigned','CASE',$id,($c['officer']?:'none').' -> '.$o['name']);
   $c2=$c;if(in_array($c['status'],['ACKNOWLEDGED','UNDER_REVIEW']))setstatus($c,$u,'ASSIGNED','Assigned to '.$o['name']);flash('Assigned to '.$o['name'].'.');}}
 header('Location: case.php?id='.$id);exit;}
$u=top('Case '.$c['code']);$next=TR[$c['type']][$c['status']];
echo '<div class="card"><dl><dt>Status</dt><dd>'.pill($c['status']).' '.pill($c['priority']).'</dd><dt>Category</dt><dd>'.h($c['category']).'</dd><dt>Description</dt><dd>'.h($c['description']).'</dd><dt>Campus location</dt><dd>'.h($c['location']).'</dd><dt>GPS</dt><dd>'.($c['lat']!==null?h($c['lat']).', '.h($c['lng']):'Location unavailable').'</dd><dt>Reporter</dt><dd>'.h($c['reporter']).' (demo)</dd><dt>Received</dt><dd>'.h($c['created_at']).'</dd><dt>Officer</dt><dd>'.h($c['officer']?:'Unassigned').'</dd></dl>';
if($next)echo '<form method="post" class="tool">'.field().'<input type="hidden" name="act" value="status"><select name="to">'.implode('',array_map(fn($x)=>"<option>$x</option>",$next)).'</select><input name="why" placeholder="Reason (optional)"><button class="btn">Update status</button></form>';
if($u['role']==='ADMIN'){echo '<form method="post" class="tool">'.field().'<input type="hidden" name="act" value="assign"><select name="officer">';
 $os=q("SELECT * FROM users WHERE role='SECURITY' AND active=1")->fetchAll();foreach($os as $o)echo '<option value="'.$o['id'].'"'.($c['officer_id']==$o['id']?' selected':'').'>'.h($o['name']).'</option>';
 echo '</select><button class="btn o"'.($os?'':' disabled').'>'.($c['officer_id']?'Reassign':'Assign').'</button></form>';}
echo '<h2>Status history</h2>';
foreach(q('SELECT s.*,u.name actor FROM status_history s LEFT JOIN users u ON u.id=s.actor_id WHERE case_id=? ORDER BY s.id DESC',[$id]) as $h)echo '<div class="tl"><b>'.h($h['to_status']).'</b> – '.h($h['actor']?:'Mobile app').', '.h($h['created_at']).($h['reason']?'<br>'.h($h['reason']):'').'</div>';
echo '</div>';bottom();
