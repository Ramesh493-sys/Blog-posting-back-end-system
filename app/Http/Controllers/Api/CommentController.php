<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Validator; 
use App\Models\Comment;  

class CommentController extends Controller
{

    public function list($blog_id, Request $request){
        $blog=Blog::where('id',$blog_id)->first();
        if($blog){

            $comments=Comment::with(['user'])->where('blog_id',$blog_id)->get();
            return response()->json([
                'message'=>"Comment successfully Fetched",
                'data'=>$comments
            ],200);

        }else{
            return response()->json([
                'message'=>"No Blog Found",
            ],400);
        }
    }

    public function create($blog_id, Request $request){
        $blog=Blod::where('id',$blog_id)->first();
        if($blog){

            $validator = Validator::make($request->all(), [
                'message'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message'=>'Validation errors',
                    'errors'=>$validator->messages()
                ], 422);
            }

            $comment=Comment::create([
                'message'=>$requst->message,
                'blog_id'=>$blog->id,
                'user_id'=>$request->user()->id
            ]);
            $comment->load('user');
            return response()->json([
                'message'=>'Comment Successfully Created',
                'data'=>$comment
            ],200);

        }else{
            return response()->json([
                'message'=>'No blog Found',
            ],400);
        }
    }

    public function update($commnet_id, Request $request){
        $comment=Commnent::with(['user'])->where('id',$comment_id)->first();

        if( $comment){

            if($comment->user_id==$request->user()->id){

                $validator = Validator::make($request->all(), [
                    'message'=>'required',
                ]);
        
                if ($validator->fails()) {
                    return response()->json([
                        'message'=>'Validation errors',
                        'errors'=>$validator->messages()
                    ], 422);
                }

                $comment->update([
                    'message'=>$request->message
                ]);

                return response()->json([
                    'message'=>'Comment Successfully Updated',
                    'data'=>$comment
                ],200);
    

            }else{
                return response()->json([
                    'message'=>'Access Denied',
                ],403);
            }

        }else{
            return response()->json([
                'message'=>'No Comment Found',
            ],400);
        }
    }

    public function delete($comment_id, Request $request){
        $comment=Comment::where('id', $comment_id)->first();
        
        if($comment){
            if($comment->user_id==$request->user()->id){

                $comment->delete();
                return response()->json([
                    'message'=>'Comment Successfully Deleted',
                    'data'=>$comment
                ],200);

            }else{
                return response()->json([
                    'message'=>'Access Denied',
                ],403);
            }
        }else{
            return response()->json([
                'message'=>'No Comment Found',
            ],400);
        }
    }
}
