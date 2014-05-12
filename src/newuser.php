<?php
# CREATE TABLE `users` (   id INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,   username VARCHAR(65) NOT NULL ,   password VARCHAR(64) NOT NULL , real_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL   );
session_start();

include "header.htmlfrag";
?>
<h1>Create an account</h1>
<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");
if(isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["password_repeat"])) {
    if($_POST["password"] != $_POST["password_repeat"]) {
        echo "<div class='login_error'>Passwords do not match!</div>";
    } else if(!preg_match("/^[A-Za-z0-9@\\.\\-_]+$/",$_POST["username"])) {
        echo "<div class='login_error'>Please use only ASCII alphanumeric characters in the user name</div>";
    } else if($_POST["username"] == "" || $_POST["password"] == "" || $_POST["password_repeat"] == "" ) {
        echo "<div class='login_error'>Required fields were not filled</div>";
    } else {
        $con = mysqli_connect("localhost",$settings["user"],$settings["password"],$settings["database"]);
        $user = mysqli_real_escape_string($con,$_POST["username"]);
        $email = mysqli_real_escape_string($con,$_POST["email"]);
        $hash = sha1($_POST["password"] . "obpawtmdpr" . $user);
        $result = mysqli_query($con,"select * from users where email='$email'");
        $row = mysqli_fetch_array($result);
        if($row) {
            echo "<div class='login_error'>An account already exists for this email</div>";
        } else {
            $result = mysqli_query($con,"select * from users where username='$user'");
            $row = mysqli_fetch_array($result);
            if($row) {
                echo "<div class='login_error'>Username already exists!</div>";
            } else {
                $real_name = mysqli_real_escape_string($con,$_POST["real_name"]);
                mysqli_query($con, "insert into users (username, password, real_name, email) values('$user','$hash','$real_name','$email')") or die(mysql_error());
                $_SESSION["username"] = $user;
                echo "<script>window.location='/index.php'</script>";
            }
        }
        mysqli_close($con);
    }
}
?>
<form method="post">
                <label for="username">Username*</label>
                <input type="text" name="username"/><br/>
                <label for="password">Password*</label>
                <input type="password" name="password"/><br/>
                <label for="password_repeat">Repeat password*</label>
                <input type="password" name="password_repeat"/><br/>
                <label for="real_name">Real name</label>
                <input type="text" name="real_name"/><br/>
                <label for="email">Email</label>
                <input type="text" name="email"/><br/>
              <input type="submit" value="Create Account"/>
</form>
    <div>&nbsp;&nbsp;&nbsp;&nbsp;*Required field</div>
<?php
include "footer.htmlfrag";
?>
