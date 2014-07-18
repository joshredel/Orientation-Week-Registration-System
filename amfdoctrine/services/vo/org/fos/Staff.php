<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Staff {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Staff";
	
	// table fields
	public $id;
	public $userId;
	public $displayName;
	public $lastName;
	public $registrationPassword;
	public $studentId;
	public $phoneNumber;
	public $email;
	public $classification;
	public $recordedName;
	public $groupNumber;
	public $lastText;
	public $braceletNumberFaculty;
	public $pastBraceletNumbersFaculty;
	public $braceletNumberSsmu;
	public $pastBraceletNumbersSsmu;
	public $pastUserIds;
	public $checkInDate;
	public $checkedInFaculty;
	public $checkedInSsmu;
	public $hasPaid;
	public $messageBuilder;
	
	// joined fields/objects
	public $event;
	
	// constructor
	public function __construct() {
	}
}
?>