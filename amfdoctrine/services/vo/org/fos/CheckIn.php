<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class CheckIn {
	// explicit ActionScript class
	public $_explicitType = "org.fos.CheckIn";
	
	// table fields
	public $id;
	public $userId;
	public $pastUserIds;
	public $checkInDate;
	public $gotMerchandise;
	public $gotBracelet;
	public $braceletNumber;
	public $pastBraceletNumbers;
	
	// joined fields/objects
	public $event;
	public $participant;
	//public $user;
	
	// constructor
	public function __construct() {
	}
}
?>