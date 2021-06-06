<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
global $sqmState, $sqmDB;

//#! Page vars
$quizID = ( $_REQUEST[ 'quiz_id' ] ?? 0 );
if ( empty( $quizID ) ) {
    exit( 'INTERNAL ERROR: the quiz id is missing.' );
}
$questionID = ( $_REQUEST[ 'question_id' ] ?? 0 );
if ( empty( $questionID ) ) {
    exit( 'INTERNAL ERROR: the quiz question id is missing.' );
}
$question = $sqmDB->getQuestionByID( $questionID );
if ( empty( $question ) ) {
    exit( 'INTERNAL ERROR: the quiz question was not found.' );
}

$questionPageUrl = add_query_arg( [
    'page' => 'sqm_quiz_list',
    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUESTION,
    'quiz_id' => $quizID,
    'question_id' => $questionID,
], admin_url( 'admin.php' ) );

//#! Quiz vars
$answers = $sqmDB->getAnswers( $questionID );
?>
<div class="wrap sqm-wrap">
    <header>
        <h1 class="wp-heading-inline">Quiz answers</h1>
        <a href="<?php echo esc_url( $questionPageUrl ); ?>" class="page-title-action">View questions</a>
    </header>

    <main>
        <?php SQM_Notices::show(); ?>

        <p>
            Question: <strong><?php echo wp_kses( $question->title, [] ); ?></strong>
        </p>

        <form method="post" id="js-form-add-answer">
            <?php wp_nonce_field( SQM_NONCE_ACTION, SQM_NONCE_NAME ); ?>
            <input type="hidden" name="sqm_action" value="<?php echo esc_attr( SQM_AdminQuizzes::ACTION_CREATE_ANSWER ); ?>"/>
            <input type="hidden" id="quiz_id" name="quiz_id" value="<?php echo esc_attr( $quizID ); ?>"/>
            <input type="hidden" id="question_id" name="question_id" value="<?php echo esc_attr( $questionID ); ?>"/>

            <div id="js-form-inner-wrap">
                <?php
                if ( !empty( $answers ) ) {
                    foreach ( $answers as $answer ) {
                        ?>
                        <div class="form-section js-section-answer" id="section-answer-<?php echo esc_attr( $answer->id ); ?>" data-id="<?php echo esc_attr( $answer->id ); ?>">
                            <p>
                                <label for="title-<?php echo esc_attr( $answer->id ); ?>">Answer</label>
                                <input id="title-<?php echo esc_attr( $answer->id ); ?>" name="answers[<?php echo esc_attr( $answer->id ); ?>]" type="text" class="widefat" value="<?php echo wp_kses( $answer->title, [] ); ?>"/>
                            </p>
                            <p>
                                <label for="points-<?php echo esc_attr( $answer->id ); ?>">Points</label>
                                <input id="points-<?php echo esc_attr( $answer->id ); ?>" name="points[<?php echo esc_attr( $answer->id ); ?>]" type="number" class="widefat" value="<?php echo $answer->points; ?>" min="0" step="1"/>
                            </p>
                            <p>
                                <label for="correct-<?php echo esc_attr( $answer->id ); ?>">Is correct answer?</label>
                                <input id="correct-<?php echo esc_attr( $answer->id ); ?>" name="correct[<?php echo esc_attr( $answer->id ); ?>]" type="checkbox" class="widefat" value="1" <?php echo( $answer->is_correct ? 'checked' : '' ); ?>/>
                            </p>

                            <button type="button" class="button button-secondary button-small js-button-update-answer" data-id="<?php echo esc_attr( $answer->id ); ?>" data-parent="section-answer-<?php echo esc_attr( $answer->id ); ?>">
                                Update
                            </button>
                            <button type="button" class="button button-link-delete button-small js-button-delete-answer" data-parent="section-answer-<?php echo esc_attr( $answer->id ); ?>">
                                Delete
                            </button>
                            <img src="<?php echo SQM_URI; ?>/admin/res/img/ajax-loader.gif" class="ajax-loader js-ajax-loader hidden js-ajax-loader" alt=""/>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <div class="form-section">
                <button type="button" class="button button-secondary js-btn-add-answer">Add answer</button>
                <button type="submit" class="button button-primary">Submit</button>
            </div>
        </form>
    </main>

</div>
