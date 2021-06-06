<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
/*
 * Listen for various ADMIN POST REQUESTS
 */

global $sqmState;
if ( !$sqmState ) {
    $sqmState = SQM_State::getInstance();
}
add_action( 'admin_init', function () {
    if ( 'POST' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
        $action = ( $_POST[ 'sqm_action' ] ?? '' );
        if ( !empty( $action ) ) {
            switch ( $action ) {
                case SQM_AdminQuizzes::ACTION_CREATE_QUIZ:
                {
                    sqm_quiz_create();
                    break;
                }
                case SQM_AdminQuizzes::ACTION_EDIT_QUIZ:
                {
                    sqm_quiz_update();
                    break;
                }
                case SQM_AdminQuizzes::ACTION_DELETE_QUIZ:
                {
                    break;
                }
                case SQM_AdminQuizzes::ACTION_CREATE_QUESTION:
                {
                    sqm_quiz_create_question();
                    break;
                }
                case SQM_AdminQuizzes::ACTION_CREATE_ANSWER:
                {
                    sqm_quiz_create_answer();
                    break;
                }
                default:
                {
                    //.. nothing to do
                }
            }
        }
    }
} );

function sqm_quiz_create()
{
    global $sqmState;

    $createUrl = add_query_arg( [
        'page' => 'sqm_quiz_list',
        SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUIZ,
    ], admin_url( 'admin.php' ) );

    $response = SQM_AdminQuizzes::create( $_POST );
    if ( is_wp_error( $response ) ) {
        SQM_Notices::create( 'error', $response->get_error_message( 'sqm' ) );
        $sqmState
            ->add( 'title', SQM_AdminQuizzes::getSanitizedField( 'title', SQM_AdminQuizzes::TYPE_STRING ) )
            ->add( 'max_attempts', SQM_AdminQuizzes::getSanitizedField( 'max_attempts', SQM_AdminQuizzes::TYPE_INT ) )
            ->add( 'date_start', SQM_AdminQuizzes::getSanitizedField( 'date_start', SQM_AdminQuizzes::TYPE_STRING ) )
            ->add( 'date_end', SQM_AdminQuizzes::getSanitizedField( 'date_end', SQM_AdminQuizzes::TYPE_STRING ) )
            ->add( 'message_success', SQM_AdminQuizzes::getSanitizedField( 'message_success', SQM_AdminQuizzes::TYPE_STRING ) )
            ->add( 'message_error', SQM_AdminQuizzes::getSanitizedField( 'message_error', SQM_AdminQuizzes::TYPE_STRING ) )
            ->save();
    }
    else {
        SQM_Notices::create( 'success', 'The quiz has been successfully created' );
    }

    wp_redirect( $createUrl );
    exit;
}

function sqm_quiz_update()
{
    global $sqmState, $sqmDB;

    $quizID = 0;
    if ( isset( $_POST[ 'id' ] ) ) {
        $quizID = SQM_AdminQuizzes::getSanitizedField( 'id', SQM_AdminQuizzes::TYPE_INT );
    }
    elseif ( isset( $_REQUEST[ 'quiz_id' ] ) ) {
        $quizID = intval( $_REQUEST[ 'quiz_id' ] );
    }

    $quiz = $sqmDB->getQuizByID( $quizID );

    if ( $quiz ) {
        $editUrl = add_query_arg( [
            'page' => 'sqm_quiz_list',
            SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_EDIT_QUIZ,
            'quiz_id' => $quizID,
        ], admin_url( 'admin.php' ) );

        $response = SQM_AdminQuizzes::update( $_POST );
        if ( is_wp_error( $response ) ) {
            SQM_Notices::create( 'error', $response->get_error_message( 'sqm' ) );
            $sqmState
                ->add( 'title', SQM_AdminQuizzes::getSanitizedField( 'title', SQM_AdminQuizzes::TYPE_STRING ) )
                ->add( 'max_attempts', SQM_AdminQuizzes::getSanitizedField( 'max_attempts', SQM_AdminQuizzes::TYPE_INT ) )
                ->add( 'date_start', SQM_AdminQuizzes::getSanitizedField( 'date_start', SQM_AdminQuizzes::TYPE_STRING ) )
                ->add( 'date_end', SQM_AdminQuizzes::getSanitizedField( 'date_end', SQM_AdminQuizzes::TYPE_STRING ) )
                ->add( 'message_success', SQM_AdminQuizzes::getSanitizedField( 'message_success', SQM_AdminQuizzes::TYPE_STRING ) )
                ->add( 'message_error', SQM_AdminQuizzes::getSanitizedField( 'message_error', SQM_AdminQuizzes::TYPE_STRING ) )
                ->save();
        }
        else {
            SQM_Notices::create( 'success', 'The quiz has been successfully updated' );
        }

        wp_redirect( $editUrl );
        exit;
    }
}

function sqm_quiz_create_question()
{
    global $sqmDB;

    $quizID = intval( $_POST[ 'id' ] );

    $createUrl = add_query_arg( [
        'page' => 'sqm_quiz_list',
        SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUESTION,
        'quiz_id' => $quizID,
    ], admin_url( 'admin.php' ) );

    $titles = SQM_AdminQuizzes::getSanitizedField( 'titles', SQM_AdminQuizzes::TYPE_ARRAY );
    if ( empty( $titles ) ) {
        SQM_Notices::create( 'error', 'Please specify at least one question.' );

        wp_redirect( $createUrl );
        exit;
    }

    $errors = [];

    foreach ( $titles as $title ) {
        $created = $sqmDB->questionCreate( $quizID, $title );
        if ( !$created ) {
            $errors[] = '<p>An error occurred and the question "' . $title . '" could not be added.</p>';
        }
    }

    if ( !empty( $errors ) ) {
        SQM_Notices::create( 'error', implode( '', $errors ) );
    }
    else {
        $content = 'Question successfully added.';
        if ( count( $titles ) > 1 ) {
            $content = 'Questions successfully added.';
        }
        SQM_Notices::create( 'success', $content );
    }

    wp_redirect( $createUrl );
    exit;
}

function sqm_quiz_create_answer()
{
    global $sqmDB;
    $quizID = ( $_POST[ 'quiz_id' ] ?? 0 );
    $questionID = ( $_POST[ 'question_id' ] ?? 0 );
    if ( !empty( $quizID ) && !empty( $questionID ) ) {
        $answersPageUrl = add_query_arg( [
            'page' => 'sqm_quiz_list',
            SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_ANSWER,
            'quiz_id' => $quizID,
            'question_id' => $questionID,
        ], admin_url( 'admin.php' ) );

        $answers = ( $_POST[ 'answers' ] ?? [] );
        $points = ( $_POST[ 'points' ] ?? [] );
        $correct = ( $_POST[ 'correct' ] ?? [] );

        if ( empty( $answers ) ) {
            SQM_Notices::create( 'error', 'Please specify at least one answer.' );

            wp_redirect( $answersPageUrl );
            exit;
        }

        $errors = [];
        foreach ( $answers as $index => $answer ) {
            $_points = ( $points[ $index ] ?? 0 );
            $_isCorrect = ( $correct[ $index ] ?? 0 );
            $created = $sqmDB->answerCreate( $questionID, $answer, $_points, $_isCorrect );
            if ( !$created ) {
                $errors[] = '<p>An error occurred and the question "' . $answer . '" could not be added.</p>';
            }
        }

        if ( !empty( $errors ) ) {
            SQM_Notices::create( 'error', implode( '', $errors ) );
        }
        else {
            $content = 'Answer successfully added.';
            if ( count( $answers ) > 1 ) {
                $content = 'Answers successfully added.';
            }
            SQM_Notices::create( 'success', $content );
        }

        wp_redirect( $answersPageUrl );
        exit;
    }
}

/**
 * Validate the quiz form submission
 */
function sqm_quiz_handle_user_form_submit( array $postData = [] )
{
    if ( !is_user_logged_in() ) {
        SQM_Notices::create( 'error', 'The form is not valid. Please login and try again!' );
        return;
    }

    $quizID = ( $postData[ 'quiz_id' ] ?? 0 );
    if ( empty( $quizID ) ) {
        SQM_Notices::create( 'error', 'The form is not valid. Please refresh the page and try again!' );
        return;
    }

    $sqmDB = new SQM_DB();
    $quiz = $sqmDB->getQuizByID( $quizID );
    if ( !$quiz ) {
        SQM_Notices::create( 'error', 'The specified quiz was not found.' );
        return;
    }

    $sqm = SomaQuizMaster::getInstance();
    $userID = wp_get_current_user()->ID;
    $result = $sqm->userCanTakeQuiz( $quizID, $userID );
    if ( is_wp_error( $result ) ) {
        SQM_Notices::create( 'error', $quiz->message_error );
        return;
    }
    if ( !$result ) {
        //#! The quiz was not found
        SQM_Notices::create( 'error', 'The form is not valid. Please refresh the page and try again!' );
        return;
    }

    //#! $userAnswers = [questionID => answerID]
    $userAnswers = ( $postData[ 'answers' ] ?? [] );
    if ( empty( $userAnswers ) ) {
        //#! Add/update user quiz attempt
        $sqmDB->updateUserQuizAnswer( $quizID, $userID, false );
        SQM_Notices::create( 'error', $quiz->message_error );
        return;
    }

    $quizInfo = $sqm->getQuizInfo( $quiz );
    $quizQuestions = $quizInfo[ 'questions' ];
    $quizAnswers = $quizInfo[ 'answers' ];

    $userPoints = 0;
    foreach ( $userAnswers as $questionID => $answerID ) {
        foreach ( $quizQuestions as $qq ) {
            if ( $qq->id == $questionID ) {
                if ( isset( $quizAnswers[ $questionID ] ) ) {
                    $qa = $quizAnswers[ $questionID ];
                    foreach ( $qa as $entry ) {
                        if ( $entry->id == $answerID && (bool)$entry->is_correct ) {
                            $userPoints += (int)$entry->points;
                        }
                    }
                }
            }
        }
    }

    //#! get quiz available points
    $quizPoints = $sqm->getQuizPoints( $quizID );
    $success = ( $quizPoints == $userPoints );

    //#! Add/update user quiz attempt
    $sqmDB->updateUserQuizAnswer( $quizID, $userID, $success );

    $class = ( $success ? 'success' : 'error' );
    $message = ( $success ? $quiz->message_success : $quiz->message_error );

    SQM_Notices::create( $class, $message );
}

/*
 * Listen for quiz forms submission events
 */
add_action( 'init', function () {
    if ( !is_admin() && ( 'POST' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) ) {
        $action = ( $_POST[ 'quiz_submit_action' ] ?? '' );
        if ( !empty( $action ) && 'submit_answer' == $action ) {
            sqm_quiz_handle_user_form_submit( $_POST );
        }
    }
} );

/*
 * [FRONTEND]
 * Render the quiz
 */
add_action( 'sqm/quiz/render', function () {
    if ( !is_user_logged_in() ) {
        do_action( 'sqm/quiz/not-logged-in' );
        return;
    }

    $sqm = SomaQuizMaster::getInstance();
    $quiz = $sqm->getActiveQuiz();
    if ( !$quiz ) {
        do_action( 'sqm/quiz/no-quizzes' );
        return;
    }
    $quizInfo = $sqm->getQuizInfo( $quiz );
    if ( !$quizInfo ) {
        do_action( 'sqm/quiz/no-quizzes' );
        return;
    }

    $userID = wp_get_current_user()->ID;
    //#! Check if user can take quiz
    $userInfo = $sqm->getQuizUserInfo( $quiz->id, $userID );
    if ( $userInfo ) {
        if ( $userInfo->success ) {
            do_action( 'sqm/quiz/taken-success' );
            return;
        }
        //#! If the user has consumed all attempts
        if ( $userInfo->attempts == $quiz->max_attempts ) {
            if ( 'POST' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
                SQM_Notices::show();
            }
            else {
                do_action( 'sqm/quiz/max-attempts-reached' );
            }
            return;
        }
    }

    $questions = $quizInfo[ 'questions' ];
    $quizAnswers = $quizInfo[ 'answers' ];
    ?>
    <div class="quiz-wrap">
        <?php
        //#! Error messages when quiz has more than 1 attempts
        SQM_Notices::show();
        ?>
        <header class="quiz-title-wrap">
            <h2><?php echo apply_filters( 'sqm/quiz/title', $quiz->title ); ?></h2>
        </header>
        <section class="quiz-content-wrap">
            <form method="post">
                <?php wp_nonce_field( SQM_NONCE_ACTION, SQM_NONCE_NAME ); ?>
                <input type="hidden" name="quiz_submit_action" value="submit_answer"/>
                <input type="hidden" name="quiz_id" value="<?php echo esc_attr( $quiz->id ); ?>"/>
                <?php
                foreach ( $questions as $question ) :
                    ?>
                    <h4 class="quiz-question-title"><?php echo $question->title; ?></h4>
                    <?php
                    $answers = $quizAnswers[ $question->id ];
                    foreach ( $answers as $answer ) :
                        ?>
                        <div class="quiz-section-answer">
                            <label for="answer-<?php echo esc_attr( $answer->id ); ?>">
                                <input type="radio" id="answer-<?php echo esc_attr( $answer->id ); ?>" name="answers[<?php echo esc_attr( $question->id ); ?>]" value="<?php echo esc_attr( $answer->id ); ?>"/>
                                <?php echo $answer->title; ?>
                            </label>
                        </div>
                    <?php
                    endforeach;
                endforeach;
                ?>
                <div>
                    <button type="submit" class="button button-primary">Submit</button>
                </div>
            </form>
        </section>
    </div>
    <?php
} );
