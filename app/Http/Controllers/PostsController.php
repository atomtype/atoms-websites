<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class PostsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $posts = DB::select('SELECT * FROM posts');
        // $posts = Post::all();
        $posts = post::orderBy('created_at', 'desc')->paginate(10);
        // $posts = Post::orderBy('title', 'desc')->take(2)->get();
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

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
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        // Handle file upload

        if ($request->hasFile('cover_image')) {
            // Get filename with the extension
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            // dd($fileNameWithExt);exit;
            // Get just Filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME); // PHP function pathinfo

            // Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();

            // Filename to store
            $fileNameToStore = $filename . "_" . time() . "." . $extension;

            // Upload the image
            $path = $request->file('cover_image')->storeAs('public/coverimages', $fileNameToStore);
            
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        // Create Post
        $post = new Post;
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id;
        $post->cover_image = $fileNameToStore;
        $post->save();

        return redirect('/posts')->with('success', 'Post Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);

        // Check for correct user
        if( auth()->user()->id !== $post->user_id ) {
            return redirect('/posts')->with('error', 'Unauthorized page');
        }

        return view('posts.edit')->with('post', $post);
    }

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
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        // Handle Image Uplaod
        if ( $request->hasFile('cover_image') ) {
            // Get filename with extension
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            // Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            // Filename to Store
            $fileNameToStore = $filename . "_" . time() . "." . $extension;
            // Upload Image
            $path = $request->file('cover_image')->storeAs('public/coverimages', $fileNameToStore);
        }

        $post = Post::find($id);
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        if( $request->hasFile('cover_image') ) {
            $post->cover_image = $fileNameToStore;
        }
        $post->save();

        return redirect('/posts')->with('success', 'Post Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        // Check if correct user is deleting post
        if ( auth()->user()->id !== $post->user_id ) {
            return redirect('/posts')->with('error', 'Unauthorized page.');
        }

        if ( $post->cover_image != "noimage.jpg" ) {
            // Delete Image from Storage
            Storage::delete('public/coverimages/'.$post->cover_image);
        }

        $post->delete();

        return redirect('/posts')->with('success', 'Post Deleted Successfully.');
    }
}
