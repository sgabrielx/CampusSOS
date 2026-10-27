<?php require 'lib.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$u=q('SELECT * FROM users WHERE email=?',[strtolower(trim($_POST['email']??''))])->fetch();
 if($u&&$u['active']&&password_verify($_POST['pw']??'',$u['pass_hash'])){session_regenerate_id(true);$_SESSION['uid']=$u['id'];audit($u,'Login','USER',$u['id']);header('Location: index.php');exit;}
 $err='Email or password is incorrect, or the account is deactivated.';sleep(1);}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in – CampusSOS</title><link rel="stylesheet" href="assets/style.css"></head><body><div class="login card"><h1>CampusSOS</h1><p class="sub">Campus Emergency Response &amp; Safety Management</p>
<div class="note">Demo accounts (development only): admin@demo.campussos or reyes@demo.campussos, password demo1234. Change them before any real use.</div>
<form method="post"><?=field()?><label>Email<input name="email" type="email" required></label><label>Password<input name="pw" type="password" required></label><p class="err"><?=h($err)?></p><button class="btn">Sign in</button></form></div></body></html>
