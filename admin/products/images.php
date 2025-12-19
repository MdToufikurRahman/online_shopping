<?php include_once("../includes/db_config.php"); 
$pid=$_GET['id'];
?>

<form method="post" enctype="multipart/form-data">
<input type="file" name="image">
Primary <input type="checkbox" name="primary">
<input type="submit" name="submit">
</form>

<?php
if(isset($_POST['submit'])){
    $img="uploads/".time().$_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'],"../".$img);
    $pri=isset($_POST['primary'])?1:0;

    $db->query("INSERT INTO product_images (product_id,image_path,is_primary)
                VALUES ('$pid','$img','$pri')");
}
?>