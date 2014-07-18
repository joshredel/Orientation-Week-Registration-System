<?
namespace services;

class RoleService {
	public function getRoles() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Role u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$roles = $query->getResult();
		
		return $roles;
	}
	
	public function getRole($roleId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Role u WHERE u.id=$roleId";
		$query = $entityManager->createQuery($dql);
		$roles = $query->getResult();
		
		// call for its event to be loaded
		if($roles[0]->event != null) {
			$roles[0]->event->load();
		}
		
		return $roles[0];
	}
	
	public function saveRole($role) {
		// stop if there was nothing passed
		if($role == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		$role->event = $entityManager->merge($role->event);
		for($i = 0; $i < count($role->users); $i++) {
			$role->users[$i] = $entityManager->merge($role->users[$i]);
		}
		
		// branch between creating a new role and updating an existing one
		if($role->id == 0) {
			// start managing this new role
			$entityManager->persist($role);
		} else {
			// merge this role so it is managed again and we can save
			$entityManager->merge($role);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved user, if the client happens to want it
		$role = $entityManager->merge($role);
		return $role;
	}
	
	public function deleteRole($role) {
		// stop if there was nothing passed
		if($role == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$role = $entityManager->merge($role);
		$entityManager->remove($role);
		$entityManager->flush();
	}
	
	public function deleteRoleById($roleId) {
		// stop if there was nothing passed
		if($roleId == null) {
			echo("ALL DONE!");
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the role with that id
		$dql = "SELECT u FROM org\\fos\Role u WHERE u.id=$roleId";
		$query = $entityManager->createQuery($dql);
		$roles = $query->getResult();
		
		// delete that role
		$role = $entityManager->merge($roles[0]);
		$entityManager->remove($role);
		$entityManager->flush();
	}
}
?>