<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

class SomaQuizMaster
{
    /**
     * @var null|SomaQuizMaster
     */
    private static $instance = null;

    private function __construct()
    {

    }

    public static function getInstance(): ?SomaQuizMaster
    {
        if ( !self::$instance || !( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function isValidWpVersion()
    {
        global $wp_version;
        return ( version_compare( $wp_version, SQM_MIN_WP_VERSION, '>=' ) );
    }

    public function isValidPhpVersion()
    {
        return ( version_compare( phpversion(), SQM_MIN_PHP_VERSION, '>=' ) );
    }

    public function initHooks()
    {
//        add_action( 'init', [ $this, 'registerCustomPostType' ] );
        add_action( 'admin_menu', [ $this, 'setupAdminMenu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'adminLoadScripts' ] );
    }

    public function registerCustomPostType()
    {
        $labels = [
            'name' => __( 'Soma Quizzes', 'soma-quiz-master' ),
            'singular_name' => __( 'Quiz', 'soma-quiz-master' ),
            'menu_name' => __( 'Soma Quizzes', 'soma-quiz-master' ),
            'name_admin_bar' => __( 'Quiz', 'soma-quiz-master' ),
            'add_new' => __( 'Add New', 'soma-quiz-master' ),
            'add_new_item' => __( 'Add New Quiz', 'soma-quiz-master' ),
            'new_item' => __( 'New Quiz', 'soma-quiz-master' ),
            'edit_item' => __( 'Edit Quiz', 'soma-quiz-master' ),
            'view_item' => __( 'View Quiz', 'soma-quiz-master' ),
            'all_items' => __( 'All Quizzes', 'soma-quiz-master' ),
            'search_items' => __( 'Search Quizzes', 'soma-quiz-master' ),
            'parent_item_colon' => __( 'Parent Quiz:', 'soma-quiz-master' ),
            'not_found' => __( 'No Quizzes Found', 'soma-quiz-master' ),
            'not_found_in_trash' => __( 'No Quizzes Found In Trash', 'soma-quiz-master' ),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            //#! Required so it will enable the Gutenberg editor and not the old one
            'show_in_rest' => true,
            'rest_base' => 'soma_quizzes',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'query_var' => true,
            'hierarchical' => true,
            //#! Ensure the URL is as we want
            'rewrite' => [ 'slug' => 'soma_quiz' ],
            'capability_type' => 'post',
            'has_archive' => false,
            //#! Below Posts
            'menu_position' => 5,
            'supports' => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions' ],
            'menu_icon' => 'dashicons-feedback',
        ];

        register_post_type( 'sqm_quiz', $args );
        flush_rewrite_rules();
    }

    public function setupAdminMenu()
    {
        $title = __( 'Soma Quizzes', 'quiz-master-next' );
        add_menu_page( $title, $title, 'edit_posts', 'sqm_dashboard', [ $this, 'render_dashboard_page' ], 'dashicons-feedback' );
        $title = __( 'Dashboard', 'quiz-master-next' );
        add_submenu_page( 'sqm_dashboard', $title, $title, 'edit_posts', 'sqm_dashboard', [ $this, 'render_dashboard_page' ] );
        $title = __( 'Quizzes', 'quiz-master-next' );
        add_submenu_page( 'sqm_dashboard', $title, $title, 'edit_posts', 'sqm_quiz_list', [ $this, 'render_quizzes_page' ] );
    }

    public function render_dashboard_page()
    {
        require_once( SQM_DIR . '/admin/pages/dashboard.php' );
    }

    public function render_quizzes_page()
    {
        require_once( SQM_DIR . '/admin/pages/quizzes.php' );
    }

    public function adminLoadScripts()
    {
        //#! Ensure we load only on our pages
        $page = ( $_REQUEST[ 'page' ] ?? false );
        if ( $page && false !== ( $pos = stripos( $page, 'sqm_' ) ) ) {
            wp_enqueue_style( 'sqm-datetime-picker-styles', SQM_URI . '/admin/res/css/jquery.datetimepicker.css' );
            wp_enqueue_style( 'sqm-admin-styles', SQM_URI . '/admin/res/css/admin-styles.css' );

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'sqm-js-datetime-picker', SQM_URI . '/admin/res/js/jquery.datetimepicker.full.min.js', [ 'jquery', ], '', true );
            wp_enqueue_script( 'sqm-js-admin-scripts', SQM_URI . '/admin/res/js/admin-scripts.js', [ 'jquery', 'sqm-js-datetime-picker' ], '', true );
            wp_localize_script( 'sqm-js-admin-scripts', 'SqmLocale', [
                'ajax' => [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'nonce_name' => SQM_NONCE_NAME,
                    'nonce_value' => wp_create_nonce( SQM_NONCE_ACTION ),
                    'loader_uri' => SQM_URI . '/admin/res/img/ajax-loader.gif',
                ],
            ] );
        }
    }

    /**
     * Get the first active quiz
     * @return mixed|null
     */
    public function getActiveQuiz()
    {
        $sqmDB = new SQM_DB();
        $quizzes = $sqmDB->getQuizzes();
        $data = null;
        if ( $quizzes ) {
            foreach ( $quizzes as $quiz ) {
                $dateStart = strtotime( $quiz->date_start );
                $dateEnd = strtotime( $quiz->date_end );
                $now = time();
                if ( $now >= $dateStart && $now <= $dateEnd ) {
                    //#! [:: 1] - Make sure the quiz has questions
                    $questions = $sqmDB->getQuestions( $quiz->id );
                    $isOk = true;
                    if ( !empty( $questions ) ) {
                        //#! [:: 2] - Make sure every question has answers
                        foreach ( $questions as $question ) {
                            $answers = $sqmDB->getAnswers( $question->id );
                            if ( empty( $answers ) ) {
                                $isOk = false;
                            }
                        }
                    }
                    if ( $isOk ) {
                        $data = $quiz;
                        break;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Retrieve the information for a quiz as an associative list [quiz, questions, answers]
     * @return null|array
     */
    public function getActiveQuizInfo(): ?array
    {
        $sqmDB = new SQM_DB();
        $quizzes = $sqmDB->getQuizzes();
        $data = null;
        if ( $quizzes ) {
            foreach ( $quizzes as $quiz ) {
                $dateStart = strtotime( $quiz->date_start );
                $dateEnd = strtotime( $quiz->date_end );
                $now = time();
                if ( $now >= $dateStart && $now <= $dateEnd ) {
                    //#! [:: 1] - Make sure the quiz has questions
                    $questions = $sqmDB->getQuestions( $quiz->id );
                    if ( !empty( $questions ) ) {
                        $answers = [];
                        $isOk = true;
                        //#! [:: 2] - Make sure every question has answers
                        foreach ( $questions as $question ) {
                            $answers = $sqmDB->getAnswers( $question->id );
                            if ( empty( $answers ) ) {
                                $isOk = false;
                            }
                        }
                        if ( $isOk ) {
                            $data = [
                                'quiz' => $quiz,
                                'questions' => $questions,
                                'answers' => $answers,
                            ];
                            break;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Retrieve the information for the specified quiz as an associative list [questions => [], answers => []]
     * @param $quiz
     * @return null|array
     */
    public function getQuizInfo( $quiz ): ?array
    {
        if ( !$quiz ) {
            return null;
        }
        $sqmDB = new SQM_DB();
        $data = null;
        $dateStart = strtotime( $quiz->date_start );
        $dateEnd = strtotime( $quiz->date_end );
        $now = time();
        if ( $now >= $dateStart && $now <= $dateEnd ) {
            //#! [:: 1] - Make sure the quiz has questions
            $questions = $sqmDB->getQuestions( $quiz->id );
            if ( !empty( $questions ) ) {
                $_answers = [];
                $isOk = true;
                //#! [:: 2] - Make sure every question has answers
                foreach ( $questions as $question ) {
                    $answers = $sqmDB->getAnswers( $question->id );
                    if ( empty( $answers ) ) {
                        $isOk = false;
                        continue;
                    }
                    $_answers[ $question->id ] = $answers;
                }
                if ( $isOk ) {
                    $data = [
                        'questions' => $questions,
                        'answers' => $_answers,
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Retrieve the maximum available points for the specified quiz
     * @param int $quizID
     * @return int
     */
    public function getQuizPoints( int $quizID ): int
    {
        $sqmDB = new SQM_DB();
        $questions = $sqmDB->getQuestions( $quizID );
        $points = 0;
        if ( !empty( $questions ) ) {
            foreach ( $questions as $question ) {
                $answers = $sqmDB->getAnswers( $question->id );
                if ( !empty( $answers ) ) {
                    foreach ( $answers as $answer ) {
                        if ( $answer->is_correct ) {
                            $points += $answer->points;
                        }
                    }
                }
            }
        }
        return $points;
    }

    public function getQuizUserInfo( int $quizID, int $userID )
    {
        $sqmDB = new SQM_DB();
        return $sqmDB->getQuizUserInfo( $quizID, $userID );
    }

    /**
     * Check to see whether the specified user can take the quiz
     * @param int $quizID
     * @param int $userID
     * @return bool|WP_Error
     */
    public function userCanTakeQuiz( int $quizID, int $userID )
    {
        $sqmDB = new SQM_DB();
        $quiz = $sqmDB->getQuizByID( $quizID );
        if ( !$quiz ) {
            return false;
        }
        $userInfo = $this->getQuizUserInfo( $quizID, $userID );
        if ( $userInfo ) {
            //#! If already taken with success
            if ( $userInfo->success ) {
                return new WP_Error( 'sqm', apply_filters( 'sqm/quiz/user/answered-quiz-success', 'You have already answered successfully to this quiz.' ) );
            }
            //#! If max attempts reached
            // <= because quiz max attempts can be edited
            if ( $quiz->max_attempts <= $userInfo->attempts ) {
                return new WP_Error( 'sqm', apply_filters( 'sqm/quiz/user/max-attempts-reached', 'You have reached the maximum allowed attempts.' ) );
            }
        }
        return true;
    }
}
