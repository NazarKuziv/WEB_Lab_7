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
    
    <title>Add\Edit Books</title>

    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>
<?php 
$pdo = require('db/connect.php');
if(!$pdo){
    die();
}

$authors_id = array();
$genres_id = array();


function select_from_($table){
    global $pdo;
    $sql = "SELECT * FROM $table";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_by_id($id){
    global $pdo;
    $sql = "SELECT * FROM `books` LEFT JOIN publishers ON books.publisher_id = publishers.publisher_id WHERE book_id = $id; ";
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_list_id($table,$id){
    global $pdo;
    $sql = "SELECT * FROM $table WHERE books = $id; ";
    $statement = $pdo->query($sql);
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $table =="books_genres"?$col = "genres":$col = "authors";
    $i=0;
    $arr = array();
    foreach($data as $item){
        $arr[$i]=$item[$col];
        $i++;
    }
    return $arr;
}

function get_data(){
    global $authors_id;
    global $genres_id;

    $id = $_GET['id'];

    if($id)
    {

        $books = get_by_id($id);
        $authors_id = get_list_id('authors_books',$id);
        $genres_id = get_list_id('books_genres',$id);
        
        foreach($books as $k)
        {
            $title = $k['title'];
            $publisher_name = $k['publisher_id'].'.'.$k['publisher_name'];
            $language= $k['language'];
            $publish_date = $k['publish_date'];
            $number_of_copies = $k['number_of_copies'];
            $in_stock = $k['in_stock'];
        }
        
    
        $data = [
            'title' => $title,
            'publisher_id'=> $publisher_name,
            'language'=> $language,
            'publish_date'=> $publish_date,
            'number_of_copies'=> $number_of_copies,
            'in_stock' => $in_stock,
        ];

        return $data;
    }
    else{
   
        $data = [
            'title' => '',
            'publisher_id'=> '',
            'language'=> '',
            'publish_date'=> '',
            'number_of_copies'=> '',
            'in_stock' => '',
        ];

        return $data;
    }
}

function verify_data($data){
        $errors=[];
        $found = true;

        if(!$data['title']){
            $errors['title'] = 'required';
        }

        if(!$data['publisher_id']){
            $errors['publisher_id'] = 'required';

        } else{
            $publishers = select_from_(' publishers ');
            $publisher_id = explode(".", $data['publisher_id'] ) ;
            $found =false;
            foreach($publishers as $publisher){
                if($publisher['publisher_id'] == $publisher_id[0] ){
                    $found =true;
                    break;
                }
            }
        }
        if($found == false){
            $errors['publisher_id'] = 'unknown publisher';
        }

        if(!$data['language']){
            $errors['language'] = 'required';
        }


        if(!$data['publish_date']){
            $errors['publish_date'] = 'required';

        }else{
            $found =true;
            if($data['publish_date']>date('Y-m-d')){
                $found =false;
            }
            if($found == false){
                $errors['publish_date'] = 'are you from the future?';
            }
        }
       
        if(!$data['number_of_copies']){
            $errors['number_of_copies'] = 'required';
        }

        if(!$data['in_stock']){
            $errors['in_stock'] = 'required';

        }else{
            $found =true;
            if($data['in_stock']>$data['number_of_copies']){
                $found =false;
            }
            if($found == false){
                $errors['in_stock'] = 'in_stock must be equal or less number_of_copies';
            }
        }
    
        return $errors;

}


function save_data($data,$id){
    global $pdo;
    global $authors_id;
    global $genres_id;
    $bindData=[];
    $set = [];

    $publisher_id = explode(".", $data['publisher_id'] ) ;

    $data_to_save = [
        'title' => $data['title'],
        'publisher_id'=> $publisher_id[0],
        'language'=> $data['language'],
        'publish_date'=> $data['publish_date'],
        'number_of_copies'=>$data['number_of_copies'],
        'in_stock' => $data['in_stock'],
     ];
  
   

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
        $sql = "UPDATE books SET ". implode(", ",$set)." WHERE book_id = :id";
        $bindData[':id'] = $id;
        

    }else{
        $sql = "INSERT books SET ". implode(", ",$set); 
    }
   

    $statement = $pdo->prepare($sql);
    $statement->execute($bindData);
    $book_id = $pdo->lastInsertId();

    if($id){
        if($authors_id){
            $sql = "DELETE FROM authors_books WHERE books = :id";
            $smt = $pdo->prepare($sql);
            $smt->execute([':id'=>$id]);
            $val ="";
            foreach($authors_id as $a){
                $val = $val."($a,$id),";
            }
            $val= rtrim($val,",");
            $sql = "INSERT INTO authors_books (authors,books) VALUES ".$val;
          
            $statement = $pdo->prepare($sql);
            $statement->execute();
        }

        if($genres_id){
            $sql = "DELETE FROM books_genres WHERE books = :id";
            $smt = $pdo->prepare($sql);
            $smt->execute([':id'=>$id]);
            $val ="";
            foreach($genres_id as $g){
                $val = $val."($id,$g),";
              
            }
            $val= rtrim($val,",");
            $sql = "INSERT INTO books_genres (books,genres) VALUES ".$val;
            $statement = $pdo->prepare($sql);
            $statement->execute();
        }

    }else{

        if($authors_id){
            
            $val ="";
            foreach($authors_id as $a){
                $val = $val."($a,$book_id),";
            }
            $val= rtrim($val,",");
            $sql = "INSERT INTO authors_books (authors,books) VALUES ".$val;
            $statement = $pdo->prepare($sql);
            $statement->execute();
        }

        if($genres_id){
            $val ="";
            foreach($genres_id as $g){
                $val = $val."($book_id,$g),";
            }
            $val= rtrim($val,",");
            $sql = "INSERT INTO books_genres (books,genres) VALUES ".$val;
            $statement = $pdo->prepare($sql);
            $statement->execute();
        }

    }
   

    echo"DataBase updated successfully";
    if($id){return false;}
    return true;
}

function processInputData(){
    global $authors_id;
    global $genres_id;
    $authors_id =isset($_POST['authors'])? $_POST['authors'] : null;
    $genres_id =isset($_POST['genres'])? $_POST['genres'] : null;

    $data = [
        'title' => isset($_POST['title'])? $_POST['title'] : '',
        'publisher_id' => isset($_POST['publisher_id'])? $_POST['publisher_id'] : '',
        'language' => isset($_POST['language'])? $_POST['language'] : '',
        'publish_date'=>isset($_POST['publish_date'])? $_POST['publish_date'] : '',
        'number_of_copies'=> isset($_POST['number_of_copies'])? $_POST['number_of_copies'] : '',
        'in_stock'=>isset($_POST['in_stock'])? $_POST['in_stock'] : '', ];
    return $data;
}
 function form($data,$errors,){
    global $authors_id;
    global $genres_id;

    $authors = select_from_('authors');
    $genres = select_from_('genres');
    $publishers = select_from_('publishers');
   ?>

<form method="POST">

    <label>Title: </label>
    <input name="title" value="<?= $data['title'] ?>" type="text"><br>
    <?= isset($errors['title'])?"<span>{$errors['title']}</span>":'' ?>
    <br><br>

    <label>Author: </label>
    <div id="list1" class="dropdown-check-list" tabindex="100">
        <p class="anchor">--Select Authors--</p>
            <ul class="items">
                <?php
                    foreach($authors as $author){ 
                        if($authors_id){
                            $id = $author['author_id'];
                            $checked = '';
                            foreach($authors_id as $value){
                                if($value==$id){
                                    $checked = 'checked';
                                    break;
                                }
                            }
                            echo "<li><input type='checkbox' name='authors[]' value='{$id}' $checked /> ".$author['author_name']."  </li>";
                        }else{
                            echo "<li><input type='checkbox' name='authors[]' value='{$author['author_id']}' /> ".$author['author_name']."  </li>";
                        }
                        
                    }
                    ?>  
            </ul>
    </div>
    <br>
    <br><br>

    <label>Genres: </label>
    <div id="list2" class="dropdown-check-list" tabindex="100">
        <p class="anchor"> --Select Genre--</p>
            <ul class="items">
                <?php
                    foreach($genres as $genre){  
                        
                        if($authors_id){
                            $id = $genre['genre_id'];
                            $checked = '';
                            foreach($genres_id as $value){
                                if($value==$id){
                                    $checked = 'checked';
                                    break;
                                }
                            }
                            echo "<li><input type='checkbox' name='genres[]' value='{$id}' $checked /> ".$genre['genre_name']."  </li>";
                        }else{
                            echo "<li><input type='checkbox' name='genres[]' value='{$genre['genre_id']}' /> ".$genre['genre_name']."   </li>";
                        }

                    }
                    ?>  
            </ul>
    </div><br>
    <br><br>

    <label>Publisher</label> 
    <input name="publisher_id" list="publisher_id" value="<?= $data['publisher_id'] ?>" type="text">
        <datalist id="publisher_id">
        <?php
            foreach($publishers as $publisher){
                
                echo "<option hidden='true' value='{$publisher['publisher_id']}.{$publisher['publisher_name']}'></option>";
            }
        ?>     
        </datalist><br>
    <?= isset($errors['publisher_id'])?"<span>{$errors['publisher_id']}</span>":'' ?>
    <br><br>
     
    <label>Language:</label>
    <input name="language" value="<?= $data['language'] ?>" type="text"><br>
    <?= isset($errors['language'])?"<span>{$errors['language']}</span>":'' ?>
    <br><br>


    <label>Publish_date:</label>
    <input name= "publish_date" value ="<?=$data['publish_date'] ?>" type = date /><br>
    <?= isset($errors['publish_date'])?"<span>{$errors['publish_date']}</span>":'' ?>
    <br><br> 

    <label>Number_of_copies: </label>
    <input name="number_of_copies" value="<?= $data['number_of_copies'] ?>" type="number"><br>
    <?= isset($errors['number_of_copies'])?"<span>{$errors['number_of_copies']}</span>":'' ?>
    <br><br>

    <label>In_stock: </label>
    <input name="in_stock" value="<?= $data['in_stock'] ?>" type="number"><br>
    <?= isset($errors['in_stock'])?"<span>{$errors['in_stock']}</span>":'' ?>
    <br><br>

    
    <button id="button">Save</button>
    </form><?php } ?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="books.php">< Back</a></li>
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
                        $data = [
                    'title' => '',
                    'publisher_id'=> '',
                    'language'=> '',
                    'publish_date'=> '',
                    'number_of_copies'=> '',
                    'in_stock' => '',
                ];
                form($data,$errors);
            }
            
            
        
        }catch(Exception $e){
            print "Error:".$e->getMessage()."<br>";
        }
        
    ?>
    </div>
    
</div>
    
<script>
                var checkList = document.getElementById('list1');
            checkList.getElementsByClassName('anchor')[0].onclick = function(evt) {
            if (checkList.classList.contains('visible'))
                checkList.classList.remove('visible');
            else
                checkList.classList.add('visible');
            }

            var checkList2 = document.getElementById('list2');
            checkList2.getElementsByClassName('anchor')[0].onclick = function(evt) {
            if (checkList2.classList.contains('visible'))
                checkList2.classList.remove('visible');
            else
                checkList2.classList.add('visible');
            }
            
           
   </script>
        
</body>
</html><?php 
}else{
     header("Location: index.php");
     exit();
}
 ?>