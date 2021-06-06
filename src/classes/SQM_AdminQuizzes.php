<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

class SQM_AdminQuizzes
{
    const ACTION_NAME = 'sqm_action';

    //#! Quiz Action
    const ACTION_CREATE_QUIZ = 'create_quiz';
    const ACTION_EDIT_QUIZ = 'edit_quiz';
    const ACTION_DELETE_QUIZ = 'delete_quiz';

    const ACTION_CREATE_QUESTION = 'create_question';
    const ACTION_EDIT_QUESTION = 'edit_question';
    const ACTION_DELETE_QUESTION = 'delete_question';

    const ACTION_CREATE_ANSWER = 'create_answer';
    const ACTION_EDIT_ANSWER = 'edit_answer';
    const ACTION_DELETE_ANSWER = 'delete_answer';

    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';

    public static function hasAction(): bool
    {
        return ( '' != self::getAction() );
    }

    public static function getAction()
    {
        return ( $_REQUEST[ self::ACTION_NAME ] ?? '' );
    }

    public static function isCreatingQuiz(): bool
    {
        return ( self::hasAction() && ( self::ACTION_CREATE_QUIZ == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isEditingQuiz(): bool
    {
        return ( self::hasAction() && ( self::ACTION_EDIT_QUIZ == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isDeletingQuiz(): bool
    {
        return ( self::hasAction() && ( self::ACTION_DELETE_QUIZ == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isCreatingQuestion(): bool
    {
        return ( self::hasAction() && ( self::ACTION_CREATE_QUESTION == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isEditingQuestion(): bool
    {
        return ( self::hasAction() && ( self::ACTION_EDIT_QUESTION == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isDeletingQuestion(): bool
    {
        return ( self::hasAction() && ( self::ACTION_DELETE_QUESTION == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isCreatingAnswer(): bool
    {
        return ( self::hasAction() && ( self::ACTION_CREATE_ANSWER == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isEditingAnswer(): bool
    {
        return ( self::hasAction() && ( self::ACTION_EDIT_ANSWER == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    public static function isDeletingAnswer(): bool
    {
        return ( self::hasAction() && ( self::ACTION_DELETE_ANSWER == $_REQUEST[ self::ACTION_NAME ] ) );
    }

    /**
     * Load a template
     * @param string $filePath The path to the template file relative to the admin/pages directory
     */
    public static function loadTemplate( string $filePath )
    {
        require_once( SQM_DIR . "/admin/pages/{$filePath}.php" );
    }

    public static function create( array $postData = [], bool $validateNonce = true )
    {
        if ( $validateNonce ) {
            if ( !isset( $postData[ SQM_NONCE_NAME ] ) || !wp_verify_nonce( $postData[ SQM_NONCE_NAME ], SQM_NONCE_ACTION ) ) {
                return new WP_Error( 'sqm', '<p>The form is not valid, the security code is either missing or expired.</p>' );
            }
        }

        if ( empty( $postData ) ) {
            return new WP_Error( 'sqm', '<p>The form is not valid, it should not be empty.</p>' );
        }

        $title = self::getSanitizedField( 'title', self::TYPE_STRING );
        $maxAttempts = self::getSanitizedField( 'max_attempts', self::TYPE_INT );
        $dateStart = self::getSanitizedField( 'date_start', self::TYPE_STRING );
        $dateEnd = self::getSanitizedField( 'date_end', self::TYPE_STRING );
        $mSuccess = self::getSanitizedField( 'message_success', self::TYPE_STRING );
        $mError = self::getSanitizedField( 'message_error', self::TYPE_STRING );

        $errors = [];
        if ( empty( $title ) ) {
            $errors[] = '<p>Please provide a title.</p>';
        }
        if ( empty( $maxAttempts ) ) {
            $errors[] = '<p>Please provide the max attempts.</p>';
        }

        if ( !empty( $errors ) ) {
            return new WP_Error( 'sqm', implode( '', $errors ) );
        }

        $sqmDB = new SQM_DB();

        //#! Ensure the title is unique
        $quiz = $sqmDB->getQuiz( $title );
        if ( $quiz ) {
            return new WP_Error( 'sqm', '<p>The specified title is already used by another quiz.</p>' );
        }

        $created = $sqmDB->quizCreate( $title, $maxAttempts, $dateStart, $dateEnd, $mSuccess, $mError );
        if ( $created ) {
            return true;
        }

        $errors[] = '<p>An error occurred and the quiz could not be created.</p>';
        return new WP_Error( 'sqm', implode( '', $errors ) );
    }

    public static function update( array $postData = [], bool $validateNonce = true )
    {
        if ( $validateNonce ) {
            if ( !isset( $postData[ SQM_NONCE_NAME ] ) || !wp_verify_nonce( $postData[ SQM_NONCE_NAME ], SQM_NONCE_ACTION ) ) {
                return new WP_Error( 'sqm', '<p>The form is not valid, the security code is either missing or expired.</p>' );
            }
        }

        if ( empty( $postData ) ) {
            return new WP_Error( 'sqm', '<p>The form is not valid, it should not be empty.</p>' );
        }

        $id = self::getSanitizedField( 'id', self::TYPE_INT );
        $title = self::getSanitizedField( 'title', self::TYPE_STRING );
        $maxAttempts = self::getSanitizedField( 'max_attempts', self::TYPE_INT );
        $dateStart = self::getSanitizedField( 'date_start', self::TYPE_STRING );
        $dateEnd = self::getSanitizedField( 'date_end', self::TYPE_STRING );
        $mSuccess = self::getSanitizedField( 'message_success', self::TYPE_STRING );
        $mError = self::getSanitizedField( 'message_error', self::TYPE_STRING );

        $errors = [];
        if ( empty( $id ) ) {
            $errors[] = '<p>The quiz ID is missing.</p>';
        }
        if ( empty( $title ) ) {
            $errors[] = '<p>Please provide a title.</p>';
        }
        if ( empty( $maxAttempts ) ) {
            $errors[] = '<p>Please provide the max attempts.</p>';
        }

        if ( !empty( $errors ) ) {
            return new WP_Error( 'sqm', implode( '', $errors ) );
        }

        $sqmDB = new SQM_DB();

        //#! If another quiz uses the same title
        $quiz = $sqmDB->getQuiz( $title );
        if ( $quiz && ( $quiz->id != $id ) ) {
            return new WP_Error( 'sqm', '<p>The specified title is already used by another quiz.</p>' );
        }
        //#! Update
        $updated = $sqmDB->quizUpdate( $id, $title, $maxAttempts, $dateStart, $dateEnd, $mSuccess, $mError );
        if ( $updated ) {
            return true;
        }

        $errors[] = '<p>An error occurred and the quiz could not be updated.</p>';
        return new WP_Error( 'sqm', implode( '', $errors ) );
    }

    public static function getSanitizedField( $fieldName, $type, $allowedMarkup = [] )
    {
        if ( !isset( $_POST[ $fieldName ] ) ) {
            return '';
        }
        elseif ( self::TYPE_INT == $type ) {
            return (int)$_POST[ $fieldName ];
        }
        elseif ( self::TYPE_STRING == $type ) {
            return wp_kses( $_POST[ $fieldName ], $allowedMarkup );
        }
        elseif ( self::TYPE_ARRAY == $type ) {
            $values = [];
            foreach ( $_POST[ $fieldName ] as $key => &$value ) {
                if ( is_string( $value ) ) {
                    $value = wp_kses( $value, $allowedMarkup );
                }
                $values[ $key ] = $value;
            }
            return $values;
        }
        return '';
    }
}
