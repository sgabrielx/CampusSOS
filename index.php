<?php require 'lib.php';$u=top('Dashboard');
$n=fn($w)=>(int)q("SELECT COUNT(*) c FROM cases WHERE $w")->fetch()['c'];
$open="status NOT IN('RESOLVED','CLOSED','REJECTED','CANCELLED')";
$cards=[['Total SOS alerts',$n("type='SOS'"),'cases.php?type=SOS'],['Pending SOS alerts',$n("type='SOS' AND status='RECEIVED'"),'cases.php?type=SOS&status=RECEIVED'],['Active cases',$n($open),'cases.php?type=INC'],['Unassigned open cases',$n("officer_id IS NULL AND $open"),'cases.php?type=INC&assigned=none'],['Resolved',$n("status IN('RESOLVED','CLOSED')"),'cases.php?type=INC&status=RESOLVED'],['Pending cancellations',(int)q("SELECT COUNT(*) c FROM cancellations WHERE status='PENDING'")->fetch()['c'],'cancellations.php']];
echo '<p class="sub">Overview of alerts and reports. Demo data only.</p><div class="cards">';
foreach($cards as $c)echo '<a class="card stat" href="'.$c[2].'"><div class="n">'.$c[1].'</div>'.$c[0].'</a>';
echo '</div><div class="card"><h2>Recent cases</h2><div class="tw"><table><tr><th>ID</th><th>Type</th><th>Location</th><th>Time</th><th>Status</th><th>Officer</th></tr>';
foreach(q('SELECT c.*,u.name officer FROM cases c LEFT JOIN users u ON u.id=c.officer_id ORDER BY c.created_at DESC LIMIT 8') as $r)
 echo '<tr><td><a href="case.php?id='.$r['id'].'">'.h($r['code']).'</a></td><td>'.$r['type'].'</td><td>'.h($r['location']).'</td><td>'.h($r['created_at']).'</td><td>'.pill($r['status']).'</td><td>'.h($r['officer']?:'Unassigned').'</td></tr>';
echo '</table></div></div><div class="card"><h2>Cases by category</h2>';
foreach(q('SELECT category,COUNT(*) c FROM cases GROUP BY category ORDER BY c DESC') as $r)echo '<div class="bar"><span>'.h($r['category']).'</span><i style="width:'.($r['c']*40).'px"></i> '.$r['c'].'</div>';
echo '</div>';bottom();
