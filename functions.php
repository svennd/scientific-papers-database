<?php

    require_once('config.php');

    function getPublishersJson()
    {
        global $dbc;

        $selPublishers = "select p_name from publisher;";

        $stmt = @mysqli_query($dbc,$selPublishers);

        $publishers = array();

        while($row = @mysqli_fetch_array($stmt))
        {
            array_push($publishers, $row['p_name']);
            //$publishers[] = $row['p_name'];
        }

        //$arr = array($publishers[]);
        $jsonRes = json_encode($publishers);

        @mysqli_stmt_close($stmt);

        return $jsonRes;
    }

    function getExpertisesJson()
    {
        global $dbc;

        $selExpertises = "select tag from expertise;";

        $stmt = @mysqli_query($dbc,$selExpertises);

        $expertises = array();

        while($row = @mysqli_fetch_array($stmt))
        {
            array_push($expertises, $row['tag']);
            //$publishers[] = $row['p_name'];
        }

        //$arr = array($publishers[]);
        $jsonRes = json_encode($expertises);

        @mysqli_stmt_close($stmt);

        return $jsonRes;
    }

    function signup()
    {
        global $dbc;

        $data_missing = array();

        if(empty($_POST['name']))
        {
            $data_missing[] = 'Name';
        }
        else
        {
            $name = $_POST['name'];
        }
        if(empty($_POST['surname']))
        {
            $data_missing[] = 'Surname';
        }
        else
        {
            $surname = $_POST['surname'];
        }
        if(empty($_POST['email']))
        {
            $data_missing[] = 'Email';
        }
        else
        {
            $email = $_POST['email'];
        }
        if(empty($_POST['password']))
        {
            $data_missing[] = 'Password';
        }
        else
        {
            $password = $_POST['password'];
        }
        if(empty($_POST['institution']))
        {
            $data_missing[] = 'Institution';
        }
        else
        {
            $institution = $_POST['institution'];
        }

        if(empty($_POST['role']))
        {
            $data_missing[] = 'Role';
        }
        else
        {
            $role = $_POST['role'];
            if ($role == "reviewer" || $role == "author")
            {
                if(empty($_POST['expertise']))
                {
                    $data_missing[] = 'Expertise';
                }
                else
                {
                    $expertises = $_POST['expertise'];
                }
            }
            else if( $role == "editor" )
            {
                if(empty($_POST['publisher']))
                {
                    $data_missing[] = 'Publisher';
                }
                else
                {
                    $publishers = $_POST['publisher'];
                }
            }
        }

        session_start();
        $_SESSION['validationMessage'] = '';

        if(empty($data_missing))
        {
            $checkEmail = "Select count(email) from subscriber where email = '".$email."' ;";

            $stmt = @mysqli_query($dbc,$checkEmail);

            $count = @mysqli_fetch_array($stmt);

            if( intval($count['count(email)']) > 0 )
            {
                // Email already exists
                $_SESSION['validationMessage'] = 'This E-mail is already used';
                header('Location: signup.php');
                exit();
            }
            else
            {
                // Create account
                $type = -1;
                if ($role == "author")
                {
                    $type = 2;
                    $addAuthor = "insert into subscriber (email,i_name,password,s_name,s_surname,usertype)"
                    ."values ('".$email."', '".$institution."', '".$password."', '".$name."', '".$surname."' , 2);";

                    $stmt = @mysqli_prepare($dbc,$addAuthor) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt) or die(mysqli_error($dbc));


                    $addAuthor = "insert into author (email) values ('".$email."');";
                    $stmt = @mysqli_prepare($dbc,$addAuthor) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt) or die(mysqli_error($dbc));

                    $addExpertise = "insert into authorExpertise (email, tag) values ('".$email."','".$expertises[0]."')";

                    $count = 0;
                    if (is_array($expertises) || is_object($expertises))
                    {
                        foreach ($expertises as $expertise) {
                            if( $count != 0)
                            {
                                $addExpertise .= ", ('".$email."','".$expertise."')";
                            }
                            $count++;
                        }
                    }

                    $addExpertise .= ";";

                    $stmt2 = @mysqli_prepare($dbc,$addExpertise) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt2) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt2) or die(mysqli_error($dbc));


                }

                else if ( $role == "reviewer" )
                {
                    $type = 1;
                    $addSub = "insert into subscriber (email,i_name,password,s_name,s_surname,usertype)"
                    ."values ('".$email."', '".$institution."', '".$password."', '".$name."', '".$surname."' , 1);";

                    $stmt = @mysqli_prepare($dbc,$addSub) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt);

                    $addReviewer = "insert into reviewer (email) values ('".$email."');";

                    $stmt = @mysqli_prepare($dbc,$addReviewer) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt);

                    $addExpertise = "insert into reviewerExpertise (email, tag) values ('".$email."','".$expertises[0]."')";

                    $count = 0;
                    if (is_array($expertises) || is_object($expertises))
                    {
                        foreach ($expertises as $expertise) {
                            if( $count != 0)
                            {
                                $addExpertise .= ", ('".$email."','".$expertise."')";
                            }
                            $count++;
                        }
                    }
                    $addExpertise .= ";";

                    $stmt2 = @mysqli_prepare($dbc,$addExpertise) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt2) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt2);

                }
                else if ( $role == "subscriber" )
                {
                    $type = 0;
                    $addSubscriber = "insert into subscriber (email,i_name,password,s_name,s_surname,usertype)"
                    ."values ('".$email."', '".$institution."', '".$password."', '".$name."', '".$surname."' , 0);";

                    $stmt = @mysqli_prepare($dbc,$addSubscriber) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt) or die(mysqli_error($dbc));

                }
                else if ( $role == "editor" )
                {
                    $type = 3;
                    $addEditor = "insert into subscriber (email,i_name,password,s_name,s_surname,usertype)"
                    ."values ('".$email."', '".$institution."', '".$password."', '".$name."', '".$surname."' , 3);";

                    $stmt = @mysqli_prepare($dbc,$addEditor) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt);

                    $addEditor = "insert into editor (email,experience) values ('".$email."', 0);";
                    $stmt = @mysqli_prepare($dbc,$addEditor) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt);

                    $addPublishers = "insert into editorPublisher (email,p_name) values ('".$email."','".$publishers[0]."')";
                    $count = 0;
                    if (is_array($publishers) || is_object($publishers))
                    {
                        foreach ($publishers as $publisher) {
                            if( $count != 0)
                            {
                                $addPublishers .= ", ('".$email."','".$publisher."')";
                            }
                            $count++;
                        }
                    }
                    $addPublishers .= ";";

                    $stmt2 = @mysqli_prepare($dbc,$addPublishers) or die(mysqli_error($dbc));
                    @mysqli_stmt_execute($stmt2) or die(mysqli_error($dbc));
                    @mysqli_stmt_close($stmt2);
                }

                $_SESSION['authenticated'] = 1;
                $_SESSION['validationMessage'] = '';
                $_SESSION['email'] = $email;
                $_SESSION['type'] = $type;
                header('Location: main.php');
                exit();
            }

            @mysqli_stmt_close($stmt);
        }
        else
        {
            $_SESSION['validationMessage'] = 'Some fields are missing!';
        }

    }

    function makeSubmission()
    {
        session_start();
        //$email = 'email2';
        $email = $_SESSION['email'];

        global $dbc;

        $data_missing = array();

        if(empty($_POST['nsubmission_title']))
        {
            $data_missing[] = 'Title';
        }
        else
        {
            $title = $_POST['nsubmission_title'];
        }
        if(empty($_POST['nsubmission_link']))
        {
            $data_missing[] = 'Link';
        }
        else
        {
            $link = $_POST['nsubmission_link'];
        }
        if(empty($_POST['nsubmission_publisher']))
        {
            $data_missing[] = 'Publisher';
        }
        else
        {
            $publisher = $_POST['nsubmission_publisher'];
        }
        if(empty($_POST['coauthors_emails']))
        {
            $data_missing[] = 'AuthorsEmails';
        }
        else
        {
            $authorsEmails = $_POST['coauthors_emails'];
        }
        if(empty($_POST['expertises']))
        {
            $data_missing[] = 'Expertises';
        }
        else
        {
            $expertises = $_POST['expertises'];
        }

        session_start();
        $_SESSION['validationMessage'] = '';

        if(empty($data_missing))
        {
            $checkLink = "Select count(doc_link) from submission where doc_link = '".$link."' ;";
            $stmt2 = @mysqli_query($dbc,$checkLink) or die(mysqli_error($dbc));
            $count2 = @mysqli_fetch_array($stmt2) or die(mysqli_error($dbc));
            @mysqli_stmt_close($stmt2);

            if( intval($count2['count(doc_link)']) > 0  )
            {
                // Link already exists
                $_SESSION['validationMessage'] = 'This Link is already submitted';
                header('Location: author-submissions.php');
                exit();
            }
            else
            {
                // Add submission
                $getLeastEditor = "select email, count(email) as count from submission where status < 4 group by email order by count ASC limit 1;";
                $stmt = @mysqli_query($dbc,$getLeastEditor) or die(mysqli_error($dbc));
                $row = @mysqli_fetch_array($stmt);
                $editorEmail = $row['email'];
                @mysqli_stmt_close($stmt);

                $addsubmissionQuery = "insert into submission (status,title,doc_link,date,email)"
                ."values ( 0, '".$title."', '".$link."', CURDATE(), '".$editorEmail."' );";
                $stmt = @mysqli_query($dbc,$addsubmissionQuery) or die(mysqli_error($dbc));
                @mysqli_stmt_close($stmt);

                $getMaxID = "select s_id from submission order by s_id DESC limit 1;";
                $stmt = @mysqli_query($dbc,$getMaxID) or die(mysqli_error($dbc));
                $row = @mysqli_fetch_array($stmt);
                $s_id = $row['s_id'];
                @mysqli_stmt_close($stmt);

                $addsubmitsQuery = "insert into submits (email,s_id,p_name)"
                ."values ( '".$email."', '".$s_id."', '".$publisher."' );";
                $stmt = @mysqli_query($dbc,$addsubmitsQuery) or die(mysqli_error($dbc));
                @mysqli_stmt_close($stmt);

                $_SESSION['validationMessage'] = 'Submission successfully added';
                header('Location: author-submissions.php');
                exit();
            }

        }
        else
        {
            $_SESSION['validationMessage'] = 'Some fields are missing!';
        }
    }

    function cancelSubmission()
    {

        global $dbc;
        $s_id = intval($_POST['id']);

        $deleteSubmitsQuery = "delete from submits where s_id = ".$s_id.";";
        $deleteInvitesQuery = "delete from invites where s_id = ".$s_id.";";
        $deleteReviewsQuery = "delete from reviews where s_id = ".$s_id.";";
        $deleteSubmissionQuery = "delete from submission where s_id = ".$s_id.";";

        $stmt = @mysqli_prepare($dbc,$deleteSubmitsQuery) or die(mysqli_error($dbc));
        @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));

        $stmt = @mysqli_prepare($dbc,$deleteInvitesQuery) or die(mysqli_error($dbc));
        @mysqli_stmt_execute($stmt2) or die(mysqli_error($dbc));

        $stmt = @mysqli_prepare($dbc,$deleteReviewsQuery) or die(mysqli_error($dbc));
        @mysqli_stmt_execute($stmt3) or die(mysqli_error($dbc));

        $stmt = @mysqli_prepare($dbc,$deleteSubmissionQuery) or die(mysqli_error($dbc));
        @mysqli_stmt_execute($stmt4) or die(mysqli_error($dbc));

        @mysqli_stmt_close($stmt);

        return 'success';
    }

    function publishSubmission()
    {
        global $dbc;
        $s_id = intval($_GET['publishID']);

        $changeState = "update submission set status = 4 where s_id = ".$s_id.";";
        $addPublication = "insert into publication (title, pages,publication_date,doc_link,downloads,s_id)"
        ." values ('',0,CURDATE(),'',0,".$s_id.");";

        //$stmt = @mysqli_query($dbc,$changeState) or die(mysqli_error($dbc));
        $stmt = @mysqli_prepare($dbc,$changeState) or die(mysqli_error($dbc));
        @mysqli_stmt_execute($stmt) or die(mysqli_error($dbc));

        $stmt = @mysqli_query($dbc,$addPublication) or die(mysqli_error($dbc));

        @mysqli_stmt_close($stmt);

        return 'success';
    }

    function getFeedbackJson()
    {
        global $dbc;
        $s_id = intval($_GET['id']);

        $selFeedbacks = "select feedback from reviews where s_id = ".$s_id.";";

        $stmt = @mysqli_query($dbc,$selFeedbacks);

        $feedbacks = array();

        while($row = @mysqli_fetch_array($stmt))
        {
            array_push($feedbacks, $row['feedback']);
        }

        $jsonRes = json_encode($feedbacks);

        @mysqli_stmt_close($stmt);

        return $jsonRes;
    }

    function signin()
        {
           global $dbc;

           if( isset($_POST['email']) && isset($_POST['password']) ) {
               $email = $_POST['email'];
               $password = $_POST['password'];

               // formulate the query
               $findUser = "select usertype from subscriber where email='$email' and password='$password'";

               // perform the query
               $result = @mysqli_query($dbc,$findUser);

               // check number of rows to see if user exists in db
               $num_rows = mysqli_num_rows($result);

               if ($num_rows == 1) {
                   $type = mysqli_fetch_object($result);
                   session_start();
                   $_SESSION['authenticated'] = 1;
                   $_SESSION['validationMessage'] = '';
                   $_SESSION['email'] = $email;
                   $_SESSION['type'] = $type->usertype;
                   header('Location: main.php');
                   session_write_close();
                   exit();
               } else {
                   $_SESSION['validationMessage'] = 'Some fields are missing!';
                   header('Location: signin.php?error');
                   session_write_close();
                   exit();
               }

               @mysqli_stmt_close($result);
           }
       }
    if (isset($_GET['getPublishers']))
    {
        $res = getPublishersJson();
        echo $res;
    }
    if (isset($_GET['getExpertises']))
    {
        $res = getExpertisesJson();
        echo $res;
    }
    if (isset($_GET['cancelSub']))
    {
        $res = cancelSubmission();
        echo $res;
    }
    if (isset($_GET['getFeedback']))
    {
        $res = getFeedbackJson();
        echo $res;
    }
    if(isset($_POST['submit_signup']))
    {
        signup();
    }

    if(isset($_POST['make_a_submission']))
    {
        makeSubmission();
    }

    if(isset($_GET['publish']))
    {
        $res = publishSubmission();
        echo $res;
    }

   if (isset($_GET['getPublishers']))
   {
       $res = getPublishersJson();
       echo $res;
       //getPublishersJson($_GET['closeID']);
   }
   if (isset($_GET['getExpertises']))
   {
       $res = getExpertisesJson();
       echo $res;
       //getExpertisesJson($_GET['closeID']);
   }

   if(isset($_POST['submit_signup']))
   {
       signup();
   }

   if(isset($_POST['submit_signin']))
   {
       signin();
   }

   @mysqli_close($dbc);
?>
