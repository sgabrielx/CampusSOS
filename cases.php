<?php require 'lib.php';$type=($_GET['type']??'SOS')==='INC'?'INC':'SOS';$u=top($type==='SOS'?'SOS Alerts':'Incident Reports');
$w=['c.type=?'];$a=[$type];
if(($s=$_GET['status']??'')!==''){$w[]='c.status=?';$a[]=$s;}
if(($_GET['assigned']??'')==='none')$w[]='c.officer_id IS NULL';
if(($k=trim($_GET['q']??''))!==''){$w[]='(c.code LIKE ? OR c.description LIKE ? OR c.location LIKE ?)';array_push($a,"%$k%","%$k%","%$k%");}
$rows=q('SELECT c.*,u.name officer FROM cases c LEFT JOIN users u ON u.id=c.officer_id WHERE '.implode(' AND ',$w).' ORDER BY (c.status=\'RECEIVED\') DESC,c.created_at DESC',$a)->fetchAll();
echo '<form class="tool"><input type="hidden" name="type" value="'.$type.'"><input name="q" placeholder="Search ID, description, location" value="'.h($k).'"><select name="status"><option value="">All statuses</option>';
foreach(array_keys(TR[$type]) as $x)echo '<option'.($s===$x?' selected':'').'>'.$x.'</option>';
echo '</select><select name="assigned"><option value="">All assignments</option><option value="none"'.(($_GET['assigned']??'')==='none'?' selected':'').'>Unassigned</option></select><button class="btn">Filter</button></form><div class="card tw"><table><tr><th>ID</th><th>Priority</th><th>Category</th><th>Location</th><th>Received</th><th>Status</th><th>Officer</th></tr>';
foreach($rows as $r)echo '<tr><td><a href="case.php?id='.$r['id'].'">'.h($r['code']).'</a></td><td>'.pill($r['priority']).'</td><td>'.h($r['category']).'</td><td>'.h($r['location']).'</td><td>'.h($r['created_at']).'</td><td>'.pill($r['status']).'</td><td>'.h($r['officer']?:'Unassigned').'</td></tr>';
if(!$rows)echo '<tr><td colspan="7" class="empty">No records match these filters.</td></tr>';
echo '</table></div>';bottom();
