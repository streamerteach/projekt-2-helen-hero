<?php
require_once __DIR__ . '/config.php';
if (!loggedIn()) redirect('login.php');

$tid   = (int)($_POST['target_id'] ?? 0);
$value = (int)($_POST['value']     ?? 0);
$back  = $_POST['back'] ?? 'index.php';

// Sanitize redirect
if (!preg_match('/^[a-zA-Z0-9_.?=&%\-\/]+$/', $back)) $back = 'index.php';

$me = (int)$_SESSION['user_id'];

if ($tid && in_array($value,[1,-1]) && $tid !== $me) {
    $st = db()->prepare("SELECT id, value FROM likes WHERE voter_id=? AND target_id=?");
    $st->execute([$me, $tid]);
    $ex = $st->fetch();
    if ($ex) {
        if ((int)$ex['value'] === $value) {
            db()->prepare("DELETE FROM likes WHERE id=?")->execute([$ex['id']]);
        } else {
            db()->prepare("UPDATE likes SET value=? WHERE id=?")->execute([$value,$ex['id']]);
        }
    } else {
        db()->prepare("INSERT INTO likes (voter_id,target_id,value) VALUES (?,?,?)")->execute([$me,$tid,$value]);
    }
}
redirect($back);
