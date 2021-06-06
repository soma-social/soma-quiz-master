<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

class SQM_Notices
{
    const TRANS_NAME = 'sqm_admin_notice';
    /**
     * The number of seconds before the transient is deleted. Set "0" to be persistent
     * @var int
     */
    const TRANS_EXPIRE = 3;

    public static function create( string $class, string $content )
    {
        set_transient( self::TRANS_NAME, [
            'class' => $class,
            'text' => $content,
        ], self::TRANS_EXPIRE );
    }

    public static function show()
    {
        $notice = get_transient( self::TRANS_NAME );
        if ( !empty( $notice ) ) {
            $content = $notice[ 'text' ];
            if ( false === stripos( $content, '<p>' ) ) {
                $content = '<p>' . $content . '</p>';
            }
            echo '<div class="alert alert-' . esc_attr( $notice[ 'class' ] ) . '">' . $content . '</div>';
        }
    }

}
