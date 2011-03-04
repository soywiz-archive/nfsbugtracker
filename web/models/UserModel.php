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
	protected $password;

	/**
	 * Returns the has of an specified password.
	 * 
	 * @param string $password
	 */
	public function passwordHash($password) {
		return md5('nfsbugtracker-' . $password);
	}
	
	public function setPassword($password) {
		$this->password = $this->passwordHash($password);
	}
}