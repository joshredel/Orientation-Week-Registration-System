<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class User {
	// explicit ActionScript class
	public $_explicitType = "org.fos.User";
	
	// static constants
	// classifications
	const PARTICIPANT = "Participant";
	const GENERAL = "General";
	const LEADER = "Leader";
	const OSTAFF = "O-Staff";
	const COORDINATOR = "Coordinator";
	const HEAD_COORDINATOR = "Head Coordinator";
	const ADMINISTRATOR = "Administrator";
	const COMMUNICATIONS = "Communications";
	
	// table fields
	public $id;
	public $username;
	public $displayName;
	public $lastName;
	public $password;
	public $phoneNumber;
	public $classification;
	public $title;
	public $lastText;
	public $messageBuilder;
	
	// joined fields/objects
	//public $checkIns = null;
	public $roles = null;
	
	// constructor
	public function __construct() {
		//$this->checkIns = new ArrayCollection();
		$this->roles = new ArrayCollection();
	}
	
	public function load() {}
}
?>