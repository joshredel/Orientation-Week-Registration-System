<?
namespace services;

class PaymentService {
	public function getPayments() {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Payment u ORDER BY u.id ASC";
		$query = $entityManager->createQuery($dql);
		$payments = $query->getResult();
		
		return $payments;
	}
	
	public function getPayment($paymentId) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Payment u WHERE u.id=$paymentId";
		$query = $entityManager->createQuery($dql);
		$payments = $query->getResult();
		
		// call for its event to be loaded
		if($payments[0]->event != null) {
			$payments[0]->event->load();
		}
		
		return $payments[0];
	}

	public function getPaymentsByPayKey($payKey) {
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Payment u WHERE u.payKey='$payKey'";
		$query = $entityManager->createQuery($dql);
		$payments = $query->getResult();
		
		// call for its event to be loaded
		foreach ($payments as $payment){
			if($payment->event != null) {
				$payment->event->load();
			}
		}
		
		return $payments;
	}
	
	public function getPaymentsContainingPayKey($payKey) {
		// bootstrap to doctrine
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// create the select query and execute
		$dql = "SELECT u FROM org\\fos\Payment u ";
		$query = $entityManager->createQuery($dql);
		$payments = $query->getResult();
		
		// look through each payment and find the one that has the passed paykey as part of its paykeys
		foreach($payments as $payment) {
			// get the array of paykeys
			$payKeys = explode(",", $payment->payKey);
			
			// see if it contains the passed paykey
			if(in_array($payKey, $payKeys)) {
				$matchedPayments[] = $payment;
			}
		}
		
		// call for its event to be loaded
		foreach ($matchedPayments as $payment){
			if($payment->event != null) {
				$payment->event->load();
			}
			if($payment->participant != null) {
				$payment->participant->load();
			}
		}
		
		return $matchedPayments;
	}
	
	public function savePayment($payment) {
		// stop if there was nothing passed
		if($payment == NULL) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// branch between creating a new payment and updating an existing one
		if($payment->id == 0) {
			// map the incoming relations to their corresponding database entities
			$payment->participant = $entityManager->merge($payment->participant);
			$payment->event = $entityManager->merge($payment->event);
			
			// start managing this new payment
			$entityManager->persist($payment);
		} else {
			// merge this payment so it is managed again and we can save
			$entityManager->merge($payment);
		}
		
		// carry out the awaiting operations
		$entityManager->flush();
		
		// return the saved payment, if the client happens to want it
		$payment = $entityManager->merge($payment);
		return $payment;
	}
	
	public function deletePayment($payment) {
		// stop if there was nothing passed
		if($payment == null) {
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// merge and then remove the entity
		$payment = $entityManager->merge($payment);
		$entityManager->remove($payment);
		$entityManager->flush();
	}
	
	public function deletePaymentById($paymentId) {
		// stop if there was nothing passed
		if($paymentId == null) {
			echo("ALL DONE!");
			return;
		}
		
		// bootstrap to doctrine
		//require($_SERVER['DOCUMENT_ROOT'] . '/amfdoctrine/bootstrapper.php');
		require(__DIR__ . '/../../amfdoctrine/bootstrapper.php');
		
		// find the payment with that id
		$dql = "SELECT u FROM org\\fos\Payment u WHERE u.id=$paymentId";
		$query = $entityManager->createQuery($dql);
		$payments = $query->getResult();
		
		// delete that payment
		$payment = $entityManager->merge($payments[0]);
		$entityManager->remove($payment);
		$entityManager->flush();
	}
}
?>