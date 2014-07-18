<?
// get the recorded message and sender info
$messageUrl = $_REQUEST['url'];
$from = $_REQUEST['from'];
?>
<Response>
	<Say>Hello. <?= $from ?> has sent you the following message:</Say>
    <Play><?= $messageUrl ?></Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/CallPoweredByTwilio.aif</Play>
    <Say>Goodbye.</Say>
</Response>