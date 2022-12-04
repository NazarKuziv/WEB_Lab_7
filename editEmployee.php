
<?php 

session_start();

if (isset($_SESSION['employee_id']) && isset($_SESSION['employee_name'])) {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Add\Edit Employee</title>

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
    $sql = "SELECT * FROM employees WHERE employee_id = $id ORDER BY employee_id DESC LIMIT 1; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_by_phone($phone){
    global $pdo;
    $sql = "SELECT phone_number FROM employees WHERE phone_number = $phone ORDER BY phone_number DESC LIMIT 1; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_data(){

    $id = $_GET['id'];

    if($id)
    {

        $Employees = get_by_id($id);


        foreach($Employees as $k)
        {
            $employee_name = $k['employee_name'];
            $address = $k['address'];
            $phone_number = $k['phone_number'];
            $password = $k['password'];
            $level = $k['level'];
        }
        $data = [
            
            'employee_name' => $employee_name,
            'address' => $address,
            'phone_number'=> $phone_number,
            'password'=> $password,
            'level'=> $level];

        return $data;
    }
    else{
        $data = [
            
            'employee_name' => '',
            'address' => '',
            'phone_number'=> '',
            'password'=> '',
            'level'=> ''];


        return $data;
    }
}

function verify_data($data){
        $errors=[];
      
        if(!$data['employee_name']){
            $errors['employee_name'] = 'required';
        }

        if(!$data['address']){
            $errors['address'] = 'required';
        }

        if(!$data['password']){
            $errors['password'] = 'required';
        }

        if(!$data['level']){
            $errors['level'] = 'required';
        }else{
            if($data['level']<1 || $data['level']>3){
                $errors['level'] = 'level can be from 1 to 3';
            }
        }

        if(!$data['phone_number']){
            $errors['phone_number'] = 'required';

        }elseif(strlen($data['phone_number']) !=10){
            $errors['phone_number'] = 'wrong number';

        }else{

            $id = $_GET['id'];
            if($id){
                $employees = get_by_id($id);
                foreach($employees as $r){
                    $phone= $r['phone_number'];
                }
                
                if($data['phone_number']!=$phone){
                    $employee = get_by_phone($data['phone_number']);
                    if($employee){
                        $errors['phone_number'] = 'the number is already in the database';
                    }
                }

            }else{
                $employee = get_by_phone($data['phone_number']);
                if($employee){
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
        $sql = "UPDATE employees SET ". implode(", ",$set)." WHERE employee_id = :id";
        $bindData[':id'] = $id;
        

    }else{
        $sql = "INSERT employees SET ". implode(", ",$set);
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
    <input name="employee_name" value="<?= $data['employee_name'] ?>" type="text"><br>
    <?= isset($errors['employee_name'])?"<span>{$errors['employee_name']}</span>":'' ?>
    <br><br>

    <label>Address: </label>
    <input name="address" value="<?= $data['address'] ?>" type="text"><br>
    <?= isset($errors['address'])?"<span>{$errors['address']}</span>":'' ?>
    <br><br>
    
     
    <label>Phone_number:</label>
    <input name="phone_number" value="<?= $data['phone_number'] ?>" type="text"><br>
    <?= isset($errors['phone_number'])?"<span>{$errors['phone_number']}</span>":'' ?>
    <br><br>

    <label>Password:</label>
    <input name="password" value="<?= $data['password'] ?>" type="text"><br>
    <?= isset($errors['password'])?"<span>{$errors['password']}</span>":'' ?>
    <br><br>

    <label>Level: </label>
    <input name="level" value="<?= $data['level'] ?>" type="number"><br>
    <?= isset($errors['level'])?"<span>{$errors['level']}</span>":'' ?>
    <br><br>

           
        <button id="button">Save</button>
    </form>
    <?php
    }

    function processInputData(){
       
        $data = [
            'employee_name' => isset($_POST['employee_name'])? $_POST['employee_name'] : '',
            'address' => isset($_POST['address'])? $_POST['address'] : '',
            'phone_number' => isset($_POST['phone_number'])? $_POST['phone_number'] : '',
            'password'=>isset($_POST['password'])? $_POST['password'] : '',
            'level'=>isset($_POST['level'])? $_POST['level'] : '',
        ];
        return $data;
    }
    ?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="Employees.php">< Back</a></li>
        </ul>
                        
      </div>

    
   
    <div id="editForm">
      <?php 
        try{
            $data = processInputData();
            $errors = [];
            $continue = false;
            
            if(isset($_POST['employee_name'])){
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
            
                    'employee_name' => '',
                    'address' => '',
                    'phone_number'=> '',
                    'password'=> '',
                    'level'=> ''];
                    form($data,$errors);
                }
                    
            
        
            
            
        
        }catch(Exception $e){
            print "Error:".$e->getMessage()."<br>";
        }
        
    ?>
    </div>
    
</div>

        
</body>
</html>
<?php 
}else{
     header("Location: index.php");
     exit();
}
 ?>