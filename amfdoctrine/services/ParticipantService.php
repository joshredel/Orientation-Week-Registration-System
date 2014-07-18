<?
namespace services;

class ParticipantService {
	public function getParticipants() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		return $participants;
	}
	
	public function getParticipant($participantId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.id=$participantId";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		if(sizeof($participants) != 1) {
			return null;
		} else {
			return $participants[0];
		}
	}
	
	public function getParticipantsInGroup($eventId, $groupNumber) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// compile information
		$combinedGroupNumber = $eventId . "::" . $groupNumber;
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.groupNumber='$combinedGroupNumber'";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		return $participants;
	}
	
	public function getParticipantByStudentId($studentId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.studentId=$studentId";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		// load the events for the participant
		if($participants != null && $participants[0]->events != null) {
			foreach($participants[0]->events as $event) {
				$event->load();
			}
		}
		
		if(sizeof($participants) != 1) {
			return null;
		} else {
			return $participants[0];
		}
	}
	
	public function getParticipantByRegistrationPassword($hash) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.registrationPassword='$hash'";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		//echo($participants[0]->events[1]->id);
		// load the events for the participant
		if($participants[0]->events != null) {
			foreach($participants[0]->events as $event) {
				$event->load();
			}
		}
		
		if(sizeof($participants) != 1) {
			return null;
		} else {
			return $participants[0];
		}
	}
	
	public function getParticipantBySearch($searchTerm) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE (u.firstName LIKE '%$searchTerm%' OR u.preferredName LIKE '%$searchTerm%' OR u.lastName LIKE '%$searchTerm%' OR u.studentId LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%')";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		return $participants;
	}
	
	public function getParticipantByPhoneNumber($phoneNumber) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.phoneNumber='$phoneNumber'";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		if(sizeof($participants) != 1) {
			return null;
		} else {
			// load the events for the participant
			if($participants[0]->events != null) {
				foreach($participants[0]->events as $event) {
					$event->load();
				}
			}
			
			return $participants[0];
		}
	}
	
	public function saveParticipant($participant) {
		// stop if there was nothing passed
		if($participant == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		for($i = 0; $i < count($participant->events); $i++) {
			$participant->events[$i] = $entityManager->merge($participant->events[$i]);
		}
		
		// branch between creating a new participant and updating an existing one
		if($participant->id == 0) {
			// start managing this new participant
			$entityManager->persist($participant);
		} else {
			// merge this participant so it is managed again and we can save
			$entityManager->merge($participant);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved participant, if the client happens to want it
		$participant = $entityManager->merge($participant);
		
		return $participant;
	}
	
	public function deleteParticipant($participant) {
		// stop if there was nothing passed
		if($participant == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$participant = $entityManager->merge($participant);
		$entityManager->remove($participant);
		$entityManager->flush();
	}
	
	public function deleteParticipantById($participantId) {
		// stop if there was nothing passed
		if($participantId == null) {
			echo("ALL DONE!");
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the participant with that id
		$dql = "SELECT u FROM org\\fos\Participant u WHERE u.id=$participantId";
		$query = $entityManager->createQuery($dql);
		$participants = $query->getResult();
		
		// delete that participant
		$participant = $entityManager->merge($participants[0]);
		$entityManager->remove($participant);
		$entityManager->flush();
	}
}
?>