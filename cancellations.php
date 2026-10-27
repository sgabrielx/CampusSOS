<?php require 'lib.php';$u=need();
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$r=q('SELECT x.*,c.type,c.status cs FROM cancellations x JOIN cases c ON c.id=x.case_id WHERE x.id=? AND x.status=\'PENDING\'',[(int)$_POST['id']])->fetch();
 $d=($_POST['d']??'')==='APPROVED'?'APPROVED':'REJECTED';$note=trim($_POST['note']??'');
 if(!$r)flash('Request not found or already decided.');elseif($r['type']==='SOS'&&$note==='')flash('A written reason is required for SOS withdrawals.');
 else{db()->beginTransaction();q('UPDATE cancellations SET status=?,decided_by=?,decision_note=? WHERE id=?',[$d,$u['id'],$note,$r['id']]);
  if($d==='APPROVED'&&!in_array($r['cs'],['CLOSED','CANCELLED','REJECTED'])){$to=$r['type']==='SOS'?'CLOSED':'REJECTED';q('UPDATE cases SET status=? WHERE id=?',[$to,$r['case_id']]);q('INSERT INTO status_history(case_id,actor_id,from_status,to_status,reason,created_at) VALUES(?,?,?,?,?,?)',[$r['case_id'],$u['id'],$r['cs'],$to,'Cancellation approved: '.$note,now()]);}
  audit($u,'Cancellation '.$d,'CANCELLATION',$r['id'],$note);db()->commit();flash('Decision saved. The original record is kept.');}
 header('Location: cancellations.php');exit;}
$u=top('Cancellation Requests');echo '<p class="sub">Approving never deletes a record. SOS withdrawals need a written reason.</p><div class="card tw"><table><tr><th>ID</th><th>Case</th><th>Requester</th><th>Reason</th><th>Case status</th><th>Request</th><th>Decision</th></tr>';
foreach(q('SELECT x.*,c.code,c.status cs FROM cancellations x JOIN cases c ON c.id=x.case_id ORDER BY x.id DESC') as $r){
 echo '<tr><td>'.$r['id'].'</td><td><a href="case.php?id='.$r['case_id'].'">'.h($r['code']).'</a></td><td>'.h($r['requester']).'</td><td>'.h($r['reason']).'</td><td>'.pill($r['cs']).'</td><td>'.pill($r['status']).'</td><td>';
 echo $r['status']==='PENDING'?'<form method="post" class="tool">'.field().'<input type="hidden" name="id" value="'.$r['id'].'"><input name="note" placeholder="Reason"><button name="d" value="APPROVED" class="btn">Approve</button><button name="d" value="REJECTED" class="btn o">Reject</button></form>':h($r['decision_note']);echo '</td></tr>';}
echo '</table></div>';bottom();
