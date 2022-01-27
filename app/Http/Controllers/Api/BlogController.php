<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use File;

class BlogController extends Controller
{
    public function create(request $request){
        $validator = Validator::make($request->all(), [
            'title'=>'required|max:250',
            'short_description'=>'required',
            'long_decsription'=>'required',
            'category_id'=>'required',
            'image'=>'required|image|mimes:jpg,bmp,png'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>'Validation errors',
                'errors'=>$validator->messages()
            ], 422);
        }

        $image_name=time().'.'.$request->image->extension();
        $request->image->move(public_path('/uploads/blog_images'),$image_name);

        $blog=Blog::create([
            'title'=>$request->title,
            'short_description'=>$request->short_description,
            'long_description'=>$request->long_description,
            'user_id'=>$request->user()->id,
            'category-id'=>$request->category_id,
            'image'=>$image_name
        ]);

        $blog->load('user:id,name,email','category:id,name');

        return response()->json([
            'message'=>'Blog Successfully Created',
            'data'=>$blog
        ], 200);
    }

    public function list(Request $request){
        $blog_query=Blog::with(['user','category']);

        if($request->keyword){
            $blog_query->where('title','LIKE','%'.$request->keyword.'%');
        }

        if($request->category){
            $blog_query->whereHas('category',function($query) use($request){
                $query->where('slug', $request->category);
            });
        }

        if($request->user_id){
            $blog_query->where('user_id',$request->user_id);
        }

        $blogs=$blog_query->get();
        return response()->json([
            'message'=>'Blog Successfully Fetched',
            'data'=>$blogs
        ], 200);
    }

    public function details($id){
        $blog=Blog::with(['user', 'category'])->where('id','$id')->first();
        if($blog){
            return response()->json([
                'message'=>'Blog Successfully Fetched',
                'data'=>$blog
            ], 200);
        }else{
            return response()->json([
                'message'=>'No Blog Found',
            ], 400);
        }
    }

    public function update($id, Request $request){
        $blog=Blog::with(['user', 'category'])->where('id',$id)->first();
        if($blog){
            if($blog->user_id==$request->user()->id){

                $validator = Validator::make($request->all(), [
                    'title'=>'required|max:250',
                    'short_description'=>'required',
                    'long_decsription'=>'required',
                    'category_id'=>'required',
                    'image'=>'nullable|image|mimes:jpg,bmp,png'
                ]);
        
                if ($validator->fails()) {
                    return response()->json([
                        'message'=>'Validation errors',
                        'errors'=>$validator->messages()
                    ], 422);
                }

                if($request->hasFile('image')){

                    $image_name=time().'.'.$request->image->extension();
                    $request->image->move(public_path('/uploads/blog_images'),$image_name);
                    $old_path=public_path().'uploads/blog_images/'.$blog->image;

                    if(File::exists($old_path)){
                        File::delete($old_path);
                    }
                }else{
                    $image_name=$blog->iamge;
                }

                $blog->update([
                    'title'=>$request->title,
                    'short_description'=>$request->short_description,
                    'long_description'=>$request->long_description,
                    'category-id'=>$request->category_id,
                    'image'=>$image_name
                ]);
        
                
        
                return response()->json([
                    'message'=>'Blog Successfully Updated',
                    'data'=>$blog
                ], 200);


            }else{
                return response()->json([
                    'message'=>'Access Denied',
                ], 403);
            }

        }else{
            return response()->json([
                'message'=>'No Blog Found',
            ], 400);
        }
    }

    public function delete($id,Request $request){
        $blog=Blog::where('id','$id')->first();
        if($blog){
            if($blog->user_id==$request->user()->id){

                $old_path=public_path().'uploads/blog_images/'.$blog->image;

                    if(File::exists($old_path)){
                        File::delete($old_path);
                    }

                    $blog->delete();

                    return response()->json([
                        'message'=>'Blog Successfully Deleted'
                    ], 200);


            }else{
                return response()->json([
                    'message'=>'Access Denied',
                ], 403);
            }
        }else{
            return response()->json([
                'message'=>'No Blog Found',
            ], 400);
        }
    }

}
