<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=egle">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Log in</title>

    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>

<div id="main">
      
   
    <div id="editForm" style="height: 200px;">
      
      
            <form  action="login.php" method="POST">

            <?php if (isset($_GET['error'])) { ?>
            <span><?php echo $_GET['error']; ?></span>
            <?php } ?><br>

        
            <input id="button" style="width: 400px ;"  name="phone_number" placeholder="Phone_number" type="text"><br>
            <br><br>

            
            <input id="button" style="width: 400px ;"  name="password"  placeholder="Password" type="password"><br>
          
            <br><br>
            
            <button type ="submit" id="button">Log in</button>
        </form>
    
    </div>
    
</div>
    

        
</body>
</html>