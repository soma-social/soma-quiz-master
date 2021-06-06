<?php
/*
 * This file provides all the actions & filters available in the plugin which themes can modify.
 * This file should be copied and loaded into the active theme
 */

//#! [QUIZ PAGE] Error message displayed if the user is not logged in
add_action( 'sqm/quiz/not-logged-in', function () {
    ?>
    <p>You need to be logged in to take the quiz.</p>
    <?php
} );

//#! [QUIZ PAGE] Error message displayed if there is no active quiz
add_action( 'sqm/quiz/no-quizzes', function () {
    ?>
    <p>There are no quizzes at the moment, please check back later.</p>
    <?php
} );

//#! [QUIZ PAGE] Message displayed if the user has solved the quiz successfully
add_action( 'sqm/quiz/taken-success', function () {
    ?>
    <p>You have already solved this quiz!.</p>
    <?php
} );

//#! [QUIZ PAGE] Message displayed if the user has reached the quiz max attempts
add_action( 'sqm/quiz/max-attempts-reached', function () {
    ?>
    <p>You have reached the maximum attempts available to solve this quiz!.</p>
    <?php
} );

//#! [QUIZ PAGE] Filters the quiz title
add_filter( 'sqm/quiz/title', function ( $quizTitle ) {
    return $quizTitle;
} );

//#! [QUIZ PAGE][FORM SUBMISSION] Filters the success message
add_filter( 'sqm/quiz/user/answered-quiz-success', function ( $message = '' ) {
//    return 'You have already answered successfully to this quiz.';
    return $message;
} );

//#! [QUIZ PAGE][FORM SUBMISSION]  Filters the error message if the user has reached the quiz max attempts
add_filter( 'sqm/quiz/user/max-attempts-reached', function ( $message = '' ) {
//    return 'You have reached the maximum allowed attempts.';
    return $message;
} );
