<?
$tpl_title="Login";
$tpl_styles=array("reset.css","text.css","buttons.css","theme-default.css","login.css");

require("be_template/header.php");
?>
<body>

<div id="login">
	<h1>Kassensystem</h1>
	<div id="login_panel">
		<form action="login.php" method="post" accept-charset="utf-8">
			<div class="login_fields">
				<div class="field">
					<label for="rfid">RFID Login</label>
					<input type="checkbox" name="rfid" id="rfid" />
				</div>
				
				<div class="field">
					<label for="username">Benutzername</label>
					<input type="text" name="username" value="" id="username" tabindex="2" placeholder="Benutzername" />
				</div>
				
				<div class="field">
					<label for="password">Passwort</label>
					<input type="password" name="password" value="" id="password" tabindex="3" placeholder="Passwort" />
				</div>
				
				<div class="field">
					<label for="tenant">Mandant</label>
					<input type="number" name="tenant" value="" id="tenant" tabindex="4" placeholder="1" value="1" />
				</div>
				
				<div class="field">
					<label for="target">Ziel</label>
					<select id="target" name="target" tabindex="5">
						<option value="app" selected="selected">Applikation</option>
						<option value="backend">Backend</option>
					</select>
				</div>
			</div> <!-- .login_fields -->
			
			<div class="login_actions">
				<button type="submit" class="btn btn-primary" tabindex="4">Login</button>
			</div>
		</form>
	</div> <!-- #login_panel -->		
</div> <!-- #login -->
<?
require("be_template/footer.php");