<?php
if(isset($_POST['submit'])){
   if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $name = $_POST["name"];
      $email = $_POST["email"];
      $subject = $_POST["subject"];
      $message = $_POST["msg"];

      $to = "alaakhalil2149@gmail.com"; // Replace with your recipient's email address
      $headers = "From: $email\r\n";

      // Compose the email message including the name
      $email_message = "Name: $name\n";
      $email_message .= "Email: $email\n";
      $email_message .= "Subject: $subject\n";
      $email_message .= "Message:\n$message";

      // Send the email
      mail($to, $subject, $email_message, $headers);

      // Optionally, you can redirect the user to a thank you page
      header("Location: thank_you.html");
      exit;
   }
}

