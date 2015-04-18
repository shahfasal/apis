<html>
<head></head>
<title>Reset password</title>
    <body>
<form action="http://192.168.1.3/vsm/api/v1/forgotpassword/resetpassword.php" method="post">
<!--<form action="http://www.vsmava.byethost33.com/vsm/api/v1/forgotpassword/resetpassword.php" method="post">-->

    <p><label>New Password</label><input type="password" name="password"   /></p>
    <p><label>Confirm Password </label><input type="password" name="confirm password"  ></p>
    <input type="hidden" name="code" value="<?php echo $_GET['code'];?>" />
    <p><input type="submit" value="Reset"></p>
</form>
    </body>
</html>