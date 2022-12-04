<?php 

session_start();

if (isset($_SESSION['employee_id']) && isset($_SESSION['employee_name'])) {

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>

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


function Print_Table($employees){

if ($employees) {
    echo "<br>";
    echo "<table style = border: 1px solid; >";
    echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Name</th>";
            echo "<th>Address</th>";
            echo "<th>Phone_number</th>";
            echo "<th>Level</th>";
            echo "<th></th>";
            echo "<th></th>";
    echo "<tr>" ;
   
    foreach ($employees as $employee) {
        echo "<tr>";
            echo "<td>{$employee['employee_id']}</td>"; 
            echo "<td>{$employee['employee_name']}</td>"; 
            echo "<td>{$employee['address']}</td>";
            echo "<td>{$employee['phone_number']}</td>";
            echo "<td>{$employee['level']}</td>";
            echo "<td><a href='editemployee.php?id={$employee['employee_id']}'>Edit</a></td>";
            echo "<td><a href='delete.php?id={$employee['employee_id']}&table=employees&col=employee_id&page=employees.php' onclick='loadmessage()'>Delete</a></td>";
              
        echo "<tr>" ;
    }
    echo "</table>";
    }else{echo"<h2>There were no results matching the query</h2>";}
    echo "<br>";
}

$data = [
    'employee_name' => '',
    'sort_by' => '',
];

function get_employees(){
    global $pdo;
    global $data;
    
    $where = ' WHERE 1 = 1';

    if($data['employee_name']){
        $where .= " AND  employee_name like '%{$data['employee_name']}%' ";
    }
   

    $order='';
    if($data['sort_by']){
        list($field,$direction) = explode(':',$data['sort_by']);
        $order = " ORDER BY $field $direction ";
    }
    $sql = "SELECT * FROM `employees` $where $order;";
   
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC); 

}

function Display_Filter(){
    global $data;
    global $pdo;
    
        echo "<div>";
            echo "<form>";

                echo "Name <input name='employee_name' value ='{$data['employee_name']}' /><br><br>";
                            
                if(isset($_GET['sort_by'])){$select= $_GET['sort_by']; }else{$select = "";} 
                echo"Sort by: <select name = 'sort_by' >";
                    echo"<option value='' >-- Select Sotr --</option\n >";
                    echo"<option value='employee_name:ASC' ";if($select == 'employee_name:ASC')echo" selected";  echo" > Name(ACS)</option\n >";
                    echo"<option value='employee_name:DESC'  ";if($select == 'employee_name:DESC')echo" selected";  echo"> Name(DECS)</option\n >"; 
                    echo"<option value='level:ASC'  ";if($select == 'level:ASC')echo" selected";  echo">Level (ACS)</option\n >";
                    echo"<option value='level:DESC' ";if($select == 'level:DESC')echo" selected";  echo" >Level (DECS)</option\n >";
                echo"</select>";
                echo"<br><br>";
                echo "<button>Go</button><br><br>";
            echo "</form>";
        echo "</div>";
    }

;

$data['employee_name'] = isset($_GET['employee_name'])? $_GET['employee_name'] : '';
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
        <a href="editEmployee.php?id="><h3>[Add Employee]</h3></a>
        
        <h3>Filters</h3>
        <?php display_filter();?>
        
      </div>

      <div id="content">
        <!-- insert the page content here -->
        <h1>List of employees</h1>
      
        <?php Print_Table(get_employees());?>
        
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
