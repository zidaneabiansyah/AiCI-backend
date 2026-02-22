<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\PageContent;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    // ============================================
    // TESTIMONIALS
    // ============================================
    
    public function testimonials()
    {
        $testimonials = Testimonial::select('id', 'name', 'role', 'quote', 'photo', 'order')
            ->orderBy('order')
            ->get();
        
        return response()->json([
            'success' => true,
            'results' => $testimonials,
        ]);
    }
    
    public function storeTestimonial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'quote' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('testimonials', 'public');
        }
        
        $testimonial = Testimonial::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ], 201);
    }
    
    public function updateTestimonial(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|max:255',
            'quote' => 'sometimes|string',
            'photo' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('photo')) {
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $data['photo'] = $request->file('photo')->store('testimonials', 'public');
        }
        
        $testimonial->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ]);
    }
    
    public function deleteTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }
        
        $testimonial->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted',
        ]);
    }
    
    public function reorderTestimonials(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            Testimonial::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
    
    // ============================================
    // PARTNERS
    // ============================================
    
    public function partners()
    {
        $partners = Partner::select('id', 'name', 'logo', 'website_url', 'order')
            ->orderBy('order')
            ->get();
        
        return response()->json([
            'success' => true,
            'results' => $partners,
        ]);
    }
    
    public function storePartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'required|image|max:2048',
            'website_url' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }
        
        $partner = Partner::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $partner,
        ], 201);
    }
    
    public function updatePartner(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'website_url' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('logo')) {
            if ($partner->logo) {
                Storage::disk('public')->delete($partner->logo);
            }
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }
        
        $partner->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $partner,
        ]);
    }
    
    public function deletePartner($id)
    {
        $partner = Partner::findOrFail($id);
        
        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
        }
        
        $partner->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Partner deleted',
        ]);
    }
    
    public function reorderPartners(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            Partner::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
    
    // ============================================
    // SITE SETTINGS
    // ============================================
    
    public function settings()
    {
        // Cache settings for 24 hours to avoid repeated queries
        $settings = \Illuminate\Support\Facades\Cache::remember(
            'site_settings',
            \Carbon\Carbon::now()->addDay(),
            function () {
                $settings = SiteSetting::first();
                
                if (!$settings) {
                    $settings = SiteSetting::create([
                        'site_name' => 'AiCI',
                    ]);
                }
                
                return $settings;
            }
        );
        
        return response()->json($settings);
    }
    
    public function updateSettings(Request $request)
    {
        $settings = SiteSetting::first();
        
        if (!$settings) {
            $settings = SiteSetting::create([]);
        }
        
        $validator = Validator::make($request->all(), [
            'site_name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $settings->update($validator->validated());
        
        // Clear cache after update
        \Illuminate\Support\Facades\Cache::forget('site_settings');
        
        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
    
    // ============================================
    // TEAM MEMBERS
    // ============================================
    
    public function team(Request $request)
    {
        $query = TeamMember::select('id', 'name', 'position', 'role_type', 'photo', 'order');
        
        if ($request->has('role_type')) {
            $query->where('role_type', $request->role_type);
        }
        
        $team = $query->orderBy('order')->get();
        
        return response()->json([
            'success' => true,
            'results' => $team,
        ]);
    }
    
    public function storeTeamMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'role_type' => 'required|in:OPERASIONAL,TUTOR',
            'photo' => 'required|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }
        
        $member = TeamMember::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $member,
        ], 201);
    }
    
    public function updateTeamMember(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'role_type' => 'sometimes|in:OPERASIONAL,TUTOR',
            'photo' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }
        
        $member->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }
    
    public function deleteTeamMember($id)
    {
        $member = TeamMember::findOrFail($id);
        
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        
        $member->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Team member deleted',
        ]);
    }
    
    public function reorderTeamMembers(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            TeamMember::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order updated',
        ]);
    }
    
    // ============================================
    // PAGE CONTENT
    // ============================================
    
    public function pageContent(Request $request)
    {
        $query = PageContent::select('key', 'title', 'content', 'image', 'updated_at');
        
        if ($request->has('key')) {
            $content = $query->where('key', $request->key)->first();
            return response()->json($content ?: []);
        }
        
        // Add pagination for listing all pages
        $perPage = min($request->integer('per_page', 20), 50);
        $contents = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $contents->items(),
            'pagination' => [
                'total' => $contents->total(),
                'per_page' => $contents->perPage(),
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
            ],
        ]);
    }
    
    public function storePageContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|unique:page_contents,key',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pages', 'public');
        }
        
        $content = PageContent::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $content,
        ], 201);
    }
    
    public function updatePageContent(Request $request, $key)
    {
        $content = PageContent::where('key', $key)->firstOrFail();
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image' => 'nullable|image|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $validator->validated();
        
        if ($request->hasFile('image')) {
            if ($content->image) {
                Storage::disk('public')->delete($content->image);
            }
            $data['image'] = $request->file('image')->store('pages', 'public');
        }
        
        $content->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }
    
    // ============================================
    // CONTACT
    // ============================================
    
    public function sendContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $contact = ContactMessage::create($validator->validated());
        
        // TODO: Send email notification to admin
        
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $contact,
        ], 201);
    }
    
    public function contactMessages(Request $request)
    {
        $query = ContactMessage::query();
        
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }
        
        $messages = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($messages);
    }
    
    public function markContactAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }
}
