<?php
$result = "No result";
session_start();
if (isset($_SESSION["isAdmin"])) $isAdmin = $_SESSION["isAdmin"];
else $isAdmin = false;

$formCode = "<p class='redactorInfo'>Something went wrong: empty response</p>";
$isTryingToDelete = false;

$isRedactedSucessfully = false;
$isDeletedSucessfully = false;
$isPostedSucessfully = false;
$id = "";
$title = "";
$author = "";




if($isAdmin){
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

    if(isset($_GET["action"])){
        if (isset($_GET["id"])) {
            $id = htmlspecialchars(string: strip_tags(trim(string: $_GET['id'])));
            $query = "SELECT * FROM `articles` WHERE `id` = ?";
            $result = $conn->execute_query($query, [$id]);

            if ($result->num_rows != 0) {
                $result = $result->fetch_assoc();
                $title = $result["title"];
                $author = $result["author"];
                $tags = $result["tags"];
                $intro = $result["intro"];
                $mainText = $result["maintext"];
                if($_GET["action"] == "redact"){
                    $formCode = "
                    <form action='redactor.php?action=redact&id=$id' method='post' class='postForm'>
                        <label for='title'>Title:</label>
                        <input type='text' name='title' id='title' value='$title' required>
                        <label for='author'>Author:</label>
                        <input type='text' name='author' id='author' value='$author' required>
                        <label for='tags'>Tags:</label>
                        <input type='text' name='tags' value='$tags' id='tags'>
                        <label for='intro'>Intro (will be shown on front page):</label>
                        <textarea name='intro' id='intro' required>$intro</textarea>
                        <label for='mainText'>Text of the article (intro is NOT included by default):</label>
                        <textarea name='mainText' id='mainText' required>$mainText</textarea>
                        <input type='submit' value='Redact' name='redactButton' id='postButton'>
                    </form>";

                    if (isset($_POST["redactButton"])) {

                        $author = htmlspecialchars(string: strip_tags(trim(string: $_POST['author']))); 
                        $title = htmlspecialchars(string: strip_tags(trim(string: $_POST['title'])));
                        $tags = htmlspecialchars(string: strip_tags(trim(string: $_POST['tags'])));
                        $intro = htmlspecialchars(string: strip_tags(trim(string: $_POST['intro'])));
                        $mainText = htmlspecialchars(string: strip_tags(trim(string: $_POST['mainText'])));


                        $query = "UPDATE `articles` SET `author`=?,`title`=?,`tags`=?,`intro`=?,`maintext`=? WHERE `id`=?";
                        $result = $conn->execute_query($query, [$author, $title, $tags, $intro, $mainText, $id]);
                        $isRedactedSucessfully = true;
                    }
                }
                elseif ($_GET["action"] == "delete") $isTryingToDelete = true;
            }
            else $formCode = "<p class='redactorInfo'>Article with id $id does not exist or was just deleted</p>";

            if(isset($_POST["deleteButton"])){
                $query = "DELETE FROM `articles` WHERE `id`=?";
                $result = $conn->execute_query($query, [$id]);
                if($result == 1) $isDeletedSucessfully = true;
                $formCode = "<p class='redactorInfo'>Deleted $id</p>";
            }


        }
        elseif ($_GET["action"] == "create") {
            $formCode = "
            <form action='redactor.php?action=create' method='post' class='postForm'>
                <label for='title'>Title:</label>
                <input type='text' name='title' id='title' required>
                <label for='author'>Author:</label>
                <input type='text' name='author' id='author' required>
                <label for='tags'>Tags:</label>
                <input type='text' name='tags' id='tags'>
                <label for='intro'>Intro (will be shown on front page):</label>
                <textarea name='intro' id='intro' required></textarea>
                <label for='mainText'>Text of the article (intro is NOT included by default):</label>
                <textarea name='mainText' id='mainText' required></textarea>
                <input type='submit' value='Post' name='postButton' id='postButton'>
            </form>"; 
        }

        else $formCode = "Something went wrong: id is not specified and action is not create";
    }

    if(isset($_POST["postButton"])){

        $author = htmlspecialchars(string: strip_tags(trim(string: $_POST['author']))); 
        $title = htmlspecialchars(string: strip_tags(trim(string: $_POST['title'])));
        $tags = htmlspecialchars(string: strip_tags(trim(string: $_POST['tags'])));
        $intro = htmlspecialchars(string: strip_tags(trim(string: $_POST['intro'])));
        $mainText = htmlspecialchars(string: strip_tags(trim(string: $_POST['mainText'])));

        $query = "INSERT INTO `articles`(`author`, `title`, `tags`, `intro`, `maintext`, `postdate`) VALUES (?, ?, ?, ?, ?, now())";
        $result = $conn->execute_query( $query, [$author, $title, $tags, $intro, $mainText]);
        if($result == 1) $isPostedSucessfully = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alkatra:wght@400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title>Owl's blog new post</title>
</head>
<body class="newArticle">
    <header>
        <a class="logo" href="index.php">Owl's blog</a>
        <div class="logInInfo" id="logInInfo">
            <p id="logedInInfo" style="display: none;">You logged in as admin</p>
        </div>
    </header>
    <?php if($isAdmin) { ?>
        <?=$formCode?>
    <?php }else{ ?>
        <p class="notLogedInError">You are not logged in</p>
    <?php }?>
</body>

<script>
    if (<?= json_encode(isset($_POST["postButton"])) ?>) {
        if (<?= json_encode($isPostedSucessfully === true) ?>) {
            alert("Your article is posted successfully!");
        } else {
            alert("Something went wrong! Double check that article is not posted and post again if needed");
        }
        window.location.href = "index.php";
    }

    if (<?= json_encode(isset($_POST["redactButton"])) ?>) {
        if (<?= json_encode($isRedactedSucessfully === true) ?>) {
            alert("Your article is redacted successfully!");
        } else {
            alert("Something went wrong! Double check that article is not redacted and try again if needed");
        }
        window.location.href = "index.php";
    }


    else if (<?= json_encode($isTryingToDelete) ?>) {
        if (confirm("Are you sure that you want to delete article with ID <?= htmlspecialchars(json_encode($id)) ?>, title <?= htmlspecialchars(json_encode($title)) ?> and author <?= htmlspecialchars(json_encode($author)) ?>?")) {
            $.ajax({
                type: "POST",
                url: window.location.href,
                data: {
                    deleteButton: "1"
                },
                success: function(response) {
                    document.body.innerHTML = response;
                },
                error: function(error) {
                    console.error("Error:", error);
                }
            });
        }
    }
</script>

</html>