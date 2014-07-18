<?
namespace services;

class PersonalEventService {
	public function getPersonalEvents() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\PersonalEvent u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$personalEvents = $query->getResult();
		
		return $personalEvents;
	}
	
	public function getPersonalEvent($personalEventId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\PersonalEvent u WHERE u.id=$personalEventId";
		$query = $entityManager->createQuery($dql);
		$personalEvents = $query->getResult();
		
		// call for its event to be loaded
		/*
		if($personalEvents[0]->event != null) {
			$personalEvents[0]->event->load();
		}
		*/
		
		return $personalEvents[0];
	}
	
	public function savePersonalEvent($personalEvent) {
		// stop if there was nothing passed
		if($personalEvent == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		$personalEvent->participant = $entityManager->merge($personalEvent->participant);
		$personalEvent->calendarEvent = $entityManager->merge($personalEvent->calendarEvent);
		
		// branch between creating a new personalEvent and updating an existing one
		if($personalEvent->id == 0) {
			// start managing this new personalEvent
			$entityManager->persist($personalEvent);
		} else {
			// merge this personalEvent so it is managed again and we can save
			$entityManager->merge($personalEvent);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved personalEvent, if the client happens to want it
		$personalEvent = $entityManager->merge($personalEvent);
		return $personalEvent;
	}
	
	public function deletePersonalEvent($personalEvent) {
		// stop if there was nothing passed
		if($personalEvent == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$personalEvent = $entityManager->merge($personalEvent);
		$entityManager->remove($personalEvent);
		$entityManager->flush();
	}
	
	public function deletePersonalEventById($personalEventId) {
		// stop if there was nothing passed
		if($personalEventId == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the personalEvent with that id
		$dql = "SELECT u FROM org\\fos\PersonalEvent u WHERE u.id=$personalEventId";
		$query = $entityManager->createQuery($dql);
		$personalEvents = $query->getResult();
		
		// delete that personalEvent
		$personalEvent = $entityManager->merge($personalEvents[0]);
		$entityManager->remove($personalEvent);
		$entityManager->flush();
	}
}
?>