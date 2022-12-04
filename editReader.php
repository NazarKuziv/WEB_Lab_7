
<?php 

session_start();

if (isset($_SESSION['employee_id']) && isset($_SESSION['employee_name'])) {

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Add\Edit Reader</title>

    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>
<?php 
$pdo = require('db/connect.php');
if(!$pdo){
    die();
}



function get_by_id($id){
    global $pdo;
    $sql = "SELECT * FROM readers WHERE reader_id = $id ORDER BY reader_id DESC LIMIT 1; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_by_phone($phone){
    global $pdo;
    $sql = "SELECT phone_number FROM readers WHERE phone_number = $phone ORDER BY phone_number DESC LIMIT 1; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_data(){

    $id = $_GET['id'];

    if($id)
    {

        $readers = get_by_id($id);


        foreach($readers as $k)
        {
            $reader_name = $k['reader_name'];
            $address = $k['address'];
            $phone_number = $k['phone_number'];
            $penalty = $k['penalty'];
        }
        $data = [
            
            'reader_name' => $reader_name,
            'address' => $address,
            'phone_number'=> $phone_number,
            'penalty'=> $penalty];

        return $data;
    }
    else{
    
       
   
        $data = [
            
            'reader_name' => '',
            'address' => '',
            'phone_number'=> '',
            'penalty'=> ''];


        return $data;
    }
}

function verify_data($data){
        $errors=[];
      
        if(!$data['reader_name']){
            $errors['reader_name'] = 'required';
        }

        if(!$data['address']){
            $errors['address'] = 'required';
        }

        if(!$data['phone_number']){
            $errors['phone_number'] = 'required';

        }elseif(strlen($data['phone_number']) !=10){
            $errors['phone_number'] = 'wrong number';

        }else{

            $id = $_GET['id'];
            if($id){
                $reader = get_by_id($id);
                foreach($reader as $r){
                    $phone= $r['phone_number'];
                }
                
                if($data['phone_number']!=$phone){
                    $reader = get_by_phone($data['phone_number']);
                    if($reader){
                        $errors['phone_number'] = 'the number is already in the database';
                    }
                }

            }else{
                $reader = get_by_phone($data['phone_number']);
                if($reader){
                    $errors['phone_number'] = 'the number is already in the database';
                }
            }
           
           
        }
       
       

        return $errors;

}


function save_data($data_to_save,$id){
    global $pdo;
    $bindData=[];
    $set = [];

    $data_to_save['penalty'] =  $data_to_save['penalty']==''? '0': $data_to_save['penalty'];
    

    foreach($data_to_save as $field=>$value){
        if($value ==''){
            $bindData[':'.$field ] = null;
        }else{
            $bindData[':'.$field ] = $value;
        }
        
        $set[] = "$field = :$field";
    }
    $id = $_GET['id'];
    
    if($id){
        $sql = "UPDATE readers SET ". implode(", ",$set)." WHERE reader_id = :id";
        $bindData[':id'] = $id;
        

    }else{
        $sql = "INSERT readers SET ". implode(", ",$set);
    }
    
    $statement = $pdo->prepare($sql);
    $statement->execute($bindData);
    
    echo"Data updated successfully";
    if($id){return false;}
    return true;
}


function form($data,$errors,){


?>

    <form method="POST">

    <label>Name: </label>
    <input name="reader_name" value="<?= $data['reader_name'] ?>" type="text"><br>
    <?= isset($errors['reader_name'])?"<span>{$errors['reader_name']}</span>":'' ?>
    <br><br>

    <label>Address: </label>
    <input name="address" value="<?= $data['address'] ?>" type="text"><br>
    <?= isset($errors['address'])?"<span>{$errors['address']}</span>":'' ?>
    <br><br>
    
     
    <label>Phone_number:</label>
    <input name="phone_number" value="<?= $data['phone_number'] ?>" type="text"><br>
    <?= isset($errors['phone_number'])?"<span>{$errors['phone_number']}</span>":'' ?>
    <br><br>

    <label>Penalty: </label>
    <input name="penalty" value="<?= $data['penalty'] ?>" type="number"><br>
    <?= isset($errors['penalty'])?"<span>{$errors['penalty']}</span>":'' ?>
    <br><br>

           
        <button id="button">Save</button>
    </form>
    <?php
    }

    function processInputData(){
       
        $data = [
            'reader_name' => isset($_POST['reader_name'])? $_POST['reader_name'] : '',
            'address' => isset($_POST['address'])? $_POST['address'] : '',
            'phone_number' => isset($_POST['phone_number'])? $_POST['phone_number'] : '',
            'penalty'=>isset($_POST['penalty'])? $_POST['penalty'] : '',
        ];
        return $data;
    }
    ?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="readers.php">< Back</a></li>
        </ul>
                        
      </div>

    
   
    <div id="editForm">
      <?php 
        try{
            $data = processInputData();
            $errors = [];
            $continue = false;
            
            if(isset($_POST['reader_name'])){
                $errors = verify_data($data);
                
                if(empty($errors)){
                    $continue = save_data($data,null);
                }else{
                    
                    form($data,$errors);
                }

            }else{
                
                if($data){
                    $data = get_data();
                    form($data,$errors);
                }else{
                    echo"Something went wrong. Try again later";
                }
            }
            if($continue == true){
                $issuance_date = date('Y-m-d');
   
                $data = [
                    'reader_name' => '',
                    'address' =>'',
                    'phone_number' => '',
                    'penalty'=> '',];
                    form($data,$errors);
                }
                    
            
        
            
            
        
        }catch(Exception $e){
            print "Error:".$e->getMessage()."<br>";
        }
        
    ?>
    </div>
    
</div>    
</body>
</html><?php 
}else{
     header("Location: index.php");
     exit();
}
 ?>