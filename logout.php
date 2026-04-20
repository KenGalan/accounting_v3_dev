<?php
// session_unset();
// session_destroy();
unset($_SESSION['ppc']);

header("Location: index.php", "Cache-Control: no-store, no-cache, must-revalidate");
?>