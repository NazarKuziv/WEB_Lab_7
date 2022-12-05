<?php 

session_start();

if (isset($_SESSION['employee_id']) && isset($_SESSION['employee_name'])) {

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Readers</title>

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


function Print_Table($readers){

if ($readers) {
    echo "<br>";
    echo "<table style = border: 1px solid; >";
    echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Name</th>";
            echo "<th>Address</th>";
            echo "<th>Phone_number</th>";
            echo "<th>Email</th>";
            echo "<th>Penalty</th>";
            echo "<th></th>";
            echo "<th></th>";
    echo "<tr>" ;
   
    foreach ($readers as $reader) {
        echo "<tr>";
            echo "<td>{$reader['reader_id']}</td>"; 
            echo "<td>{$reader['reader_name']}</td>"; 
            echo "<td>{$reader['address']}</td>";
            echo "<td>{$reader['phone_number']}</td>";
            echo "<td>{$reader['email']}</td>";
            echo "<td>{$reader['penalty']}</td>";
            echo "<td><a href='editReader.php?id={$reader['reader_id']}'>Edit</a></td>";
            echo "<td><a href='delete.php?id={$reader['reader_id']}&table=readers&col=reader_id&page=readers.php' onclick='loadmessage()'>Delete</a></td>";
              
        echo "<tr>" ;
    }
    echo "</table>";
    }else{echo"<h2>There were no results matching the query</h2>";}
    echo "<br>";
}

$data = [
    'reader_name' => '',
    'penalty_from'=> '',
    'penalty_to'=> '',
    'sort_by' => '',
];

function get_readers(){
    global $pdo;
    global $data;
    
    $where = ' WHERE 1 = 1';

    if($data['reader_name']){
        $where .= " AND  reader_name like '%{$data['reader_name']}%' ";
    }
   
    if($data['penalty_from']){
        $where .= " AND  penalty >= '{$data['penalty_from']}' ";
    }
    if($data['penalty_to']){
        $where .= " AND  penalty <='{$data['penalty_to']}' ";
    }

    $order='';
    if($data['sort_by']){
        list($field,$direction) = explode(':',$data['sort_by']);
        $order = " ORDER BY $field $direction ";
    }
    $sql = "SELECT * FROM `readers` $where $order;";
   
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC); 

}

function Display_Filter(){
    global $data;
    global $pdo;
    
        echo "<div>";
            echo "<form>";

                echo "Name <input name='reader_name' value ='{$data['reader_name']}' /><br><br>";

                echo "Penalty_from <input type='number' name='penalty_from' value ='{$data['penalty_from']}' /><br><br>";

                echo "Penalty_to <input type='number' name='penalty_to' value ='{$data['penalty_to']}' /><br><br>";
           
                            
                if(isset($_GET['sort_by'])){$select= $_GET['sort_by']; }else{$select = "";} 
                echo"Sort by: <select name = 'sort_by' >";
                    echo"<option value='' >-- Select Sotr --</option\n >";
                    echo"<option value='reader_name:ASC' ";if($select == 'reader_name:ASC')echo" selected";  echo" > Name(ACS)</option\n >";
                    echo"<option value='reader_name:DESC'  ";if($select == 'reader_name:DESC')echo" selected";  echo"> Name(DECS)</option\n >"; 
                    echo"<option value='penalty:ASC'  ";if($select == 'penalty:ASC')echo" selected";  echo">Penalty (ACS)</option\n >";
                    echo"<option value='penalty:DESC' ";if($select == 'penalty:DESC')echo" selected";  echo" >Penalty (DECS)</option\n >";
                echo"</select>";
                echo"<br><br>";
                echo "<button>Go</button><br><br>";
            echo "</form>";
        echo "</div>";
    }

;

$data['reader_name'] = isset($_GET['reader_name'])? $_GET['reader_name'] : '';
$data['penalty_from'] = isset($_GET['penalty_from'])? $_GET['penalty_from'] : '';
$data['penalty_to'] = isset($_GET['penalty_to'])? $_GET['penalty_to'] : '';
$data['sort_by'] = isset($_GET['sort_by'])? $_GET['sort_by'] : '';


?>
<!-- html -->
<div id="main">
      <div id="menubar">
        <ul id="menu">
          
          <li ><a href="issuance_literature.php">< Back</a></li>
        </ul>
      </div>
  
      
    <div id="site_content">
      <div class="sidebar">
        <a href="editReader.php?id="><h3>[Add Reader]</h3></a>
        
        <h3>Filters</h3>
        <?php display_filter();?>
        
      </div>

      <div id="content">
        <!-- insert the page content here -->
        <h1>List of readers</h1>
      
        <?php Print_Table(get_readers());?>
        
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