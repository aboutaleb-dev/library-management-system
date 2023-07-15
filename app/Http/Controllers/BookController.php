<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::all();

        return response($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'string', 'max:' . env('ADMIN_ADDING_STRING_FILEDS_MAX_LENGHT'), 'unique:books,name'],
            'writer' => ['required', 'string', 'max:' . env('ADMIN_ADDING_STRING_FILEDS_MAX_LENGHT')],
            'image' => ['required', 'image', 'max:' . (env('BOOK_IMAGE_MAX_SIZE') * 1024)],
            'stock' => ['required', 'numeric', 'max:' . env('BOOK_MAX_STOCK')],
        ]);

        $path = Storage::putFile(env('BOOK_IMAGE_ROUTE'), $fields['image']);

        $fields['image'] = $path;

        Book::create($fields);

        return response([
            'Message' => 'Book added successfully!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::find($id);

        if(!$book) {
            return response()->notFound('Book');
        }

        return response($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fields = $request->validate([
            'name' => ['nullable', 'string'],
            'writer' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:' . (env('BOOK_IMAGE_MAX_SIZE') * 1024)],
            'stock' => ['nullable', 'numeric', 'max:' . env('BOOK_MAX_STOCK')],
        ]);

        $book = Book::find($id);

        if(!$book) {
            return response()->notFound('Book');
        }

        //Check image uploaded correctly or not
        if($request->hasFile('image')) {
            if(!$request->file('image')->isValid()) {
                return response([
                    'message' => 'Image wasn\'t uploaded correctly.'
                ], 422);
            }

            Storage::delete($book->image);

            $path = Storage::putFile(env('BOOK_IMAGE_ROUTE'), $fields['image']);

            $fields['image'] = $path;
        }

        //Creating array for update method
        foreach($fields as $key => $value) {
            if($value) {
                $book->$key = $value;
            }
        }

        $book->save();

        return response([
            'message' => 'Book updated successfully!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);

        if(!$book) {
            return response()->notFound('Book');
        }

        $book->delete();

        return response([
            'message' => 'Book deleted successfully!',
        ]);
    }
}
