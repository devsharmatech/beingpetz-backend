<?php 

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('admin')->latest()->paginate(10);
        return response()->json([
            'status' => true,
            'data'   => $events
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id'    => 'required|integer|exists:users,id',
            'title'       => 'required|string|max:255',
            'description' => 'required',
            'event_date'  => 'required|date',
            'location'    => 'nullable|string|max:255',
            'image'       => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message'=> $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $this->generateUniqueSlug($request->title);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/events');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = time() . '-' . Str::slug(pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->image->getClientOriginalExtension();
            $request->image->move($uploadPath, $fileName);

            $imagePath = 'uploads/events/' . $fileName;
        }

        $event = Event::create([
            'admin_id'    => $request->admin_id,
            'title'       => $request->title,
            'slug'        => $slug,
            'description' => $request->description,
            'event_date'  => $request->event_date,
            'location'    => $request->location ?? null,
            'image'       => $imagePath,
        ]);

        return response()->json([
            'status' => true,
            'data'   => $event
        ], 201);
    }

    public function show(Event $event)
    {
        return response()->json([
            'status' => true,
            'data'   => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes',
            'event_date'  => 'sometimes|date',
            'location'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message'=> $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $request->title 
            ? $this->generateUniqueSlug($request->title, $event->id) 
            : $event->slug;

        $imagePath = $event->image;
        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/events');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = time() . '-' . Str::slug(pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->image->getClientOriginalExtension();
            $request->image->move($uploadPath, $fileName);

            $imagePath = 'uploads/events/' . $fileName;
        }

        $event->update([
            'title'       => $request->title ?? $event->title,
            'slug'        => $slug,
            'description' => $request->description ?? $event->description,
            'event_date'  => $request->event_date ?? $event->event_date,
            'location'    => $request->location ?? $event->location,
            'image'       => $imagePath,
        ]);

        return response()->json([
            'status' => true,
            'data'   => $event
        ]);
    }

    public function destroy(Event $event)
    {
        if ($event->image && file_exists(public_path($event->image))) {
            unlink(public_path($event->image));
        }

        $event->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    private function generateUniqueSlug($title, $ignoreId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Event::where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }




    // admin Dashboard functions

    public function event_list()
    {
        $events = Event::with('category')->latest()->get();
        return view('admin.events.index', compact('events'));
    }

    public function event_create()
    {
        $categories = Category::where('type', 'event')->get();
        return view('admin.events.create', compact('categories'));
    }

    public function event_store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'event_date'    => 'required|date',
            'location'      => 'nullable|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif,gif|max:2048',
            'slug'          => 'nullable|string|unique:events,slug',
        ]);

        $eventData = $request->only([
            'title',
            'description',
            'event_date',
            'location',
            'category_id',
            'slug'
        ]);

        // ✅ Set admin ID
        $eventData['admin_id'] = auth()->id();

        // ✅ Generate slug automatically if not provided
        $eventData['slug'] = $request->filled('slug')
            ? \Str::slug($request->slug)
            : \Str::slug($request->title);

        // ✅ Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/events'), $imageName);
            $eventData['image'] = 'uploads/events/' . $imageName;
        }

        // ✅ Create event record
        Event::create($eventData);

        // ✅ Redirect with success message
        return redirect()->route('admin.events.list')
            ->with('success', 'Event created successfully.');
    }


    public function event_edit(Event $event)
    {
        $categories = Category::where('type', 'event')->get();
        return view('admin.events.edit', compact('event', 'categories'));
    }

   public function event_update(Request $request, Event $event)
    {
        // 🔹 Validation rules
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        ]);

        // 🔹 Prepare event data
        $eventData = $request->only([
            'title',
            'description',
            'event_date',
            'location',
            'category_id',
        ]);

        // Generate slug from title
        $eventData['slug'] = \Str::slug($request->title);

        // 🔹 Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (!empty($event->image) && \Storage::disk('public')->exists($event->image)) {
                \Storage::disk('public')->delete($event->image);
            }

            // Store new image
            $eventData['image'] = $request->file('image')->store('events', 'public');
        } else {
            // Keep old image if not updated
            $eventData['image'] = $event->image;
        }

        // 🔹 Update event
        $event->update($eventData);

        // 🔹 Redirect with success message
        return redirect()->route('admin.events.list')
            ->with('success', 'Event updated successfully.');
    }


    public function event_delete(Event $event)
    {
        if (!empty($event->image) && \Storage::disk('public')->exists($event->image)) {
            \Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('admin.events.list')
            ->with('success', 'Event deleted successfully.');
    }


}
