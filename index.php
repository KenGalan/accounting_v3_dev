<?php
if($_SESSION){
    if(isset($_SESSION['ppc'])){
        header('Location: home.php');  
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>A/P DISTRIBUTION</title>
    
    <!-- Favicon-->
    <link rel="icon" href="public/assets/images/mrp_icon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="public/theme/css/login/css/util.css">
    <link rel="stylesheet" type="text/css" href="public/theme/css/login/css/main.css">

</head>

<body>
    <section class="content-full">
        <?php include("controller/" . getFileName());?>
    </section>

   
</body>
</html>