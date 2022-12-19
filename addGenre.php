
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
    
    <title>Add Genre</title>

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

    $sql = "SELECT * FROM `genres`";
    $statement = $pdo->query($sql);
    $genres = $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($genres) {
        echo "<br>";
        echo "<table style = border: 1px solid; >";
        echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>Name</th>";  
                // echo "<th></th>";
        echo "<tr>" ;
       
        foreach ($genres as $genre) {
            echo "<tr>";
                echo "<td>{$genre['genre_id']}</td>"; 
                echo "<td>{$genre['genre_name']}</td>";
                // echo "<td><a href='delete.php?id={$genre['genre_id']}&table=genres&col=genre_id&page=addGenre.php' onclick='loadmessage()'>Delete</a></td>";
                  
            echo "<tr>" ;
        }
        echo "</table>";
        }else{echo"<h2>Tables is empty</h2>";}
        echo "<br>";
    }


function get_data(){
   
        $data = [
            'genre_name' => '',
        ];

        return $data;
}

function verify_data($data){
        $errors=[];

        if(!$data['genre_name']){
            $errors['genre_name'] = 'required';
        }

    
        return $errors;

}


function save_data($data,$id){
    global $pdo;
   
    $bindData=[];
    $set = [];


    $data_to_save = [
        'genre_name' => $data['genre_name'],
     ];
  
   

    foreach($data_to_save as $field=>$value){
        if($value ==''){
            $bindData[':'.$field ] = null;
        }else{
            $bindData[':'.$field ] = $value;
        }
        
        $set[] = "$field = :$field";
    }
  
    $sql = "INSERT genres SET ". implode(", ",$set); 
    
    $statement = $pdo->prepare($sql);
    $statement->execute($bindData);
   

    echo"DataBase updated successfully";
    return true;
}

function processInputData(){
   
    $data = [
        'genre_name' => isset($_POST['genre_name'])? $_POST['genre_name'] : '',
        ];
    return $data;
}
 function form($data,$errors,){
   ?>

<form method="POST">

    <label>Name: </label>
    <input name="genre_name" value="<?= $data['genre_name'] ?>" type="text"><br>
    <?= isset($errors['genre_name'])?"<span>{$errors['genre_name']}</span>":'' ?>
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
        <h3>Add  Genre</h3>
      <?php 
        try{
        
            $data = processInputData();
            $errors = [];
            $continue = false;
            
            if(isset($_POST['genre_name'])){
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
                    'genre_name' => '',
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
        <h1>List of genres</h1>
      
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