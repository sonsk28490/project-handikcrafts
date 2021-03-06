<?php
require_once('../admin/adminheader.php');

$errors = [];
function getExtension($str) {
    $i = strrpos($str,".");
    if (!$i) { return ""; }
    $length = strlen($str) - $i;
    $str2 = substr($str,$i+1,$length);
    return $str2;
}
function isFormValidated(){
    global $errors;
    return count($errors) == 0;
}

if ($_SERVER["REQUEST_METHOD"] == 'POST'){
    $target_dir="uploadimage/";
    $target_file = $target_dir.basename($_FILES['newimage']['name']);
    if (empty($_POST['name'])){
        $errors[] = 'Product Name is required';
    }
    if (empty($_POST['Category'])){
        $errors[] = 'Category is required';
    }
    if (empty($_POST['Price'])){
        $errors[] = 'Price is required';
    }
    if (empty($_POST['information'])){
        $errors[] = 'Information is required';
    }
    if(!empty($_FILES['newimage']['name'])){
        if($_FILES['newimage']['error']>0){
            $errors[]= "Upload Image is error";
            print_r($_FILES);
        }else{
            unlink($_POST['UrloldImage']);
            move_uploaded_file($_FILES['newimage']['tmp_name'],$target_file);
        }
        $filename = stripslashes($_FILES['newimage']['name']);
        $extension = getExtension($filename);
        $extension = strtolower($extension);
        if (($extension != "jpg") && ($extension != "jpeg") && ($extension !="png") && ($extension != "gif"))
        {
            $errors[]= 'Please select an image';
        }
    }
    if (isFormValidated()){
        //do update
        $_POST['Image'] = $target_dir.$_FILES['newimage']['name'];
        if(empty($_FILES['newimage']['name'])){
            $_POST['Image'] = $_POST['UrloldImage'];
        }

        $product = [];
        
        $product['ProductID'] = $_POST['ProductID'];
        $product['Name'] = $_POST['name'];
        $product['CatID'] = $_POST['Category'];
        $product['Image'] = $_POST['Image'];
        $product['Price'] = $_POST['Price'];
        $product['Quantity'] = $_POST['Quantity'];
        $product['Information'] = $_POST['information'];

        update_products($product);
        $_SESSION['Update'] = 'Update Successfull';
        redirect_to('viewproduct.php');
    }
}else {
    if(!isset($_GET['id'])) {
        redirect_to('viewproduct.php');
    }
    $id = $_GET['id'];
    $product = find_products_by_id($id);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Edit Product</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <style>
        label {
            font-weight: bold;
        }
        .error {
            color: #FF0000;
        }
        div.error{
            border: thin solid red; 
            display: inline-block;
            padding: 5px;
        }
		body{
            margin:50px auto;
        }
    </style>
</head>
<body>
    <?php if ($_SERVER["REQUEST_METHOD"] == 'POST' && !isFormValidated()): ?> 
        <div class="error">
            <span> Please fix the following errors </span>
            <ul>
                <?php
                foreach ($errors as $key => $value){
                    if (!empty($value)){
                        echo '<li>', $value, '</li>';
                    }
                }
                ?>
            </ul>
        </div><br><br>
    <?php endif; ?>
    <br>
    <div class="row">
        <div class="col-xs-offset-4 col-xs-4">
            <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" role="form" enctype="multipart/form-data">
                <legend>Edit Product Form</legend>
                <input type="hidden" class="form-control" name="ProductID" value="<?php echo isFormValidated()? $product['ProductID']: $_POST['ProductID'] ?>" >
                <div class="form-group">
                    <label for="name">Product name:</label>
                    <input type="text" class="form-control" name="name" value="<?php echo isFormValidated()? $product['Name']: $_POST['name'] ?>">
                </div>
                <div class="form-group">
                    <label for="CatID">CatID:</label>
                    <select name="Category" id="">
                        <option value="">--Choose Category--</option>
                        <?php
                            $categories_set = find_all_categories();
                            $count = mysqli_num_rows($categories_set);
                            $x = [];
                            for ($i = 0; $i < $count; $i++):
                                $category = mysqli_fetch_assoc($categories_set); 
                                $x[$i]=$category['CatID'];
                        ?>
                        <option value="<?php echo $category['CatID']; ?>" <?php if(!empty($_POST['Category']) && $_POST['Category'] == $category['CatID']) echo 'selected';?>><?php echo $category['Name']; ?></option>
                        <?php
                            endfor; 
                            mysqli_free_result($categories_set);
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="oldimage">Old Image: </label>
                    <img height=200 src="<?php echo $product['Image'];?>">
                </div>
                <div class="form-group" disabled>
                    <label for="UrloldImage">Url of Old Image: </label>
                    <input type="text" class="form-control" name="UrloldImage" value="<?php echo isFormValidated()? $product['Image']: $_POST['UrloldImage'];?>">
                </div>
                <div class="form-group">
                    <label for="newimage">New Image:</label>
                    <input type="file" class="form-control" name="newimage">
                </div>
                <div class="form-group">
                    <label for="Price">Price:</label>
                    <input type="number" step="0.01" class="form-control" name="Price" value="<?php echo isFormValidated()? $product['Price']: $_POST['Price'] ?>">
                </div>
                <div class="form-group">
                    <label for="Quantity">Quantity:</label>
                    <input type="number" step="0.01" class="form-control" name="Quantity" value="<?php echo isFormValidated()? $product['Quantity']: $_POST['Quantity'] ?>">
                </div>
                <div class="form-group">
                    <label for="information">Information:</label>
                    <input type="text" class="form-control" name="information" value="<?php echo isFormValidated()? $product['Information']: $_POST['information'] ?>">
                    <!-- <textarea class="form-control col-xs-4" name="information" placeholder="Write information of product.." style="height:200px" value="<?php echo isFormValidated()? $product['Information']: $_POST['information'] ?>"></textarea> -->
                </div>
                <input type="submit"  name="submit"  class="btn btn-success" value="Submit">
                <input type="reset" name="reset" value="Reset" class="btn btn-danger">
            </form>
            <br><br>
            <a href="viewproduct.php">Return</a> 
        </div>
    </div>
    
    
</body>
</html>
<?php 
db_disconnect($db);
?>