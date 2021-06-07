<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

global $sqmDB;

$createUrl = add_query_arg( [
    'page' => 'sqm_quiz_list',
    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUIZ,
], admin_url( 'admin.php' ) );

$quizzes = $sqmDB->getQuizzes();
?>
<div class="wrap sqm-wrap">
    <header>
        <h1 class="wp-heading-inline">Quizzes</h1>
        <a href="<?php echo esc_url( $createUrl ); ?>" class="page-title-action">Add New</a>
    </header>

    <main>
        <?php SQM_Notices::show(); ?>


        <?php
        if ( empty( $quizzes ) ) {
            ?>
            <div class="alert alert-info">
                <p>
                    There are no quizzes, why not <a href="<?php echo esc_url( $createUrl ); ?>">create</a> one?
                </p>
            </div>
            <?php
        }
        else {
            ?>
            <table class="table widefat table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Title</th>
                        <th scope="col">Max attempts</th>
                        <th scope="col">Date start</th>
                        <th scope="col">Date end</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $quizzes as $quiz ) {
                        $editUrl = add_query_arg( [
                            'page' => 'sqm_quiz_list',
                            SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_EDIT_QUIZ,
                            'quiz_id' => $quiz->id,
                        ], admin_url( 'admin.php' ) );
                        ?>
                        <tr>
                            <td><?php echo $quiz->id; ?></td>
                            <td><?php echo $quiz->title; ?></td>
                            <td><?php echo $quiz->max_attempts; ?></td>
                            <td><?php echo date( SQM_DATE_FORMAT_DISPLAY . ' H:i:s', strtotime( $quiz->date_start ) ); ?></td>
                            <td><?php echo date( SQM_DATE_FORMAT_DISPLAY . ' H:i:s', strtotime( $quiz->date_end ) ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( $editUrl ); ?>">Edit</a>
                                <span class="bar-sep">|</span>
                                <?php
                                $questionsPageUrl = add_query_arg( [
                                    'page' => 'sqm_quiz_list',
                                    SQM_AdminQuizzes::ACTION_NAME => SQM_AdminQuizzes::ACTION_CREATE_QUESTION,
                                    'quiz_id' => $quiz->id,
                                ], admin_url( 'admin.php' ) );
                                ?>
                                <a href="<?php echo esc_url( $questionsPageUrl ); ?>">Questions</a>
                                <span class="bar-sep">|</span>
                                <span>Delete</span>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </main>
</div>
