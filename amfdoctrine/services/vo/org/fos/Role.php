<?
namespace org\fos;

use Doctrine\Common\Collections\ArrayCollection;

class Role {
	// explicit ActionScript class
	public $_explicitType = "org.fos.Role";
	
	// static constants
	public static $ALL_PERMISSIONS = "ALL";
	public static $VIEW_FINANCIAL_OVERVIEW = "ViewFinancialOverview";
	public static $VIEW_PARTICIPANTS = "ViewParticipants";
	public static $EDIT_PARTICIPANTS = "EditParticipants";
	public static $CHECK_IN_PARTICIPANTS = "CheckInParticipants";
	public static $DELETE_PARTICIPANTS = "DeleteParticipants";
	public static $VIEW_STAFF = "ViewStaff";
	public static $MANAGE_STAFF = "ManageStaff";
	public static $VIEW_REPORTS = "ViewReports";
	public static $EDIT_EVENT = "EditEvent";
	public static $EDIT_PAYSCHEDULE = "EditPayschedule";
	public static $EDIT_SCHEDULE = "EditSchedule";
	public static $EDIT_STAFF_ROLES = "EditStaffRoles";
	public static $KIOSK_MODE = "KioskMode";
	
	// table fields
	public $id;
	public $roleName;
	public $permissions;
	
	// joined fields/objects
	public $event;
	public $users;
	
	// constructor
	public function __construct() {
		$this->users = new ArrayCollection();
	}
	
	public function load() {}
	
	/**
	 * Checks to see if the current role has the passed permission.
	 */
	public function hasPermission($permission) {
		// separate the permissions into an array
		$permissionArray = explode(";", $this->permissions);
		
		// see if the passed one exists in the array
		return in_array($permission, $permissionArray) || in_array(self::$ALL_PERMISSIONS, $permissionArray);
	}
}
?>