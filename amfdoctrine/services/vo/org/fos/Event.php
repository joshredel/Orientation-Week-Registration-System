<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Event {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Event";
	
	// static constants
	// category codes
	const REZ_FEST = "RezFest";
	const DISCOVER_MCGILL = "DiscoverMcGill";
	const ACADEMIC_EXPECTATIONS = "AcademicExpectations";
	const A_LA_CARTE = "ALaCarte";
	const ORIENTATION_CENTRE = "OrientationCentre";
	const DROP_IN = "DropIn";
	const FACULTY_FROSH = "Faculty";
	const NON_FACULTY_FROSH = "NonFaculty";
	const ORANGE_EVENT = "Orange";
	const OOHLALA = "OohLaLa";
	const PLUS_EVENT = "PlusEvent";
	const INTERNATIONAL = "International";
	
	public static $ALL_CATEGORIES = array(self::REZ_FEST, 
										  self::DISCOVER_MCGILL, 
										  self::ACADEMIC_EXPECTATIONS, 
										  self::A_LA_CARTE, 
										  self::ORIENTATION_CENTRE, 
										  self::DROP_IN, 
										  self::FACULTY_FROSH, 
										  self::NON_FACULTY_FROSH, 
										  self::ORANGE_EVENT, 
										  self::OOHLALA, 
										  self::PLUS_EVENT,
										  self::INTERNATIONAL);
	
	// action types
	const ACTION_REGISTER = "Register";
	const ACTION_REMINDER = "Reminder";
	const ACTION_AUTO_REGISTER = "AutoRegister";
	const ACTION_INFO_ONLY = "InfoOnly";
	
	// calendar colour categories
	const DISPLAY_STANDARD = "Standard";
	const DISPLAY_DONT_MISS = "DontMiss";
	const DISPLAY_FROSH = "Frosh";
	const DISPLAY_DROP_BY = "DropBy";
	
	// table fields
	public $id;
	public $eventName;
	public $category;
	public $faculty;
	public $livingStyle;
	public $description;
	public $priceBreakdown;
	public $startDate;
	public $endDate;
	public $location;
	public $registrationOpenDate;
	public $registrationCloseDate;
	public $paypalBusiness;
	public $participantCap;
	public $hostedBy;
	public $website;
	public $email;
	public $logoFileName;
	public $customFields;
	public $acceptedPayments;
	public $action;
	public $bursaryNotice;
	public $hasSelectableEvents;
	public $displayType;
	
	// joined fields/objects
	public $parentEvent;
	public $options = null; // AKA childEvents
	public $payments = null;
	public $roles = null;
	public $checkIns = null;
	public $participants = null;
	public $costs = null;
	public $calendarEvents = null;
	public $staffs = null;
	
	// constructor
	public function __construct() {
		$this->options = new ArrayCollection();
		$this->payments = new ArrayCollection();
		$this->roles = new ArrayCollection();
		$this->checkIns = new ArrayCollection();
		$this->participants = new ArrayCollection();
		$this->costs = new ArrayCollection();
		$this->calendarEvents = new ArrayCollection();
		$this->staffs = new ArrayCollection();
	}
	
	public function load() {}
	
	public function faculties() {
		return explode(",", $this->faculty);
	}
}
?>