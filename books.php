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
    <title>Books</title>

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

function select_from($tables1,$tables2,$id){
    global $pdo;

    $tables1 =="books_genres"?$row = "genre_id":$row = "author_id ";
    $sql = "SELECT * FROM $tables1 INNER JOIN $tables2 ON  $tables1.$tables2 = $tables2.$row WHERE books = $id";   
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}
function Print_Table($books){

if ($books) {
    echo "<br>";
    echo "<table style = border: 1px solid; >";
    echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Title</th>";
            echo "<th>Author</th>";
            echo "<th>Genre</th>";
            echo "<th>Publisher</th>";
            echo "<th>Language</th>";
            echo "<th>Publish_Date</th>";
            echo "<th>Num Of Copies</th>";
            echo "<th>In Stock</th>";
            echo "<th></th>";
            echo "<th></th>";
    echo "<tr>" ;
   
    foreach ($books as $book) {
        echo "<tr>";
            echo "<td>{$book['book_id']}</td>"; 
            echo "<td>{$book['title']}</td>";
            echo "<td>";
            $authors = select_from("authors_books","authors",$book['book_id']);
            foreach($authors as $a){
                echo " ".$a['author_name']." ";
            } 
            echo "</td>";
            echo "<td>";
            $genres = select_from("books_genres","genres",$book['book_id']);
            foreach($genres as $g){
                echo " ".$g['genre_name']." ";
            } 
            echo "</td>";
            echo "<td>{$book['publisher_name']}</td>";
            echo "<td>{$book['language']}</td>";
            echo "<td>{$book['publish_date']}</td>";
            echo "<td>{$book['number_of_copies']}</td>";
            echo "<td>{$book['in_stock']}</td>";
            echo "<td><a href='editBook.php?id={$book['book_id']}'>Edit</a></td>";
            echo "<td><a href='delete.php?id={$book['book_id']}&table=books&col=book_id&page=books.php' onclick='loadmessage()'>Delete</a></td>";
              
        echo "<tr>" ;
    }
    echo "</table>";
    }else{echo"<h2>There were no results matching the query</h2>";}
    echo "<br>";
}

$data = [
    'title' => '',
    'authors' => '',
    'genres' => '',
    'publisher_id'=> '',
    'language'=> '',
    'publish_date_from'=> '',
    'publish_date_to'=> '',
    'sort_by' => '',
];

function authors_genres_filter($books,$tables,$id){
    global $pdo;

    $i=0;
    $tables =="books_genres"?$row = "genres":$row = "authors ";
    $sql = "SELECT books FROM $tables WHERE {$row} = {$id}";   
    $statement = $pdo->query($sql);
    $book_id =  $statement->fetchAll(PDO::FETCH_ASSOC);
    $is=false;
    $remove = array();

    foreach($books as $b ){
        foreach($book_id as $id){
            if($b['book_id']==$id['books']){
               $is = true;
               break;
              
            } 
        } 
        if($is==true){
            $is = false;
        }else{
           $remove[$i] = $b;
           $i++;
        } 
    }
    foreach($remove as $value){
        $key =  array_search($value,$books);
        unset($books[$key]);
    }
    return $books;
   
}

function get_books(){
    global $pdo;
    global $data;
    
    $where = ' WHERE 1 = 1';

    if($data['title']){
        $where .= " AND  title like '%{$data['title']}%' ";
    }
   
    if($data['publisher_id']){
        $where .= " AND  publisher_id = {$data['publisher_id']} ";
    }

     if($data['language']){
        $where .= " AND  language like '%{$data['language']}%' ";
    }

    if($data['publish_date_from']){
        $where .= " AND  publish_date >= '{$data['publish_date_from']}' ";
    }
    if($data['publish_date_to']){
        $where .= " AND  publish_date <='{$data['publish_date_to']}' ";
    }

    $order='';
    if($data['sort_by']){
        list($field,$direction) = explode(':',$data['sort_by']);
        $order = " ORDER BY $field $direction ";
    }
    $sql = "SELECT * FROM `books` LEFT JOIN publishers ON books.publisher_id = publishers.publisher_id $where $order;";
   
    $statement = $pdo->query($sql);
    $books = $statement->fetchAll(PDO::FETCH_ASSOC);
   
    if($data['authors']){
         $books = authors_genres_filter($books,"authors_books",$data['authors']);      
    }
    
    if($data['genres']){
        $books = authors_genres_filter($books,"books_genres",$data['genres']); 
    }

    return $books;

}

function Display_Filter(){
    global $data;
    global $pdo;
    
        echo "<div>";
            echo "<form>";

                echo "Title <input name='title' value ='{$data['title']}' /><br><br>";


                if(isset($_GET['authors'])){$select= $_GET['authors']; }else{$select = "";} 
                echo"Author: <select name = 'authors' >";
                    echo"<option value='' >-- Select Author --</option\n >";
                    $sql = "SELECT * FROM authors";   
                    $statement = $pdo->query($sql);
                    $authors =  $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach($authors as $g){
                        $id = $g['author_id'];
                        echo"<option value='{$id}' ";if($select == $id)echo" selected";  echo" >{$g['author_name']}</option\n >";
                    }   
                echo"</select>";
                echo"<br><br>";

                if(isset($_GET['genres'])){$select= $_GET['genres']; }else{$select = "";} 
                echo"Genre: <select name = 'genres' >";
                    echo"<option value='' >-- Select Genre --</option\n >";
                    $sql = "SELECT * FROM genres ";   
                    $statement = $pdo->query($sql);
                    $genres  =  $statement->fetchAll(PDO::FETCH_ASSOC);
                  
                    foreach($genres as $g){
                        $id = $g['genre_id'];
                        echo"<option value='{$id}' ";if($select == $id)echo" selected";  echo" >{$g['genre_name']}</option\n >";
                    }   
                echo"</select>";
                echo"<br><br>";

                echo "Language <input name='language' value ='{$data['language']}' /><br><br>";
                echo "Publish Date_From <input name='publish_date_from' value ='{$data['publish_date_from']}' type = date /><br><br>";
                echo "Publish Date_To <input name='publish_date_to' value ='{$data['publish_date_to']}' type = date /><br><br>";
                            
                if(isset($_GET['sort_by'])){$select= $_GET['sort_by']; }else{$select = "";} 
                echo"Sort by: <select name = 'sort_by' >";
                    echo"<option value='' >-- Select Sotr --</option\n >";
                    echo"<option value='title:ASC' ";if($select == 'title:ASC')echo" selected";  echo" >Title Namec(ACS)</option\n >";
                    echo"<option value='title:DESC'  ";if($select == 'title:DESC')echo" selected";  echo">Title Namec(DECS)</option\n >"; 
                    echo"<option value='publish_date:ASC'  ";if($select == 'publish_date:ASC')echo" selected";  echo">Publish Date (ACS)</option\n >";
                    echo"<option value='publish_date:DESC' ";if($select == 'publish_date:DESC')echo" selected";  echo" >Publish Date (DECS)</option\n >";
                echo"</select>";
                echo"<br><br>";
                echo "<button>Go</button><br><br>";
            echo "</form>";
        echo "</div>";
    }

;

$data['title'] = isset($_GET['title'])? $_GET['title'] : '';
$data['authors'] = isset($_GET['authors'])? $_GET['authors'] : '';
$data['genres'] = isset($_GET['genres'])? $_GET['genres'] : '';
$data['publisher_id'] = isset($_GET['publisher_id'])? $_GET['publisher_id'] : '';
$data['language'] = isset($_GET['language'])? $_GET['language'] : '';
$data['publish_date_from'] = isset($_GET['publish_date_from'])? $_GET['publish_date_from'] : '';
$data['publish_date_to'] = isset($_GET['publish_date_to'])? $_GET['publish_date_to'] : '';
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
        <a href="editBook.php?id="><h3>[Add Book]</h3></a>
        <a href="addAuthor.php?id="><h3>[Add Author]</h3></a>
        <a href="addGenre.php?id="><h3>[Add Genre]</h3></a>
        <a href="addPublisher.php?id="><h3>[Add Publisher]</h3></a>
        <h3>Filters</h3>
        <?php display_filter();?>
        
      </div>

      <div id="content">
        <!-- insert the page content here -->
        <h1>List of literature</h1>
      
        <?php Print_Table(get_books());?>
        
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
