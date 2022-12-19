<?php 
try{
    $pdo = require('db/connect.php');
    
    if(!isset($_GET['id'])){
        echo "Erroe: id is required";
        return;
    }
    $id = $_GET['id'];
    $table = $_GET['table'];

    if($table == 'books'){

        $sql = "DELETE FROM authors_books WHERE books = :id";
        $smt = $pdo->prepare($sql);
        $smt->execute([':id'=>$id]);

        $sql = "DELETE FROM books_genres WHERE books = :id";
        $smt = $pdo->prepare($sql);
        $smt->execute([':id'=>$id]);
       
    }  
   
   
    $sql = "DELETE FROM {$table} WHERE {$_GET['col']} = :id";
    $smt = $pdo->prepare($sql);
    $smt->execute([':id'=>$id]);

    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $extra = $_GET['page'];
    header("Location: http://$host$uri/$extra");
   

}catch(PDOException $e){
    print "Error!: ".$e->getMessage()."</br>";
}
?>


