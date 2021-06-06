<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

$action = SQM_AdminQuizzes::getAction();

switch ( $action ) {
    case SQM_AdminQuizzes::ACTION_CREATE_QUIZ:
    {
        SQM_AdminQuizzes::loadTemplate( 'inc/quizzes/create' );
        break;
    }
    case SQM_AdminQuizzes::ACTION_EDIT_QUIZ:
    {
        SQM_AdminQuizzes::loadTemplate( 'inc/quizzes/edit' );
        break;
    }
    case SQM_AdminQuizzes::ACTION_CREATE_QUESTION:
    {
        SQM_AdminQuizzes::loadTemplate( 'inc/questions/create' );
        break;
    }
    case SQM_AdminQuizzes::ACTION_CREATE_ANSWER:
    {
        SQM_AdminQuizzes::loadTemplate( 'inc/answers/create' );
        break;
    }
    default:
    {
        SQM_AdminQuizzes::loadTemplate( 'inc/quizzes/list' );
    }
}

