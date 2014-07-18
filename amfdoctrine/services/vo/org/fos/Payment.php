<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Payment {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Payment";
	
	// table fields
	public $id;
	public $method;
	public $payKey;
	public $paymentDate;
	public $finalCost;
	public $hasPaid;
	public $status;
	public $description;
	public $isAdminPayment;
	
	// joined fields/objects
	public $participant;
	public $event;
	
	// constructor
	public function __construct() {
	}
	
	public function load() {}
}
?>