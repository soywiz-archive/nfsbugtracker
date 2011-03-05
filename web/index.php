<?php

require_once(__DIR__ . '/classes/Core.php');

$start = microtime(true);

$db = new Db(__DIR__ . '/data/database.sqlite');
//DbModel::upgradeTables($db);

$db->beginTransaction();

foreach (UserModel::getAll($db) as $user) {
	$user->delete($db);
}

$user = new UserModel();
$user->name = 'test';
$user->email = 'test@test.com';
$user->setPassword('test');
$user->save($db);

$user = new UserModel();
$user->name = 'test2';
$user->email = 'test@test.com';
$user->setPassword('test');
$user->save($db);

foreach (UserModel::getAll($db) as $user) {
	$user->delete($db);
	print_r_pre($user);
}

$db->commit();
//print_r_pre(iterator_to_array(UserModel::getAll($db)));

$end = microtime(true);

printf("%.6f", $end - $start);