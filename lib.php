<?php
// Shared helpers: database, auth, CSRF, layout. PHP 8+ with PDO SQLite (enabled by default in XAMPP).
session_start(['cookie_httponly'=>true,'cookie_samesite'=>'Lax']);
date_default_timezone_set('Asia/Manila');
const TR=['SOS'=>['RECEIVED'=>['ACKNOWLEDGED'],'ACKNOWLEDGED'=>['ASSIGNED','IN_PROGRESS'],'ASSIGNED'=>['IN_PROGRESS'],'IN_PROGRESS'=>['RESOLVED'],'RESOLVED'=>['CLOSED'],'CLOSED'=>[]],
 'INC'=>['SUBMITTED'=>['UNDER_REVIEW','REJECTED'],'UNDER_REVIEW'=>['ASSIGNED','REJECTED'],'ASSIGNED'=>['IN_PROGRESS'],'IN_PROGRESS'=>['RESOLVED'],'RESOLVED'=>['CLOSED','IN_PROGRESS'],'CLOSED'=>[],'REJECTED'=>[]]];
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function now(){return date('Y-m-d H:i:s');}
function db(){static $p;if($p)return $p;
 $f=__DIR__.'/data/campussos.sqlite';$new=!file_exists($f);@mkdir(__DIR__.'/data');
 file_put_contents(__DIR__.'/data/.htaccess',"Require all denied\n");
 // To use MySQL instead: new PDO('mysql:host=localhost;dbname=campussos;charset=utf8mb4','user','pass')
 $p=new PDO('sqlite:'.$f);$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$p->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);$p->exec('PRAGMA foreign_keys=ON');
 if($new)install($p);return $p;}
function q($sql,$a=[]){$s=db()->prepare($sql);$s->execute($a);return $s;}
function install($p){$p->exec(file_get_contents(__DIR__.'/schema.sql'));
 $ph=password_hash('demo1234',PASSWORD_DEFAULT);$n=now();
 foreach([['Demo Admin','admin@demo.campussos','ADMIN'],['Officer Reyes (demo)','reyes@demo.campussos','SECURITY'],['Officer Tan (demo)','tan@demo.campussos','SECURITY']] as $u)
  $p->prepare('INSERT INTO users(name,email,pass_hash,role,created_at) VALUES(?,?,?,?,?)')->execute([$u[0],$u[1],$ph,$u[2],$n]);
 $c=[['S-101','SOS','Medical','Person collapsed','Building A, 2nd floor','CRITICAL','RECEIVED',null,0.2],['S-102','SOS','Security','Suspicious person near parking','Parking Area 1','HIGH','ACKNOWLEDGED',null,1.5],['S-103','SOS','Fire','Fire alarm triggered','Building C, Room 204','CRITICAL','IN_PROGRESS',2,6],
 ['R-201','INC','Theft','Wallet missing from locker','Gym Lockers','NORMAL','SUBMITTED',null,3],['R-202','INC','Harassment','Verbal harassment reported','Library entrance','HIGH','UNDER_REVIEW',null,20],['R-203','INC','Facility hazard','Broken handrail on ramp','Building B ramp','NORMAL','ASSIGNED',3,26],['R-204','INC','Theft','Phone stolen','Library','NORMAL','RESOLVED',2,70]];
 foreach($c as $i=>$r){$t=date('Y-m-d H:i:s',time()-(int)($r[8]*3600));
  $p->prepare('INSERT INTO cases(code,type,category,description,location,priority,status,officer_id,reporter,created_at) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],'Demo User '.($i+1),$t]);
  $p->prepare('INSERT INTO status_history(case_id,actor_id,from_status,to_status,reason,created_at) VALUES(?,?,?,?,?,?)')->execute([$i+1,null,null,$r[1]=='SOS'?'RECEIVED':'SUBMITTED','Created by mobile app (demo)',$t]);}
 $p->prepare('INSERT INTO cancellations(case_id,requester,reason,created_at) VALUES(?,?,?,?)')->execute([2,'Demo User 2','Situation resolved on its own',now()]);
 $p->prepare('INSERT INTO cancellations(case_id,requester,reason,created_at) VALUES(?,?,?,?)')->execute([4,'Demo User 4','Found my wallet',now()]);}
function me(){return empty($_SESSION['uid'])?null:(q('SELECT * FROM users WHERE id=? AND active=1',[$_SESSION['uid']])->fetch()?:null);}
function need($role=null){$u=me();if(!$u){header('Location: login.php');exit;}if($role&&$u['role']!==$role){http_response_code(403);exit('Forbidden: administrator only.');}return $u;}
function csrf(){return $_SESSION['t']??($_SESSION['t']=bin2hex(random_bytes(16)));}
function field(){return '<input type="hidden" name="t" value="'.csrf().'">';}
function chk(){if(!hash_equals($_SESSION['t']??'',$_POST['t']??'')){http_response_code(400);exit('Invalid form token.');}}
function audit($u,$a,$e,$id,$d=''){q('INSERT INTO audit_logs(actor_id,action,entity,entity_id,detail,created_at) VALUES(?,?,?,?,?,?)',[$u['id'],$a,$e,$id,$d,now()]);}
function flash($m=null){if($m!==null){$_SESSION['f']=$m;return;}$x=$_SESSION['f']??'';unset($_SESSION['f']);return $x;}
function pill($s){$c=in_array($s,['RECEIVED','CRITICAL','PENDING'])?'r':(in_array($s,['RESOLVED','CLOSED','APPROVED'])?'g':'y');return '<span class="pill '.$c.'">'.h(str_replace('_',' ',$s)).'</span>';}
function top($title){$u=need();$cur=basename($_SERVER['SCRIPT_NAME']).'?'.($_GET['type']??'');
 $nav=[['index.php?','Dashboard'],['cases.php?SOS','SOS Alerts'],['cases.php?INC','Incident Reports'],['cancellations.php?','Cancellation Requests']];
 if($u['role']==='ADMIN'){$nav[]=['users.php?','User Management'];$nav[]=['audit.php?','Audit Logs'];}
 echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' – CampusSOS</title><link rel="stylesheet" href="assets/style.css"></head><body><aside><div class="brand"><b>CampusSOS</b><small>Campus Emergency Response &amp; Safety Management</small></div><nav>';
 foreach($nav as $n){[$href,$l]=$n;$p=explode('?',$href);echo '<a href="'.$p[0].($p[1]?'?type='.$p[1]:'').'" class="'.($cur===$p[0].'?'.$p[1]?'on':'').'">'.$l.'</a>';}
 echo '</nav></aside><main><div class="top"><h1>'.h($title).'</h1><span><b>'.h($u['name']).'</b> '.($u['role']==='ADMIN'?'Administrator':'Security Personnel').' <form method="post" action="logout.php" style="display:inline">'.field().'<button class="btn o">Log out</button></form></span></div><div class="wrap">';
 if($m=flash())echo '<div class="note">'.h($m).'</div>';return $u;}
function bottom(){echo '</div></main></body></html>';}
