<?php

      require_once('config.php');
      session_start();

      if(isset($_SESSION['email'])){
        $email = $_SESSION['email'];
        $user_type = $_SESSION['type'];
      }else{
        header("location:index.php");
      }

      $i_name = $_GET['i_name'];

      $sql = "SELECT city_name, country, count(title) as total_publications FROM submits NATURAL JOIN publication NATURAL JOIN subscriber NATURAL JOIN institution WHERE i_name='$i_name'";
      $result = mysqli_query($dbc, $sql);
      $result = mysqli_fetch_array($result, MYSQLI_NUM);
 ?>
<html>
  <head>
  <title>Institution</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous" />
  </head>
  <body>
    <div id="top-panel" align="center">
			<div id="nav-bar">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
					<a class="navbar-brand" href="#">Scilib</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarNavDropdown">
						<ul class="navbar-nav">
							<li class="nav-item active">
								<a class="nav-link" href="main.php">Home <span class="sr-only">(current)</span></a>
							</li>
                           <li class="nav-item">
               <a class="nav-link" id="navbar-subscriptions" href="subscriptions.php">My Subscriptions</a>
             </li>
              <?php
                  // Reviewer
                  if($user_type == 1){
                    echo '<li class="nav-item">
                            <a class="nav-link" id="reviewer-submission" href="reviewer-submission.php">My Invitations</a>
                          </li>';
                  // Author
                }else if($user_type == 2){
                    echo '<li class="nav-item">
                                  <a class="nav-link" id="author-submission" href="author-submissions.php">My Submissions</a>
                                </li>';
                    echo '<li class="nav-item">
                                  <a class="nav-link" id="author-submission" href="author-publications.php">My Publications</a>
                                </li>';
                  }
                  // Editor
                  else if($user_type == 3){
                    echo '<li class="nav-item">
                            <a class="nav-link" id="submissions" href="editor-submission.php">My Submission</a>
                          </li>';
                  }else{ // Subscriber

                  }
               ?>
               <li class="nav-item">
                 <a class="nav-link" id="navbar-institution" href="institutions.php">Institutions</a>
               </li>
               <li class="nav-item">
                 <a class="nav-link" id="navbar-conferences" href="conferences.php">Conferences</a>
               </li>
						</ul>
            <ul class="navbar-nav ml-auto">
              <li class="nav-item">
                <a class="nav-link" id="navbar-email" href="#"><i><?php echo $_SESSION['email']; ?></i></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="navbar-logout" href="logout.php">Logout</a>
              </li>
            </ul>
					</div>
				</nav>
			</div>
      <div class="container">
        <div class="jumbotron">
        <h1><?php echo "$i_name"?></h1>
        <h4><?php echo "$result[0], $result[1]" ?></h4>
        <h6><?php echo "Total Publications: $result[2]" ?></h6>
        </div>
        <div>
          <h3>Publications of Institution</h3>
          <table class="table table-striped">
            <?php
              $sql = "SELECT title, p_name, p_id FROM submits NATURAL JOIN publication NATURAL JOIN subscriber NATURAL JOIN institution WHERE i_name='$i_name'";
              $all_publications = mysqli_query($dbc, $sql);

              if (mysqli_num_rows($all_publications)) {
                echo "<tr align='center'><th class='thead-light'>Publication</th><th>Publisher</th></tr>";
              }
              while ($row = mysqli_fetch_array($all_publications, MYSQLI_NUM)) {
                echo "<tr align='center'><td><a href='publication-page.php?p_id=$row[2]'>$row[0]</a></td><td><a href='find-publisher.php?p_name=$row[1]'>$row[1]</a></td></tr>";
              }
             ?>
          </table>
        </div>
      </div>

    </body>
  </html>
