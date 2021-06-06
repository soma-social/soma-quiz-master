<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

class SQM_DB
{
    const DB_VERSION_OPT_NAME = 'sqm_quizzes_db_version';
    const DB_VERSION = '1.0';

    public $tableQuizzes = '';
    public $tableQuizQuestions = '';
    public $tableQuizAnswers = '';
    public $tableQuizUsers = '';

    public function __construct()
    {
        global $wpdb;

        $this->tableQuizzes = $wpdb->prefix . 'sqm_quizzes';
        $this->tableQuizQuestions = $wpdb->prefix . 'sqm_quiz_questions';
        $this->tableQuizAnswers = $wpdb->prefix . 'sqm_quiz_answers';
        $this->tableQuizUsers = $wpdb->prefix . 'sqm_quiz_users';
    }

    //<editor-fold desc=":: QUERIES ::">
    public function getQuiz( string $title )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizzes} WHERE title='%s'", $title );
        return $wpdb->get_row( $query );
    }

    public function getQuestion( int $quizID, string $title )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizQuestions} WHERE quiz_id=%d AND title='%s'", $quizID, $title );
        return $wpdb->get_row( $query );
    }

    public function getQuizByID( int $id )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizzes} WHERE id=%d", $id );
        return $wpdb->get_row( $query );
    }

    public function getQuestionByID( int $id )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizQuestions} WHERE id=%d", $id );
        return $wpdb->get_row( $query );
    }

    public function getQuizzes( int $offset = 0, int $limit = 0 )
    {
        global $wpdb;

        $limit = ( !empty( $limit ) ? "LIMIT {$offset},{$limit}" : '' );
        $query = "SELECT * FROM {$this->tableQuizzes} {$limit}";
        return $wpdb->get_results( $query );
    }

    public function getQuestions( int $quizID )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizQuestions} WHERE quiz_id = %d", $quizID );
        return $wpdb->get_results( $query );
    }

    public function getAnswers( int $quizQuestionID )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizAnswers} WHERE quiz_question_id = %d", $quizQuestionID );
        return $wpdb->get_results( $query );
    }

    public function getAnswer( int $quizQuestionID, string $title )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizAnswers} WHERE quiz_question_id=%d AND title='%s'", $quizQuestionID, $title );
        return $wpdb->get_row( $query );
    }

    public function getAnswerByID( int $id )
    {
        global $wpdb;

        $query = sprintf( "SELECT * FROM {$this->tableQuizAnswers} WHERE id=%d", $id );
        return $wpdb->get_row( $query );
    }

    public function quizExists( string $title ): bool
    {
        $quiz = $this->getQuiz( $title );
        return !empty( $quiz );
    }

    public function getQuizUserInfo( int $quizID, int $userID )
    {
        global $wpdb;
        $query = sprintf( "SELECT * FROM $this->tableQuizUsers WHERE quiz_id=%d AND user_id=%d", $quizID, $userID );
        return $wpdb->get_row( $query );
    }
    //</editor-fold desc=":: QUERIES ::">

    //<editor-fold desc=":: DB MANAGEMENT ::">
    /**
     * Insert the quiz in the database. if the quiz exists, it will be updated
     * @param string $title
     * @param int $maxAttempts
     * @param string $dateStart
     * @param string $dateEnd
     * @param string $messageSuccess
     * @param string $messageError
     * @return bool
     */
    public function quizCreate( string $title, int $maxAttempts, string $dateStart, string $dateEnd, string $messageSuccess = '', string $messageError = '' ): bool
    {
        global $wpdb;

        $data = [
            'title' => $title,
            'max_attempts' => $maxAttempts,
            'date_start' => date( 'Y-m-d H:i:s', strtotime( $dateStart ) ),
            'date_end' => date( 'Y-m-d H:i:s', strtotime( $dateEnd ) ),
            'message_success' => $messageSuccess,
            'message_error' => $messageError,
        ];
        $dataFormat = [ '%s', '%s', '%s', '%s', '%s', '%s', ];

        if ( $this->quizExists( $title ) ) {
            $updated = $wpdb->update(
                $this->tableQuizzes,
                $data,
                $where = [ 'title' => $title ],
                $dataFormat,
                $whereFormat = [ '%s' ]
            );
            return ( false !== $updated );
        }

        $created = $wpdb->insert(
            $this->tableQuizzes,
            $data,
            $dataFormat
        );
        return ( false !== $created );
    }

    /**
     * Update a quiz
     * @param int $id
     * @param string $title
     * @param int $maxAttempts
     * @param string $dateStart
     * @param string $dateEnd
     * @param string $messageSuccess
     * @param string $messageError
     * @return bool
     */
    public function quizUpdate( int $id, string $title, int $maxAttempts, string $dateStart, string $dateEnd, string $messageSuccess = '', string $messageError = '' ): bool
    {
        global $wpdb;

        $data = [
            'title' => $title,
            'max_attempts' => $maxAttempts,
            'date_start' => date( 'Y-m-d H:i:s', strtotime( $dateStart ) ),
            'date_end' => date( 'Y-m-d H:i:s', strtotime( $dateEnd ) ),
            'message_success' => $messageSuccess,
            'message_error' => $messageError,
        ];
        $dataFormat = [ '%s', '%s', '%s', '%s', '%s', '%s', ];

        $updated = $wpdb->update(
            $this->tableQuizzes,
            $data,
            $where = [ 'id' => $id ],
            $dataFormat,
            $whereFormat = [ '%d' ]
        );
        return ( false !== $updated );
    }

    public function questionCreate( int $quizID, string $title ): bool
    {
        global $wpdb;
        $data = [ 'quiz_id' => $quizID, 'title' => $title ];
        $dataFormat = [ '%d', '%s' ];

        if ( $this->getQuestion( $quizID, $title ) ) {
            $updated = $wpdb->update(
                $this->tableQuizQuestions,
                $data,
                $where = [ 'quiz_id' => $quizID, 'title' => $title ],
                $dataFormat,
                $whereFormat = [ '%d', '%s' ]
            );
            return ( false !== $updated );
        }

        $created = $wpdb->insert(
            $this->tableQuizQuestions,
            $data,
            $dataFormat
        );
        return ( false !== $created );
    }

    public function questionUpdate( int $quizID, int $questionID, string $title ): bool
    {
        global $wpdb;
        $data = [
            'quiz_id' => $quizID,
            'title' => $title,
        ];
        $dataFormat = [ '%d', '%s' ];
        $updated = $wpdb->update(
            $this->tableQuizQuestions,
            $data,
            $where = [ 'id' => $questionID ],
            $dataFormat,
            $whereFormat = [ '%d' ]
        );
        return ( false !== $updated );
    }

    public function questionDelete( int $questionID ): bool
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->tableQuizQuestions,
            $where = [ 'id' => $questionID ],
            $whereFormat = [ '%d' ]
        );
        //#! Delete Answers
        if ( false !== $result ) {
            $wpdb->delete(
                $this->tableQuizAnswers,
                $where = [ 'quiz_question_id' => $questionID ],
                $whereFormat = [ '%d' ]
            );
            return true;
        }
        return false;
    }

    public function answerCreate( int $quizQuestionID, string $title, $points = 0, $isCorrect = false ): bool
    {
        global $wpdb;
        $data = [
            'quiz_question_id' => $quizQuestionID,
            'title' => $title,
            'points' => $points,
            'is_correct' => $isCorrect,
        ];
        $dataFormat = [ '%d', '%s', '%d', '%d' ];

        if ( $this->getAnswer( $quizQuestionID, $title ) ) {
            $updated = $wpdb->update(
                $this->tableQuizAnswers,
                $data,
                $where = [ 'quiz_question_id' => $quizQuestionID, 'title' => $title ],
                $dataFormat,
                $whereFormat = [ '%d', '%s' ]
            );
            return ( false !== $updated );
        }

        $created = $wpdb->insert(
            $this->tableQuizAnswers,
            $data,
            $dataFormat
        );
        return ( false !== $created );
    }

    public function answerUpdate( int $quizAnswerID, int $quizQuestionID, string $title, $points = 0, $isCorrect = 1 ): bool
    {
        global $wpdb;
        $data = [
            'quiz_question_id' => $quizQuestionID,
            'title' => $title,
            'points' => $points,
            'is_correct' => $isCorrect,
        ];
        $dataFormat = [ '%d', '%s', '%d', '%d' ];
        $updated = $wpdb->update(
            $this->tableQuizAnswers,
            $data,
            $where = [ 'id' => $quizAnswerID ],
            $dataFormat,
            $whereFormat = [ '%d' ]
        );
        return ( false !== $updated );
    }

    public function answerDelete( int $answerID ): bool
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->tableQuizAnswers,
            $where = [ 'id' => $answerID ],
            $whereFormat = [ '%d' ]
        );
        //#! Delete Answer
        return ( false !== $result );
    }

    /**
     * Update the user's answer for the specified quiz
     * @param int $quizID
     * @param int $userID
     * @param bool $success
     * @return bool
     */
    public function updateUserQuizAnswer( int $quizID, int $userID, bool $success = false ): bool
    {
        global $wpdb;

        $record = $this->getUserQuizAnswer( $quizID, $userID );
        if ( $record ) {
            $data = [
                'attempts' => $record->attempts + 1,
                'date_submitted' => date( 'Y-m-d H:i:s' ),
                'success' => (int)$success,
            ];
            $dataFormat = [ '%d', '%s', '%d' ];
            $updated = $wpdb->update(
                $this->tableQuizUsers,
                $data,
                $where = [ 'id' => $record->id ],
                $dataFormat,
                $whereFormat = [ '%d' ]
            );
            return ( $updated != false );
        }

        $result = $wpdb->insert(
            $this->tableQuizUsers,
            [
                'quiz_id' => $quizID,
                'user_id' => $userID,
                'attempts' => 1,
                'date_submitted' => date( 'Y-m-d H:i:s' ),
                'success' => (int)$success,
            ],
            [ '%d', '%d', '%d', '%s', '%d' ]
        );
        return ( false != $result );
    }

    /**
     * Retrieve the database entry for the user answer
     * @param int $quizID
     * @param int $userID
     * @return array|object|void|null
     */
    public function getUserQuizAnswer( int $quizID, int $userID )
    {
        global $wpdb;
        $query = sprintf( "SELECT * FROM $this->tableQuizUsers WHERE quiz_id=%d AND user_id=%d", $quizID, $userID );
        return $wpdb->get_row( $query );
    }
    //</editor-fold desc=":: DB MANAGEMENT ::">

    //<editor-fold desc=":: DB TABLES ::">
    public function createTables()
    {
        //#! Check to see if we've already created the tables
        $dbVersion = get_option( self::DB_VERSION_OPT_NAME, false );
        if ( empty( $dbVersion ) || version_compare( $dbVersion, self::DB_VERSION, '<' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            global $wpdb;

            $this->createTable_quizzes( $wpdb );
            $this->createTable_quiz_questions( $wpdb );
            $this->createTable_quiz_answers( $wpdb );
            $this->createTable_quiz_users( $wpdb );

            update_option( self::DB_VERSION_OPT_NAME, self::DB_VERSION );
        }
    }

    public function dropTables()
    {
        global $wpdb;

        $query = "DROP TABLE IF EXISTS {$this->tableQuizUsers}";
        $wpdb->query( $query );
        $query = "DROP TABLE IF EXISTS {$this->tableQuizAnswers}";
        $wpdb->query( $query );
        $query = "DROP TABLE IF EXISTS {$this->tableQuizQuestions}";
        $wpdb->query( $query );
        $query = "DROP TABLE IF EXISTS {$this->tableQuizzes}";
        $wpdb->query( $query );

        delete_option( self::DB_VERSION_OPT_NAME );
    }

    private function createTable_quizzes( wpdb $wpdb )
    {
        $table_name = $this->tableQuizzes;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(250) NOT NULL default '',
        max_attempts int(8) NOT NULL default 1,
        date_start DATETIME NOT NULL default '0000-00-00 00:00:00',
        date_end DATETIME NOT NULL default '0000-00-00 00:00:00',
        message_success TEXT NOT NULL default '',
        message_error TEXT NOT NULL default '',
        PRIMARY KEY (`id`),
        INDEX `title` (`title`(191))
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private function createTable_quiz_questions( wpdb $wpdb )
    {
        $table_name = $this->tableQuizQuestions;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        quiz_id bigint(20) NOT NULL,
        title varchar(250) NOT NULL default '',
    
        PRIMARY KEY (`id`),
        INDEX `quiz_id` (`quiz_id`),
        INDEX `title` (`title`(191))
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private function createTable_quiz_answers( wpdb $wpdb )
    {
        $table_name = $this->tableQuizAnswers;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        quiz_question_id bigint(20) NOT NULL,
        title varchar(250) NOT NULL default '',
        points int(8) NOT NULL default 0,
        is_correct int(8) NOT NULL default 0,
    
        PRIMARY KEY (`id`),
        INDEX `quiz_question_id` (`quiz_question_id`),
        INDEX `title` (`title`(191))
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private function createTable_quiz_users( wpdb $wpdb )
    {
        $table_name = $this->tableQuizUsers;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        quiz_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        attempts int(8) NOT NULL default 0,
        date_submitted DATETIME NOT NULL default '0000-00-00 00:00:00',
        success int(8) NOT NULL default 0,
    
        PRIMARY KEY (`id`),
        INDEX `quiz_id` (`quiz_id`),
        INDEX `user_id` (`user_id`)
        ) $charset_collate;";

        dbDelta( $sql );
    }
    //</editor-fold desc=":: DB TABLES ::">

}
