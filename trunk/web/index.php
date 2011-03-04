<?php

require_once(__DIR__ . '/classes/Core.php');

$db = new Db('sqlite:' . __DIR__ . '/data/database.sqlite');
//DbModel::upgradeTables($db);
//print_r(UserModel::getAll($db));

$user = new UserModel();
$user->name = 'test';
$user->email = 'test@test.com';
$user->setPassword('test');
$user->save($db);

//$db->upgradeModelsTable();
//$db->query('CREATE TABLE users (name, email, password);');