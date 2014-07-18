<div id='topNav'>
    <?
    
    $f = dirname($file);
    $f =  basename($f);
    
    ?>
	
   	<div class='navButtonTop<? if (strcmp("overview", $f) == 0) {echo " current";} ?>'><a href='/admin/overview/'>Overview</a></div>
   	<div class='navButtonTop<? if (strcmp("participants", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/'>Participants</a></div>
   	<div class='navButtonTop<? if (strcmp("staff", $f) == 0) {echo " current";} ?>'><a href='/admin/staff/'>Staff</a></div>
   	<div class='navButtonTop<? if (strcmp("reports", $f) == 0) {echo " current";} ?>'><a href='/admin/reports/'>Reports</a></div>
   	<div class='navButtonTop<? if (strcmp("management", $f) == 0) {echo " current";} ?>'><a href='/admin/management/'>Event Management</a></div>
</div>
