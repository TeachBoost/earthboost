<?php

/**
 * Base Entity class used as parent for other entities.
 * All entities in the application extend this class to
 * gain access to those services since they're used
 * everywhere.
 */
namespace App;

use App\Exception
  , App\Exceptions\Validation as ValidationException;

abstract class Entity
{
    protected $_modelClass;

    const POPULATE_SQL = 'populate_sql';

    public function __construct( $id = NULL, $options = [] )
    {
        if ( is_null( $id ) ) {
            return;
        }

        if ( is_array( $id ) ) {
            $this->populateArray( $id );
            return;
        }

        $this->id = (int) $id;

        if ( $this->_modelClass
            && get( $options, self::POPULATE_SQL, TRUE ) === TRUE )
        {
            $model = "App\Models\\". $this->_modelClass;
            $this->populate( new $model, (int) $id );
        }
    }

    /**
     * Populate model data onto an entity object.
     * @param $model Model object to call getById()
     * @param $id ID of the record to get
     */
    public function populate( Model $model, $id )
    {
        if ( ! valid( $id, INT ) ) {
            throw new ValidationException( "Missing ID in Entity:populate" );
        }

        $data = $model->getById( $id );

        if ( $data ) {
            $this->populateArray( $data );
        }
    }

    /**
     * Attach array keys and and values onto the entity as
     * properties and values.
     */
    public function populateArray( $data )
    {
        $keyMap = ( isset( $this->keyMap ) )
            ? $this->keyMap
            : [];

        foreach ( $data as $key => $value ) {
            if ( $keyMap ) {
                if ( isset( $keyMap[ $key ] ) ) {
                    $key = $keyMap[ $key ];
                }
                // Skip if the key is in the replacements, i.e.
                // to-be-replaced
                elseif ( in_array( $key, $keyMap ) ) {
                    continue;
                }
            }

            if ( ! property_exists( $this, $key ) ) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    public function exists()
    {
        return isset( $this->id )
            && valid( $this->id, INT );
    }

    public function toArray()
    {
        return get_public_vars( $this );
    }

    /**
     * Saves the data to the entity in SQL.
     * @param array $data
     */
    public function save( array $data )
    {
        if ( !  $this->_modelClass ) {
            throw new Exception( "Missing model class in Entity save()" );
        }

        $modelClass = "App\Models\\". $this->_modelClass;
        $model = new $modelClass([
            'id' => $this->id
        ]);
        $data = array_map( 'trim', $data );
        $sqlObject = $model->save( $data );

        if ( $sqlObject ) {
            $this->populateArray( $sqlObject );
        }
    }
}