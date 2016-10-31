<!DOCTYPE html>
<html>
<head>
</head>

<!--
PHP block chooses/displays appropriate login procedure based on user type.
New users will be prompted to create password and input course history. Returning
users will see a personal dashboard after entering a password. Users
entering an invalid student ID will be returned to the login, with an error message
displayed.
-->
<?php

  function debug_to_console($message){
    echo "<script>";
    echo "console.log(\"$message\")";
    echo "</script>";
  }

  function login_link(){
    echo "<a href='index.html'>Return to login page.</a>";
  }

  function load_users($source_file){
    $user_array = array();

    $handle = fopen($source_file, "r") or die("Unable to open file!");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if($line[0] == '#')
              continue;
            else {
              $u_hash = strtok($line, " ");
              $p_hash = strtok(" ");
              //array_push($user_array, $u_hash, $p_hash);
              $user_array[$u_hash] = rtrim($p_hash);
            }
        }
        fclose($handle);
    } else {
        echo "Error Opening User File!";
    }
    return $user_array;
  }

  function passwords_match($password_input, $user_password_hash){
    $input_hash = md5($password_input);
    if($user_password_hash === $input_hash){
      return true;
    }
    return false;
  }

  function is_previous_user($username, $previous_users){
    $username_hash = sha1($username);
    if(array_key_exists($username_hash, $previous_users)){
      return true;
    }
    else
      return false;
  }

  function is_bad_username($username){
    if(strlen($username) != 7){
      return true;
    }
    if(!ctype_digit($username)){
      return true;
    }
    return false;
  }

  function auth_handler($user_array){
    //RESPONSE CASE 1: Username authentication
    if(isset($_POST['username'])){
      debug_to_console("RESPONDING TO USERNAME");
      $username_input = $_POST['username'];
      $user_key = sha1($username_input);
      if(is_bad_username($username_input)){
        echo "<h3>Error: invalid username (longer than seven digits, or contains non-number characters)</h3><br>";
        login_link();
      }
      elseif(is_previous_user($username_input, $user_array)){
        //$user_key = sha1($username_input);
        echo "<h3>Welcome user #";
        echo $username_input;
        echo "</h3><br>";
        echo "<form action='auth.php' method='post'>";
        echo "<input type='hidden' value='$user_key' name='user_key'/>";
        echo "<h4>Please enter your password to view stored data:</h4>";
        echo "<input type='password' name='password'/><br>";
        echo "<input type='submit' value='Submit'>";
        echo "</form><br>";
        echo "Not you? ";
        login_link();
      }
      else{
        //$user_key = sha1($username_input);
        echo "<h3>Welcome new user!</h3>";
        echo "<p>Please enter a username to save your information for later!</p><br>";
        echo "<form action='auth.php' method='post'><input type='password' name='new_password'><br><input type='hidden' name='user_key' value=$user_key><input type='submit' value='Submit'></form>";
        login_link();
      }
    }
    //END RESPONSE CASE 1

    //RESPONSE CASE 2: Password authentication
    elseif(isset($_POST['password'])){
      debug_to_console("PASSWORD AUTHENTICATION");

      $input_hash = md5($_POST['password']);
      $correct_hash = $user_array[$_POST['user_key']];

      if($input_hash == $correct_hash){
        setcookie("adapt_session", $_POST['user_key'], time() + (86400 * 30), "/");
        readfile('Page2.html');
      }
      else{
        echo "<h3>Incorrect username or password!</h3>";
        login_link();
      }
    }
    //END RESPONSE CASE 2

    //RESPONSE CASE 3: New user password creation.
    elseif(isset($_POST['new_password'])){
      debug_to_console("PASSWORD CREATION");
      $username_hash = $_POST['user_key']; //username should already be in SHA1 form.
      $password_hash = md5($_POST['new_password']); //password converted to MD5 form.

      $user_data_line = $username_hash . " " . $password_hash . "\n";
      $userFile = "data/users.txt";
      $current = file_get_contents($userFile);
      $current .= $user_data_line;
      file_put_contents($userFile, $current);
      setcookie("adapt_session", $_POST['user_key'], time() + (86400 * 30), "/");
      readfile('Page1.html');
    }
    //END RESPONSE CASE 3

    //RESPONSE CASE 4: No valid $_POST parameters recieved (Error Case)
    else {
      echo "ERROR: No valid data recieved from client.";
      login_link();
    }
    //END RESPONSE CASE 4
  }

  //MAIN REQUEST HANDLING SEQUENCE
  $previous_users = load_users("data/users.txt");
  debug_to_console("PHP Running.");
  auth_handler($previous_users);
?>
</html>
