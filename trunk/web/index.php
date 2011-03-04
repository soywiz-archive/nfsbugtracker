<?php

require_once(__DIR__ . '/classes/Core.php');

$db = new Db('sqlite:' . __DIR__ . '/data/database.sqlite');
DbModel::upgradeTables($db);

$user = new UserModel();
$user->name = 'test';
$user->email = 'test@test.com';
$user->setPassword('test');
$user->save($db);

print_r(iterator_to_array(UserModel::getAll($db)));