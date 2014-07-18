<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class CoordRecording {
	// explicit ActionScript class
	public $_explicitType = "org.fos.CoordRecording";
	
	// static constants
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
	public $eventId;
	public $submitterId;
	public $messageDate;
	public $url;
	public $recordingCategory;
	
	// constructor
	public function __construct() {
		$this->users = new ArrayCollection();
	}
	
	public function load() {}
}
?>