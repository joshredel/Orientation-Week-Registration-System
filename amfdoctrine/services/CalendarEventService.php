<?
namespace services;

class CalendarEventService {
	public function getCalendarEvents() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CalendarEvent u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$calendarEvents = $query->getResult();
		
		return $calendarEvents;
	}
	
	public function getCalendarEvent($calendarEventId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CalendarEvent u WHERE u.id=$calendarEventId";
		$query = $entityManager->createQuery($dql);
		$calendarEvents = $query->getResult();
		
		// call for its event to be loaded
		if($calendarEvents[0]->event != null) {
			$calendarEvents[0]->event->load();
		}
		
		return $calendarEvents[0];
	}
	
	public function saveCalendarEvent($calendarEvent) {
		// stop if there was nothing passed
		if($calendarEvent == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		$calendarEvent->event = $entityManager->merge($calendarEvent->event);
		
		// branch between creating a new calendarEvent and updating an existing one
		if($calendarEvent->id == 0) {
			// start managing this new calendarEvent
			$entityManager->persist($calendarEvent);
		} else {
			// merge this calendarEvent so it is managed again and we can save
			$entityManager->merge($calendarEvent);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved calendarEvent, if the client happens to want it
		$calendarEvent = $entityManager->merge($calendarEvent);
		return $calendarEvent;
	}
	
	public function deleteCalendarEvent($calendarEvent) {
		// stop if there was nothing passed
		if($calendarEvent == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$calendarEvent = $entityManager->merge($calendarEvent);
		$entityManager->remove($calendarEvent);
		$entityManager->flush();
	}
	
	public function deleteCalendarEventById($calendarEventId) {
		// stop if there was nothing passed
		if($calendarEventId == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the calendarEvent with that id
		$dql = "SELECT u FROM org\\fos\CalendarEvent u WHERE u.id=$calendarEventId";
		$query = $entityManager->createQuery($dql);
		$calendarEvents = $query->getResult();
		
		// delete that calendarEvent
		$calendarEvent = $entityManager->merge($calendarEvents[0]);
		$entityManager->remove($calendarEvent);
		$entityManager->flush();
	}
}
?>