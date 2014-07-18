<div id='topNav'>
    <?
    
    $f = dirname($file);
    $f =  basename($f);
    
    ?>
	
   	<div class='navButtonTop<? if (strcmp("overview", $f) == 0) {echo " current";} ?>'><a href='/orientation/kiosk/overview/'>Overview</a></div>
   	<div class='navButtonTop<? if (strcmp("participants", $f) == 0) {echo " current";} ?>'><a href='/orientation/kiosk/participants/'>Participants</a></div>
   	<div class='navButtonTop<? if (strcmp("reports", $f) == 0) {echo " current";} ?>'><a href='/orientation/kiosk/reports/'>Reports</a></div>
</div>
