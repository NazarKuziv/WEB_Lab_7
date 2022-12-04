
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
    
    <title>Add Author</title>

    <link rel="stylesheet" href="./css/style.css">

    <script>
            function loadmessage() {
                alert('are you sure you want to delete?')
            } 
    </script>

</head>
<body>
<?php 
$pdo = require('db/connect.php');
if(!$pdo){
    die();
}
function Print_Table(){
    global $pdo;

    $sql = "SELECT * FROM `authors`";
    $statement = $pdo->query($sql);
    $authors = $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($authors) {
        echo "<br>";
        echo "<table style = border: 1px solid; >";
        echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>Name</th>";
                echo "<th>Country</th>";           
                echo "<th></th>";
        echo "<tr>" ;
       
        foreach ($authors as $author) {
            echo "<tr>";
                echo "<td>{$author['author_id']}</td>"; 
                echo "<td>{$author['author_name']}</td>";
                echo "<td>{$author['country']}</td>";
                echo "<td><a href='delete.php?id={$author['author_id']}&table=authors&col=author_id&page=addAuthor.php' onclick='loadmessage()'>Delete</a></td>";
                  
            echo "<tr>" ;
        }
        echo "</table>";
        }else{echo"<h2>Tables is empty</h2>";}
        echo "<br>";
    }


function get_data(){
   
        $data = [
            'author_name' => '',
            'country'=> '',
        ];

        return $data;
}

function verify_data($data){
        $errors=[];

        if(!$data['author_name']){
            $errors['author_name'] = 'required';
        }

        if(!$data['country']){
            $errors['country'] = 'required';
        }

    
        return $errors;

}


function save_data($data,$id){
    global $pdo;
   
    $bindData=[];
    $set = [];


    $data_to_save = [
        'author_name' => $data['author_name'],
        'country' => $data['country']
     ];
  
   

    foreach($data_to_save as $field=>$value){
        if($value ==''){
            $bindData[':'.$field ] = null;
        }else{
            $bindData[':'.$field ] = $value;
        }
        
        $set[] = "$field = :$field";
    }
  
    $sql = "INSERT authors SET ". implode(", ",$set); 
    
    $statement = $pdo->prepare($sql);
    $statement->execute($bindData);
   

    echo"DataBase updated successfully";
    return true;
}

function processInputData(){
   
    $data = [
        'author_name' => isset($_POST['author_name'])? $_POST['author_name'] : '',
        'country' => isset($_POST['country'])? $_POST['country'] : '',
        ];
    return $data;
}
 function form($data,$errors,){
   ?>

<form method="POST">

    <label>Name: </label>
    <input name="author_name" value="<?= $data['author_name'] ?>" type="text"><br>
    <?= isset($errors['author_name'])?"<span>{$errors['author_name']}</span>":'' ?>
    <br><br>
    <label>Country: </label>
    <input name="country" value="<?= $data['country'] ?>" type="text"><br>
    <?= isset($errors['country'])?"<span>{$errors['country']}</span>":'' ?>
    <br><br>

    
    <button>Save</button>
    </form><?php } ?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="books.php">< Back</a></li>
        </ul>
                        
      </div>
      <div id="site_content">
      <div class="sidebar">
        <h3>Add  Author</h3>
      <?php 
        try{
        
            $data = processInputData();
            $errors = [];
            $continue = false;
            
            if(isset($_POST['author_name'])){
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
                $data = [
                    'author_name' => '',
                    'country'=> '',
                ];
                form($data,$errors);
            }
            
            
        
        }catch(Exception $e){
            print "Error:".$e->getMessage()."<br>";
        }
        
    ?>
        
      </div>

      <div id="content">
        <!-- insert the page content here -->
        <h1>List of authors</h1>
      
       <?php Print_Table();?> 
        
      </div>
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