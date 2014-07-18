<?
require("../../functions.php");
checkForSession();

// a date compare function for calendar events
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// redirect if they do not have permission to be here
if(!$currentRole->hasPermission(org\fos\Role::$CHECK_IN_PARTICIPANTS) || $currentEvent == null) {
	// the user does not have permissions
	redirect("/admin/participants/");
}

// see if there is a success message
if(isset($_SESSION['checkInSuccess'])) {
	$successMessage = $_SESSION['checkInSuccess'];
	unset($_SESSION['checkInSuccess']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../../css/layout.css" />
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
        <script src="js/IE8.js" type="text/javascript"></script>
    <![endif]-->

    <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" media="all" href="css/ie6.css"/>
    <![endif]-->
	<script type="text/javascript" src="../../js/jquery.js"></script>
    <script type="text/javascript">
		// case insensitive version of 'contains'
		$(document).ready(function() {
			jQuery.expr[':'].Contains = function(a, i, m) { 
				return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; 
			};
		});
		
		function filterLinks() {
			// get the search term
			var searchTerm = $('#filter').val();
			
			// hide everything
			$("tr.event").hide();
			
			// only show each link if it contains the search item
			$("tr.event:Contains('" + searchTerm + "')").show();
		}
	</script>
</head>
<body>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	   <p><a href="../logout.php">logout</a><br />
    	   <a href="../changepassword.php">change password</a></p>
    	</div>
    	<?
		$file = __FILE__;
		include("../includes/html/topNav.php");
		
		if(isset($successMessage) && $successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
            
			
			<?
			if (isset($_GET['error'])) {
				echo "<div class='error'>".$_GET['error']."</div>";
			}
			
			
			if ($currentEvent != null) {
			
			?>
            
            
            <form onSubmit="return false">
                <input id="filter" type="text" onKeyUp="filterLinks()" placeholder="Search participants..." >
            </form>
            <table id="people">
            	<thead>
                    <tr>
                        <th style="max-width: 200px; word-wrap:break-word">Name</th>
                        <th>Role</th>
                        <th>Student ID</th>
                        <th>Paid?</th>
                        <th>Bracelet (Faculty)</th>
                        <th>Bracelet (SSMU)</th>
                        <th>Checked in?</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?
				
				// get the participants for the current event
				$allStaffs = $currentEvent->staffs;
				//$allParticipants = $participantService->getParticipants();
				foreach($allStaffs as $staff) {
					$prettyName = toPrettyPrint($staff->displayName . " " . $staff->lastName);
					
					//echo("<tr class='event'><td  style=\"max-width: 200px; word-wrap:break-word\"><a href='../staff/profile.php?id={$staff->id}'>{$prettyName}</a></td>");
					echo("<tr class='event'><td  style=\"max-width: 200px; word-wrap:break-word\">{$prettyName}</td>");
					echo("<td>{$staff->classification}</td>");
					echo("<td>{$staff->studentId}</td>");
					
					// payment status
					if($staff->hasPaid) {
						echo("<td>Paid</td>");
						$paid = "true";
					} else {
						echo("<td>Unpaid</td>");
						$paid = "false";
					}
					
					// faculty bracelet number
					if($staff->braceletNumberFaculty != null) {
						echo("<td>" . $staff->braceletNumberFaculty . "</td>");
						$braceletNumberFaculty = $staff->braceletNumberFaculty;
					} else {
						echo("<td></td>");
						$braceletNumberFaculty = "";
					}
					
					// ssmu bracelet number
					if($staff->braceletNumberSsmu != null) {
						echo("<td>" . $staff->braceletNumberSsmu . "</td>");
						$braceletNumberSsmu = $staff->braceletNumberSsmu;
					} else {
						echo("<td></td>");
						$braceletNumberSsmu = "";
					}
					
					// phone number
					if($staff->phoneNumber == null) {
						$phone = "";
					} else {
						$phone = $staff->phoneNumber;
					}
					
					// checked in status
					if($staff->checkedInSsmu) {
						$checkedInSsmu = "true";
					} else {
						$checkedInSsmu = "false";
					}
					
					// checked in status
					if($staff->checkedInFaculty) {
						echo("<td>Yes</td>");
						$checkedInFaculty = "true";
					} else {
						echo("<td>No</td>");
						$checkedInFaculty = "false";
					}
					
					// email
					if($staff->email == null) {
						$email = "";
					} else {
						$email = $staff->email;
					}
					
					// student id
					if($staff->studentId == 0) {
						$studentId = "";
					} else {
						$studentId = $staff->studentId;
					}
					
					// print the button plus the checkin javascript
					echo("<td><input type='button' value='Check-In' onclick='checkin(\"{$staff->displayName}\", \"{$staff->lastName}\", \"$studentId\", \"{$staff->registrationPassword}\", $paid, \"$braceletNumberSsmu\", \"$braceletNumberFaculty\", \"$phone\", $checkedInSsmu, $checkedInFaculty, \"$email\", \"{$staff->classification}\")' /></td></tr>\n");
				}
				?>
                </tbody>
            </table>
            <table id="header-fixed"></table>
           <?
			} else {
		   		echo "<article><p>Your user account does not have privilege to view this page.</p></article>";
			}
		   ?>
           <article id="checkinBox" style="display: none">
			<form method="post" action="../../actions/admin/processStaffCheckin.php" name="checkinPanel" id="checkinPanel" onsubmit='return checkForm(this)'>
           		<header>
                	<h2 id="name">Check-In Leader/O-Staff - </h2>
           		</header>
                
                
                <table  class="checkInTable">
                	<tr>
                    	<td class="left-column">
                        	Role:
                        </td>
                        <td>
                        	<p id="roleField"></p>
                        </td>
                    </tr>
                	<!-- Has paid? -->
                	<tr>
                    	<td onClick="toggleCheckbox('checkInPaid')" class="left-column">
                        	<span id="hasPaidText" class="red-text">Has paid?*</span>:
                        </td>
                        <td>
                        	<input id="checkInPaid" type="checkbox" name="paid" value="1" onChange="toggleHasPaid()" />
                        </td>
                    </tr>
                    <!-- Checked in with Faculty? -->
                    <tr>
                    	<td onClick="toggleCheckbox('checkedInFaculty')" class="left-column">
                        	Checked in with faculty?:
                        </td>
                        <td>
                        	<input id="checkedInFaculty" type="checkbox" name="checkedInFaculty" value="1"  />
                        </td>
                    </tr>
                    <!-- Bracelet number -->
                    <tr>
                    	<td class="left-column">
                        	Bracelet number (Faculty):
                        </td>
                        <td>
                        	<input id="checkInBraceletNumberFaculty" type="text" name="braceletNumberFaculty" />
                        </td>
                    </tr>
                    <!-- Checked in with SSMU? -->
                    <tr>
                    	<td onClick="toggleCheckbox('checkedInSSMU')" class="left-column">
                        	Checked in with SSMU?:
                        </td>
                        <td>
                        	<input id="checkedInSSMU" type="checkbox" name="checkedInSSMU" value="1"  />
                        </td>
                    </tr>
                    <!-- Bracelet number -->
                    <tr>
                    	<td class="left-column">
                        	Bracelet number (SSMU):
                        </td>
                        <td>
                        	<input id="checkInBraceletNumberSsmu" type="text" name="braceletNumberSsmu" />
                        </td>
                    </tr>
                    <!-- Student ID-->
                    <tr>
                    	<td class="left-column">
                        	Student ID*:
                        </td>
                        <td>
                        	<input id="checkInStudentId" type="text" name="studentId" />
                        </td>
                    </tr>
                    <!-- Email -->
                    <tr>
                        <td class="left-column">
                            Email*:
                        </td>
                        <td>
                            <input id="checkInEmail" type="text" name="email" style="width:80%" />
                        </td>
                    </tr>
                    <!-- Phone Number -->
                    <tr>
                    	<td class="left-column">
                        	Phone number:
                        </td>
                        <td>
                        	<input id="checkInPhone" type="text" name="phone" onChange="checkPhoneFormat(this)" onKeyUp="checkPhoneFormat(this)" /> (Detected format: <span id="phoneFormat"></span>)<br />
                			<p style="font-size:14px">
                            	<strong>Phone formats:</strong><br />
                                <em>North America:</em> xxx-xxx-xxxx or xxx.xxx.xxxx or xxx xxx xxxx or +1xxxxxxxxx<br />
                                <em>International:</em> +xxxxxxxxx...
                            </p>
                        </td>
                    </tr>
                    <tr>
                    	<td>
                        </td>
                        <td>
                        	<input class="redbutton" style="display: inline-block" type="submit" value="&nbsp;&nbsp;&nbsp;Check-In&nbsp;&nbsp;&nbsp;" />
                			<input type="button" class="button" style="display: inline-block" onClick="clearCheckin()" value="Cancel" />
                        </td>
                    </tr>
                </table>
                
                <input id="checkInPasskey" type="hidden" name="passkey" value="" />
			</form>
           </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
    
<script>
	// toggle the paid checkbox
	function toggleCheckbox(element) {
		// get the DOM element
		var checkbox = document.getElementById(element);
		
		// toggle the checkbox
		if(!checkbox.disabled) {
			if(checkbox.checked) {
				checkbox.checked = false;
			} else {
				checkbox.checked = true;
			}
		}
		
		if(element == "checkInPaid") {
			// show or hide the red if it has been paid
			if(checkbox.checked) {
				$("#hasPaidText").removeClass("red-text");
			} else {
				$("#hasPaidText").addClass("red-text");
			}
		}
	}
	
	// toggle the paid checkbox
	function toggleHasPaid() {
		// get the DOM element
		var checkbox = document.getElementById("checkInPaid");
		
		// show or hide the red if it has been paid
		if(checkbox.checked) {
			$("#hasPaidText").removeClass("red-text");
		} else {
			$("#hasPaidText").addClass("red-text");
		}
	}
	
	function checkin(firstName, lastName, studentId, passkey, paid, braceletNoSsmu, braceletNoFaculty, phone , checkedInSSMU, checkedInFaculty, email, classification) {
		// Clear form (Just in case)
		clearCheckin();
		
		// clear and enable the search bar
		document.getElementById("filter").value = "";
		document.getElementById("filter").disabled = true;
		
		// hide Table
		document.getElementById("people").style.display = "none";
		
		// show form
		document.getElementById("checkinBox").style.display = "";
		
		// set form to current State
		document.getElementById("name").innerHTML = "Check-In Leader/O-Staff - " + firstName + " " + lastName + " (" + studentId + ")";
		document.getElementById("roleField").innerHTML = classification;
		
		document.getElementById("checkInPaid").checked = paid;
		if(paid) {
			$("#hasPaidText").removeClass("red-text");
		} else {
			$("#hasPaidText").addClass("red-text");
		}
		document.getElementById("checkedInFaculty").checked = checkedInFaculty;
		document.getElementById("checkedInSSMU").checked = checkedInSSMU;
		document.getElementById("checkInBraceletNumberSsmu").value = braceletNoSsmu;
		document.getElementById("checkInBraceletNumberFaculty").value = braceletNoFaculty;
		document.getElementById("checkInStudentId").value = studentId;
		document.getElementById("checkInPhone").value = phone;
		document.getElementById("checkInPasskey").value = passkey;
		document.getElementById("checkInEmail").value = email;
		checkPhoneFormat(document.getElementById("checkInPhone"));
	}
	
	function clearCheckin() {
		// clear and enable the search bar
		document.getElementById("filter").value = "";
		document.getElementById("filter").disabled = false;
		
		filterLinks();
		
		// show the form
		document.getElementById("people").style.display = "";
		
		// hide the form
		document.getElementById("checkinBox").style.display = "none";
		
		// clear the checkin panel
		document.getElementById("name").innerHTML = "";
		document.getElementById("roleField").innerHTML = "";
		document.getElementById("checkInPaid").checked = false;
		$("#hasPaidText").addClass("red-text");
		document.getElementById("checkedInFaculty").checked = false;
		document.getElementById("checkedInSSMU").checked = false;
		document.getElementById("checkInBraceletNumberSsmu").value = "";
		document.getElementById("checkInBraceletNumberFaculty").value = "";
		document.getElementById("checkInStudentId").value = "";
		document.getElementById("checkInPhone").value = "";
		document.getElementById("checkInPasskey").value = "";
		document.getElementById("checkInEmail").value = "";
	}
	
	function checkForm(form) {
		var confirmationText = "";
		
		// get everything from the form
		var checkedInSsmu = form.elements['checkedInSSMU'].checked;
		var braceletNumberSsmu = form.elements['braceletNumberSsmu'].value;
		var phoneNumber = form.elements['phone'].value;
		var paymentMarked = form.elements['paid'].checked;
		var email = form.elements['email'].value;
		var studentId = form.elements['studentId'].value;
		
		// check the studentid
		if(checkedInSsmu) {
			if(braceletNumberSsmu == "" || isNaN(braceletNumberSsmu)) {
				// it's not a number
				confirmationText = "The SSMU bracelet number is empty or not valid.  ";
			}
		}
		
		// check the phone number
		if(phoneNumber != "" && phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
			var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if(!phoneNumber.match(phoneno)) {
				confirmationText += "The phone number is not a valid format.  "
			}
		}
		
		// see if they checked payment
		if(paymentMarked == false) {
			// they didn't mark it as paid
			confirmationText += "The participant has not been marked as paid.  ";
		}
		
		// check for email
		if(email == "") {
			confirmationText += "There was no email address.  ";
		}
		
		// check for student ID
		if(studentId == "" || isNaN(studentId) || studentId.length != 9) {
			confirmationText += "There was a missing or invalid student ID.";
		}
		
		// Continue with processing or return errors
		if(confirmationText == "") {
			return true;
		} else {
			alert(confirmationText);
			return false;
		}
	}
	
	function checkPhoneFormat(phoneField) {
		// get the value
		var phoneNumber = phoneField.value;
		
		phoneFormat = "Unknown";
		if(phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
			var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if(phoneNumber.match(phoneno)) {
				phoneFormat = "North America";
			}
		} else {
			// it starts with a plus, but first check to see if it is a +1 and then 10 digits (a North America number)
			if(phoneNumber.length == 12 && phoneNumber.charAt(1) == '1') {
				// we have a north america number still
				phoneFormat = "North America";
			} else {
				phoneFormat = "International";
			}
		}
		
		$("#phoneFormat").html(phoneFormat);
	}
</script>
</body>
</html>