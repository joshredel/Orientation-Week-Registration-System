<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Cost {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Cost";
	
	// table fields
	public $id;
	public $amount;
	public $summary;
	public $isAdminFee;
	public $isOptional;
	public $adminEventId;
	
	// joined fields/objects
	public $event;
	
	// constructor
	public function __construct() {
	}
}
?>