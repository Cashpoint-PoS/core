<?php
require("lib.php");
?>
<!doctype html>
<html>
<head>
<title>CashPoint</title>
<style type="text/css">
* {
margin:0;
padding:0;
}

#login {
width: 300px;
margin: 20px auto 0;
padding: 20px;
border: 1px solid grey;
}
#login h1 {
margin-bottom:10px;
}
#login_panel td,#login_panel th {
padding:5px 10px;
}
#login_panel button {
	width:100%;
	padding:15px;
}
</style>
</head>
<body>

<div id="login">
	<h1>CashPoint Login</h1>
	<div id="login_panel">
		<form action="login.php" method="post" accept-charset="utf-8">
			<table>
				<tr>
					<th><label for="username">Benutzername</label></th>
					<td><input type="text" name="username" value="" id="username" tabindex="2" placeholder="Benutzername" /></td>
				</tr>
				<tr>
					<th><label for="password">Passwort</label></th>
					<td><input type="password" name="password" value="" id="password" tabindex="3" placeholder="Passwort" /></td>
				</tr>
				<tr>
					<th><label for="tenant">Mandant</label></th>
					<td><input type="number" name="tenant" id="tenant" tabindex="4" placeholder="1" value="1" /></td>
				</tr>
				<tr>
					<th><label for="target">Ziel</label></th>
					<td>
						<select id="target" name="target" tabindex="5">
<?php
foreach($_core_targets as $plugin=>$data) {
	foreach($data as $idx=>$target) {
?>
							<option value="<?=$plugin."/".$idx?>"><?=$target["label"]?></option>
<?php
	}
}
?>
					</td>
				</tr>
				<tr>
					<td colspan="2"><button type="submit" class="btn btn-primary" tabindex="4">Login</button></td>
				</tr>
			</table>
		</form>
	</div> <!-- #login -->
</body>
</html>
