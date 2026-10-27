<?php require 'lib.php';$u=need('ADMIN');$u=top('Audit Logs');echo '<p class="sub">Read-only record of staff actions.</p><div class="card tw"><table><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr>';
foreach(q('SELECT a.*,u.name actor FROM audit_logs a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.id DESC LIMIT 200') as $r)echo '<tr><td>'.h($r['created_at']).'</td><td>'.h($r['actor']).'</td><td>'.h($r['action']).'</td><td>'.h($r['entity'].' #'.$r['entity_id']).'</td><td>'.h($r['detail']).'</td></tr>';
echo '</table></div>';bottom();
