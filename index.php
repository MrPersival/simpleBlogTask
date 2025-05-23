<?php
$isTryingToLogIn = false;
$isLogInSuccessful = false;
session_start();
if (isset($_SESSION["isAdmin"])) $isAdmin = $_SESSION["isAdmin"];
else $isAdmin = false;

$servername = "localhost";
$username = "root";
$password = "";
$dbName = "simpleblog";    
// Create connection
$conn = new mysqli($servername, $username, $password, $dbName);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Log in logic
if(isset($_POST["logInSubmit"])){
    $isTryingToLogIn = true;

    $login = htmlspecialchars(string: strip_tags(trim(string: $_POST['login']))); //Because we using execute_query, SQL injections is impossible
    $password = md5(htmlspecialchars(string: strip_tags(trim(string: $_POST['password'])))); //Because we using execute_query, SQL injections is impossible

    $query = "SELECT * FROM accounts WHERE login=?";
    $result = $conn->execute_query( $query, [$login]);
    if($result->num_rows != 0){
        while($row = $result->fetch_assoc()){
            if($row["password"] == $password) {
                $isLogInSuccessful = true;
                $_SESSION["isAdmin"] = true;
                $isAdmin = true;
            }
        }
    }
}


//Articles display
$articlesCode = "";

$result = $conn->execute_query("SELECT * FROM `articles` ORDER BY id DESC");

while($article = $result->fetch_assoc()){
    $id = $article["id"];
    $author = $article["author"];
    $title = $article["title"];
    $tags = $article["tags"];
    $intro = nl2br($article["intro"]);
    $postDate = $article["postdate"];

    $articlesCode .= "
        <div class='article'>
            <h3 class='author'>$author, $postDate</h3>
            <div class='titleAndButtons'>
                <a class='title' href='articleView.php?id=$id'>$title</a>
        ";
    if($isAdmin) $articlesCode .= "                <div class='buttonsHolder'><a href='redactor.php?action=redact&id=$id'><img src='svgIcons/redact.svg' alt='Redact' width='40px'></a><a href='redactor.php?action=delete&id=$id'><img src='svgIcons/delete.svg' alt='Delete' width='45px'></a></div>
";
    $articlesCode .= "        </div>
            <h3 class='tags'>$tags</h3>
            <span class='introText' style='font-style: italic;'>$intro</span>
            <div class='readMoreDiv'><a class='readMoreAnchor' href='articleView.php?id=$id'>See full post</a></div>
        </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owl's blog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alkatra:wght@400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <a class="logo" href="index.php">Owl's blog</a>
        <div class="logInInfo" id="logInInfo">
            <?php if($isAdmin) {?>
            <a href="redactor.php?action=create" class="newArticleButton" >New post</a>
            <p id="logedInInfo">You logged in as admin</p>
            <?php } else {?>
            <form id="logInFields" style="display: none;" action="index.php" method="post">
                <label for="login">Login:</label>
                <input type="text" name="login" id="login" required maxlength="100">
                <label for="login">Password:</label>
                <input type="password" name="password" id="password" required>
                <input type="submit" name="logInSubmit" id="logInSubmit" value="Log in" class="logInButton" maxlength="100">
            </form>
            <button class="logInButton" id="showLogInForm" onclick="logInButtonPressed()">Log in</button> <?php }?>

        </div>
    </header>
    <div class="articlesHolder">
        <?=$articlesCode?>
        
    </div>
</body>

<script>
    if(<?=json_encode($isTryingToLogIn) ?>){
        logInButtonPressed();
        if(<?=json_encode($isLogInSuccessful) ?>){
            window.location.href = "index.php";
            document.getElementById("logInFields").style.display = "none";
            document.getElementById("showLogInForm").style.display = "block";
        }
        else{
            window.alert("Wrong password or login");
        }
    }

    function logInButtonPressed() {
        document.getElementById("logInFields").style.display = "block";
        document.getElementById("showLogInForm").style.display = "none";
    }

</script>

</html>