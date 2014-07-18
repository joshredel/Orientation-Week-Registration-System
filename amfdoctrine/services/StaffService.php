<?
namespace services;

use Doctrine\Common\Collections\ArrayCollection;

class StaffService {
	public function getStaffs() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		return $staffs;
	}
	
	public function getStaff($staffId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.id=$staffId";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		// call for its event to be loaded
		if($staffs[0]->event != null) {
			$staffs[0]->event->load();
		}
		
		return $staffs[0];
	}
	
	public function getCoStaffs($eventId, $groupNumber, $currentStaffId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.groupNumber=$groupNumber";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		// call for its event to be loaded
		$coStaffs = new ArrayCollection();
		foreach($staffs as $staff) {
			$staff->event->load();
			if($staff->event->id == $eventId && $staff->id != $currentStaffId) {
				$coStaffs[] = $staff;
			}
		}
		
		return $coStaffs;
	}
	
	public function getStaffInGroup($eventId, $groupNumber) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.groupNumber=$groupNumber";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		// call for its event to be loaded
		$coStaffs = new ArrayCollection();
		foreach($staffs as $staff) {
			$staff->event->load();
			if($staff->event->id == $eventId) {
				$coStaffs[] = $staff;
			}
		}
		
		return $coStaffs;
	}
	
	public function getStaffByPhoneNumber($phoneNumber) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.phoneNumber='$phoneNumber'";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		return $staffs[0];
	}
	
	public function getStaffByRegistrationPassword($hash) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.registrationPassword='$hash'";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		if(sizeof($staffs) != 1) {
			return null;
		} else {
			return $staffs[0];
		}
	}
	
	public function saveStaff($staff) {
		// stop if there was nothing passed
		if($staff == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		//$staff->event = $entityManager->merge($staff->event);
		
		// branch between creating a new staff and updating an existing one
		if($staff->id == 0) {
			// start managing this new staff
			$entityManager->persist($staff);
		} else {
			// merge this staff so it is managed again and we can save
			$entityManager->merge($staff);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved staff, if the client happens to want it
		$staff = $entityManager->merge($staff);
		return $staff;
	}
	
	public function deleteStaff($staff) {
		// stop if there was nothing passed
		if($staff == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$staff = $entityManager->merge($staff);
		$entityManager->remove($staff);
		$entityManager->flush();
	}
	
	public function deleteStaffById($staffId) {
		// stop if there was nothing passed
		if($staffId == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the staff with that id
		$dql = "SELECT u FROM org\\fos\Staff u WHERE u.id=$staffId";
		$query = $entityManager->createQuery($dql);
		$staffs = $query->getResult();
		
		// delete that staff
		$staff = $entityManager->merge($staffs[0]);
		$entityManager->remove($staff);
		$entityManager->flush();
	}
}
?>