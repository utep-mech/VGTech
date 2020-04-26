<html>
<?php 
 $urlHOME = 'http://localhost'; $url = "$urlHOME/dimagRemote.php"; 
echo "
<head>
<script> 
  var url = '$url'; 
  var mathJaxLib = '$urlHOME/Softwares/MathJax/MathJax.js?config=TeX-AMS_HTML';
  //var mathJaxLib = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML';
</script>

<!--
        <script src='//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js' type='text/javascript'></script>
        <script src='http://dynamic-learning-framework.org/ckeditor4.3.2/ckeditor.js'></script>
        <script src='http://dynamic-learning-framework.org/ckeditor4.3.2/adapters/jquery.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML'></script>
-->
        <script src='$urlHOME/jquery.min.js' type='text/javascript'></script>
        <script src='$urlHOME/Softwares/ckeditor4.3.2/ckeditor.js'></script>
        <script src='$urlHOME/Softwares/ckeditor4.3.2/adapters/jquery.js'></script>
        <script src='$urlHOME/Softwares/MathJax/MathJax.js?config=TeX-AMS_HTML'></script>

	<script src='$urlHOME/form.js' type='text/javascript'></script>
        <script src='$urlHOME/JS/camera.js' type='text/javascript'></script>
	<script src='$urlHOME/dimag5.js' type='text/javascript'></script>
</head>
"; 
?>

<body id=bodymain data-logged=0 data-group="guest" data-Folder="CASSSTEM">

 
</body>

</html>
