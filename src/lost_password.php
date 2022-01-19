<?php
session_start();

include "header.htmlfrag";
?>
<h1>Request a new password</h1>
<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// From http://www.laughing-buddha.net/php/password/
function generatePassword ($length = 8) { 
    // start with a blank password
    $password = "";

    // define possible characters - any character in this string can be
    // picked for use in the password, so if you want to put vowels back in
    // or add special characters such as exclamation marks, this is where
    // you should do it
    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

    // we refer to the length of $possible a few times, so let's grab it now
    $maxlength = strlen($possible);
  
    // check for length overflow and truncate if necessary
    if ($length > $maxlength) {
      $length = $maxlength;
    }
	
    // set up a counter for how many characters are in the password so far
    $i = 0; 
    
    // add random characters to $password until $length is reached
    while ($i < $length) { 

      // pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
      // have we already used this character in $password?
      if (!strstr($password, $char)) { 
        // no, so it's OK to add it onto the end of whatever we've already got...
        $password .= $char;
        // ... and increase the counter by one
        $i++;
      }

    }

    // done!
    return $password; 
}

$settings=parse_ini_file("settings.ini");
if(isset($_POST["email"])) {
    $con = mysqli_connect("127.0.0.1",$settings["user"],$settings["password"],$settings["database"]);
    $email = mysqli_real_escape_string($con,$_POST["email"]);
    $result = mysqli_query($con,"select * from users where email='$email'");
    $row = mysqli_fetch_array($result);
    if($row) {
        $newpass = generatePassword();
        $user = $row["username"];
        mail($email,"New password at lemon-model.net",
            "We have generated a new password for you at lemon-model.net. Please use the following details to log in. \n\n  Username:$user\n\n  Password:$newpass","From: noreply@lemon-model.net");
        $hash = sha1($newpass . "obpawtmdpr" . $user);
        mysqli_query($con, "update users set password='$hash' where email='$email'");
        echo "<div>An email has been sent!</div>";
    } else {
        echo "<div class='login_error'>We do not have a user account for that email address</div>";
    }
    mysqli_close($con);
} else {
?>
<form method="post">
    <table>
        <tr>
            <td>
                <label for="email">Email</label>
            </td>
            <td>
                <input type="text" name="email"/>
            </td>
        </tr>
        <tr style="background-color:#fff;">
            <td>
            </td>
            <td style="text-align:right;margin:5px;">
              <input type="submit" value="Request new password"/>
          </td>
      </tr>
  </table>
</form>
<?php
}
include "footer.htmlfrag";
?>
