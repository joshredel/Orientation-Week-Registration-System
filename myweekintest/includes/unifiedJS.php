    <script>
		$(document).ready(function() {
			// see how many events we have left to select an option for
			calculateEventsAwaitingSelection();
      	});
		
		// determines how many events we have left to select an option for
		function calculateEventsAwaitingSelection() {
			$.post("/myweek/eventsAwaitingSelection.php", { passkey: '<?= $_GET['passkey'] ?>' },
					function(data){
						//alert("Data loaded: " + data);
						$(".remainingEventOptionsNumber").html(data);
						
						// see if we should hide or show the notification
						if(parseInt(data) == 0) {
							$(".remainingEventOptions").addClass("hidden");
						} else {
							$(".remainingEventOptions").removeClass("hidden");
						}
					});
		}
	</script>