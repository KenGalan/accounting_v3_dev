<?php
// if($_SESSION){
if (!isset($_SESSION['ppc'])) {
  header('location:index.php');
  die();
}
// }
?>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<title>A/P DISTRIBUTION</title>
<!-- Favicon-->
<link rel="icon" href="public/assets/images/mrp_icon.png" type="image/x-icon">

<!-- Bootstrap Core Css -->
<link href="public/theme/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="public/theme/css/jquery.dataTables.min.css" rel="stylesheet">

<!-- Waves Effect Css -->
<link href="public/theme/plugins/node-waves/waves.css" rel="stylesheet" />

<!-- Animation Css -->
<link href="public/theme/plugins/animate-css/animate.css" rel="stylesheet" />

<!-- Morris Chart Css-->
<link href="public/theme/plugins/morrisjs/morris.css" rel="stylesheet" />

<link href="public/theme/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="public/theme/plugins/waitme/waitMe.min.css" rel="stylesheet">
<!-- Sweetalert Css -->
<link href="public/theme/plugins/sweetalert/sweetalert.css" rel="stylesheet" />

<!-- JQuery Nestable Css -->
<link href="public/theme/plugins/nestable/jquery-nestable.css" rel="stylesheet" />

<!-- Custom Css -->
<link href="public/theme/css/wizard.css" rel="stylesheet">
<link href="public/theme/css/style.css" rel="stylesheet">
<!-- <link href="public/theme/css/normalize.css" rel="stylesheet">
    <link href="public/theme/css/skeleton.css" rel="stylesheet"> -->


<!-- Bootstrap Material Datetime Picker Css -->
<link href="public/theme/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />


<!-- Bootstrap Select Css -->
<!-- <link href="public/theme/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" /> -->

<!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
<link href="public/theme/css/themes/all-themes.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="public/assets/plugins/chosen/chosen.css">
<!-- <link rel="stylesheet" type="text/css" href="public/assets/plugins/chosen/chosen-component.css">  -->
<link href="public/theme/css/toggle-switch.css" rel="stylesheet">
<link href="public/theme/css/custom.css" rel="stylesheet">


<!-- select2 -->
<link href="public/theme/plugins/select2/css/select2.min.css" rel="stylesheet">
<link href="public/theme/plugins/select2/bootstrap-select2.css" rel="stylesheet">


<!-- Jquery Core Js -->
<script src="public/theme/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap Core Js -->
<script src="public/theme/plugins/bootstrap/js/bootstrap.js"></script>
<script src="public/theme/plugins/jquery-datatable/jquery.dataTables.js"></script>
<script src="public/theme/js/bootstrap-datepicker.js"></script>
<script src="public/theme/js/wizard.min.js"></script>

<link href="public/theme/plugins/fixed-columns/fixedColumns.css" rel="stylesheet">
<link href="public/theme/plugins/jquery-ui/css/jquery-ui.css" rel="stylesheet">
<script src="public/theme/plugins/jquery-ui/js/jquery-ui.js"></script>

<link href="public/theme/plugins/jquery-datatable/select1.3.1/select.dataTables.min.css" rel="stylesheet">


<link href="public/theme/plugins/jquery-datatable/rowgroup1.1.2/rowGroup.css" rel="stylesheet">
<script src="public/theme/plugins/jquery-datatable/rowgroup1.1.2/rowGroup.js"></script>

<script src="public/theme/plugins/jquery-datatable/fnFakeRowspan.js"></script>

<!--Spectrum Plugins-->
<script src='public/theme/plugins/spectrum-1.8.1/spectrum.js'></script>
<link rel='stylesheet' href='public/theme/plugins/spectrum-1.8.1/spectrum.css' />

<script src="public/app/helpers.js"></script>

<!-- <script src="public/theme/plugins/jquery-datatable/select1.3.1/dataTables,select.min.js"></script> -->