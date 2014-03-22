<!DOCTYPE html>
<html>
<head>
<link href="<?echo base_url();?>css/board.css" rel="stylesheet" type="text/css"/>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
<script src="<?= base_url() ?>/js/detectWin.js"></script>
<script>
var otherUser = "<?= $otherUser->login ?>";
var user = "<?= $user->login ?>";
var status = "<?= $status ?>";
// make sure these JQuery functions only fire when all DOM objects have loaded
$(function(){
	// every 2 seconds, use ajax querying for updates
	$('body').everyTime(2000,function(){
		if (status == 'waiting') {
			$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
				if (data && data.status=='rejected') {
					alert("Sorry, your invitation to play was declined!");
					window.location.href = '<?= base_url() ?>arcade/index';
				}
				if (data && data.status=='accepted') {
					status = 'playing';
					$('#status').html('Playing ' + otherUser);
					// set current turn to that of the player who started the match
					var url = "<?= base_url() ?>board/setGameState";
					$.post(url,'turn='+user);
				}
					
			});
		}
		// see if there are any new messages
		var url = "<?= base_url() ?>board/getMsg";
		$.getJSON(url, function (data,text,jqXHR){
			if (data && data.status=='success') {
				var conversation = $("[name='conversation']").val();
				var msg = data.message;
				if (msg.length > 0)
					$("[name='conversation']").val(conversation + "\n" + otherUser + ": " + msg);
			}
			$("[name='conversation']").scrollTop($("[name='conversation']")[0].scrollHeight);
		});
	});
	// every 500 ms, check whose turn it is and reprint the board if necessary
	$('#turn').everyTime(500,'updater',function(){
		// grab the game state via ajax
		var url = "<?= base_url() ?>board/getGameState";
		$.getJSON(url, function (data,text,jqXHR){
			if (data && data.status=='success') {
				// set the turn
				var turn = data.turn;
				$('#turn').html(turn);
				// if it's the opponent's turn, set the font colour to red
				if (turn==otherUser)
					$('#turn').css('color','red');
				// get the filled cells (JSON string)
				var filled = JSON.parse(data.filled);
				// populate the filled cells appropriately
				for (var key in filled) {
					$("td[id="+key+"]").val(filled[key]);
					// if it's the player's own cell
					if (filled[key]==user)
						$("td[id="+key+"]").html('C'); 
					else // if it's the opponent's cell
						$("td[id="+key+"]").html('X');
				}
				// see if anyone has won the game
				var win = data.win;
				// if so, stop updating the board
				if (win==user || win==otherUser){ 
					$('#turn').stopTime('updater');
					if (win==user)
						$('#win').html("You win");
					else if (win==otherUser)
						$('#win').html("You lose");
				}
			}
		});
	});
	// reprint the chat box whenever the user sends a message (via POST form)
	$('form').submit(function(){
		var arguments = $(this).serialize();
		var url = "<?= base_url() ?>board/postMsg";
		$.post(url,arguments, function (data,textStatus,jqXHR){
			var conversation = $("[name='conversation']").val();
			var msg = $('[name=msg]').val();
			$("[name='conversation']").val(conversation + "\n" + user + ": " + msg);
			$("input[name='msg']").val('');
		});
		$("[name='conversation']").scrollTop($("[name='conversation']")[0].scrollHeight);
		return false;
	});
	// make chat box always scroll to bottom
	$("[name='conversation']").change(function() {
		console.log('shit');
		$("[name='conversation']").scrollTop($("[name='conversation']")[0].scrollHeight);
	});
	// event handler for clicking on the game's board
	$('td').click(function(){
		// first check to see if it's the user's turn, otherwise exit
		// also if someone won the game, exit
		if ($('#turn').html()!=user || $('#win').html()=="You win" || $('#win').html()=="You lose")
			return;		
		// coordinates are the ID tag of the table cell
		var position = $(this).attr('id');
		// x-coord is 2nd char, y-coord is 4th char
		var x = position[1];
		var y = position[3];
		// search current column to see if there is space for a piece to "drop"
		var i = 0;
		for (i=0; i<5; i++){
			if ($("#x"+x+'y'+(i+1)).val()!='')
				break;
		}
		// fill in the space
		$("#x"+x+'y'+i).val(user);
		$("#x"+x+'y'+i).html('C');
		
		// keep track of filled cells
		var filled = {};
		// convert the board into an array and JSON it to the controller
		// to save space/time, just get filled cells, not empty ones
		$("td").each(function(){
			if ($(this).val()!="") {
				filled[$(this).attr('id')] = $(this).val();
			}
		});
		// see if this current move happens to be a winning move
		var win = "";
		if (checkWin(parseInt(i),parseInt(x),user,filled)){
			win = user;
		}
		// JSON the board and the turn (make it that of other player)
		var url = "<?= base_url() ?>board/setGameState";
		var tmp = JSON.stringify(filled);
		var arguments = {"turn":otherUser, "filled":tmp, "win":win};
		$.post(url,arguments);
	});
});
</script>
</head> 
<body>  
<h1>Game Area</h1>
<div>
Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
</div>
<div id='status'> 
<?php 
	if ($status == "playing")
		echo "Playing " . $otherUser->login;
	else
		echo "Wating on " . $otherUser->login;
?>
</div>
<p>Current turn: <span id='turn'></span></p>
<?php 
	echo form_textarea('conversation');
	
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();
	// print out the game board
	echo "<br>\n<table>\n";
	for ($i=0;$i<6;$i++){
		echo "<tr>";
		for ($j=0;$j<7;$j++){
			echo "<td id='x{$j}y{$i}' value=''></td>";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	
?>
<p><span id='win'></span></p>
</body>
</html>
