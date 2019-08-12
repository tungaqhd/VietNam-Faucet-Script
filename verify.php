<?php
if (!isset($_GET['k'])) { die("fuck you"); } ?>
<script type="text/javascript">
window.location.href = "dashboard.php?k=<?=$_GET['k']?>";
</script>