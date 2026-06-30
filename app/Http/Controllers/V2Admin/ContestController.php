<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;
use App\Models\Contest;

class ContestController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $query = Contest::query();

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $data = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Contest list',
            'data' => $data
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'banner' => 'required|image',
            'thumbnail' => 'nullable|image',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // UPLOADS
        $bannerPath = $this->upload($request, 'banner');
        $thumbPath = $this->upload($request, 'thumbnail');

        $contest = Contest::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'short_description' => $request->short_description,
            'description' => $request->description,
            'banner' => $bannerPath,
            'thumbnail' => $thumbPath,
            'prize' => $request->prize,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'result_date' => $request->result_date,
            'status' => $request->status ?? 'open',
            'max_entries_per_user' => $request->max_entries_per_user ?? 1,
            'allowed_media' => $request->allowed_media ?? 'image',
            'views' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contest created',
            'data' => $contest
        ]);
    }

    // SHOW
    public function show($id)
    {
        $data = Contest::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $contest = Contest::findOrFail($id);

        $data = $request->only([
            'title','short_description','description',
            'prize','start_date','end_date','result_date',
            'status','max_entries_per_user','allowed_media'
        ]);

        if ($request->hasFile('banner')) {
            $data['banner'] = $this->upload($request, 'banner');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->upload($request, 'thumbnail');
        }

        $contest->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Contest updated'
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Contest::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contest deleted'
        ]);
    }

    // FILE UPLOAD HELPER
    private function upload($request, $field)
    {
        if (!$request->hasFile($field)) return null;

        $folder = public_path('uploads/contest');

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $name = time().'_'.$request->file($field)->getClientOriginalName();
        $request->file($field)->move($folder, $name);

        return 'uploads/contest/'.$name;
    }
}