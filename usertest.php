 <?php
  $src = $_SERVER['REDIRECT_URL'];
  $src = $_SERVER['DOCUMENT_ROOT'].substr($src, 0 ,  strrpos($src,".") ).".php";
  if(file_exists($src)){
    #echo "$src<hr>\n";
    echo highlight_file($src,1);
  }else{
    echo "Error: $src not found";
  }
?>