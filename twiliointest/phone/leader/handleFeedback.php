<?
// initialize services
require_once("../../../functions.php");
$recordingService = new services\FeedbackRecordingService();
session_start();

// get the caller
$caller = $_SESSION['caller'];

// create the recording object
$recording = new org\fos\FeedbackRecording();
$recording->submitterId = $caller->id;
$recording->url = $_REQUEST['RecordingUrl'];
$recording->recordingCategory = org\fos\FeedbackRecording::LEADER;

$recordingService->saveFeedbackRecording($recording);

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>Thank you for the message.</Say>
    <Redirect>/twiliointest/phone/leader/</Redirect>
</Response>