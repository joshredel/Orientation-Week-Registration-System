<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class CalendarEvent {
	// explicit ActionScript class
	public $_explicitType = "org.fos.CalendarEvent";
	
	
	// table fields
	public $id;
	public $title;
	public $location;
	public $notes;
	public $startTime;
	public $endTime;
	public $ofAgeMarker;
	
	// joined fields/objects
	public $event;
	public $personalEvents = null;
	
	// constructor
	public function __construct() {
		$this->personalEvents = new ArrayCollection();
	}
	
	public function load() {}
}
?>