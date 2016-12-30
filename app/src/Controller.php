<?php

namespace App;

use App\Entity
  , Silex\Application
  , App\Entities\User
  , App\Entities\Group
  , App\Entities\Member
  , Symfony\Component\HttpFoundation\Request
  , App\Exceptions\NotFound as NotFoundException
  , Symfony\Component\HttpFoundation\JsonResponse;

class Controller
{
    protected $data = [];
    protected $code = 200;
    protected $message = "";
    protected $messages = [];
    protected $status = SUCCESS;

    public function ping()
    {
        return $this->respond( SUCCESS, "Pong" );
    }

    public function error()
    {
        throw new NotFoundException;
    }

    public function login( Request $request )
    {
        // Verify the email and password. Create a new session
        // and save the cookie.
        return $this->respond( SUCCESS, "You have logged in" );
    }

    public function logout()
    {

    }

    /**
     * Records user info and redirects them to success message.
     */
    public function signup( Request $request )
    {
    }

    public function dashboard()
    {
        // @TODO AUTH, STUBBED
        // THIS SHOULD USE A USER OBJECT IN THE SESSION
        $user = new User( 1 );

        $this->data[ 'user' ] = $user;
        $this->data[ 'groups' ] = $user->getGroups();

        return $this->respond( SUCCESS );
    }

    /**
     * Prepare all of the dashboard data for a group.
     */
    public function group( $name, $year = "" )
    {
        // @TODO AUTH, STUBBED
        // THIS SHOULD USE A USER OBJECT IN THE SESSION
        $user = new User( 1 );

        $year = ( $year ) ?: date( "Y" );
        $group = Group::loadByName( $name );

        if ( ! $group->exists() ) {
            throw new NotFoundException( GROUP, $name );
        }

        // Prepare all of the statistics and return them
        $this->data[ 'year' ] = $year;
        $this->data[ 'user' ] = $user;
        $this->data[ 'group' ] = $group;
        $this->data[ 'groups' ] = $user->getGroups();
        $this->data[ 'members' ] = $group->getMembers( $year );
        $this->data[ 'emissions' ] = $group->getEmissions( $year );
        $this->data[ 'offset_amount' ] = $group->getOffsetAmount( $year );

        return $this->respond( SUCCESS );
    }

    /**
     * Saves a group member.
     */
    public function saveMember( $name, $year, Request $request )
    {
        $post = $request->request->all();
        $id = get( $post, 'id', NULL );
        $group = Group::loadByName( $name );
        $months = get( $post, 'locale_months', 12 );
        $params = [
            'name' => get( $post, 'name' ),
            'email' => get( $post, 'email' ),
            'locale' => get( $post, 'locale' ),
            'is_admin' => ( get( $post, 'is_admin' ) == 1 ) ? 1 : 0,
            'locale_percent' => round( intval( $months ) / 12 * 100 ),
            'is_champion' => ( get( $post, 'is_champion' ) == 1 ) ? 1 : 0
        ];

        if ( ! $group->exists() ) {
            throw new NotFoundException( GROUP, $name );
        }

        $member = new Member( $id ?: NULL );
        $member->save( $params );

        $this->data[ 'member' ] = new Member(
            $member->id, [
                Entity::POPULATE_SQL => FALSE,
                Member::POPULATE_FULL => TRUE
            ]);
        $this->messages[] = [
            'type' => SUCCESS,
            'message' => "New account successfully saved."
        ];

        return $this->group( $name, $year );
    }

    /**
     * Master response method. This sends back a response in the
     * same format to the client.
     * @param const $status
     * @param string|array $message
     * @param int $code
     * @return JsonResponse
     */
    protected function respond( $status = NULL, $message = NULL, $code = NULL )
    {
        if ( ! is_null( $status ) ) {
            $this->status = $status;
        }

        if ( ! is_null( $code ) ) {
            $this->code = $code;
        }

        if ( ! is_null( $message ) ) {
            if ( is_array( $message ) ) {
                $this->messages = $message;
            }
            else {
                $this->message = $message;
                $this->messages = [[
                    'type' => $status,
                    'message' => $message
                ]];
            }
        }

        return new JsonResponse([
            'code' => $this->code,
            'data' => $this->data,
            'status' => $this->status,
            'message' => $this->message,
            'messages' => $this->messages
        ]);
    }
}