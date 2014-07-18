<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class User {
	// explicit ActionScript class
	public $_explicitType = "org.fos.User";
	
	// table fields
	public $id;
	
	// joined fields/objects
	public $checkIns = null;
	
	// constructor
	public function __construct() {
		$this->checkIns = new ArrayCollection();
	}
}
?>