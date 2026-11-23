<?php
session_start();
require 'db.php';
require 'auth.php';
$subs=$conn->query("SELECT email,subscribed_at,is_active FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll();
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="subscribers_'.date('Y-m-d').'.csv"');
$out=fopen('php://output','w');
fputcsv($out,['Email','Subscribed At','Status']);
foreach($subs as $s) fputcsv($out,[$s['email'],$s['subscribed_at'],$s['is_active']?'Active':'Inactive']);
fclose($out);
