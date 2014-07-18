<?
namespace services;

class CoordRecordingService {
	public function getCoordRecordings() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CoordRecording u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$recordings = $query->getResult();
		
		return $recordings;
	}
	
	public function getCoordRecording($recordingId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\CoordRecording u WHERE u.id=$recordingId";
		$query = $entityManager->createQuery($dql);
		$recordings = $query->getResult();
		
		return $recordings[0];
	}
	
	public function saveCoordRecording($recording) {
		// stop if there was nothing passed
		if($recording == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// branch between creating a new role and updating an existing one
		if($recording->id == 0) {
			// start managing this new role
			$entityManager->persist($recording);
		} else {
			// merge this role so it is managed again and we can save
			$entityManager->merge($recording);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved recording, if the client happens to want it
		$recording = $entityManager->merge($recording);
		return $recording;
	}
	
	public function deleteCoordRecording($recording) {
		// stop if there was nothing passed
		if($recording == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$recording = $entityManager->merge($recording);
		$entityManager->remove($recording);
		$entityManager->flush();
	}
	
	public function deleteCoordRecordingById($recordingId) {
		// stop if there was nothing passed
		if($recordingId == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the role with that id
		$dql = "SELECT u FROM org\\fos\CoordRecording u WHERE u.id=$recordingId";
		$query = $entityManager->createQuery($dql);
		$recordings = $query->getResult();
		
		// delete that role
		$recording = $entityManager->merge($recordings[0]);
		$entityManager->remove($recording);
		$entityManager->flush();
	}
}
?>