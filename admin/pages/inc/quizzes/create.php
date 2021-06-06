<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
global $sqmState;

//#! Page vars
$createUrl = add_query_arg( [
    'page' => 'sqm_quiz_list',
    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUIZ,
], admin_url( 'admin.php' ) );

//#! Quiz vars
$title = $sqmState->get( 'title' );
$maxAttempts = $sqmState->get( 'max_attempts' );
$dateStart = $sqmState->get( 'date_start' );
$dateEnd = $sqmState->get( 'date_end' );
?>
<div class="wrap sqm-wrap">
    <header>
        <h1>Add new Quiz</h1>
    </header>

    <main>
        <?php SQM_Notices::show(); ?>

        <form method="post">
            <?php wp_nonce_field( SQM_NONCE_ACTION, SQM_NONCE_NAME ); ?>
            <input type="hidden" name="sqm_action" value="<?php echo esc_attr( SQM_AdminQuizzes::ACTION_CREATE_QUIZ ); ?>"/>

            <div class="form-section">
                <label for="title">Title</label>
                <input id="title" name="title" type="text" class="widefat" value="<?php echo wp_kses( $title, [] ); ?>"/>
            </div>
            <div class="form-section">
                <label for="max_attempts">Max attempts</label>
                <input id="max_attempts" name="max_attempts" type="text" class="widefat" value="<?php echo $maxAttempts; ?>"/>
            </div>
            <div class="form-section">
                <label for="date_start">Date start</label>
                <input id="date_start" name="date_start" type="text" class="widefat js-datepicker" value="<?php echo $dateStart; ?>"/>
            </div>
            <div class="form-section">
                <label for="date_end">Date end</label>
                <input id="date_end" name="date_end" type="text" class="widefat js-datepicker" value="<?php echo $dateEnd; ?>"/>
            </div>

            <div class="form-section">
                <button type="submit" class="button button-primary">Submit</button>
            </div>
        </form>
    </main>

</div>
