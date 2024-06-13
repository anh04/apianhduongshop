<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Library;
use Illuminate\Support\Facades\File as File2;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    /************************************/
    public function libraries()
    {
        $files_temp =  Storage::disk('local')->files('images');
        $files = array_map(function($file){
            return basename($file); // remove the folder name
        }, $files_temp);
        //print_r($files);die();
        //$files = Library::all()->toArray();
        //print_r($files);die();
        return response()->json(['files' => $files], 200);
    }
    /***********************************/
    public function add_image_library(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required',
            'file.*' => 'required'
        ]);

        $files = [];
        $filenames =array();
        if($request->hasfile('file'))
        {
            foreach($request->file('file') as $file)
            {
                $name = rand(1,100).'.'.$file->getClientOriginalName();
                $file->storeAs('images', $name);
                $filenames['filenames'] = $name;
                $files[]=$filenames;
            }
        }
        // print_r($files); die();

        Library::insert($files);

        return response()->json(['files' => $files], 200);
    }
}
