<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }
    public function index()
    {
        $meetings = Meeting::all();
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href'=> 'api/v1/meeting/'. $meeting->id,
                'method'=> 'GET'

            ];
        }
        $response = [
            'msg' => 'List of all meetings',
            'meetings' => $meetings

        ];
        return response()->json($response, 201); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'description'=> 'required',
            'time'=> 'required',
            'user_id'=> 'required'

        ]);
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = new Meeting([
            'title'=> $title,
            'description'=> $description,
            'time'=> $time
        ]);

        if ($meeting->save()) {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href'=> 'api/v1/meeting/' . $meeting->id,
                'method'=> 'GET',

            ];

            $message = [
                'msg'=> 'Meeting Created',
                'meeting'=> $meeting
            ];

            return response()->json($message, 201);
        }
        $response = [
            'msg'=> 'An Error Occurated',
        ];


        return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting',
            'method'=> 'GET'
        ];

        $response = [
            'msg' => 'Meeting Information',
            'meeting' => $meeting
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'description'=> 'required',
            'time'=> 'required',
            'user_id'=> 'required'

        ]);
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = Meeting::with('users')->findOrFail($id);
        if (!$meeting->users()->where('users.id', $user_id)->first()) {
            return response()->json(['msg' => 'user not registered for meeting, update not succesful', 401]);
        }

        $meeting->time = $time;
        $meeting->title = $title;
        $meeting->description = $description;
    

        if (!$meeting->update()) {
            return response()->json([
                'msg' => 'Error During Update'
            ], 400);
        }

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting Updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $meeting = Meeting::with('users')->findOrFail($id);
       $users = $meeting->users;
       $meeting->users()->detach();

       if (!$meeting->delete()) {
           foreach ($users as $user) {
               $meeting->users()->attach($user);
           }

           return response()->json([
                'msg' => 'Deleted Failed'
           ], 404);
       }

       $response = [
            'msg' => 'Meeting Deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'

            ]

       ];

       return response()->json($response, 200);
    }
}
