
<?
$f =  basename($file);
?>

<div id='secondNav'>
    <div class='navButton<? if (strcmp("index.php", $f) == 0) {echo " current";} ?>'><a href='/admin/reports/' >General</a></div>
    <div class='navButton<? if (strcmp("payments.php", $f) == 0) {echo " current";} ?>'><a href='/admin/reports/payments.php'>Payment Summary</a></div>
</div>
