
<?
$f =  basename($file);
?>

<div id='secondNav'>
    <div class='navButton<? if (strcmp("index.php", $f) == 0) {echo " current";} ?>'><a href='/admin/management/' >Event Details</a></div>
    <div class='navButton<? if (strcmp("schedule.php", $f) == 0) {echo " current";} ?>'><a href='/admin/management/schedule.php'>Calendar Schedule</a></div>
    <!--
   	<div class='navButton<? if (strcmp("payschedule.php", $f) == 0) {echo " current";} ?>'><a href='/admin/management/payschedule.php'>Payment Schedule</a></div>
   	<div class='navButton<? if (strcmp("staffroles.php", $f) == 0) {echo " current";} ?>'><a href='/admin/management/staffroles.php'>Staff Roles</a></div>
    -->
</div>
