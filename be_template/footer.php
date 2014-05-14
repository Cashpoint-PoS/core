<div id="footer">
  Copyright &copy; 2012 MSD &mdash; <?=DB::$querycount?> MySQL-Abfragen, gesamte MySQL-Zeit <?=DB::$querytime?> Sek. &mdash; PHP-Zeit <?=(microtime(true)+$GLOBALS["tpl_start"])?> Sek.
</div>
<script src="js/all.js"></script>
</body>
</html>
<!--
MySQL profiling:
<?
  print_r(DB::$queries);
?>
-->