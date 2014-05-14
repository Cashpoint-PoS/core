<html>
<head>
</head>
<body>
<?
if(isset($_POST["trace"])) {
?>
<table border="1">
<tr><th>query</th><th>runtime</th><th>trace</th></tr>
<?
  $tr=json_decode($_POST["trace"]);
  $qs=$tr->allqueries;
  foreach($qs as $q) {
?>
  <tr><td><?=$q->q ?></td><td><?=$q->rt ?></td><td><pre><?= $q->t ?></pre></td></tr>
<?
  }
?>
</table>
<?
} else {
?>
<form action="traceprofile.php" method="post">
<textarea name="trace" cols="100" rows="20">
</textarea>
<input type="submit" value="show trace" />
</form>
<?
}
?>
</body>
</html>