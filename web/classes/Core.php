<?php

class Core {
	static public function __autoload($className) {
		$className = basename($className);
		
		if (preg_match('@^(\\w+)(Model|Controller)$@', $className, $matches)) {
			require_once(__DIR__ . '/../' . strtolower($matches[2]) . 's/' . $className . '.php');
		} else {
			require_once(__DIR__ . '/' . $className . '.php');
		}
	}
	
	static public function getAllModels() {
		return array_map(function($v) { return pathinfo($v, PATHINFO_FILENAME); }, glob(__DIR__ . '/../models/*Model.php'));
	}
	
	static public function register_autoload() {
		spl_autoload_register(array('Core', '__autoload'));
	}
}

Core::register_autoload();