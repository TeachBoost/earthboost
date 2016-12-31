<?php

namespace App\Libraries;

use App\Exceptions\Validation as ValidationException;

/**
 * Assertion library for checking if an object contains
 * the expected properties and types.
 */
class Expects
{
    private $data;

    public function __construct( $data )
    {
        if ( is_object( $data ) ) {
            $data = (array) $data;
        }

        if ( ! is_array( $data ) ) {
            throw new ValidationException( "Expects requires an array" );
        }

        $this->data = $data;
    }

    /**
     * Checks if the array contains the given keys.
     * @param Array $keys
     * @throws ValidationException
     */
    function toHave( Array $keys )
    {
        $intersection = array_intersect_key(
            array_flip( $keys ),
            $this->data );

        if ( count( $intersection ) < count( $keys ) ) {
            $missing = array_diff( $keys, array_flip( $this->data ) );
            $keys = implode( ", ", $keys );
            $missing = implode( ", ", $missing );

            throw new ValidationException(
                "toHave() expects argument to contain keys '$keys' but ".
                "was missing '$missing'" );
        }

        return $this;
    }
}