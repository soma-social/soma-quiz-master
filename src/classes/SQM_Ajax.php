<?php if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SQM_Ajax
 */
class SQM_Ajax
{
    public function __construct()
    {
        add_action( 'wp_ajax_sqm_ajax', [ $this, 'request' ] );
    }

    /**
     * Required request vars:
     *  = BTH_NONCE_NAME
     *  = request: action to trigger
     *
     */
    public function request()
    {
        if ( 'POST' != strtoupper( getenv( 'REQUEST_METHOD' ) ) ) {
            wp_send_json_error( esc_html__( '[' . __CLASS__ . '] Invalid Request.', 'soma-quiz-master' ) );
        }
        if ( !isset( $_REQUEST[ SQM_NONCE_NAME ] ) || !wp_verify_nonce( $_REQUEST[ SQM_NONCE_NAME ], SQM_NONCE_ACTION ) ) {
            wp_send_json_error( esc_html__( '[' . __CLASS__ . '] Invalid Request. Nonce is either missing or expired.', 'soma-quiz-master' ) );
        }
        if ( !isset( $_REQUEST[ 'request' ] ) || empty( $_REQUEST[ 'request' ] ) ) {
            wp_send_json_error( esc_html__( '[' . __CLASS__ . '] Invalid Request. request var is either missing or empty.', 'soma-quiz-master' ) );
        }
        $function = 'action_' . $_REQUEST[ 'request' ];
        if ( !is_callable( [ $this, $function ] ) ) {
            wp_send_json_error( esc_html__( '[' . __CLASS__ . '] Invalid Request. The requested action is not valid.', 'soma-quiz-master' ) );
        }
        call_user_func( [ $this, $function ], $_REQUEST );
    }

    private function action_delete_question()
    {
        $questionID = ( $_POST[ 'question_id' ] ?? 0 );
        if ( empty( $questionID ) ) {
            wp_send_json_error( 'The question ID is either missing or empty.' );
        }
        global $sqmDB;

        $question = $sqmDB->getQuestionByID( $questionID );
        if ( !$question ) {
            wp_send_json_error( 'The question was not found.' );
        }

        $result = $sqmDB->questionDelete( $questionID );

        if ( $result ) {
            wp_send_json_success( 'Question deleted.' );
        }
        wp_send_json_error( 'An error occurred and the question could not be deleted.' );
    }

    private function action_delete_answer()
    {
        $answerID = ( $_POST[ 'answer_id' ] ?? 0 );
        if ( empty( $answerID ) ) {
            wp_send_json_error( 'The answer ID is either missing or empty.' );
        }
        global $sqmDB;

        $answer = $sqmDB->getAnswerByID( $answerID );
        if ( !$answer ) {
            wp_send_json_error( 'The answer was not found.' );
        }

        $result = $sqmDB->answerDelete( $answerID );

        if ( $result ) {
            wp_send_json_success( 'Answer deleted.' );
        }
        wp_send_json_error( 'An error occurred and the answer could not be deleted.' );
    }

    private function action_update_answer()
    {
        $answerID = SQM_AdminQuizzes::getSanitizedField( 'answer_id', SQM_AdminQuizzes::TYPE_INT );
        if ( empty( $answerID ) ) {
            wp_send_json_error( 'The answer ID is either missing or empty.' );
        }
        $questionID = SQM_AdminQuizzes::getSanitizedField( 'question_id', SQM_AdminQuizzes::TYPE_INT );
        if ( empty( $questionID ) ) {
            wp_send_json_error( 'The question ID is either missing or empty.' );
        }
        $title = SQM_AdminQuizzes::getSanitizedField( 'title', SQM_AdminQuizzes::TYPE_STRING );
        $points = SQM_AdminQuizzes::getSanitizedField( 'points', SQM_AdminQuizzes::TYPE_INT );
        $correct = ( isset( $_POST[ 'correct' ] ) && !empty( $_POST[ 'correct' ] ) );
        if ( empty( $title ) ) {
            wp_send_json_error( 'The answer cannot be empty.' );
        }

        global $sqmDB;

        $answer = $sqmDB->getAnswerByID( $answerID );
        if ( !$answer ) {
            wp_send_json_error( 'The answer was not found.' );
        }

        $result = $sqmDB->answerUpdate( $answerID, $questionID, $title, $points, (int)$correct );

        if ( $result ) {
            wp_send_json_success( 'Answer updated.' );
        }
        wp_send_json_error( 'An error occurred and the answer could not be updated.' );
    }

    private function action_update_question()
    {
        $quizID = SQM_AdminQuizzes::getSanitizedField( 'quiz_id', SQM_AdminQuizzes::TYPE_INT );
        if ( empty( $quizID ) ) {
            wp_send_json_error( 'The quiz ID is either missing or empty.' );
        }
        $questionID = SQM_AdminQuizzes::getSanitizedField( 'question_id', SQM_AdminQuizzes::TYPE_INT );
        if ( empty( $questionID ) ) {
            wp_send_json_error( 'The question ID is either missing or empty.' );
        }
        $title = SQM_AdminQuizzes::getSanitizedField( 'title', SQM_AdminQuizzes::TYPE_STRING );
        if ( empty( $title ) ) {
            wp_send_json_error( 'The answer cannot be empty.' );
        }

        global $sqmDB;

        $question = $sqmDB->getQuestionByID( $questionID );
        if ( !$question ) {
            wp_send_json_error( 'The question was not found.' );
        }

        $result = $sqmDB->questionUpdate( $quizID, $questionID, $title );

        if ( $result ) {
            wp_send_json_success( 'Question updated.' );
        }
        wp_send_json_error( 'An error occurred and the question could not be updated.' );
    }
}
