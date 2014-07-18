<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class PersonalEvent {
	// explicit ActionScript class
	public $_explicitType = "org.fos.PersonalEvent";
	
	
	// table fields
	public $id;
	public $eventId;
	public $title;
	public $location;
	public $notes;
	public $startTime;
	public $endTime;
	
	// joined fields/objects
	public $participant;
	public $calendarEvent;
	
	// constructor
	public function __construct() {
		//$this->personalEvents = new ArrayCollection();
	}
	
	public function load() {}
}
?>