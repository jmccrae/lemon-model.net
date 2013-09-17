<?php
session_start();

include "header.htmlfrag";
?>
<h1>Log in</h1>
<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");
if(isset($_POST["username"]) && isset($_POST["password"])) {
    $con = mysql_connect("localhost",$settings["user"],$settings["password"]);
    mysql_select_db("lemonmodel",$con);
    $user = mysql_real_escape_string($_POST["username"]);
    $hash = sha1($_POST["password"] . "obpawtmdpr" . $user);
    $result = mysql_query("select * from users where username='$user' and password='$hash'");
    $row = mysql_fetch_array($result);
    if($row) {
      $_SESSION["username"] = $user;
    ?>
    <h5>Log in succesful</h5>
    <script>window.location="/index.php"</script>
    
    <?php 
    } else {
    
    ?><h5>Log in details were not correct</h5><?php
    }
mysql_close($con);
}
?>
<form method="post">
    <table>
        <tr>
            <td>
                <label for="username">Username</label>
            </td>
            <td>
                <input type="text" name="username"/>
            </td>
        </tr>
        <tr style="background-color:#fff;">
            <td>
                <label for="password">Password</label>
            </td>
            <td>
                <input type="password" name="password"/>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td style="text-align:right;margin:5px;">
              <input type="submit" value="Log In"/>
          </td>
      </tr>
  </table>
</form>
<?php
include "footer.htmlfrag";
?>
