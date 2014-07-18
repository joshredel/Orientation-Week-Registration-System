<?
namespace services;

class CheckInService {
	public function getCheckIns() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CheckIn u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$checkIns = $query->getResult();
		
		return $checkIns;
	}
	
	public function getCheckIn($checkInId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CheckIn u WHERE u.id=$checkInId";
		$query = $entityManager->createQuery($dql);
		$checkIns = $query->getResult();
		
		// call for its event to be loaded
		if($checkIns[0]->event != null) {
			$checkIns[0]->event->load();
		}
		
		return $checkIns[0];
	}
	
	public function saveCheckIn($checkIn, $saveEvent) {
		// stop if there was nothing passed
		if($checkIn == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		if($saveEvent) {
			$checkIn->event = $entityManager->merge($checkIn->event);
		}
		$checkIn->participant = $entityManager->merge($checkIn->participant);
		
		// branch between creating a new checkIn and updating an existing one
		if($checkIn->id == 0) {
			//$checkIn->event = $entityManager->merge($checkIn->event);
			//$checkIn->participant = $entityManager->merge($checkIn->participant);
			//$checkIn->user = $entityManager->merge($checkIn->user);
			
			// start managing this new checkIn
			$entityManager->persist($checkIn);
		} else {
			// merge this checkIn so it is managed again and we can save
			$entityManager->merge($checkIn);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved user, if the client happens to want it
		$checkIn = $entityManager->merge($checkIn);
		return $checkIn;
	}
	
	public function deleteCheckIn($checkIn) {
		// stop if there was nothing passed
		if($checkIn == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$checkIn = $entityManager->merge($checkIn);
		$entityManager->remove($checkIn);
		$entityManager->flush();
	}
	
	public function deleteCheckInById($checkInId) {
		// stop if there was nothing passed
		if($checkInId == null) {
			echo("ALL DONE!");
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the checkIn with that id
		$dql = "SELECT u FROM org\\fos\CheckIn u WHERE u.id=$checkInId";
		$query = $entityManager->createQuery($dql);
		$checkIns = $query->getResult();
		
		// delete that checkIn
		$checkIn = $entityManager->merge($checkIns[0]);
		$entityManager->remove($checkIn);
		$entityManager->flush();
	}
}
?>