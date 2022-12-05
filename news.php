
<?php 

session_start();

if (isset($_SESSION['employee_id']) && isset($_SESSION['employee_name'])) {

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Send Newsletter</title>

    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>
<?php 
$pdo = require('db/connect.php');
if(!$pdo){
    die();
}

$statement = $pdo->query('SELECT * FROM readers WHERE email IS NOT NULL;');
$readers = $statement->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['recipient'], $_POST['subject'], $_POST['template'])) {

    $to = $_POST['recipient'];
    $subject = $_POST['subject'];
    $message = nl2br(htmlspecialchars($_POST['template']));
    $from = 'Library <kuzivn123@gmail.com>';

    $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'Return-Path: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        if (mail($to,$subject,$message,$headers)) {
            exit('success');
        } else {
            
            exit('Failed to send newsletter! Please check your SMTP mail server!');
        }

}

function select_from($tables1,$tables2,$id){
    global $pdo;

    $tables1 =="books_genres"?$row = "genre_id":$row = "author_id ";
    $sql = "SELECT * FROM $tables1 INNER JOIN $tables2 ON  $tables1.$tables2 = $tables2.$row WHERE books = $id";   
    $statement = $pdo->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_text(){
    global $pdo;
    $sql = "SELECT * FROM books  LEFT JOIN publishers ON books.publisher_id = publishers.publisher_id  ORDER BY book_id DESC LIMIT 1;";
    $statement = $pdo->query($sql);
    $books= $statement->fetchAll(PDO::FETCH_ASSOC);
    $text = "Hello Friend,\nWes have a new book:\n";
    foreach($books as $b){
        $text = $text."Title: ".$b['title'];
       
        $authors = select_from("authors_books","authors",$b['book_id']);
        if($authors){
            $text = $text."Author(s): ";
            foreach($authors as $a){
                if($a){
                    $text = $text." ".$a['author_name']." ";
                }
              
            } 
        }
       
       
        $genres = select_from("books_genres","genres",$b['book_id']);
        if($genres){
            $text = $text."\nGenre(s): ";
            foreach($genres as $g){
                $text = $text." ".$g['genre_name']." ";
            } 

        }

        $text = $text."\nPublisher: ".$b['publisher_name']."\n";
        $text = $text."Language: ".$b['language']."\n";
    }
    $text = $text."If you are interested in the book, come to us and takes it.\n(Address: 202 E Main St, Dundee, FL 33838, United States)\nGood  luck!";
    return $text;
}



?>



<div id="main">
      <div id="menubar">
        <ul id="menu">
          <li ><a href="issuance_literature.php">< Back</a></li>
        </ul>
                        
      </div>

    
   
    <div style="width: 600px;" id="editForm">
            <form class="send-newsletter-form" method="post" action="">

                <h1><i class="fa-regular fa-envelope"></i>Send Newsletter</h1>

                <div class="fields">

                    <label for="recipients">Readers</label><br>
                    <div  id="list1" class="dropdown-check-list" tabindex="100">
                        <p  style="width:500px;" class="anchor">--Select Email--</p>
                            <ul style="width:400px;" class="items">
                            <?php foreach ($readers as $reader): ?>
                        <label>
                            <input  type="checkbox" class="recipient" checked name="recipients[]" value="<?=$reader['email']?>"> <?=$reader['email']?>
                        </label>
                        <?php endforeach; ?>
                            </ul>
                    </div><br><br>

                    <label for="subject">Subject</label>
                    <div class="field">
                        <input  style="width:560px; font: size 18px;" type="text" id="subject" name="subject" placeholder="Subject" value="New Book!" required>
                    </div><br>

                    <label for="template">Email Template</label>
                    <div class="field">
                        <textarea style="width:560px; height:280px; max-height:300px;" id="template" name="template"  required>
                            <?php echo get_text(); ?>
                        </textarea>
                    </div><div  id="res" class="responses"></div><br><br>

                   

                </div>

                <input id="button" type="submit" value="Send">
             
        </form>

 <script>
            
            var checkList = document.getElementById('list1');
            checkList.getElementsByClassName('anchor')[0].onclick = function(evt) {
            if (checkList.classList.contains('visible'))
                checkList.classList.remove('visible');
            else
                checkList.classList.add('visible');
            }      
           

        
// Retrieve the form element
const newsletterForm = document.querySelector('.send-newsletter-form');
// Declare variables
let recipients = [], totalRecipients = 0, recipientsProcessed = 0;
// Form submit event
newsletterForm.onsubmit = event => {
    event.preventDefault();
    // Retrieve all recipients and delcare as an array
    recipients = [...document.querySelectorAll('.recipient:checked')];
    // Total number of selected recipients
    totalRecipients = recipients.length;
    // Total number of recipients processed
    recipientsProcessed = 0;
    // Clear the responses (if any)
    document.querySelector('.responses').innerHTML = '';
    // Temporarily disable the submit button
    document.querySelector('#button').disabled = true;
    // Update the button value
    document.querySelector('#button').value = `(1/${totalRecipients}) Processing...`;
};
// The below code will send a new email every 3 seconds, but only if the form has been processed
setInterval(() => {
    // If there are recipients...
    if (recipients.length > 0) {
        // Create form data
        let formData = new FormData();
        // Append essential data
        formData.append('recipient', recipients[0].value);
        formData.append('template', document.querySelector('#template').value);
        formData.append('subject', document.querySelector('#subject').value);
        // Use AJAX to process the form
        fetch(newsletterForm.action, {
            method: 'POST',
            body: formData
        }).then(response => response.text()).then(data => {
            // If success
            if (data.includes('success')) {
                // Increment variables
                recipientsProcessed++;
                // Update button value
                document.querySelector('#button').value = `(${recipientsProcessed}/${totalRecipients}) Processing...`;
                // When all recipients have been processed...
                if (recipientsProcessed == totalRecipients) {
                    // Reset everything
                    newsletterForm.reset();
                    document.querySelector('#button').disabled = false;
                    document.querySelector('#button').value = `Submit`;
                    document.querySelector('.responses').innerHTML = 'Newsletter sent successfully!';
                }
            } else {
                // Error
                document.querySelector('.responses').innerHTML = data;
            }
        });
        // Remove the first item from array
        recipients.shift();
    }
}, 1000); // 3000 ms = 3 seconds
</script>
    </div>
    
</div>    
</body>
</html><?php 
}else{
     header("Location: index.php");
     exit();
}
 ?>