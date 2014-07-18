<?
// get the recorded message, sender info, and conference room
$messageUrl = $_REQUEST['url'];
$from = $_REQUEST['from'];
$conferenceName = $_REQUEST['conference'];
?>
<Response>
	<Say>Hello. <?= $from ?> is requesting that you join a conference call with the coordinators with the following description:</Say>
    <Play><?= $messageUrl ?></Play>
    <Say>We are now entering you into the conference room.</Say>
    <Dial>
    	<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars"><?= $conferenceName ?></Conference>
    </Dial>
    <Say>Goodbye.</Say>
</Response>