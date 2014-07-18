<?
namespace services;

class CostService {
	public function getCosts() {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Cost u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$costs = $query->getResult();
		
		return $costs;
	}
	
	public function getCost($costId) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Cost u WHERE u.id=$costId";
		$query = $entityManager->createQuery($dql);
		$costs = $query->getResult();
		
		// call for its event to be loaded
		if($costs[0]->event != null) {
			$costs[0]->event->load();
		}
		
		return $costs[0];
	}
	
	public function saveCost($cost) {
		// stop if there was nothing passed
		if($cost == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		$cost->event = $entityManager->merge($cost->event);
		
		// branch between creating a new cost and updating an existing one
		if($cost->id == 0) {
			// start managing this new cost
			$entityManager->persist($cost);
		} else {
			// merge this cost so it is managed again and we can save
			$entityManager->merge($cost);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved cost, if the client happens to want it
		$cost = $entityManager->merge($cost);
		return $cost;
	}
	
	public function deleteCost($cost) {
		// stop if there was nothing passed
		if($cost == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$cost = $entityManager->merge($cost);
		$entityManager->remove($cost);
		$entityManager->flush();
	}
	
	public function deleteCostById($costId) {
		// stop if there was nothing passed
		if($costId == null) {
			return;
		}
		
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the cost with that id
		$dql = "SELECT u FROM org\\fos\Cost u WHERE u.id=$costId";
		$query = $entityManager->createQuery($dql);
		$costs = $query->getResult();
		
		// delete that cost
		$cost = $entityManager->merge($costs[0]);
		$entityManager->remove($cost);
		$entityManager->flush();
	}
}
?>