<?php require('header.php'); ?>
<?php if(isset($message)): ?>
<div class="alert alert-danger" role="alert">
<?php echo $message; ?>
</div>
<?php endif; ?>
<form action="login.php" method="post">
  <div class="form-group">
    <label for="email">Email address</label>
    <input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Enter email" name="email" <?php if(isset($_SESSION['input_email'])) echo 'value="'.$_SESSION['input_email'].'"' ?>>
  </div>
  <div class="form-group">
    <label for="pass">Password</label>
    <input type="password" class="form-control" id="pass" placeholder="Password" name="pass">
  </div>
  <?php if($doRecaptcha): ?>
    <div class="g-recaptcha" data-sitekey="<?php echo $RECAPTCHA_SITEKEY; ?>"></div>
  <?php endif; ?>
<br/>
  <button type="submit" class="btn btn-primary">Submit</button>
</form>
<?php require('footer.php'); ?>
