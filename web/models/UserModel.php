<?php

/**
 * Class representing an user in the bug tracker system.
 */
class UserModel extends DbModel {
	/**
	 * Name of the user.
	 * 
	 * @unique
	 * 
	 * @var string
	 */
	public $name;

	/**
	 * Email of the user.
	 * 
	 * @var string
	 */
	public $email;

	/**
	 * Hash of the user's password.
	 *  
	 * @var string
	 */
	protected $passwordHash;
	
	/**
	 * Determines if the user can create projects.
	 * 
	 * @var boolean
	 */
	public $can_create_projects = false;

	/**
	 * Returns the has of an specified password.
	 * 
	 * @param string $password
	 */
	public function passwordHash($password) {
		return md5('nfsbugtracker-' . $password);
	}
	
	public function getPasswordHash() {
		return $this->passwordHash;
	}
	
	public function setPassword($password) {
		$this->passwordHash = $this->passwordHash($password);
	}
	
	public function validate() {
		if (!preg_match('@^\\w{3,16}$@', $this->name)) throw(new Exception("User name must contain at least 3 leters and 16 at most"));
		if (empty($this->passwordHash)) throw(new Exception("Password must be setted"));
	}
}