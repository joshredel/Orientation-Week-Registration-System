<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Participant {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Participant";
	
	// table fields
	public $id;
	public $studentId;
	public $firstName;
	public $lastName;
	public $preferredName;
	public $preferredPronoun;
	public $email;
	public $registrationPassword;
	public $approvedFacultyCheck;
	public $faculty;
	public $dateOfBirth;
	public $shirtSize;
	public $dietaryRestrictions;
	public $allergies;
	public $physicalNeeds;
	public $placeOfOrigin;
	public $enteringYear;
	public $registrationDate;
	public $livingStyle;
	public $froshAddress;
	public $customFieldAnswers;
	public $rawRegistrationData;
	public $phoneNumber;
	public $sentNightlyReminder;
	public $recordedName;
	public $groupNumber;
	public $lastText;
	public $messageBuilder;
	
	// joined fields/objects
	public $checkIns = null;
	public $payments = null;
	public $events = null;
	public $personalEvents = null;
	
	// constructor
	public function __construct() {
		$this->checkIns = new ArrayCollection();
		$this->payments = new ArrayCollection();
		$this->events = new ArrayCollection();
		$this->personalEvents = new ArrayCollection();
	}
	
	public function load() {}
}
?>