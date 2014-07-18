<?
namespace services;

class EventService {
	public function getEvents() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Event u ORDER BY u.eventName ASC";
		$query = $entityManager->createQuery($dql);
		$events = $query->getResult();
		
		return $events;
	}
	
	public function getEventsForSelect2() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		//$dql = "SELECT u FROM org\\fos\Event u WHERE u.category='$category' ORDER BY u.eventName ASC";
		$dql = "SELECT u FROM org\\fos\Event u ORDER BY u.eventName ASC";
		$query = $entityManager->createQuery($dql);
		$events = $query->getResult();
		
		// preload any member entities
		//TODO... needed?
		
		// faculty frosh
		foreach($events as $event) {
			if($event->category == "faculty") {
				$matchedEvents[] = $event;
			}
		}
		
		// non-faculty frosh
		foreach($events as $event) {
			if($event->category == "alternative") {
				$matchedEvents[] = $event;
			}
		}
		
		// discover mcgill
		foreach($events as $event) {
			if($event->category == "discoverMcGill") {
				$matchedEvents[] = $event;
			}
		}
		
		// a la carte events
		foreach($events as $event) {
			if($event->category == "callfortender") {
				$matchedEvents[] = $event;
			}
		}
		
		//return $events;
		return $matchedEvents;
	}
	
	public function getEvent($eventId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Event u WHERE u.id='$eventId'";
		$query = $entityManager->createQuery($dql);
		$events = $query->getResult();
		
		// preload any member entities
		/*
		foreach($events as $event) {
			$event->load();
			
			foreach($event->participants as $participant) {
				$participant->load();
				foreach($participant->payments as $payment) {
					$payment->load();
				}
			}
		}
		*/
		
		//return $events[0];
		return $events[0];
	}
	
	public function getEventsWithCategory($category) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Event u WHERE u.category='$category' ORDER BY u.eventName ASC";
		//$dql = "SELECT u FROM org\\fos\Event u ORDER BY u.eventName ASC";
		$query = $entityManager->createQuery($dql);
		$events = $query->getResult();
		
		// preload any member entities
		//TODO... needed?
		/*
		foreach($events as $event) {
			if($event->category == $category) {
				$matchedEvents[] = $event;
			}
		}
		*/
		return $events;
		//return $matchedEvents;
	}
	
	public function saveEvent($event) {
		// stop if there was nothing passed
		if($event == NULL) {
			return null;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		for($i = 0; $i < count($event->roles); $i++) {
			$event->roles[$i] = $entityManager->merge($event->roles[$i]);
		}
		for($i = 0; $i < count($event->participants); $i++) {
			$event->participants[$i] = $entityManager->merge($event->participants[$i]);
		}
		
		// create or update the entity
		if($event->id == 0) {
			// start managing this new event
			$entityManager->persist($event);
		} else {
			// merge this event so it is managed again and we can save
			$entityManager->merge($event);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved event, if the client happens to want it
		$event = $entityManager->merge($event);
		return $event;
	}
	
	public function deleteEvent($event) {
		// stop if there was nothing passed
		if($event == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$event = $entityManager->merge($event);
		$entityManager->remove($event);
		$entityManager->flush();
	}
	
	public function deleteEventById($eventId) {
		// stop if there was nothing passed
		if($eventId == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the user with that id
		$dql = "SELECT u FROM org\\fos\Event u WHERE u.id=$eventId";
		$query = $entityManager->createQuery($dql);
		$events = $query->getResult();
		
		// delete that user
		$event = $entityManager->merge($events[0]);
		$entityManager->remove($event);
		$entityManager->flush();
	}
}
?>