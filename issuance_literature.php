
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
    <title>Issuance literature</title>

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
function select_from($id){
    global $pdo;

    $sql = "SELECT * FROM authors_books INNER JOIN authors ON  authors_books.authors = authors.author_id WHERE books = $id";   
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function Print_Table($issuance_literature){

    echo "<br>";
    echo "<table>";
    echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Reader Name</th>";
            echo "<th>Employee Name</th>";
            echo "<th>Title</th>";
            echo "<th>Author</th>";
            echo "<th>IssuanceDate</th>";
            echo "<th>ReturnDate</th>";
            echo "<th> </th>";
            echo "<th> </th>";
    echo "<tr>" ;

   
    foreach ($issuance_literature as $k) 
    {
            echo "<tr>";
            echo "<td>{$k['id']}</td>";
            echo "<td>{$k['reader_name']}</td>";
            echo "<td>{$k['employee_name']}</td>";
            echo "<td>{$k['title']}</td>";
            echo "<td>";
            $authors = select_from($k['book_id']);
            foreach($authors as $a){
                echo " ".$a['author_name']." ";
            } 
            echo "</td>";
            echo "<td>{$k['issuance_date']}</td>";
            echo "<td>{$k['return_date']}</td>";
            echo "<td><a href='editIL.php?id={$k['id']}'>Edit</a></td>";
            echo "<td><a href='delete.php?id={$k['id']}&table=issuance_literature&col=id&page=issuance_literature.php' onclick='loadmessage()'>Delete</a></td>";
            
            echo "<tr>";
       
    }
    echo "</table>";
    
    echo "<br>";
}

$data = [
    'title' => '',
    'reader_name' => '',
    'employee_name' => '',
    'issuance_date_from'=> '',
    'issuance_date_to'=> '',
    'return_date_from'=> '',
    'return_date_to'=> '',
    'unreturned' => '',
    'sort_by' => '',
];



function get_issuance_literature(){
    global $pdo;
    global $data;
    
    $where = ' WHERE 1 = 1';

    if($data['title']){
        $where .= " AND  title like '%{$data['title']}%' ";
    }
    if($data['reader_name']){
        $where .= " AND  reader_name like '%{$data['reader_name']}%' ";
    }
    if($data['employee_name']){
        $where .= " AND  employee_name like '%{$data['employee_name']}%' ";
    }
    if($data['issuance_date_from']  ){
        $where .= " AND  issuance_date >= '{$data['issuance_date_from']}' ";
    }
    if($data['issuance_date_to'] ){
        $where .= " AND  issuance_date <='{$data['issuance_date_to']}' ";
    }
   
    if($data['return_date_from']  ){
        $where .= " AND  return_date >= '{$data['return_date_from']}' ";
    }
    if($data['return_date_to'] ){
        $where .= " AND  return_date <='{$data['return_date_to']}' ";
    }
    if($data['unreturned'] ){
        $where .= " AND  return_date IS NUll ";
    }

    $order='';
    if($data['sort_by']){
        list($field,$direction) = explode(':',$data['sort_by']);
        $order = " ORDER BY $field $direction ";
    }
    
    $sql = " SELECT * FROM `issuance_literature` 
    INNER JOIN readers ON issuance_literature.reader_id = readers.reader_id 
    INNER JOIN employees ON issuance_literature.employee_id = employees.employee_id 
    INNER JOIN books ON issuance_literature.book_id = books.book_id 
    LEFT JOIN publishers ON books.publisher_id = publishers.publisher_id 
    $where $order;";
    
    $statement = $pdo->query($sql);
    $issuance_literature = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $issuance_literature;

}

function Display_Filter(){
    global $data;
    
    
    echo "<div>";
    echo "<form>";
        echo "Title <input name='title' value ='{$data['title']}' /><br><br>";
        echo "Reader Name <input name='reader_name' value ='{$data['reader_name']}' /><br><br>";
        echo "Employee Name <input name='employee_name' value ='{$data['employee_name']}' /><br><br>";
        echo "Issuance Date From <input name='issuance_date_from' value ='{$data['issuance_date_from']}' type = date /><br><br>";
        echo "Issuance Date To <input name='issuance_date_to' value ='{$data['issuance_date_to']}' type = date /><br><br>";
        echo "Return Date From <input name='return_date_from' value ='{$data['return_date_from']}' type = date /><br><br>";
        echo "Return Date To <input name='return_date_to' value ='{$data['return_date_to']}' type = date /><br><br>";
        echo "Unreturned books <input name='unreturned' id='unreturned' value = 'checked' ";if(isset($_GET['unreturned'])) echo "checked='checked'"; echo " type = checkbox /><br>";
                    
        if(isset($_GET['sort_by'])){$select= $_GET['sort_by']; }else{$select = " ";} 
        echo"<br>Sort by: <select name = 'sort_by' >";
            echo"<option value='' >-- Select Sort --</option\n >";
            echo"<option value='title:ASC' ";if($select == 'title:ASC')echo" selected";  echo" >Title (ACS)</option\n >";
            echo"<option value='title:DESC'  ";if($select == 'title:DESC')echo" selected";  echo">Title (DECS)</option\n >";
            echo"<option value='reader_name:ASC'  ";if($select == 'reader_name:ASC')echo" selected";  echo">Reader Name(ACS)</option\n >";
            echo"<option value='reader_name:DESC'  ";if($select == 'reader_name:DESC')echo" selected";  echo">Reader Name(DECS)</option\n >";
            echo"<option value='employee_name:ASC'  ";if($select == 'employee_name:ASC')echo" selected";  echo">Employee Namec(ACS)</option\n >";
            echo"<option value='employee_name:DESC'  ";if($select == 'employee_name:DESC')echo" selected";  echo">Employee Namec(DECS)</option\n >";
            echo"<option value='issuance_date:ASC'  ";if($select == 'issuance_date:ASC')echo" selected";  echo">Issuance Date (ACS)</option\n >";
            echo"<option value='issuance_date:DESC' ";if($select == 'issuance_date:DESC')echo" selected";  echo" >Issuance Date (DECS)</option\n >";
            echo"<option value='return_date:ASC'  ";if($select == 'return_date:ASC')echo" selected";  echo">Return Date (ACS)</option\n >";
            echo"<option value='return_date:DESC' ";if($select == 'return_date:DESC')echo" selected";  echo" >Return Date (DECS)</option\n >";
        echo"</select><br>";
        echo "<br><button>Go</button><br><br>";

    echo "</form>";
echo "</div>";
    }

;

$data['title'] = isset($_GET['title'])? $_GET['title'] : '';
$data['reader_name'] = isset($_GET['reader_name'])? $_GET['reader_name'] : '';
$data['employee_name'] = isset($_GET['employee_name'])? $_GET['employee_name'] : '';
$data['issuance_date_from'] = isset($_GET['issuance_date_from'])? $_GET['issuance_date_from'] : '';
$data['issuance_date_to'] = isset($_GET['issuance_date_to'])? $_GET['issuance_date_to'] : '';
$data['return_date_from'] = isset($_GET['return_date_from'])? $_GET['return_date_from'] : '';
$data['return_date_to'] = isset($_GET['return_date_to'])? $_GET['return_date_to'] : '';
$data['unreturned'] = isset($_GET['unreturned'])? $_GET['unreturned'] : '';
$data['sort_by'] = isset($_GET['sort_by'])? $_GET['sort_by'] : '';

?>
<!-- html -->
<div id="main">
      <div id="menubar">
        <ul id="menu">
        <li ><a href="readers.php">Readers</a></li>
        <li ><a href="books.php" <?php if($_SESSION['level']<2){ echo "style='pointer-events: none'";} ?>>Books</a></li>
         
          <li ><a href="employees.php"<?php if($_SESSION['level']<3){ echo "style='pointer-events: none'";} ?>>Employees</a></li>
        </ul>

        <ul id="menu" style="float:right;">
          <li > <a>Hello, <?php echo $_SESSION['employee_name']; ?></a></li>
          <li ><a href="logout.php">Log out</a></li>
        </ul>
      </div>
  
      
    <div id="site_content">
      <div class="sidebar">
        <a href="editIL.php?id="><h3>[Add Issuance]</h3></a>
        <a href="news.php"><h3>[Send Newsletter]</h3></a>
        
        <h3>Filters</h3>
        <?php display_filter();?>
        
      </div>

      <div id="content">
        <!-- insert the page content here -->
        <h1>List of issuance literature</h1>
      
        <?php Print_Table(get_issuance_literature());?>
        
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