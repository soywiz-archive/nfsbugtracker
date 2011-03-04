<?php

/**
 * Represents an issue/bug in the system.
 */
class IssueModel extends DbModel {
	/**
	 * Project vinculated to this bug.
	 * 
	 * @var string
	 */
	public $project_id;

	/**
	 * User who created the issue.
	 * 
	 * @var string
	 */
	public $user_name_created;
	
	/**
	 * User who created the issue.
	 * 
	 * @var string
	 */
	public $user_name_assigned;
}
