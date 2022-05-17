<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Poll;
use App\Models\Like;
use App\Models\Option;
use App\Models\UserOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{

    public function list($count,$user_id)
    {
        if($user_id==0){
            $polls = Poll::withCount('comments', 'likes')->with('comments', 'options')->limit($count)->get();
        }
        else{
            $polls = Poll::withCount('comments', 'likes')->with('comments', 'options')->where('user_id',$user_id)->limit($count)->get();
        }
        return response()->json($polls);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'audience' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'user_id' => 'required',
            'participation' => 'required',
            'vote_question' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();

            return response()->json([
                'status' => 'Error',
                'message' => $messages[0],
            ], 200);
        }

        if(isset($request->id)){
            $polls = Poll::find($request->id);
        }
        else{
            $polls = new Poll;
        }

        $polls->title = $request->title;
        $polls->description = $request->description;
        $polls->address = $request->address;
        $polls->latitude = $request->latitude;
        $polls->longitude = $request->longitude;
        $polls->audience = $request->audience;
        $polls->start_date = $request->start_date;
        $polls->end_date = $request->end_date;
        $polls->start_time = $request->start_time;
        $polls->end_time = $request->end_time;
        $polls->participation = $request->participation;
        $polls->vote_question = $request->vote_question;
        $polls->user_id = $request->user_id;
        if ($polls->save()) {
            if(!isset($request->id)){
                foreach ($request->vote_option as $key => $vote_option) {
                    $option = new Option;
                    $option->parent_id = $polls->id;
                    $option->vote_option = $vote_option;
                    $option->vote_description = $request->vote_description[$key];
                    $option->save();
                }
            }
        }

        return response()->json($polls);
    }

    public function find($id)
    {
        $polls = Poll::with('comments', 'options')->withCount('comments', 'likes')->find($id);
        return response()->json($polls);
    }

    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'comment' => 'required',
            'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();

            return response()->json([
                'status' => 'Error',
                'message' => $messages[0],
            ], 200);
        }

        $comment = new Comment;
        $comment->user_id = $request->user_id;
        $comment->parent_id = $request->parent_id;
        $comment->comment = $request->comment;
        $comment->save();

        return response()->json($comment);
    }

    public function like(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();

            return response()->json([
                'status' => 'Error',
                'message' => $messages[0],
            ], 200);
        }

        $liked=Like::where(['user_id'=>$request->user_id,'parent_id'=>$request->parent_id])->first();
        
        if(!is_null($liked)){
            $liked->delete();
            $likes = Like::where(['parent_id' => $request->parent_id])->count();

            return response()->json($likes);
        }
        
        $like = new Like;
        $like->user_id = $request->user_id;
        $like->parent_id = $request->parent_id;
        $like->save();

        $likes = Like::where(['parent_id' => $request->parent_id])->count();

        return response()->json($likes);
    }

    public function user_option(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'parent_id' => 'required',
            'option_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();

            return response()->json([
                'status' => 'Error',
                'message' => $messages[0],
            ], 200);
        }

        $user_option = new UserOption;
        $user_option->user_id = $request->user_id;
        $user_option->parent_id = $request->parent_id;
        $user_option->option_id = $request->option_id;
        $user_option->save();

        $option_array=array();
        $option=Option::where(['parent_id'=>$request->parent_id])->get();
        foreach($option as $item){
            $option_array[$item->id]=count(UserOption::where(['option_id'=>$item->id])->get());
        }

        return response()->json($option_array);
    }

    public function delete($id)
    {
        $polls = Poll::with('comments', 'options','likes','user_option')->find($id);
        if(!is_null($polls)){
            $polls->comments()->delete();
            $polls->user_option()->delete();
            $polls->likes()->delete();
            $polls->options()->delete();
            $polls->delete();
        }
        return response()->json(['message'=>'Deleted']);
    }
}