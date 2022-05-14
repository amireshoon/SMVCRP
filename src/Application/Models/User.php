<?php

namespace App\Application\Models;

use \RedBeanPHP\R as RedBean;
use stdClass;

class User extends BaseModel {
    
    protected $target = null;
    protected $model_id = 'user';

    public function __construct( $target = null ) {
        parent::__construct( $this->model_id, $target );

        $this->target = $target;
    }

    /**
     * Load user with user id
     * 
     * @param   string  phone number
     * @return  User
     * @since   1.0.0
     */
    public function with_phone( $phone ) {
        $this->model = RedBean::findOne( $this->model_id, 'phone = ?', [ $phone ] );

        if ( is_null( $this->model ) )
            return false;
        
        $payload = new stdClass();

        foreach ($this->model as $key => $value) {
            $payload->$key = $value;
        }

        $this->target = $payload->id;
        $this->payload = $payload;

        return $this;
    }

    public function payload() {
        $modified_payload = $this->payload;
        unset( $modified_payload->sf_password );

        $avatar_slug = $this->payload->avatar;
        if ( !empty( $avatar_slug ) ) {
            $avatar = new Avatar();
            $avatar->with_slug( $avatar_slug );
            $modified_payload->avatar_data = $avatar->payload();
        }

        if ( !isset( $modified_payload->role ) || is_null( $modified_payload->role ) )
            $modified_payload->role = 'user';

        return $modified_payload;
    }

    public function update_last_login( $user_id = null ) {
        if ( is_null( $user_id ) )
            $user_id = $this->target;

        $user = RedBean::load( $this->model_id, $user_id );
        $user->last_login = date( 'Y-m-d H:i:s' );
        RedBean::store( $user );
    }

    public function save() {

        // Removed avatar data because we don't need it on database
        if ( isset( $this->payload->avatar_data ) ) {
            unset( $this->payload->avatar_data );
        }

        return parent::save();
    }

    public function get_all( $query = null, $limit = 50, $offset = 0 ) {
        

        if ( !is_null( $query ) ) {
            $users = RedBean::find( $this->model_id, ' fullname LIKE ? OR nickname LIKE ? OR phone LIKE ? LIMIT ? OFFSET ?', [ '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', $limit, $offset ] );
        }else {
            $users = RedBean::find( $this->model_id, '1 ORDER BY id DESC LIMIT ? OFFSET ?', [ $limit, $offset ] );
        }
        
        $payloads = [];
        foreach ($users as $user) {
            $u = new User( $user->id );
            $payloads[] = $u->payload();
        }

        return $payloads;
    }

    public function count() {
        return RedBean::count( $this->model_id );
    }

    public function removeUser( $user_id = null ) {
        if ( is_null( $user_id ) )
            $user_id = $this->target;

        $user = RedBean::load( $this->model_id, $user_id );
        RedBean::trash( $user );
    }

}
