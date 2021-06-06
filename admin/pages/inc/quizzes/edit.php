<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
global $sqmState, $sqmDB;

//#! Make sure we have a quiz to work with
$quizID = ( $_REQUEST[ 'quiz_id' ] ?? 0 );
if ( empty( $quizID ) ) {
    SQM_Notices::create( 'success', 'The quiz ID is missing.' );
    ?>
    <script>
        window.location.href = "<?php echo add_query_arg( [ 'page' => 'sqm_quiz_list', ], admin_url( 'admin.php' ) );?>";
    </script>
    <?php
    exit;
}

//#! Page vars

$editUrl = add_query_arg( [
    'page' => 'sqm_quiz_list',
    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUIZ,
    'quiz_id' => $quizID,
], admin_url( 'admin.php' ) );

$urlCreateQuestion = add_query_arg( [
    'page' => 'sqm_quiz_list',
    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUESTION,
    'quiz_id' => $quizID,
], admin_url( 'admin.php' ) );
//=========================================================

$result = [
    'class' => '',
    'text' => '',
];

//#! Quiz vars
$quiz = $sqmDB->getQuizByID( $quizID );
$title = '';
$maxAttempts = 1;
$dateStart = '';
$dateEnd = '';
$messageSuccess = '';
$messageError = '';

if ( $quiz ) {
    $title = $quiz->title;
    $maxAttempts = $quiz->max_attempts;
    $dateStart = $quiz->date_start;
    $dateEnd = $quiz->date_end;
    $messageSuccess = $quiz->message_success;
    $messageError = $quiz->message_error;
}
else {
    $result = [
        'class' => 'error',
        'text' => 'The specified quiz was not found',
    ];
}

if ( 'POST' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
    $title = $sqmState->get( 'title' );
    $maxAttempts = $sqmState->get( 'max_attempts' );
    $dateStart = $sqmState->get( 'date_start' );
    $dateEnd = $sqmState->get( 'date_end' );
    $messageSuccess = $sqmState->get( 'message_success' );
    $messageError = $sqmState->get( 'message_error' );
}
?>
<div class="wrap sqm-wrap">
    <header>
        <h1 class="wp-heading-inline">Update Quiz</h1>
        <a href="<?php echo esc_url( $urlCreateQuestion ); ?>" class="page-title-action">Add question</a>
    </header>

    <main>
        <?php
        if ( !empty( $result[ 'text' ] ) ) {
            echo '<div class="alert alert-' . esc_attr( $result[ 'class' ] ) . '">' . $result[ 'text' ] . '</div>';
        }
        else {
            SQM_Notices::show();
        }
        ?>

        <?php if ( $quiz ): ?>
            <form method="post">
                <?php wp_nonce_field( SQM_NONCE_ACTION, SQM_NONCE_NAME ); ?>

                <input type="hidden" name="id" value="<?php echo esc_attr( $quizID ); ?>"/>
                <input type="hidden" name="sqm_action" value="<?php echo esc_attr( SQM_AdminQuizzes::ACTION_EDIT_QUIZ ); ?>"/>

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
                    <label for="message_success">Success message</label>
                    <textarea id="message_success" name="message_success" rows="4" class="widefat"><?php echo $messageSuccess; ?></textarea>
                </div>
                <div class="form-section">
                    <label for="message_error">Error message</label>
                    <textarea id="message_error" name="message_error" rows="4" class="widefat"><?php echo $messageError; ?></textarea>
                </div>

                <div class="form-section">
                    <button type="submit" class="button button-primary">Update</button>
                </div>
            </form>
        <?php endif; ?>
    </main>

</div>
