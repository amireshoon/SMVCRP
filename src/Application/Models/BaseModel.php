<?php

namespace App\Application\Models;

use \RedBeanPHP\R as R;

abstract class BaseModel {

    /** @var string model name */
    protected $name;

    /** @var object model instance */
    protected $model = null;

    protected $payload = null;

    /**
     * Initialize model
     * 
     * @param string $name
     * @return BaseModel
     * @since   1.0.0
     */
    public function __construct( $model, $target_id = null ) {
        $this->name = $model;
        $this->model = R::dispense( $model );

        $this->payload = new \stdClass();

        // if target id is given, load model
        if ( !is_null( $target_id ) ) {
            $this->target = $target_id;
            $this->model = R::load( $model, $target_id );
            $this->payload = $this->model;
        }

        return $this;
    }

    public function afterUpdate() {

    }

    public function afterDelete() {

    }

    public function save() {
        foreach ($this->payload as $key => $value) {
            $this->model->$key = $value;
        }

        // Reload model
        $row_id = R::store( $this->model );

        $this->model = $this->loadRow( $row_id );
        if ( is_null( $this->model ) )
            return false;
        
        $payload = new \stdClass();

        foreach ($this->model as $key => $value) {
            $payload->$key = $value;
        }

        $this->target = $payload->id;
        $this->payload = $payload;

        return $row_id;
    }

    public function remove() {
        return R::trash( $this->model );
    }

    public function loadRow( $row_id ) {
        $this->model = R::load( $this->name, $row_id );
        return $this->model;
    }

    public function where( $field, $value ) {
        return R::find( $this->name, $field . ' = ?', [ $value ] );
    }

    public function __set( $key, $value ) {
        $this->payload->$key = $value;
    }

    public function __get( $key ) {
        return isset( $this->payload->$key ) ? $this->payload->$key : null;
    }

    public function to_json() {
        return json_encode( $this->payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    }

    public function payload() {
        return $this->payload;
    }

    /**
     * Check if model data exists or not
     * 
     * @since   1.0.0
     * @return  bool
     */
    public function exists() {
        return $this->model->id !== 0;
    }
}