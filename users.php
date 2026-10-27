<?php require 'lib.php';$u=need('ADMIN');
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$t=q('SELECT * FROM users WHERE id=?',[(int)$_POST['id']])->fetch();
 if($t){$act=$_POST['act']??'';$admins=(int)q("SELECT COUNT(*) c FROM users WHERE role='ADMIN' AND active=1")->fetch()['c'];
  if($act==='toggle'){if($t['active']&&$t['id']==$u['id'])flash('You cannot deactivate your own account.');elseif($t['active']&&$t['role']==='ADMIN'&&$admins<2)flash('At least one active administrator is required.');
   else{q('UPDATE users SET active=? WHERE id=?',[$t['active']?0:1,$t['id']]);audit($u,$t['active']?'Deactivated':'Activated','USER',$t['id'],$t['email']);flash('Account updated.');}}
  if($act==='role'){$to=$t['role']==='ADMIN'?'SECURITY':'ADMIN';if($t['role']==='ADMIN'&&$admins<2)flash('At least one active administrator is required.');else{q('UPDATE users SET role=? WHERE id=?',[$to,$t['id']]);audit($u,'Role change','USER',$t['id'],$t['role'].' -> '.$to);flash('Role updated.');}}}
 header('Location: users.php');exit;}
$u=top('User Management');echo '<div class="card tw"><table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr>';
foreach(q('SELECT id,name,email,role,active,created_at FROM users') as $r)echo '<tr><td>'.$r['id'].'</td><td>'.h($r['name']).'</td><td>'.h($r['email']).'</td><td>'.h($r['role']).'</td><td>'.($r['active']?'Active':'Disabled').'</td><td>'.h($r['created_at']).'</td><td><form method="post" class="tool">'.field().'<input type="hidden" name="id" value="'.$r['id'].'"><button name="act" value="toggle" class="btn o">'.($r['active']?'Deactivate':'Activate').'</button><button name="act" value="role" class="btn o" onclick="return confirm(\'Change this user\\\'s role?\')">Change role</button></form></td></tr>';
echo '</table></div>';bottom();
