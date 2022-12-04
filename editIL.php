
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
    
    <title>Add\Edit Issuance</title>

    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>
<?php 
$pdo = require('db/connect.php');
if(!$pdo){
    die();
}

function select_from_($table){
    global $pdo;
    $sql = "SELECT * FROM $table";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_by_id($id){
    global $pdo;
    $sql = "SELECT * FROM issuance_literature 
    INNER JOIN books ON issuance_literature.book_id = books.book_id
     INNER JOIN employees ON issuance_literature.employee_id = employees.employee_id
     INNER JOIN readers ON issuance_literature.reader_id = readers.reader_id   WHERE id = $id; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_data(){

    $id = $_GET['id'];

    if($id)
    {

        $issuance_literature = get_by_id($id);


        foreach($issuance_literature as $k)
        {
            $title = $k['book_id'].'.'.$k['title'];
            $reader_name = $k['reader_id'].'.'.$k['reader_name'];
            $employee_name= $k['employee_id'].'.'.$k['employee_name'];
            $issuance_date = $k['issuance_date'];
            $return_date = $k['return_date'];
        }
        if($return_date == null||$return_date == '0000-00-00'){ $return_date = date('Y-m-d');}
    
        $data = [
            'title' => $title,
            'reader_name' => $reader_name,
            'employee_name' => $employee_name,
            'issuance_date'=> $issuance_date,
            'return_date'=> $return_date ];

        return $data;
    }
    else{
    
        $issuance_date = date('Y-m-d');
        $employee =  $_SESSION['employee_id'].'.'.$_SESSION['employee_name'];
        $data = [
            'title' => '',
            'reader_name' =>'',
            'employee_name' => $employee,
            'issuance_date'=> $issuance_date,
            'return_date'=> ''];

        return $data;
    }
}

function verify_data($data){
        $errors=[];
        $books = select_from_(' books');
        $users = select_from_(' readers ');
        $found = true;
       
       

        if(!$data['title']){
            $errors['title'] = 'required';
        }
        else{
            $id = $_GET['id'];

            if(!$id)
            {
                $found =false;
                $title = explode(".", $data['title'] ) ;
                foreach($books as $book){
    
                    if($book['book_id'] == $title[0] )
                    {
                        if($book['in_stock']!=null)
                        {
                            $found =true;
                            break;
                        }
                        
                    }
                }
            }

           
        }

        if($found == false){
            $errors['title'] = 'out of stock';
        }

        if(!$data['reader_name']){
            $errors['reader_name'] = 'required';
        }else{
            $reader_name = explode(".", $data['reader_name'] ) ;
            $found =false;
            foreach($users as $user){
                if($user['reader_id'] == $reader_name[0] ){
                    $found =true;
                    break;
                }
            }
        }
        if($found == false){
            $errors['reader_name'] = 'unknown user';
        }


        if(!$data['issuance_date']){
            $errors['issuance_date'] = 'required';

        }else{
            $found =true;
            if($data['issuance_date']>date('Y-m-d')){
                $found =false;
            }
            if($found == false){
                $errors['issuance_date'] = 'are you from the future?';
            }
        }
       

        
        if(($data['return_date']<$data['issuance_date'])&&($data['return_date']!=null)){
            $errors['return_date'] = "can`t be less then IssuanceDate";
        }

        return $errors;

}


function save_data($data,$id){
    global $pdo;
    $bindData=[];
    $set = [];

    $book_id = explode(".", $data['title'] ) ;
    $reader_id = explode(".", $data['reader_name'] ) ;
    $employee_id = explode(".", $data['employee_name'] ) ;

    $data_to_save = [
        'book_id' => $book_id[0],
        'reader_id' =>$reader_id[0] ,
        'employee_id' =>  $employee_id[0],
        'issuance_date'=> $data['issuance_date'],
        'return_date'=> $data['return_date'] ];
  

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
        $sql = "UPDATE issuance_literature SET ". implode(", ",$set)." WHERE id = :id";
        $bindData[':id'] = $id;
        if($bindData[':return_date'] != null){
            $sql2 = "UPDATE books  SET in_stock = in_stock+1 WHERE book_id = {$bindData[':book_id']}";
            $pdo->query($sql2);
        }
        $iss = date_create($data_to_save['issuance_date']);
        $ret = date_create($data_to_save['return_date']);
        $isPenalty = date_diff($iss,$ret);

        if($isPenalty->format('%a')>'30'){
            $sql2 = "UPDATE readers  SET penalty = penalty+1 WHERE reader_id = {$reader_id[0]}";
            $pdo->query($sql2);
        }
        

    }else{
        $sql = "INSERT issuance_literature SET ". implode(", ",$set);
        if($bindData[':return_date'] == null){
            $sql2 = "UPDATE books  SET in_stock = in_stock-1 WHERE book_id = {$bindData[':book_id']} ;";
            $pdo->query($sql2);
        }   
    }
    
    $statement = $pdo->prepare($sql);
    $statement->execute($bindData);
    
    echo"Data updated successfully";
    if($id){return false;}
    return true;
}


function form($data,$errors,){

    $books = select_from_('books');
    $readers = select_from_('readers');
 
    
?>

    <form method="POST">

        <label>Title: </label>

        <input name="title" list="book_id" value="<?= $data['title'] ?>" type="text">
            <datalist id="book_id">
            <?php
                foreach($books as $book){
                    
                    echo "<option hidden='true' value='{$book['book_id']}.{$book['title']}'></option>";
                }
            ?>     
            </datalist>

        <?= isset($errors['title'])?"<span>{$errors['title']}</span>":'' ?>
        <br><br>

        <label>Reader_name</label> 
        <input name="reader_name" list="reader_id" value="<?= $data['reader_name'] ?>" type="text">
            <datalist id="reader_id">
            <?php
                foreach($readers as $reader){
                    
                    echo "<option hidden='true' value='{$reader['reader_id']}.{$reader['reader_name']}'></option>";
                }
            ?>     
            </datalist>
        <?= isset($errors['reader_name'])?"<span>{$errors['reader_name']}</span>":'' ?>
        <br><br>

        <label>Employee_name</label>
        
        <input name="employee_name"   value="<?=$data['employee_name'];?>" type="text" readonly>
            
        <br><br>

        <label>Issuance_Date</label>
        <input name= "issuance_date" value ="<?=$data['issuance_date'] ?>" type = date />
        <?= isset($errors['issuance_date'])?"<span>{$errors['issuance_date']}</span>":'' ?>
        <br><br> 
       
        <label>Return_Date</label>
        <input name= "return_date" value ="<?=$data['return_date'] ?>" type = date />
        <?= isset($errors['return_date'])?"<span>{$errors['return_date']}</span>":'' ?>
        <br><br>  
           
        <button id="button">Save</button>
    </form>
    <?php
    }

    function processInputData(){
       
        $data = [
            'title' => isset($_POST['title'])? $_POST['title'] : '',
            'reader_name' => isset($_POST['reader_name'])? $_POST['reader_name'] : '',
            'employee_name' => isset($_POST['employee_name'])? $_POST['employee_name'] : '',
            'issuance_date'=>isset($_POST['issuance_date'])? $_POST['issuance_date'] : '',
            'return_date'=> isset($_POST['return_date'])? $_POST['return_date'] : '' ];
        return $data;
    }
    ?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="issuance_literature.php">< Back</a></li>
        </ul>
                        
      </div>

    
   
    <div id="editForm">
      <?php 
        try{
            $data = processInputData();
            $errors = [];
            $continue = false;
           
            if(isset($_POST['title'])){
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
                    'title' => '',
                    'reader_name' =>'',
                    'employee_name' => '',
                    'issuance_date'=> $issuance_date,
                    'return_date'=> ''];
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