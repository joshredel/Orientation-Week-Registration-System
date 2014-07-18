<?
namespace services;

class UserService {
	public function getUsers() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\User u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		return $users;
	}
	
	public function getUser($userId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\User u WHERE u.id='$userId'";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		return $users[0];
	}
	
	public function getUserByPhoneNumber($phoneNumber) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\User u WHERE u.phoneNumber='$phoneNumber'";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		return $users[0];
	}
	
	public function getUsersInClassification($classification) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\User u WHERE u.classification='$classification'";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		return $users;
	}
	
	public function getEventsUsers($event) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// loop through the event's roles and collect users
		foreach($event->roles as $role) {
			// loop through the role's users and store them
			foreach($role->users as $user) {
				$users[] = $user;
			}
		}
		
		return $users;
	}
	
	public function attemptLogin($username, $password) {
		// stop if there was nothing passed
		if($username == null || $password == null) {
			return null;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// search for the user
		$dql = "SELECT u FROM org\\fos\User u WHERE u.username='$username' AND u.password='$password'";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		// return the user only if it is unique
		if(sizeof($users) != 1) {
			return null;
		} else {
			// store the last login date
			$entityManager->merge($users[0]);
			$entityManager->flush();
			
			// preload information attached to the role
			foreach($users[0]->roles as $role) {
				if($role->event != null) {
					$role->event->load();
					foreach($role->event->roles as $tempRole) {
						$tempRole->load();
					}
				}
				foreach($role->users as $user) {
					$user->load();
				}
			}
			
			return $users[0];
		}
	}
	
	public function saveUser($user) {
		// stop if there was nothing passed
		if($user == NULL) {
			return null;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// map the incoming relations to their corresponding database entities
		for($i = 0; $i < count($user->roles); $i++) {
			$user->roles[$i] = $entityManager->merge($user->roles[$i]);
		}
		
		// update dates if neccessary
		if($user->id == 0) {
			// start managing this new user
			$entityManager->persist($user);
		} else {
			// merge this user so it is managed again and we can save
			$entityManager->merge($user);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved user, if the client happens to want it
		$user = $entityManager->merge($user);
		return $user;
	}
	
	public function deleteUser($user) {
		// stop if there was nothing passed
		if($user == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$user = $entityManager->merge($user);
		$entityManager->remove($user);
		$entityManager->flush();
	}
	
	public function deleteUserById($userId) {
		// stop if there was nothing passed
		if($userId == null) {
			echo("ALL DONE!");
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the user with that id
		$dql = "SELECT u FROM org\\fos\User u WHERE u.id=$userId";
		$query = $entityManager->createQuery($dql);
		$users = $query->getResult();
		
		// delete that user
		$user = $entityManager->merge($users[0]);
		$entityManager->remove($user);
		$entityManager->flush();
	}
}
?>