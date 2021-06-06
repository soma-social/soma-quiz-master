<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
global $sqmState, $sqmDB;

//#! Page vars

$quizID = ( $_REQUEST[ 'quiz_id' ] ?? 0 );
if ( empty( $quizID ) ) {
    exit( 'INTERNAL ERROR: the quiz id is missing.' );
}
$quiz = $sqmDB->getQuizByID( $quizID );
if ( !$quiz ) {
    exit( 'INTERNAL ERROR: the quiz was not found.' );
}

//#! Quiz vars
$questions = $sqmDB->getQuestions( $quizID );
?>
<div class="wrap sqm-wrap">
    <header>
        <h1>Quiz questions</h1>
    </header>

    <main>
        <?php SQM_Notices::show(); ?>

        <form method="post" id="js-form-add-question">
            <?php wp_nonce_field( SQM_NONCE_ACTION, SQM_NONCE_NAME ); ?>
            <input type="hidden" name="sqm_action" value="<?php echo esc_attr( SQM_AdminQuizzes::ACTION_CREATE_QUESTION ); ?>"/>
            <input type="hidden" id="quiz_id" name="id" value="<?php echo esc_attr( $quizID ); ?>"/>

            <div id="js-form-inner-wrap">
                <?php
                if ( !empty( $questions ) ) {
                    foreach ( $questions as $question ) {
                        $manageAnswersUrl = add_query_arg( [
                            'page' => 'sqm_quiz_list',
                            SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_ANSWER,
                            'quiz_id' => $quizID,
                            'question_id' => $question->id,
                        ], admin_url( 'admin.php' ) );
                        ?>
                        <div class="form-section js-section-question" id="section-<?php echo esc_attr( $question->id ); ?>" data-id="<?php echo esc_attr( $question->id ); ?>">
                            <label for="title-<?php echo esc_attr( $question->id ); ?>">Question</label>
                            <input id="title-<?php echo esc_attr( $question->id ); ?>" name="titles[]" type="text" class="widefat" value="<?php echo wp_kses( $question->title, [] ); ?>"/>

                            <a href="<?php echo $manageAnswersUrl; ?>" class="button button-primary button-small" data-parent="section-<?php echo esc_attr( $question->id ); ?>">
                                Manage answers
                            </a>
                            <button type="button" class="button button-secondary button-small js-button-update-question" data-parent="section-<?php echo esc_attr( $question->id ); ?>">
                                Update
                            </button>
                            <button type="button" class="button button-link-delete button-small js-button-delete-question" data-parent="section-<?php echo esc_attr( $question->id ); ?>">
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
                <button type="button" class="button button-secondary js-btn-add-question">Add question</button>
                <button type="submit" class="button button-primary">Submit</button>
            </div>
        </form>
    </main>

</div>
