<?php

/**
 * Class SQM_State
 *
 * Utility class to store state (for 5 seconds) between requests
 */
class SQM_State
{
    const TRANS_NAME = 'sqm_state_storage';
    const TRANS_EXPIRE = 5;

    private $data = [];
    private static $instance = null;

    private function __construct()
    {
        $this->getData();
    }

    public static function getInstance(): ?SQM_State
    {
        if ( !self::$instance || !( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function add( $name, $value ): SQM_State
    {
        $this->data[ $name ] = $value;
        return $this;
    }

    public function get( $name, $defaultValue = '' )
    {
        if ( empty( $this->data ) ) {
            $this->getData();
        }
        return ( $this->data[ $name ] ?? $defaultValue );
    }

    public function save()
    {
        set_transient( self::TRANS_NAME, $this->data, self::TRANS_EXPIRE );
    }

    public function getData()
    {
        $data = get_transient( self::TRANS_NAME );
        if ( !empty( $data ) && is_array( $data ) ) {
            $this->data = $data;
        }
        return $this->data;
    }
}
