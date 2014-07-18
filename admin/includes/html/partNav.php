
<?
$f =  basename($file);

if($currentEvent != null) {
?>
<div id='secondNav'>
    <div class='navButton<? if (strcmp("index.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/'>Quick Search</a></div>
    <div class='navButton<? if (strcmp("quickcheckin.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/quickcheckin.php'>Quick Check In</a></div>
    <div class='navButton<? if (strcmp("full.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/full.php'>All Participants</a></div>
    <div class='navButton<? if (strcmp("checkin.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/checkin.php'>All Check Ins</a></div>
    <div class='navButton<? if (strcmp("register.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/register.php'>Cross Register</a></div>
</div>
<?
} else {
?>
<div id='secondNav'>
    <div class='navButton<? if (strcmp("index.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/'>Quick Search</a></div>
    <div class='navButton<? if (strcmp("full.php", $f) == 0) {echo " current";} ?>'><a href='/admin/participants/full.php'>All Participants</a></div>
</div>
<?
}
?>